<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiPetugas;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class KoordinatorPenilaianController extends Controller
{
    // ─────────────────────────────────────────────────────────
    // Helper: ambil wilayah_id milik koordinator yg login
    // ─────────────────────────────────────────────────────────
    private function wilayahId(): int
    {
        return (int) Auth::user()->wilayah_id;
    }

    // ════════════════════════════════════════════════════════
    // INDEX — Rekap penilaian wilayah koordinator sendiri
    // Route: GET /koordinator/penilaian
    // ════════════════════════════════════════════════════════
    public function index(Request $request)
    {
        $wilayahId      = $this->wilayahId();
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $wilayah        = Wilayah::findOrFail($wilayahId);

        $evaluasiList = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->with('petugas.user')
            ->orderByDesc('jumlah_nilai')
            ->get();

        $totalPetugas = $evaluasiList->count();
        $sudahSelesai = $evaluasiList->where('status', 'selesai')->count();
        $rataRata     = $evaluasiList->whereNotNull('jumlah_nilai')->avg('jumlah_nilai');

        $distribusi = [
            'SB' => $evaluasiList->where('grade', 'SB')->count(),
            'B'  => $evaluasiList->where('grade', 'B')->count(),
            'C'  => $evaluasiList->where('grade', 'C')->count(),
            'K'  => $evaluasiList->where('grade', 'K')->count(),
            'SK' => $evaluasiList->where('grade', 'SK')->count(),
        ];

        return view('koordinator.penilaian.index', compact(
            'wilayah',
            'evaluasiList',
            'periode',
            'periodeOptions',
            'totalPetugas',
            'sudahSelesai',
            'rataRata',
            'distribusi'
        ));
    }

    // ════════════════════════════════════════════════════════
    // EXPORT EXCEL — Hanya wilayah koordinator yg login
    // Route: GET /koordinator/penilaian/export?periode=2025-TW1
    // ════════════════════════════════════════════════════════
    public function export(Request $request)
    {
        $wilayahId      = $this->wilayahId();
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

    // ════════════════════════════════════════════════════════
    // EXPORT EXCEL TAHUNAN — Rekap 1 tahun (semua triwulan)
    // Sheet 1 = Rekap Tahunan (ringkasan), Sheet 2..n = detail per TW
    // Route: GET /koordinator/penilaian/export-tahunan?tahun=2026
    // ════════════════════════════════════════════════════════
    public function exportTahunan(Request $request)
    {
        $wilayahId = $this->wilayahId();
        $tahun     = $request->input('tahun', date('Y'));
        $wilayah   = Wilayah::findOrFail($wilayahId);

        abort_unless(preg_match('/^\d{4}$/', (string) $tahun), 404);

        // ── Ambil data tiap triwulan yg tersedia ──
        $dataPerTw = [];
        for ($tw = 1; $tw <= 4; $tw++) {
            $periode = "{$tahun}-TW{$tw}";
            $list = EvaluasiPetugas::where('wilayah_id', $wilayahId)
                ->where('periode', $periode)
                ->whereIn('status', ['selesai', 'draft'])
                ->whereNotNull('jumlah_nilai')
                ->with('petugas.user')
                ->orderByDesc('jumlah_nilai')
                ->get();

            if ($list->isNotEmpty()) {
                $dataPerTw[$tw] = $list;
            }
        }

        if (empty($dataPerTw)) {
            return back()->with('error', 'Belum ada data penilaian untuk ' . $wilayah->nama . ' tahun ' . $tahun);
        }

        // ── Kumpulkan nilai per petugas lintas triwulan ──
        $petugasMap = []; // petugas_id => ['nama' => ..., 'tw' => [1=>nilai,...]]
        foreach ($dataPerTw as $tw => $list) {
            foreach ($list as $ev) {
                $pid = $ev->petugas_id;
                if (!isset($petugasMap[$pid])) {
                    $petugasMap[$pid] = [
                        'nama' => $ev->petugas->user->name ?? '-',
                        'tw'   => [],
                    ];
                }
                $petugasMap[$pid]['tw'][$tw] = $ev->jumlah_nilai;
            }
        }

        // ── Hitung rata-rata tahunan & grade, lalu urutkan ──
        $rekap = collect($petugasMap)->map(function ($p, $pid) {
            $nilaiList = collect($p['tw'])->filter(fn ($v) => $v !== null);
            $rata      = $nilaiList->isNotEmpty() ? round($nilaiList->avg(), 2) : null;
            return [
                'petugas_id' => $pid,
                'nama'       => $p['nama'],
                'tw'         => $p['tw'],
                'rata'       => $rata,
                'grade'      => EvaluasiPetugas::hitungGrade($rata),
            ];
        })->sortByDesc(fn ($r) => $r['rata'] ?? -1)->values();

        // ── Bangun workbook ──
        $spreadsheet = new Spreadsheet();

        // Sheet 1: Rekap Tahunan
        $sheetRekap = $spreadsheet->getActiveSheet();
        $sheetRekap->setTitle('Rekap Tahunan');
        $this->fillSheetTahunan($sheetRekap, $rekap, $tahun, $wilayah->nama, array_keys($dataPerTw));

        // Sheet 2..n: detail per triwulan
        foreach ($dataPerTw as $tw => $list) {
            $sheetTw = $spreadsheet->createSheet();
            $sheetTw->setTitle("Triwulan {$tw}");
            $this->fillSheet($sheetTw, $list, "{$tahun}-TW{$tw}", $wilayah->nama);
        }

        $spreadsheet->setActiveSheetIndex(0);

        $filename = 'Rekap_Tahunan_PST_' . preg_replace('/\s+/', '_', $wilayah->nama) . '_' . $tahun . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pst_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────
    // FILL SHEET TAHUNAN — ringkasan nilai 4 triwulan per petugas
    // ─────────────────────────────────────────────────────────
    private function fillSheetTahunan($sheet, $rekap, $tahun, string $namaWilayah, array $twTersedia): void
    {
        // Kolom: A=No, B=Nama, C..F=TW1..TW4, G=Rata-rata, H=Grade, I=Peringkat
        $twCols   = ['C', 'D', 'E', 'F'];
        $colEnd   = 'I';

        // ── BARIS 1-2: Judul ──
        $sheet->mergeCells("A1:{$colEnd}1");
        $sheet->setCellValue('A1', "REKAP TAHUNAN PENILAIAN PETUGAS PST TAHUN {$tahun}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(22);

        $sheet->mergeCells("A2:{$colEnd}2");
        $sheet->setCellValue('A2', "Wilayah: {$namaWilayah}");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10, 'color' => ['rgb' => '444444']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // ── BARIS 3: Header kolom ──
        $sheet->setCellValue('A3', 'No');
        $sheet->setCellValue('B3', 'Nama Petugas');
        foreach ($twCols as $i => $col) {
            $sheet->setCellValue("{$col}3", 'TW ' . ($i + 1));
        }
        $sheet->setCellValue('G3', 'Rata-rata Tahunan');
        $sheet->setCellValue('H3', 'Grade');
        $sheet->setCellValue('I3', 'Peringkat');

        $sheet->getStyle("A3:{$colEnd}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(26);

        // Redupkan kolom TW yg tidak tersedia datanya
        foreach ($twCols as $i => $col) {
            if (!in_array($i + 1, $twTersedia, true)) {
                $sheet->getStyle("{$col}3")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB8C6E0'));
            }
        }

        // ── BARIS 4+: Data per petugas ──
        $row = 4;
        $rankColors = [1 => 'FFD700', 2 => 'C0C0C0', 3 => 'CD7F32'];
        $peringkat  = 1;
        foreach ($rekap as $r) {
            $sheet->setCellValue("A{$row}", $peringkat);
            $sheet->setCellValue("B{$row}", $r['nama']);
            foreach ($twCols as $i => $col) {
                $val = $r['tw'][$i + 1] ?? null;
                $sheet->setCellValue("{$col}{$row}", $val !== null ? round($val, 2) : '-');
            }
            $sheet->setCellValue("G{$row}", $r['rata'] !== null ? round($r['rata'], 2) : '-');
            $sheet->setCellValue("H{$row}", $r['grade'] ?? '-');
            $sheet->setCellValue("I{$row}", $peringkat);

            $sheet->getStyle("C{$row}:{$colEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getFont()->setBold(true);

            if (isset($rankColors[$peringkat])) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rankColors[$peringkat]]],
                    'font' => ['bold' => true, 'color' => ['rgb' => '000000']],
                ]);
            }

            $row++;
            $peringkat++;
        }

        $dataEndRow = $row - 1;

        // ── Border tabel ──
        $sheet->getStyle("A3:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'AAAAAA']]],
        ]);

        $row += 2;
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
        $sheet->setCellValue("A{$row}", '*) Rata-rata tahunan dihitung dari triwulan yang sudah dievaluasi koordinator (' . implode(', ', array_map(fn($t)=>"TW{$t}", $twTersedia)) . ').');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9);
        $row += 2;

        $sheet->setCellValue("{$colEnd}{$row}", 'Mengetahui,');
        $row++;
        $sheet->setCellValue("{$colEnd}{$row}", 'Koordinator Wilayah ' . $namaWilayah);
        $row += 4;
        $sheet->setCellValue("{$colEnd}{$row}", '(.....................................)');

        // ── Lebar kolom ──
        $sheet->getColumnDimension('A')->setWidth(7);
        $sheet->getColumnDimension('B')->setWidth(28);
        foreach ($twCols as $col) {
            $sheet->getColumnDimension($col)->setWidth(10);
        }
        $sheet->getColumnDimension('G')->setWidth(16);
        $sheet->getColumnDimension('H')->setWidth(9);
        $sheet->getColumnDimension('I')->setWidth(11);
    }

    // ─────────────────────────────────────────────────────────
    // FILL SHEET — isi sheet dengan data evaluasi 1 wilayah
    // (logika sama persis dgn AdminPenilaianController::fillSheet)
    // ─────────────────────────────────────────────────────────
    private function fillSheet($sheet, $evaluasiList, string $periode, string $namaWilayah): void
    {
        $judulPeriode  = $this->formatJudulPeriode($periode);
        $namaPertugas  = $evaluasiList->pluck('petugas.user.name')->toArray();
        $jumlahPetugas = count($namaPertugas);

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
        $col       = 3;
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

        // Highlight peringkat 1, 2, 3 dengan warna emas/perak/perunggu
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
        $sheet->setCellValue("{$ttdCol}{$row}", 'Koordinator Wilayah ' . $namaWilayah);
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

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────

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
}