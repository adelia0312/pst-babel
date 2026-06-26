<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiPetugas;
use App\Models\Petugas;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;

class AdminPenilaianController extends Controller
{
    // ════════════════════════════════════════════════════════
    // DEBUG — Cek kenapa grafik kosong
    // Route: GET /admin/penilaian/debug
    // HAPUS method ini & route-nya setelah selesai debug!
    // ════════════════════════════════════════════════════════
    public function debug(Request $request)
    {
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $wilayahList    = Wilayah::where('status', 'aktif')->orderBy('nama')->get();

        $rekapWilayah = $wilayahList->map(function ($w) use ($periode) {
            $evaluasiList = EvaluasiPetugas::where('wilayah_id', $w->id)
                ->where('periode', $periode)
                ->with('petugas.user')
                ->get();

            $selesai = $evaluasiList->where('status', 'selesai');

            $komponen = [
                'Sikap Kerja'      => $selesai->whereNotNull('rata_sikap_kerja')->avg('rata_sikap_kerja'),
                'Indikator Hasil'  => $selesai->whereNotNull('rata_indikator_hasil')->avg('rata_indikator_hasil'),
                'Indikator Proses' => $selesai->whereNotNull('rata_indikator_proses')->avg('rata_indikator_proses'),
                'Mutu Pelayanan'   => $selesai->whereNotNull('rata_mutu_pelayanan')->avg('rata_mutu_pelayanan'),
            ];

            return [
                'wilayah'       => $w,
                'evaluasi_list' => $evaluasiList,
                'sudah'         => $selesai->count(),
                'komponen'      => $komponen,
            ];
        });

        $grafikKomponenGlobal = [
            'Sikap Kerja' => [], 'Indikator Hasil' => [],
            'Indikator Proses' => [], 'Mutu Pelayanan' => [],
        ];
        foreach ($rekapWilayah as $r) {
            foreach ($grafikKomponenGlobal as $k => &$arr) {
                $arr[] = $r['komponen'][$k] ? round($r['komponen'][$k], 2) : 0;
            }
        }
        unset($arr);

        $wilayahLabels = $rekapWilayah->pluck('wilayah.nama')->values()->toArray();

        return view('admin.penilaian.debug', compact(
            'periode', 'periodeOptions', 'rekapWilayah',
            'grafikKomponenGlobal', 'wilayahLabels'
        ));
    }

    public function index(Request $request)
    {
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();

        $wilayahList = Wilayah::where('status', 'aktif')->orderBy('nama')->get();

        // Rekap per wilayah
        $rekapWilayah = $wilayahList->map(function ($w) use ($periode) {
            $totalPetugas = Petugas::where('wilayah_id', $w->id)->count();

            $evaluasiList = EvaluasiPetugas::where('wilayah_id', $w->id)
                ->where('periode', $periode)
                ->with('petugas.user')
                ->get();

            // Grafik pakai semua data yang punya nilai (selesai maupun draft)
            $punyaNilai = $evaluasiList->whereNotNull('jumlah_nilai');
            $selesai    = $evaluasiList->where('status', 'selesai');

            $sudah    = $selesai->count();
            $rataRata = $punyaNilai->avg('jumlah_nilai');
            $terbaik  = $punyaNilai->sortByDesc('jumlah_nilai')->first();

            $distribusi = [
                'SB' => $punyaNilai->where('grade', 'SB')->count(),
                'B'  => $punyaNilai->where('grade', 'B')->count(),
                'C'  => $punyaNilai->where('grade', 'C')->count(),
                'K'  => $punyaNilai->where('grade', 'K')->count(),
                'SK' => $punyaNilai->where('grade', 'SK')->count(),
            ];

            // Rata-rata per komponen — pakai semua yang punya nilai (bukan hanya selesai)
            $komponen = [
                'Sikap Kerja'      => $punyaNilai->whereNotNull('rata_sikap_kerja')->avg('rata_sikap_kerja'),
                'Indikator Hasil'  => $punyaNilai->whereNotNull('rata_indikator_hasil')->avg('rata_indikator_hasil'),
                'Indikator Proses' => $punyaNilai->whereNotNull('rata_indikator_proses')->avg('rata_indikator_proses'),
                'Mutu Pelayanan'   => $punyaNilai->whereNotNull('rata_mutu_pelayanan')->avg('rata_mutu_pelayanan'),
            ];

            // Data petugas untuk grafik ranking per wilayah
            $petugasGrafik = $punyaNilai->sortByDesc('jumlah_nilai')->map(function ($ev) {
                return [
                    'nama'             => $ev->petugas?->user?->name ?? 'N/A',
                    'jumlah_nilai'     => round($ev->jumlah_nilai ?? 0, 2),
                    'sikap_kerja'      => round($ev->rata_sikap_kerja ?? 0, 2),
                    'indikator_hasil'  => round($ev->rata_indikator_hasil ?? 0, 2),
                    'indikator_proses' => round($ev->rata_indikator_proses ?? 0, 2),
                    'mutu_pelayanan'   => round($ev->rata_mutu_pelayanan ?? 0, 2),
                    'grade'            => $ev->grade ?? '-',
                ];
            })->values()->toArray();

            return [
                'wilayah'        => $w,
                'total_petugas'  => $totalPetugas,
                'sudah'          => $sudah,
                'belum'          => $totalPetugas - $evaluasiList->count(),
                'rata_rata'      => $rataRata ? round($rataRata, 2) : null,
                'terbaik'        => $terbaik,
                'distribusi'     => $distribusi,
                'evaluasi_list'  => $evaluasiList,
                'komponen'       => $komponen,
                'petugas_grafik' => $petugasGrafik,
            ];
        });

        // Statistik global
        $allEvaluasi   = EvaluasiPetugas::where('periode', $periode)->whereNotNull('jumlah_nilai')->get();
        $globalRata    = $allEvaluasi->avg('jumlah_nilai');
        $globalTerbaik = $allEvaluasi->sortByDesc('jumlah_nilai')->first()?->load('petugas.user', 'wilayah');
        $totalSelesai  = EvaluasiPetugas::where('periode', $periode)->whereNotNull('jumlah_nilai')->count();
        $totalPetugas  = Petugas::count();

        $gradeDistribusi = [
            'SB' => EvaluasiPetugas::where('periode', $periode)->where('grade', 'SB')->count(),
            'B'  => EvaluasiPetugas::where('periode', $periode)->where('grade', 'B')->count(),
            'C'  => EvaluasiPetugas::where('periode', $periode)->where('grade', 'C')->count(),
            'K'  => EvaluasiPetugas::where('periode', $periode)->where('grade', 'K')->count(),
            'SK' => EvaluasiPetugas::where('periode', $periode)->where('grade', 'SK')->count(),
        ];

        // Data grafik garis per komponen per wilayah
        $grafikKomponenGlobal = [
            'Sikap Kerja'      => [],
            'Indikator Hasil'  => [],
            'Indikator Proses' => [],
            'Mutu Pelayanan'   => [],
        ];
        foreach ($rekapWilayah as $r) {
            foreach ($grafikKomponenGlobal as $k => &$arr) {
                $arr[] = $r['komponen'][$k] ? round($r['komponen'][$k], 2) : 0;
            }
        }
        unset($arr);

        $wilayahLabels = $rekapWilayah->pluck('wilayah.nama')->values()->toArray();

        return view('admin.penilaian.index', compact(
            'periode', 'periodeOptions',
            'rekapWilayah',
            'globalRata', 'globalTerbaik', 'totalSelesai', 'totalPetugas',
            'gradeDistribusi', 'grafikKomponenGlobal', 'wilayahLabels'
        ));
    }


    public function detail(Request $request, $wilayahId)
    {
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $wilayah        = Wilayah::findOrFail($wilayahId);

        $evaluasiList = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->with('petugas.user')
            ->orderByDesc('jumlah_nilai')
            ->get();

        return view('admin.penilaian.detail', compact(
            'wilayah', 'evaluasiList', 'periode', 'periodeOptions'
        ));
    }

    // ════════════════════════════════════════════════════════════════
    // EXPORT EXCEL — Semua wilayah, tiap wilayah 1 sheet
    // Route: GET /admin/penilaian/export?periode=2025-TW1
    // ════════════════════════════════════════════════════════════════
    public function export(Request $request)
    {
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $periodeLabel   = $periodeOptions[$periode] ?? $periode;

        $wilayahList = Wilayah::where('status', 'aktif')->orderBy('nama')->get();

        $spreadsheet = new Spreadsheet();
        $spreadsheet->removeSheetByIndex(0); // hapus sheet default

        $sheetAdded = 0;
        foreach ($wilayahList as $wilayah) {
            $evaluasiList = EvaluasiPetugas::where('wilayah_id', $wilayah->id)
                ->where('periode', $periode)
                ->whereIn('status', ['selesai', 'draft'])
                ->whereNotNull('jumlah_nilai')
                ->with('petugas.user')
                ->orderByDesc('jumlah_nilai') // peringkat 1, 2, 3, ...
                ->get();

            if ($evaluasiList->isEmpty()) {
                continue;
            }

            $sheet = $spreadsheet->createSheet();
            // Nama sheet: nama wilayah, max 31 char (batasan Excel)
            $sheetTitle = mb_substr(preg_replace('/[\/\\\\?*:\[\]]/', '-', $wilayah->nama), 0, 31);
            $sheet->setTitle($sheetTitle);

            $this->fillSheet($sheet, $evaluasiList, $periode, $wilayah->nama);
            $sheetAdded++;
        }

        if ($sheetAdded === 0) {
            return back()->with('error', 'Belum ada data penilaian untuk periode ' . $periodeLabel);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Penilaian_PST_Semua_Wilayah_' . str_replace('-', '_', $periode) . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pst_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ════════════════════════════════════════════════════════════════
    // EXPORT PER WILAYAH — 1 file, 1 sheet untuk wilayah yg dipilih
    // Route: GET /admin/penilaian/export-wilayah/{wilayahId}?periode=2025-TW1
    // ════════════════════════════════════════════════════════════════
    public function exportWilayah(Request $request, $wilayahId)
    {
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $periodeLabel   = $periodeOptions[$periode] ?? $periode;
        $wilayah        = Wilayah::findOrFail($wilayahId);

        $evaluasiList = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->whereIn('status', ['selesai', 'draft'])
            ->whereNotNull('jumlah_nilai')
            ->with('petugas.user')
            ->orderByDesc('jumlah_nilai') // peringkat 1, 2, 3, ...
            ->get();

        if ($evaluasiList->isEmpty()) {
            return back()->with('error', 'Belum ada data penilaian untuk ' . $wilayah->nama . ' periode ' . $periodeLabel);
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr(preg_replace('/[\/\\\\?*:\[\]]/', '-', $wilayah->nama), 0, 31));

        $this->fillSheet($sheet, $evaluasiList, $periode, $wilayah->nama);

        $filename = 'Penilaian_PST_' . preg_replace('/\s+/', '_', $wilayah->nama) . '_' . str_replace('-', '_', $periode) . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pst_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────────
    // FILL SHEET — isi 1 sheet dengan data evaluasi 1 wilayah
    // $evaluasiList sudah diurutkan desc (peringkat 1 = index 0)
    // ─────────────────────────────────────────────────────────────
    private function fillSheet($sheet, $evaluasiList, string $periode, string $namaWilayah): void
    {
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $judulPeriode   = $this->formatJudulPeriode($periode);
        $namaPertugas   = $evaluasiList->pluck('petugas.user.name')->toArray();
        $jumlahPetugas  = count($namaPertugas);

        // Kolom: A=No, B=Komponen, C...(C+n)=nama petugas
        $colEnd = $this->columnLetter(2 + $jumlahPetugas);

        // ── BARIS 1: Judul ────────────────────────────────────────
        $sheet->mergeCells("A1:{$colEnd}1");
        $sheet->setCellValue('A1', "LAPORAN PENILAIAN PETUGAS PST BERPRESTASI {$judulPeriode}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        // ── BARIS 2: Sub judul wilayah ────────────────────────────
        $sheet->mergeCells("A2:{$colEnd}2");
        $sheet->setCellValue('A2', "Wilayah: {$namaWilayah}");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '444444']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── BARIS 3: Header kolom ─────────────────────────────────
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Komponen Penilaian');

        $col = 3;
        foreach ($evaluasiList as $evaluasi) {
            $colLetter = $this->columnLetter($col);
            $sheet->setCellValue("{$colLetter}3", $evaluasi->petugas->user->name ?? '-');
            $col++;
        }

        $sheet->getStyle("A3:{$colEnd}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(30);

        // ── BARIS 4+: Data komponen ───────────────────────────────
        $row = 4;
        $komponenData = [
            ['I',    'SIKAP KERJA',                               'rata_sikap_kerja',          true],
            ['',     'Kehadiran / Absensi',                       'nilai_kehadiran',            false],
            ['',     'Disiplin',                                  'nilai_disiplin',             false],
            ['',     'Komunikasi',                                'nilai_komunikasi',           false],
            ['',     'Kerjasama',                                 'nilai_kerjasama',            false],
            ['',     'Inovatif',                                  'nilai_inovatif',             false],
            ['II.A', 'KINERJA PELAYANAN INDIKATOR HASIL',         'rata_indikator_hasil',       true],
            ['',     'Kepastian Waktu',                           'nilai_kepastian_waktu',      false],
            ['',     'Akurasi',                                   'nilai_akurasi',              false],
            ['II.B', 'KINERJA PELAYANAN INDIKATOR PROSES',        'rata_indikator_proses',      true],
            ['',     'Kejelasan',                                 'nilai_kejelasan',            false],
            ['',     'Tanggungjawab',                             'nilai_tanggungjawab',        false],
            ['',     'Kelengkapan Sarana Prasarana',              'nilai_kelengkapan_sarpras',  false],
            ['',     'Kesopanan & Keramahan',                     'nilai_kesopanan_keramahan',  false],
            ['',     'Kesesuaian dengan standar atribut layanan', 'nilai_kesesuaian_atribut',   false],
            ['III',  'MUTU PELAYANAN',                            'rata_mutu_pelayanan',        true],
            ['',     'Kepatuhan atas SOP',                        'nilai_kepatuhan_sop',        false],
            ['',     'Kepuasan pelanggan eksternal *)',            'nilai_kepuasan_pelanggan',   false],
        ];

        foreach ($komponenData as [$no, $label, $field, $isHeader]) {
            $sheet->setCellValue('A' . $row, $no);
            $sheet->setCellValue('B' . $row, $label);
            $sheet->getStyle('A' . $row)->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);

            $col = 3;
            foreach ($evaluasiList as $evaluasi) {
                $colLetter = $this->columnLetter($col);
                $val = $evaluasi->$field;
                $sheet->setCellValue("{$colLetter}{$row}", $val !== null ? round($val, 2) : '-');
                $col++;
            }

            if ($isHeader) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'font' => ['bold' => true],
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'DCE6F1']],
                ]);
            }
            $row++;
        }

        // ── JUMLAH NILAI ──────────────────────────────────────────
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'JUMLAH NILAI');
        $col = 3;
        foreach ($evaluasiList as $evaluasi) {
            $colLetter = $this->columnLetter($col);
            $sheet->setCellValue("{$colLetter}{$row}", $evaluasi->jumlah_nilai !== null ? round($evaluasi->jumlah_nilai, 2) : '-');
            $col++;
        }
        $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFEB9C']],
        ]);
        $row++;

        // ── GRADE ─────────────────────────────────────────────────
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'GRADE');
        $col = 3;
        foreach ($evaluasiList as $evaluasi) {
            $colLetter = $this->columnLetter($col);
            $sheet->setCellValue("{$colLetter}{$row}", $evaluasi->grade ?? '-');
            $col++;
        }
        $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'E2EFDA']],
        ]);
        $row++;

        // ── PERINGKAT (1,2,3,...) ─────────────────────────────────
        $sheet->mergeCells("A{$row}:B{$row}");
        $sheet->setCellValue("A{$row}", 'PERINGKAT');
        $col      = 3;
        $peringkat = 1;
        foreach ($evaluasiList as $evaluasi) {
            $colLetter = $this->columnLetter($col);
            $sheet->setCellValue("{$colLetter}{$row}", $peringkat);
            $peringkat++;
            $col++;
        }
        $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFF2CC']],
        ]);

        // Highlight peringkat 1,2,3 dengan warna emas/perak/perunggu
        $rankColors = [1 => 'FFD700', 2 => 'C0C0C0', 3 => 'CD7F32'];
        $rCol = 3;
        $rNum = 1;
        foreach ($evaluasiList as $evaluasi) {
            if (isset($rankColors[$rNum])) {
                $rColLetter = $this->columnLetter($rCol);
                $sheet->getStyle("{$rColLetter}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rankColors[$rNum]]],
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                ]);
                // Highlight juga kolom nama di header (baris 3)
                $headerFill = $rankColors[$rNum] === 'FFD700' ? '7B6000' : ($rankColors[$rNum] === 'C0C0C0' ? '5C5C5C' : '6E3A0A');
                $sheet->getStyle("{$rColLetter}3")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $headerFill]],
                    'font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
                ]);
            }
            $rNum++;
            $rCol++;
        }

        $dataEndRow = $row;
        $row += 2;

        // ── KESIMPULAN NOMINASI ───────────────────────────────────
        $top3 = $evaluasiList->take(3);
        if ($top3->count() > 0) {
            $nominasiStr = $top3->values()->map(function ($ev, $i) {
                $nama = $ev->petugas?->user?->name ?? '?';
                return ($i + 1) . '. ' . $nama . ' (' . number_format($ev->jumlah_nilai, 2) . ')';
            })->implode(' ; ');

            $sheet->mergeCells("A{$row}:{$colEnd}{$row}");
            $sheet->setCellValue("A{$row}", 'KESIMPULAN: Nominasi Petugas PST Berprestasi ' . $this->formatJudulPeriode($periode) . ' : ' . $nominasiStr);
            $sheet->getStyle("A{$row}")->applyFromArray([
                'font' => ['bold' => true, 'italic' => true],
                'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'FFFACD']],
            ]);
            $row += 2;
        }

        // ── KETERANGAN GRADE ──────────────────────────────────────
        $sheet->setCellValue("A{$row}", 'Keterangan :');
        $sheet->getStyle("A{$row}")->getFont()->setBold(true);
        $row++;
        foreach ([
            'SANGAT BAIK (SB)     : > 95',
            'BAIK (B)             : 86 – 95',
            'CUKUP (C)            : 66 – 85',
            'KURANG (K)           : 51 – 65',
            'SANGAT KURANG (SK)   : < 50',
        ] as $ket) {
            $sheet->setCellValue("A{$row}", $ket);
            $row++;
        }

        $row++;
        $sheet->setCellValue("A{$row}", '*) Nilai kepuasan pelanggan dihitung otomatis dari data survey kepuasan periode bersangkutan.');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);
        $row += 2;

        // ── TTD ───────────────────────────────────────────────────
        $ttdCol = $this->columnLetter(2 + $jumlahPetugas);
        $sheet->setCellValue("{$ttdCol}{$row}", 'Mengetahui,');
        $row++;
        $sheet->setCellValue("{$ttdCol}{$row}", 'Ketua Tim Diseminasi Statistik');
        $row += 4;
        $sheet->setCellValue("{$ttdCol}{$row}", '(.....................................)');

        // ── BORDER TABEL ──────────────────────────────────────────
        $sheet->getStyle("A3:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => [
                'allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']],
            ],
        ]);

        // ── ALIGNMENT NILAI ───────────────────────────────────────
        $sheet->getStyle("C4:{$colEnd}{$dataEndRow}")->getAlignment()
            ->setHorizontal(Alignment::HORIZONTAL_CENTER);

        // ── LEBAR KOLOM ───────────────────────────────────────────
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(42);
        for ($c = 3; $c <= (2 + $jumlahPetugas); $c++) {
            $sheet->getColumnDimension($this->columnLetter($c))->setWidth(14);
        }
    }

    // ─────────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────────

    private function columnLetter(int $col): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    }

    private function formatJudulPeriode(string $periode): string
    {
        $roman = ['I', 'II', 'III', 'IV'];
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            return 'TRIWULAN ' . $roman[(int)$m[2] - 1] . ' TAHUN ' . $m[1];
        }
        return strtoupper($periode);
    }

    // ════════════════════════════════════════════════════════
    // SELESAIKAN SEMUA DRAFT (Admin — semua wilayah)
    // Route: POST /admin/penilaian/selesaikan-semua
    // ════════════════════════════════════════════════════════
    public function selesaikanSemua(Request $request)
    {
        $periode = $request->input('periode', NilaiEvaluasiController::periodeSekarang());

        $drafts = EvaluasiPetugas::where('periode', $periode)
            ->where('status', 'draft')
            ->whereNotNull('jumlah_nilai')
            ->get();

        $jumlah = 0;
        foreach ($drafts as $ev) {
            $ev->status = 'selesai';
            $ev->hitungKomposit();
            $ev->save();
            $jumlah++;
        }

        if ($jumlah === 0) {
            return back()->with('info', 'Tidak ada data draft yang siap diselesaikan untuk periode ini.');
        }

        return back()->with('success', "{$jumlah} evaluasi berhasil diselesaikan. Grafik rekap sudah diperbarui.");
    }
}