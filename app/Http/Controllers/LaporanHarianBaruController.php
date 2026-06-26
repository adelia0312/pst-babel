<?php

namespace App\Http\Controllers;

use App\Models\LaporanHarianBaru;
use App\Models\LaporanTemplate;
use App\Models\JadwalPetugas;
use App\Models\Petugas;
use App\Models\Wilayah;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Str;

class LaporanHarianBaruController extends Controller
{
    // ────────────────────────────────────────────────────────────────
    //  ADMIN – CRUD Template Pertanyaan
    // ────────────────────────────────────────────────────────────────

    /** Halaman daftar template + list laporan semua wilayah */
    public function adminIndex(Request $request)
    {
        // Admin melihat SEMUA template (termasuk yang belum berlaku) untuk keperluan kelola
        $templates = LaporanTemplate::orderBy('urutan')->get();

        $query = LaporanHarianBaru::with(['user', 'wilayah'])->orderByDesc('tanggal')->orderBy('sesi');

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporan   = $query->paginate(30)->withQueryString();
        $wilayahs  = Wilayah::orderBy('nama')->get();

        $stats = [
            'total'     => LaporanHarianBaru::count(),
            'submitted' => LaporanHarianBaru::where('status', 'submitted')->count(),
            'approved'  => LaporanHarianBaru::where('status', 'approved')->count(),
            'rejected'  => LaporanHarianBaru::where('status', 'rejected')->count(),
        ];

        return view('admin.laporanharian.index', compact(
            'templates', 'laporan', 'wilayahs', 'stats'
        ));
    }

    /** Export laporan harian ke XLSX (format resmi PST) — TIDAK BERUBAH */
    public function adminExport(Request $request)
    {
        $query = LaporanHarianBaru::with(['user', 'wilayah'])
            ->orderBy('tanggal')
            ->orderBy('sesi');

        if ($request->filled('wilayah_id')) {
            $query->where('wilayah_id', $request->wilayah_id);
        }
        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporan   = $query->get();
        $templates = LaporanTemplate::orderBy('urutan')->get();

        $tahun    = $request->filled('bulan') ? date('Y', strtotime($request->bulan)) : now()->year;
        $filename = 'laporan-harian-admin-' . now()->format('Ymd-His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Harian');

        $numCols = 5 + $templates->count() + 1;
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numCols);

        // ── Row 1: Judul ──────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', "LAPORAN HASIL PELAYANAN PETUGAS PST {$tahun} — BPS PANGKALPINANG");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12, 'name' => 'Arial'],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A3A6B']],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Row 2: spacer ─────────────────────────────────────
        $sheet->getRowDimension(2)->setRowHeight(6);

        // ── Row 3: Header kolom ───────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(40);

        $tmplColors = ['1A3A6B', '7B2020', '4A2080', 'C97A00', '444444', '1F7A4D'];
        $fixedCols  = [
            ['Bulan',            '1F7A4D', 14],
            ['Tanggal',          '1F7A4D', 10],
            ['Hari',             '1F7A4D', 12],
            ['Sesi',             '1F7A4D', 10],
            ['Nama Petugas PST', '1F7A4D', 24],
        ];

        foreach ($fixedCols as $i => [$label, $color, $width]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}3", $label);
            $sheet->getStyle("{$col}3")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        foreach ($templates as $ti => $tmpl) {
            $colIdx = 6 + $ti;
            $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $color  = $tmplColors[$ti % count($tmplColors)];
            $sheet->setCellValue("{$col}3", $tmpl->judul);
            $sheet->getStyle("{$col}3")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
            $sheet->getColumnDimensionByColumn($colIdx)->setWidth(28);
        }

        $statusColIdx = 6 + $templates->count();
        $statusCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($statusColIdx);
        $sheet->setCellValue("{$statusCol}3", 'Status');
        $sheet->getStyle("{$statusCol}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '444444']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
        ]);
        $sheet->getColumnDimensionByColumn($statusColIdx)->setWidth(14);

        // ── Data rows ─────────────────────────────────────────
        $hariMap   = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $statusMap = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'submitted' => 'Menunggu'];

        foreach ($laporan as $ri => $l) {
            $row     = 4 + $ri;
            $bgColor = $ri % 2 === 0 ? 'F7F9FC' : 'FFFFFF';
            $rowData = [
                \Carbon\Carbon::parse($l->tanggal)->isoFormat('MMMM'),
                \Carbon\Carbon::parse($l->tanggal)->day,
                $hariMap[\Carbon\Carbon::parse($l->tanggal)->format('l')] ?? '-',
                ucfirst($l->sesi),
                $l->user?->name ?? $l->nama_petugas ?? '-',
            ];

            foreach ($rowData as $ci => $val) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $sheet->setCellValue("{$col}{$row}", $val);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font'    => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '000000']],
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
                ]);
            }

            $jawaban = $l->jawaban ?? [];
            foreach ($templates as $ti => $tmpl) {
                $colIdx = 6 + $ti;
                $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $val    = $jawaban[$tmpl->id] ?? $jawaban[(string)$tmpl->id] ?? '-';
                $sheet->setCellValue("{$col}{$row}", $val);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '000000']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                    'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
                    'alignment' => ['wrapText' => true, 'vertical' => 'center'],
                ]);
            }

            $statusVal   = $statusMap[$l->status] ?? ucfirst($l->status);
            $statusColor = match($l->status) { 'approved' => '166534', 'rejected' => '991B1B', default => '92400E' };
            $sheet->setCellValue("{$statusCol}{$row}", $statusVal);
            $sheet->getStyle("{$statusCol}{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $statusColor]],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
        }

        $sheet->freezePane('A4');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    /**
     * Simpan template baru.
     * PERUBAHAN: set berlaku_mulai = hari ini otomatis saat pertanyaan baru dibuat.
     */
    public function adminTemplateStore(Request $request)
    {
        $request->validate([
            'judul' => 'required|string|max:255',
            'tipe'  => 'required|in:teks,pilihan',
        ]);

        if ($request->tipe === 'pilihan') {
            $request->validate([
                'opsi'   => 'required|array|min:1',
                'opsi.*' => 'required|string|max:200',
            ]);
        }

        $urutan = LaporanTemplate::max('urutan') + 1;

      LaporanTemplate::create([
    'judul'         => $request->judul,
    'deskripsi'     => $request->deskripsi,
    'tipe'          => $request->tipe,
    'opsi'          => ($request->tipe === 'pilihan') ? array_values(array_filter($request->opsi ?? [])) : null,
    'wajib'         => $request->boolean('wajib', true),
    'urutan'        => $urutan,
    'aktif'         => true,
    'berlaku_mulai' => now('Asia/Jakarta')->toDateString(),
]);

        return redirect()->route('admin.laporanharian.index', ['tab' => 'template'])
            ->with('success', 'Pertanyaan berhasil ditambahkan.');
    }

    /**
     * Update template.
     * berlaku_mulai TIDAK diubah saat edit — agar laporan lama yang sudah pakai
     * pertanyaan ini tidak terdampak. Hanya judul/deskripsi/tipe/opsi yang boleh berubah.
     */
    public function adminTemplateUpdate(Request $request, $id)
    {
        $template = LaporanTemplate::findOrFail($id);

        $request->validate([
            'judul' => 'required|string|max:255',
            'tipe'  => 'required|in:teks,pilihan',
            'opsi'  => 'nullable|array',
            'opsi.*'=> 'nullable|string|max:200',
        ]);

        // berlaku_mulai TIDAK disertakan di sini agar tidak berubah
        $template->update([
            'judul'     => $request->judul,
            'deskripsi' => $request->deskripsi,
            'tipe'      => $request->tipe,
            'opsi'      => ($request->tipe === 'pilihan') ? array_values(array_filter($request->opsi ?? [])) : null,
            'wajib'     => $request->boolean('wajib', true),
            'aktif'     => $request->boolean('aktif', true),
        ]);

        return redirect()->route('admin.laporanharian.index', ['tab' => 'template'])
            ->with('success', 'Pertanyaan berhasil diperbarui.');
    }

    /** Hapus template — tidak berubah */
    public function adminTemplateDestroy($id)
    {
        LaporanTemplate::findOrFail($id)->delete();
        return redirect()->route('admin.laporanharian.index', ['tab' => 'template'])
            ->with('success', 'Pertanyaan dihapus.');
    }

    /** Ubah urutan template (drag & drop) — tidak berubah */
    public function adminTemplateReorder(Request $request)
    {
        foreach ($request->urutan as $item) {
            LaporanTemplate::where('id', $item['id'])->update(['urutan' => $item['urutan']]);
        }
        return response()->json(['ok' => true]);
    }

    /** Detail laporan (admin view) — tidak berubah */
    public function adminDetail($id)
    {
        $laporan   = LaporanHarianBaru::with(['user', 'wilayah', 'reviewer'])->findOrFail($id);
        $templates = LaporanTemplate::orderBy('urutan')->get();
        return view('admin.laporanharian.detail', compact('laporan', 'templates'));
    }

    // ────────────────────────────────────────────────────────────────
    //  PETUGAS – Isi Laporan Harian
    // ────────────────────────────────────────────────────────────────

    /** Daftar laporan milik petugas — tidak berubah */
    public function petugasIndex()
    {
        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('user_id', $user->id)
            ->orderByDesc('tanggal')
            ->orderBy('sesi')
            ->paginate(20);

        $laporanHariIni = LaporanHarianBaru::where('user_id', $user->id)
            ->where('tanggal', now('Asia/Jakarta')->toDateString())
            ->whereIn('status', ['submitted', 'approved'])
            ->exists();

        $adaJadwal = JadwalPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', now('Asia/Jakarta')->toDateString())
            ->exists();

        $templates = LaporanTemplate::orderBy('urutan')->get();

        $stats = [
            'total'     => LaporanHarianBaru::where('user_id', $user->id)->count(),
            'submitted' => LaporanHarianBaru::where('user_id', $user->id)->where('status', 'submitted')->count(),
            'approved'  => LaporanHarianBaru::where('user_id', $user->id)->where('status', 'approved')->count(),
            'rejected'  => LaporanHarianBaru::where('user_id', $user->id)->where('status', 'rejected')->count(),
        ];

        return view('petugas.laporanharian.index', compact('laporan', 'user', 'laporanHariIni', 'adaJadwal', 'templates', 'stats'));
    }

    /** Detail laporan milik petugas sendiri — tidak berubah */
    public function petugasShow($id)
    {
        $user      = Auth::user();
        $laporan   = LaporanHarianBaru::where('user_id', $user->id)->findOrFail($id);
        // Gunakan scope aktifPadaTanggal agar hanya tampil pertanyaan yang relevan
        $templates = LaporanTemplate::aktifPadaTanggal($laporan->tanggal->toDateString())->get();

        return view('petugas.laporanharian.show', compact('laporan', 'templates', 'user'));
    }

    /**
     * Form buat laporan baru.
     * PERUBAHAN: gunakan aktifPadaTanggal(hari_ini) agar hanya pertanyaan
     * yang berlaku hari ini yang tampil di form.
     */
    public function petugasCreate()
    {
        $user    = Auth::user();
        $petugas = Petugas::where('user_id', $user->id)->first();
        $tanggal = now('Asia/Jakarta');
        $jam     = $tanggal->hour;

        if ($jam >= 7 && $jam < 12) {
            $sesiAktif = 'Pagi';
        } elseif ($jam >= 12 && $jam < 17) {
            $sesiAktif = 'Siang';
        } else {
            return redirect()->route('petugas.laporan.harian.index')
                ->with('error', 'Input laporan hanya tersedia pada jam kerja: 07.00–12.00 (Pagi) dan 12.00–17.00 (Siang).');
        }

        $adaJadwal = JadwalPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $tanggal->toDateString())
            ->exists();

        if (!$adaJadwal) {
            return redirect()->route('petugas.laporan.harian.index')
                ->with('error', 'Anda tidak terjadwal bertugas hari ini. Laporan hanya bisa diisi sesuai jadwal.');
        }

        // ← PERUBAHAN: hanya tampilkan pertanyaan yang berlaku hari ini
        $templates = LaporanTemplate::aktifPadaTanggal($tanggal->toDateString())->get();

        $sudahAda = LaporanHarianBaru::where('user_id', $user->id)
            ->where('tanggal', $tanggal->toDateString())
            ->where('sesi', $sesiAktif)
            ->exists();

        return view('petugas.laporanharian.create', compact(
            'user', 'petugas', 'templates', 'tanggal', 'sesiAktif', 'sudahAda'
        ));
    }

    /**
     * Simpan laporan petugas.
     * PERUBAHAN: gunakan aktifPadaTanggal(tanggal_laporan) saat mengumpulkan jawaban.
     */
    public function petugasStore(Request $request)
    {
        $user    = Auth::user();
        $petugas = Petugas::where('user_id', $user->id)->first();

        $request->validate([
            'tanggal' => 'required|date',
        ]);

        $jam       = now('Asia/Jakarta')->hour;
        $sesiAktif = $jam < 12 ? 'Pagi' : 'Siang';

        $adaJadwal = JadwalPetugas::where('user_id', $user->id)
            ->whereDate('tanggal', $request->tanggal)
            ->exists();

        if (!$adaJadwal) {
            return redirect()->route('petugas.laporan.harian.index')
                ->with('error', 'Anda tidak terjadwal bertugas hari ini. Laporan tidak dapat dikirim.');
        }

        $sudahAda = LaporanHarianBaru::where('user_id', $user->id)
            ->where('tanggal', $request->tanggal)
            ->where('sesi', $sesiAktif)
            ->exists();

        if ($sudahAda) {
            return back()->with('error', 'Laporan sesi ' . $sesiAktif . ' hari ini sudah pernah dikirim.');
        }

        // ← PERUBAHAN: hanya simpan jawaban untuk pertanyaan yang berlaku pada tanggal laporan
        $templates = LaporanTemplate::aktifPadaTanggal($request->tanggal)->get();
        $jawaban   = [];
        foreach ($templates as $tpl) {
            $jawaban[$tpl->id] = $request->input('jawaban_' . $tpl->id, '');
        }

        LaporanHarianBaru::create([
            'user_id'      => $user->id,
            'wilayah_id'   => $petugas?->wilayah_id,
            'nama_petugas' => $user->name,
            'tanggal'      => $request->tanggal,
            'hari'         => LaporanHarianBaru::namaHari($request->tanggal),
            'sesi'         => $sesiAktif,
            'jawaban'      => $jawaban,
            'status'       => $request->input('action') === 'submit' ? 'submitted' : 'draft',
        ]);

        $msg = $request->input('action') === 'submit'
            ? 'Laporan berhasil dikirim ke koordinator!'
            : 'Draft laporan tersimpan.';

        return redirect()->route('petugas.laporan.harian.index')->with('success', $msg);
    }

    /**
     * Edit laporan (hanya draft/rejected).
     * PERUBAHAN: gunakan aktifPadaTanggal(tanggal_laporan) agar pertanyaan baru
     * tidak muncul di form edit laporan lama.
     */
    public function petugasEdit($id)
    {
        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('user_id', $user->id)->findOrFail($id);

        if (!in_array($laporan->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Laporan yang sudah disubmit tidak bisa diedit.');
        }

        // ← PERUBAHAN: filter pertanyaan berdasarkan tanggal laporan yang sedang diedit
        $templates = LaporanTemplate::aktifPadaTanggal($laporan->tanggal->toDateString())->get();

        return view('petugas.laporanharian.edit', compact('laporan', 'templates', 'user'));
    }

    /**
     * Update laporan petugas.
     * PERUBAHAN: gunakan aktifPadaTanggal(tanggal_laporan) saat mengumpulkan jawaban.
     */
    public function petugasUpdate(Request $request, $id)
    {
        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('user_id', $user->id)->findOrFail($id);

        if (!in_array($laporan->status, ['draft', 'rejected'])) {
            return back()->with('error', 'Laporan tidak bisa diubah.');
        }

        // ← PERUBAHAN: hanya proses jawaban untuk pertanyaan yang berlaku pada tanggal laporan
        $templates = LaporanTemplate::aktifPadaTanggal($laporan->tanggal->toDateString())->get();
        $jawaban   = [];
        foreach ($templates as $tpl) {
            $jawaban[$tpl->id] = $request->input('jawaban_' . $tpl->id, '');
        }

        $laporan->update([
            'jawaban' => $jawaban,
            'status'  => $request->input('action') === 'submit' ? 'submitted' : 'draft',
        ]);

        $msg = $request->input('action') === 'submit'
            ? 'Laporan berhasil dikirim ke koordinator!'
            : 'Draft laporan diperbarui.';

        return redirect()->route('petugas.laporan.harian.index')->with('success', $msg);
    }

    // ────────────────────────────────────────────────────────────────
    //  KOORDINATOR – Review Laporan Wilayahnya
    // ────────────────────────────────────────────────────────────────

    /** Daftar laporan wilayah koordinator — tidak berubah */
    public function koordinatorIndex(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        $query = LaporanHarianBaru::where('wilayah_id', $wilayahId)
            ->with('user')
            ->orderByDesc('tanggal')
            ->orderBy('sesi');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporan = $query->paginate(25)->withQueryString();

        $stats = [
            'total'     => LaporanHarianBaru::where('wilayah_id', $wilayahId)->count(),
            'submitted' => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'submitted')->count(),
            'approved'  => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'approved')->count(),
            'rejected'  => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'rejected')->count(),
        ];

        $tugasList = collect();
        $templates = LaporanTemplate::orderBy('urutan')->get();

        // Laporan yang menunggu review (untuk tab verifikasi)
        $laporanPending = LaporanHarianBaru::where('wilayah_id', $wilayahId)
            ->where('status', 'submitted')
            ->with('user')
            ->orderByDesc('tanggal')
            ->orderBy('sesi')
            ->get();

        return view('koordinator.laporanharian.index', compact('laporan', 'stats', 'user', 'tugasList', 'templates', 'laporanPending'));
    }

    public function koordinatorPolling(Request $request)
    {
        $wilayahId = Auth::user()->wilayah_id;

        $rows = LaporanHarianBaru::where('wilayah_id', $wilayahId)
            ->with('user')
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn($l) => [
                'id'      => $l->id,
                'nama'    => $l->user->name ?? '-',
                'sesi'    => ucfirst($l->sesi ?? '-'),
                'tanggal' => $l->tanggal ? \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') : '-',
                'status'  => $l->status,
            ]);

        $stats = [
            'total'     => LaporanHarianBaru::where('wilayah_id', $wilayahId)->count(),
            'submitted' => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'submitted')->count(),
            'approved'  => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'approved')->count(),
            'rejected'  => LaporanHarianBaru::where('wilayah_id', $wilayahId)->where('status', 'rejected')->count(),
        ];

        return response()->json(['rows' => $rows, 'stats' => $stats]);
    }

    /**
     * Detail laporan untuk koordinator.
     * PERUBAHAN: gunakan aktifPadaTanggal(tanggal_laporan) agar pertanyaan baru
     * yang belum ada waktu laporan dibuat tidak muncul di halaman review.
     */
    public function koordinatorDetail($id)
    {
        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('wilayah_id', $user->wilayah_id)
            ->with(['user', 'reviewer'])
            ->findOrFail($id);

        // ← PERUBAHAN: hanya tampilkan pertanyaan yang berlaku pada tanggal laporan ini
        $templates = LaporanTemplate::aktifPadaTanggal($laporan->tanggal->toDateString())->get();

        return view('koordinator.laporanharian.detail', compact('laporan', 'templates', 'user'));
    }

    /** Approve laporan — tidak berubah */
    public function koordinatorApprove(Request $request, $id)
    {
        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('wilayah_id', $user->wilayah_id)->findOrFail($id);

        $laporan->update([
            'status'              => 'approved',
            'catatan_koordinator' => $request->catatan_koordinator,
            'reviewed_by'         => $user->id,
            'reviewed_at'         => now(),
        ]);

        return back()->with('success', 'Laporan disetujui.');
    }

    /** Reject laporan — tidak berubah */
    public function koordinatorReject(Request $request, $id)
    {
        $request->validate(['catatan_koordinator' => 'required|string']);

        $user    = Auth::user();
        $laporan = LaporanHarianBaru::where('wilayah_id', $user->wilayah_id)->findOrFail($id);

        $laporan->update([
            'status'              => 'rejected',
            'catatan_koordinator' => $request->catatan_koordinator,
            'reviewed_by'         => $user->id,
            'reviewed_at'         => now(),
        ]);

        return back()->with('success', 'Laporan dikembalikan ke petugas.');
    }

    /** Export koordinator — TIDAK BERUBAH */
    public function koordinatorExport(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        $query = LaporanHarianBaru::with(['user', 'wilayah'])
            ->where('wilayah_id', $wilayahId)
            ->orderBy('tanggal')
            ->orderBy('sesi');

        if ($request->filled('bulan')) {
            $query->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                  ->whereYear('tanggal', date('Y', strtotime($request->bulan)));
        }
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $laporan   = $query->get();
        $templates = LaporanTemplate::orderBy('urutan')->get();

        $tahun    = $request->filled('bulan') ? date('Y', strtotime($request->bulan)) : now()->year;
        $wilayah  = $user->wilayah->nama ?? 'Wilayah';
        $filename = 'laporan-harian-' . \Illuminate\Support\Str::slug($wilayah) . '-' . now()->format('Ymd-His') . '.xlsx';

        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Laporan Harian');

        $numCols = 5 + $templates->count() + 1;
        $lastCol = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($numCols);

        // ── Row 1: Judul ──────────────────────────────────────
        $sheet->mergeCells("A1:{$lastCol}1");
        $sheet->setCellValue('A1', "LAPORAN HASIL PELAYANAN PETUGAS PST {$tahun} – {$wilayah}");
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 12, 'name' => 'Arial'],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '1A3A6B']],
            'alignment' => ['horizontal' => 'left', 'vertical' => 'center'],
        ]);
        $sheet->getRowDimension(1)->setRowHeight(24);

        // ── Row 2: spacer ─────────────────────────────────────
        $sheet->getRowDimension(2)->setRowHeight(6);

        // ── Row 3: Header kolom ───────────────────────────────
        $sheet->getRowDimension(3)->setRowHeight(40);

        $tmplColors = ['1A3A6B', '7B2020', '4A2080', 'C97A00', '444444', '1F7A4D'];
        $fixedCols  = [
            ['Bulan',            '1F7A4D', 14],
            ['Tanggal',          '1F7A4D', 10],
            ['Hari',             '1F7A4D', 12],
            ['Sesi',             '1F7A4D', 10],
            ['Nama Petugas PST', '1F7A4D', 24],
        ];

        foreach ($fixedCols as $i => [$label, $color, $width]) {
            $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($i + 1);
            $sheet->setCellValue("{$col}3", $label);
            $sheet->getStyle("{$col}3")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
            $sheet->getColumnDimensionByColumn($i + 1)->setWidth($width);
        }

        foreach ($templates as $ti => $tmpl) {
            $colIdx = 6 + $ti;
            $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
            $color  = $tmplColors[$ti % count($tmplColors)];
            $sheet->setCellValue("{$col}3", $tmpl->judul);
            $sheet->getStyle("{$col}3")->applyFromArray([
                'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $color]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center', 'wrapText' => true],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
            $sheet->getColumnDimensionByColumn($colIdx)->setWidth(28);
        }

        $statusColIdx = 6 + $templates->count();
        $statusCol    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($statusColIdx);
        $sheet->setCellValue("{$statusCol}3", 'Status');
        $sheet->getStyle("{$statusCol}3")->applyFromArray([
            'font'      => ['bold' => true, 'color' => ['rgb' => 'FFFFFF'], 'size' => 10, 'name' => 'Arial'],
            'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => '444444']],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
        ]);
        $sheet->getColumnDimensionByColumn($statusColIdx)->setWidth(14);

        // ── Data rows ─────────────────────────────────────────
        $hariMap   = [
            'Sunday' => 'Minggu', 'Monday' => 'Senin', 'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu', 'Thursday' => 'Kamis', 'Friday' => 'Jumat', 'Saturday' => 'Sabtu',
        ];
        $statusMap = ['approved' => 'Disetujui', 'rejected' => 'Ditolak', 'submitted' => 'Menunggu'];

        foreach ($laporan as $ri => $l) {
            $row     = 4 + $ri;
            $bgColor = $ri % 2 === 0 ? 'F7F9FC' : 'FFFFFF';
            $rowData = [
                \Carbon\Carbon::parse($l->tanggal)->isoFormat('MMMM'),
                \Carbon\Carbon::parse($l->tanggal)->day,
                $hariMap[\Carbon\Carbon::parse($l->tanggal)->format('l')] ?? '-',
                ucfirst($l->sesi),
                $l->user?->name ?? $l->nama_petugas ?? '-',
            ];

            foreach ($rowData as $ci => $val) {
                $col = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($ci + 1);
                $sheet->setCellValue("{$col}{$row}", $val);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font'    => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '000000']],
                    'fill'    => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                    'borders' => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
                ]);
            }

            $jawaban = $l->jawaban ?? [];
            foreach ($templates as $ti => $tmpl) {
                $colIdx = 6 + $ti;
                $col    = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIdx);
                $val    = $jawaban[$tmpl->id] ?? $jawaban[(string)$tmpl->id] ?? '-';
                $sheet->setCellValue("{$col}{$row}", $val);
                $sheet->getStyle("{$col}{$row}")->applyFromArray([
                    'font'      => ['name' => 'Arial', 'size' => 10, 'color' => ['rgb' => '000000']],
                    'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                    'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
                    'alignment' => ['wrapText' => true, 'vertical' => 'center'],
                ]);
            }

            $statusVal   = $statusMap[$l->status] ?? ucfirst($l->status);
            $statusColor = match($l->status) { 'approved' => '166534', 'rejected' => '991B1B', default => '92400E' };
            $sheet->setCellValue("{$statusCol}{$row}", $statusVal);
            $sheet->getStyle("{$statusCol}{$row}")->applyFromArray([
                'font'      => ['bold' => true, 'name' => 'Arial', 'size' => 10, 'color' => ['rgb' => $statusColor]],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['rgb' => $bgColor]],
                'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
                'borders'   => ['allBorders' => ['borderStyle' => 'thin', 'color' => ['rgb' => 'D0D0D0']]],
            ]);
        }

        $sheet->freezePane('A4');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);

        return response()->streamDownload(function () use ($writer) {
            $writer->save('php://output');
        }, $filename, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        ]);
    }

    public function adminPolling()
    {
        $rows = LaporanHarianBaru::with(['user', 'wilayah'])
            ->orderByDesc('id')
            ->limit(50)
            ->get()
            ->map(fn($l) => [
                'id'      => $l->id,
                'nama'    => $l->user->name ?? '-',
                'sesi'    => ucfirst($l->sesi ?? '-'),
                'wilayah' => $l->wilayah->nama ?? '-',
                'tanggal' => $l->tanggal ? \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') : '-',
                'status'  => $l->status,
            ]);

        $stats = [
            'total'     => LaporanHarianBaru::count(),
            'submitted' => LaporanHarianBaru::where('status', 'submitted')->count(),
            'approved'  => LaporanHarianBaru::where('status', 'approved')->count(),
            'rejected'  => LaporanHarianBaru::where('status', 'rejected')->count(),
        ];

        return response()->json(['rows' => $rows, 'stats' => $stats]);
    }

}