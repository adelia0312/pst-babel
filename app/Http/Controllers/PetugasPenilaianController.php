<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiPetugas;
use App\Models\Wilayah;
use App\Helpers\PeriodeHelper;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class PetugasPenilaianController extends Controller
{
    private function petugasId(): int
    {
        return (int) Auth::user()->petugas->id;
    }

    public function index(Request $request)
    {
        $user      = Auth::user();
        $petugasId = $this->petugasId();

        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());

        // ── Semua evaluasi petugas (ascending untuk tren) ──
        $evaluasiList = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->orderBy('periode')
            ->get();

        // ── Evaluasi periode aktif ──
        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->first();

        // ── Hitung nilai otomatis sebagai preview jika belum dievaluasi koordinator ──
        $nilaiOtomatis = (new NilaiEvaluasiController)->hitungNilaiOtomatisPublic(
            $user->id, $petugasId, $periode
        );

        // ── Komponen periode aktif ──
        if ($evaluasi) {
            $kompSikap  = $evaluasi->rata_sikap_kerja;
            $kompHasil  = $evaluasi->rata_indikator_hasil;
            $kompProses = $evaluasi->rata_indikator_proses;
            $kompMutu   = $evaluasi->rata_mutu_pelayanan;
        } else {
            // Preview otomatis — rata-rata sub-indikator per komponen (sama dengan hitungKomposit)
            //
            // PERBAIKAN: whenNotEmpty($cb, fn() => null) TIDAK aman untuk
            // mengembalikan null — closure default mengembalikan null, lalu
            // `null ?? $this` di Conditionable::when() jatuh balik ke $this
            // (Collection kosong itu sendiri), bukan null. Akibatnya jika semua
            // sub-komponen null, hasilnya Collection, bukan null — yang lalu
            // menyebabkan "Unsupported operand types: float + Collection" saat
            // di-avg() bersama komponen lain. Pakai pengecekan isNotEmpty() biasa.
            $avg = function (array $vals) {
                $valid = collect($vals)->filter(fn($v) => $v !== null);
                return $valid->isNotEmpty() ? round($valid->avg(), 4) : null;
            };

            $kompSikap  = $avg([$nilaiOtomatis['kehadiran'], $nilaiOtomatis['disiplin'], $nilaiOtomatis['komunikasi'], $nilaiOtomatis['kerjasama'], $nilaiOtomatis['inovatif']]);
            $kompHasil  = $avg([$nilaiOtomatis['kepastian_waktu'], $nilaiOtomatis['akurasi']]);
            $kompProses = $avg([$nilaiOtomatis['tanggungjawab'], $nilaiOtomatis['kesopanan_keramahan'], $nilaiOtomatis['kesesuaian_atribut']]);
            $kompMutu   = $avg([$nilaiOtomatis['kepatuhan_sop'], $nilaiOtomatis['kepuasan_pelanggan']]);
        }

        // ── Nilai preview: rata-rata biasa 4 komponen (sesuai Excel resmi) ──
        $komponenPreview = collect([$kompSikap, $kompHasil, $kompProses, $kompMutu])->filter(fn($v) => $v !== null);
        $nilaiPreview = $evaluasi?->jumlah_nilai
            ?? ($komponenPreview->isNotEmpty() ? round($komponenPreview->avg(), 4) : null);

        $gradePreview = $evaluasi?->grade ?? EvaluasiPetugas::hitungGrade($nilaiPreview);

        // ── Ranking wilayah ──
        $wilayahId      = $user->wilayah_id;
        $rankingWilayah = collect();
        $myRank         = null;

        if ($wilayahId) {
            $rankingWilayah = EvaluasiPetugas::where('periode', $periode)
                ->where('status', 'selesai')
                ->where('wilayah_id', $wilayahId)
                ->with(['petugas.user'])
                ->orderByDesc('jumlah_nilai')
                ->get()
                ->values()
                ->map(function ($ev, $idx) use ($petugasId) {
                    return (object) [
                        'rank'                  => $idx + 1,
                        'petugas_id'            => $ev->petugas_id,
                        'nama'                  => optional(optional($ev->petugas)->user)->name ?? 'Petugas',
                        'jumlah_nilai'          => $ev->jumlah_nilai,
                        'grade'                 => $ev->grade,
                        'rata_sikap_kerja'      => $ev->rata_sikap_kerja,
                        'rata_indikator_hasil'  => $ev->rata_indikator_hasil,
                        'rata_indikator_proses' => $ev->rata_indikator_proses,
                        'rata_mutu_pelayanan'   => $ev->rata_mutu_pelayanan,
                        'is_me'                 => $ev->petugas_id === $petugasId,
                    ];
                });

            $myRank = optional($rankingWilayah->firstWhere('is_me', true))->rank;
        }

        // ── Data tren grafik ──
        $semuaSelesai = $evaluasiList
            ->where('status', 'selesai')
            ->whereNotNull('jumlah_nilai')
            ->sortBy('periode')
            ->values();

        $trendRaw = $semuaSelesai->map(fn($e) => [
            'periode' => $e->periode,
            'tipe'    => $e->tipe_periode,
            'label'   => PeriodeHelper::isoLabel($e->periode),
            'nilai'   => round($e->jumlah_nilai, 4),
            'sikap'   => $e->rata_sikap_kerja      !== null ? round($e->rata_sikap_kerja, 4)      : null,
            'hasil'   => $e->rata_indikator_hasil   !== null ? round($e->rata_indikator_hasil, 4)  : null,
            'proses'  => $e->rata_indikator_proses  !== null ? round($e->rata_indikator_proses, 4) : null,
            'mutu'    => $e->rata_mutu_pelayanan    !== null ? round($e->rata_mutu_pelayanan, 4)   : null,
            'grade'   => $e->grade,
            'tahun'   => (int) substr($e->periode, 0, 4),
        ])->values()->toArray();

        // Agregasi per tahun — rata-rata biasa per tahun (konsisten dengan Excel)
        $trendPerTahun = collect($trendRaw)
            ->groupBy('tahun')
            ->map(function ($group, $tahun) {
                $avgSikap  = $group->whereNotNull('sikap')->avg('sikap');
                $avgHasil  = $group->whereNotNull('hasil')->avg('hasil');
                $avgProses = $group->whereNotNull('proses')->avg('proses');
                $avgMutu   = $group->whereNotNull('mutu')->avg('mutu');
                // Nilai tahunan = avg dari avg komponen (konsisten dengan hitungKomposit)
                $komponen  = collect([$avgSikap, $avgHasil, $avgProses, $avgMutu])->filter();
                $nilaiThn  = $komponen->isNotEmpty() ? round($komponen->avg(), 4) : null;
                return [
                    'periode' => (string) $tahun,
                    'label'   => "Tahun {$tahun}",
                    'nilai'   => $nilaiThn,
                    'sikap'   => $avgSikap  ? round($avgSikap, 4)  : null,
                    'hasil'   => $avgHasil  ? round($avgHasil, 4)  : null,
                    'proses'  => $avgProses ? round($avgProses, 4) : null,
                    'mutu'    => $avgMutu   ? round($avgMutu, 4)   : null,
                    'grade'   => EvaluasiPetugas::hitungGrade($nilaiThn),
                ];
            })->values()->toArray();

        // Perubahan tren antar periode terakhir
        $trendChange = null;
        $allNilai    = array_column($trendRaw, 'nilai');
        if (count($allNilai) >= 2) {
            $last = end($allNilai);
            $prev = $allNilai[count($allNilai) - 2];
            $diff = round($last - $prev, 2);
            $trendChange = [
                'diff'      => $diff,
                'direction' => $diff > 0 ? 'up' : ($diff < 0 ? 'down' : 'same'),
                'label'     => $diff > 0 ? "+{$diff}" : ($diff < 0 ? "{$diff}" : "±0"),
            ];
        }

        // ── Info untuk tombol unduh PDF ──
        $tahunAktif = substr($periode, 0, 4);
        $jumlahSelesaiTahunIni = $evaluasiList
            ->where('status', 'selesai')
            ->whereNotNull('jumlah_nilai')
            ->filter(fn ($e) => str_starts_with($e->periode, $tahunAktif))
            ->count();

        return view('petugas.penilaian.index', compact(
            'evaluasiList', 'evaluasi', 'periode',
            'periodeOptions', 'rankingWilayah', 'myRank',
            'nilaiOtomatis', 'kompSikap', 'kompHasil', 'kompProses', 'kompMutu',
            'nilaiPreview', 'gradePreview',
            'trendRaw', 'trendPerTahun', 'trendChange',
            'tahunAktif', 'jumlahSelesaiTahunIni'
        ));
    }

    // ════════════════════════════════════════════════════════
    // EXPORT EXCEL — Rekap ranking petugas se-wilayah, 1 triwulan
    // (versi simpel: sama seperti tabel "Ranking Petugas — Wilayah
    // Ini" yang tampil di halaman Nilai Saya)
    // Route: GET /petugas/penilaian/export?periode=2026-TW2
    // ════════════════════════════════════════════════════════
    public function export(Request $request)
    {
        $user           = Auth::user();
        $wilayahId      = (int) $user->wilayah_id;
        $periode        = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $periodeOptions = NilaiEvaluasiController::periodeOptions();
        $periodeLabel   = $periodeOptions[$periode] ?? $periode;

        abort_unless($wilayahId, 404);
        $wilayah = Wilayah::findOrFail($wilayahId);

        $rankingWilayah = EvaluasiPetugas::where('wilayah_id', $wilayahId)
            ->where('periode', $periode)
            ->where('status', 'selesai')
            ->whereNotNull('jumlah_nilai')
            ->with('petugas.user')
            ->orderByDesc('jumlah_nilai')
            ->get();

        if ($rankingWilayah->isEmpty()) {
            return back()->with('error', 'Belum ada data penilaian wilayah untuk periode ' . $periodeLabel);
        }

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle(mb_substr(preg_replace('/[\/\\\\?*:\[\]]/', '-', $wilayah->nama), 0, 31));

        $this->fillSheetRanking($sheet, $rankingWilayah, $periode, $wilayah->nama);

        $filename = 'Ranking_Petugas_PST_' . preg_replace('/\s+/', '_', $wilayah->nama) . '_' . str_replace('-', '_', $periode) . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pst_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ════════════════════════════════════════════════════════
    // EXPORT EXCEL TAHUNAN — Rekap ranking petugas se-wilayah,
    // 4 triwulan dalam 1 tahun
    // Route: GET /petugas/penilaian/export-tahunan?tahun=2026
    // ════════════════════════════════════════════════════════
    public function exportTahunan(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = (int) $user->wilayah_id;
        $tahun     = $request->input('tahun', date('Y'));

        abort_unless($wilayahId, 404);
        abort_unless(preg_match('/^\d{4}$/', (string) $tahun), 404);

        $wilayah = Wilayah::findOrFail($wilayahId);

        // ── Ambil data tiap triwulan yg tersedia ──
        $dataPerTw = [];
        for ($tw = 1; $tw <= 4; $tw++) {
            $periode = "{$tahun}-TW{$tw}";
            $list = EvaluasiPetugas::where('wilayah_id', $wilayahId)
                ->where('periode', $periode)
                ->where('status', 'selesai')
                ->whereNotNull('jumlah_nilai')
                ->with('petugas.user')
                ->orderByDesc('jumlah_nilai')
                ->get();

            if ($list->isNotEmpty()) {
                $dataPerTw[$tw] = $list;
            }
        }

        if (empty($dataPerTw)) {
            return back()->with('error', 'Belum ada data penilaian wilayah untuk tahun ' . $tahun);
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

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Rekap Tahunan');
        $this->fillSheetTahunan($sheet, $rekap, $tahun, $wilayah->nama, array_keys($dataPerTw));

        $filename = 'Rekap_Tahunan_Ranking_PST_' . preg_replace('/\s+/', '_', $wilayah->nama) . '_' . $tahun . '.xlsx';
        $tmpFile  = tempnam(sys_get_temp_dir(), 'pst_export_');

        $writer = new Xlsx($spreadsheet);
        $writer->save($tmpFile);

        return response()->download($tmpFile, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ])->deleteFileAfterSend(true);
    }

    // ─────────────────────────────────────────────────────────
    // FILL SHEET — versi simpel: Peringkat, Nama, 4 komponen,
    // Nilai, Grade (sama seperti tabel "Ranking Petugas — Wilayah
    // Ini" di web), dengan struktur rapi: border penuh, kolom rata,
    // header beku, dan kotak keterangan terpisah.
    // ─────────────────────────────────────────────────────────
    private function fillSheetRanking($sheet, $rankingWilayah, string $periode, string $namaWilayah): void
    {
        $judulPeriode = $this->formatJudulPeriode($periode);
        $colEnd       = 'H';
        $jumlahData   = $rankingWilayah->count();

        // ── BARIS 1: Judul utama ──
        $sheet->mergeCells("A1:{$colEnd}1");
        $sheet->setCellValue('A1', "RANKING PETUGAS PST {$judulPeriode}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // ── BARIS 2: Sub judul wilayah ──
        $sheet->mergeCells("A2:{$colEnd}2");
        $sheet->setCellValue('A2', "Wilayah: {$namaWilayah}");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10.5, 'color' => ['rgb' => '555555']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── BARIS 3: Info jumlah petugas & tanggal cetak ──
        $sheet->mergeCells("A3:{$colEnd}3");
        $sheet->setCellValue('A3', $jumlahData . ' petugas terevaluasi · Dicetak ' . $this->tanggalIndo(now()));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '888888']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // ── BARIS 5: Header kolom (baris 4 dikosongkan sbg jarak) ──
        $headerRow = 5;
        $headers   = ['Peringkat', 'Nama Petugas', 'Sikap Kerja', 'Ind. Hasil', 'Ind. Proses', 'Mutu Pelayanan', 'Nilai Akhir', 'Grade'];
        foreach ($headers as $i => $label) {
            $sheet->setCellValue($this->columnLetter(1 + $i) . $headerRow, $label);
        }
        $sheet->getStyle("A{$headerRow}:{$colEnd}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10.5],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(28);

        // ── BARIS DATA: terurut nilai tertinggi, peringkat eksplisit ──
        $row        = $headerRow + 1;
        $rank       = 1;
        $rankColors = [1 => 'FFD700', 2 => 'D9D9D9', 3 => 'F4C28F'];
        foreach ($rankingWilayah as $ev) {
            $sheet->setCellValue("A{$row}", $rank);
            $sheet->setCellValue("B{$row}", $ev->petugas->user->name ?? '-');
            $sheet->setCellValue("C{$row}", $ev->rata_sikap_kerja      !== null ? round($ev->rata_sikap_kerja, 2)      : '-');
            $sheet->setCellValue("D{$row}", $ev->rata_indikator_hasil   !== null ? round($ev->rata_indikator_hasil, 2)  : '-');
            $sheet->setCellValue("E{$row}", $ev->rata_indikator_proses  !== null ? round($ev->rata_indikator_proses, 2) : '-');
            $sheet->setCellValue("F{$row}", $ev->rata_mutu_pelayanan    !== null ? round($ev->rata_mutu_pelayanan, 2)   : '-');
            $sheet->setCellValue("G{$row}", $ev->jumlah_nilai           !== null ? round($ev->jumlah_nilai, 2)          : '-');
            $sheet->setCellValue("H{$row}", $ev->grade ?? '-');

            // Baris selang-seling (zebra) supaya mudah dibaca
            if (!isset($rankColors[$rank]) && $rank % 2 === 0) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F5FA']],
                ]);
            }

            $sheet->getStyle("A{$row}:H{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("C{$row}:{$colEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $sheet->getRowDimension($row)->setRowHeight(22);

            if (isset($rankColors[$rank])) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rankColors[$rank]]],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1A1A1A']],
                ]);
            }

            $row++;
            $rank++;
        }

        $dataEndRow = $row - 1;

        // ── Border tabel penuh (header + data) ──
        $sheet->getStyle("A{$headerRow}:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B0B0']]],
        ]);
        // Garis luar tabel lebih tebal
        $sheet->getStyle("A{$headerRow}:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F497D']]],
        ]);

        // ── Kotak keterangan grade (dengan border, terpisah dari tabel) ──
        $row += 2;
        $ketStart = $row;
        $sheet->mergeCells("A{$row}:{$colEnd}{$row}");
        $sheet->setCellValue("A{$row}", 'Keterangan Grade');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDEDED']],
        ]);
        $row++;
        foreach ([
            'SANGAT BAIK (SB)' => '> 95',
            'BAIK (B)'         => '86 – 95',
            'CUKUP (C)'        => '66 – 85',
            'KURANG (K)'       => '51 – 65',
            'SANGAT KURANG (SK)' => '< 50',
        ] as $label => $range) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->mergeCells("B{$row}:{$colEnd}{$row}");
            $sheet->setCellValue("B{$row}", $range);
            $sheet->getStyle("A{$row}")->getFont()->setSize(9.5);
            $sheet->getStyle("B{$row}")->getFont()->setSize(9.5);
            $row++;
        }
        $ketEnd = $row - 1;
        $sheet->getStyle("A{$ketStart}:{$colEnd}{$ketEnd}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Freeze pane di bawah header agar mudah dibaca saat scroll ──
        $sheet->freezePane('B' . ($headerRow + 1));

        // ── Lebar kolom ──
        $sheet->getColumnDimension('A')->setWidth(11);
        $sheet->getColumnDimension('B')->setWidth(28);
        foreach (['C', 'D', 'E', 'F'] as $col) {
            $sheet->getColumnDimension($col)->setWidth(15);
        }
        $sheet->getColumnDimension('G')->setWidth(13);
        $sheet->getColumnDimension('H')->setWidth(10);
    }

    // ─────────────────────────────────────────────────────────
    // FILL SHEET TAHUNAN — ringkasan nilai 4 triwulan per petugas
    // (sama persis dgn KoordinatorPenilaianController::fillSheetTahunan,
    // dipakai di sini agar petugas bisa mengunduh rekap yg sama untuk
    // wilayahnya sendiri tanpa perlu akses menu koordinator)
    // ─────────────────────────────────────────────────────────
    private function fillSheetTahunan($sheet, $rekap, $tahun, string $namaWilayah, array $twTersedia): void
    {
        $twCols     = ['C', 'D', 'E', 'F'];
        $colEnd     = 'I';
        $jumlahData = $rekap->count();

        // ── BARIS 1: Judul utama ──
        $sheet->mergeCells("A1:{$colEnd}1");
        $sheet->setCellValue('A1', "REKAP TAHUNAN RANKING PETUGAS PST TAHUN {$tahun}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 14, 'name' => 'Calibri'],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(26);

        // ── BARIS 2: Sub judul wilayah ──
        $sheet->mergeCells("A2:{$colEnd}2");
        $sheet->setCellValue('A2', "Wilayah: {$namaWilayah}");
        $sheet->getStyle('A2')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 10.5, 'color' => ['rgb' => '555555']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(2)->setRowHeight(18);

        // ── BARIS 3: Info jumlah petugas, TW tersedia & tanggal cetak ──
        $twLabel = implode(', ', array_map(fn($t) => "TW{$t}", $twTersedia));
        $sheet->mergeCells("A3:{$colEnd}3");
        $sheet->setCellValue('A3', $jumlahData . ' petugas · Triwulan tersedia: ' . $twLabel . ' · Dicetak ' . $this->tanggalIndo(now()));
        $sheet->getStyle('A3')->applyFromArray([
            'font'      => ['italic' => true, 'size' => 9, 'color' => ['rgb' => '888888']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);
        $sheet->getRowDimension(3)->setRowHeight(16);

        // ── BARIS 5: Header kolom (baris 4 dikosongkan sbg jarak) ──
        $headerRow = 5;
        $sheet->setCellValue("A{$headerRow}", 'Peringkat');
        $sheet->setCellValue("B{$headerRow}", 'Nama Petugas');
        foreach ($twCols as $i => $col) {
            $sheet->setCellValue("{$col}{$headerRow}", 'Triwulan ' . ($i + 1));
        }
        $sheet->setCellValue("G{$headerRow}", 'Rata-rata Tahunan');
        $sheet->setCellValue("H{$headerRow}", 'Grade');
        $sheet->setCellValue("I{$headerRow}", 'Peringkat');

        $sheet->getStyle("A{$headerRow}:{$colEnd}{$headerRow}")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10.5],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => '1F497D']],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER, 'wrapText' => true],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'FFFFFF']]],
        ]);
        $sheet->getRowDimension($headerRow)->setRowHeight(30);

        // Redupkan kolom TW yg datanya tidak tersedia pada tahun ini
        foreach ($twCols as $i => $col) {
            if (!in_array($i + 1, $twTersedia, true)) {
                $sheet->getStyle("{$col}{$headerRow}")->getFont()->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('FFB8C6E0'));
            }
        }

        // ── BARIS DATA ──
        $row        = $headerRow + 1;
        $rankColors = [1 => 'FFD700', 2 => 'D9D9D9', 3 => 'F4C28F'];
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

            // Baris selang-seling (zebra) supaya mudah dibaca
            if (!isset($rankColors[$peringkat]) && $peringkat % 2 === 0) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'F2F5FA']],
                ]);
            }

            $sheet->getStyle("A{$row}:{$colEnd}{$row}")->getAlignment()->setVertical(Alignment::VERTICAL_CENTER);
            $sheet->getStyle("A{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("A{$row}")->getFont()->setBold(true);
            $sheet->getStyle("C{$row}:{$colEnd}{$row}")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("G{$row}")->getFont()->setBold(true)->setSize(11);
            $sheet->getStyle("H{$row}")->getFont()->setBold(true);
            $sheet->getStyle("I{$row}")->getFont()->setBold(true);
            $sheet->getRowDimension($row)->setRowHeight(22);

            if (isset($rankColors[$peringkat])) {
                $sheet->getStyle("A{$row}:{$colEnd}{$row}")->applyFromArray([
                    'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => $rankColors[$peringkat]]],
                    'font' => ['bold' => true, 'color' => ['rgb' => '1A1A1A']],
                ]);
            }

            $row++;
            $peringkat++;
        }

        $dataEndRow = $row - 1;

        // ── Border tabel penuh + garis luar tebal ──
        $sheet->getStyle("A{$headerRow}:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'B0B0B0']]],
        ]);
        $sheet->getStyle("A{$headerRow}:{$colEnd}{$dataEndRow}")->applyFromArray([
            'borders' => ['outline' => ['borderStyle' => Border::BORDER_MEDIUM, 'color' => ['rgb' => '1F497D']]],
        ]);

        // ── Kotak keterangan grade ──
        $row += 2;
        $ketStart = $row;
        $sheet->mergeCells("A{$row}:{$colEnd}{$row}");
        $sheet->setCellValue("A{$row}", 'Keterangan Grade');
        $sheet->getStyle("A{$row}")->applyFromArray([
            'font' => ['bold' => true, 'size' => 10],
            'fill' => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['rgb' => 'EDEDED']],
        ]);
        $row++;
        foreach ([
            'SANGAT BAIK (SB)'    => '> 95',
            'BAIK (B)'            => '86 – 95',
            'CUKUP (C)'           => '66 – 85',
            'KURANG (K)'          => '51 – 65',
            'SANGAT KURANG (SK)'  => '< 50',
        ] as $label => $range) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->mergeCells("B{$row}:{$colEnd}{$row}");
            $sheet->setCellValue("B{$row}", $range);
            $sheet->getStyle("A{$row}")->getFont()->setSize(9.5);
            $sheet->getStyle("B{$row}")->getFont()->setSize(9.5);
            $row++;
        }
        $ketEnd = $row - 1;
        $sheet->getStyle("A{$ketStart}:{$colEnd}{$ketEnd}")->applyFromArray([
            'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN, 'color' => ['rgb' => 'CCCCCC']]],
        ]);

        // ── Catatan kaki ──
        $row++;
        $sheet->mergeCells("A{$row}:{$colEnd}{$row}");
        $sheet->setCellValue("A{$row}", '*) Rata-rata tahunan dihitung dari triwulan yang sudah dievaluasi koordinator (' . $twLabel . ').');
        $sheet->getStyle("A{$row}")->getFont()->setItalic(true)->setSize(9)->setColor(new \PhpOffice\PhpSpreadsheet\Style\Color('777777'));

        // ── Freeze pane di bawah header agar mudah dibaca saat scroll ──
        $sheet->freezePane('B' . ($headerRow + 1));

        // ── Lebar kolom ──
        $sheet->getColumnDimension('A')->setWidth(11);
        $sheet->getColumnDimension('B')->setWidth(28);
        foreach ($twCols as $col) {
            $sheet->getColumnDimension($col)->setWidth(12);
        }
        $sheet->getColumnDimension('G')->setWidth(17);
        $sheet->getColumnDimension('H')->setWidth(10);
        $sheet->getColumnDimension('I')->setWidth(11);
    }

    // ─────────────────────────────────────────────────────────
    // HELPERS
    // ─────────────────────────────────────────────────────────
    private function columnLetter(int $col): string
    {
        return \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
    }

    // Format tanggal Indonesia tanpa bergantung pada locale aplikasi
    // (config('app.locale') defaultnya 'en', jadi translatedFormat()
    // bisa menghasilkan nama bulan Inggris kalau tidak di-set manual).
    private function tanggalIndo($date): string
    {
        $bulan = [
            1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April',
            5 => 'Mei', 6 => 'Juni', 7 => 'Juli', 8 => 'Agustus',
            9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember',
        ];
        return $date->day . ' ' . $bulan[(int) $date->month] . ' ' . $date->year;
    }

    private function formatJudulPeriode(string $periode): string
    {
        $roman = ['I', 'II', 'III', 'IV'];
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            return 'TRIWULAN ' . $roman[(int) $m[2] - 1] . ' TAHUN ' . $m[1];
        }
        return strtoupper($periode);
    }

    public function show(Request $request, string $periode)
    {
        $petugasId = $this->petugasId();

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->firstOrFail();

        $periodeOptions = NilaiEvaluasiController::periodeOptions();

        return view('petugas.penilaian.show', compact(
            'evaluasi', 'periode', 'periodeOptions'
        ));
    }
}