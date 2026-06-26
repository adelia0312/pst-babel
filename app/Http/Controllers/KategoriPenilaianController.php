<?php

namespace App\Http\Controllers;

use App\Models\KategoriPenilaian;
use Illuminate\Http\Request;

/**
 * KategoriPenilaianController
 *
 * Admin dapat menambah kategori penilaian baru (selain Komunikasi,
 * Kerja Sama, Inovatif, Kesopanan & Keramahan), lalu memilih komponen
 * induknya (Sikap Kerja / Indikator Hasil / Indikator Proses / Mutu
 * Pelayanan). Kategori baru ini langsung tersedia sebagai pilihan di
 * form Tambah/Edit Pertanyaan Survey Internal (lihat
 * AdminSurveyController::pertanyaanIndex, variabel $kategoriList).
 *
 * Kategori TIDAK PERNAH benar-benar dihapus dari database (hard
 * delete) jika sudah pernah dipakai — hanya dinonaktifkan
 * (is_active = false). Ini menjaga histori evaluasi triwulan lama
 * tetap valid karena snapshot nilainya tersimpan terpisah di tabel
 * evaluasi_kategori_nilai (lihat App\Models\EvaluasiKategoriNilai).
 *
 * Letak file: app/Http/Controllers/KategoriPenilaianController.php
 * Status     : FILE BARU
 */
class KategoriPenilaianController extends Controller
{
    public function index()
    {
        $kategoriPerKomponen = KategoriPenilaian::orderBy('urutan')
            ->get()
            ->groupBy('komponen');

        $komponenList = KategoriPenilaian::KOMPONEN_LIST;

        return view('admin.kategori-penilaian.index', compact(
            'kategoriPerKomponen', 'komponenList'
        ));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'nama'     => 'required|string|max:100',
            'kode'     => 'nullable|string|max:50|alpha_dash|unique:kategori_penilaian,kode',
            'komponen' => 'required|in:' . implode(',', array_keys(KategoriPenilaian::KOMPONEN_LIST)),
            'urutan'   => 'nullable|integer|min:0',
        ]);

        // Jika admin tidak mengisi kode manual, buat otomatis dari nama
        // (slug huruf kecil + underscore) supaya tetap konsisten dengan
        // format kategori bawaan ('komunikasi', 'kerja_sama', dst).
        if (empty($data['kode'])) {
            $data['kode'] = \Illuminate\Support\Str::slug($data['nama'], '_');
        }

        // Pastikan kode tetap unik walau auto-generate dari nama yang sama
        $kodeAsli = $data['kode'];
        $suffix   = 1;
        while (KategoriPenilaian::where('kode', $data['kode'])->exists()) {
            $suffix++;
            $data['kode'] = $kodeAsli . '_' . $suffix;
        }

        if (empty($data['urutan'])) {
            $data['urutan'] = KategoriPenilaian::max('urutan') + 1;
        }

        $data['sumber']    = 'tambahan';
        $data['is_active'] = true;

        $kategori = KategoriPenilaian::create($data);

        // Dipanggil juga via AJAX dari modal Tambah/Edit Pertanyaan
        // (lihat resources/views/admin/survey/pertanyaan.blade.php,
        // fungsi buatKategoriBaru()) — di sana admin bisa membuat
        // kategori baru tanpa pindah halaman. Jika request AJAX,
        // kembalikan JSON berisi kode & nama agar JS bisa langsung
        // menambahkan <option> baru ke dropdown yang sedang dibuka.
        if ($request->wantsJson() || $request->ajax()) {
            return response()->json([
                'ok'    => true,
                'kode'  => $kategori->kode,
                'nama'  => $kategori->nama,
            ]);
        }

        return back()->with('success', 'Kategori penilaian baru berhasil ditambahkan. Kategori ini sudah bisa dipilih saat membuat pertanyaan Survey Internal.');
    }

    public function update(Request $request, int $id)
    {
        $kategori = KategoriPenilaian::findOrFail($id);

        $data = $request->validate([
            'nama'     => 'required|string|max:100',
            'komponen' => 'required|in:' . implode(',', array_keys(KategoriPenilaian::KOMPONEN_LIST)),
            'urutan'   => 'nullable|integer|min:0',
        ]);

        // CATATAN HISTORI: mengubah nama/komponen di sini TIDAK mengubah
        // data evaluasi triwulan yang sudah tersimpan, karena setiap
        // nilai triwulan menyimpan snapshot nama & komponennya sendiri
        // di evaluasi_kategori_nilai (kolom nama_kategori_snapshot &
        // komponen_snapshot). Hanya kategori_penilaian.kode yang TIDAK
        // diizinkan diubah di sini, supaya tautan ke survey_pertanyaan
        // .kategori (yang menyimpan kode, bukan id) tidak putus.
        $kategori->update($data);

        return back()->with('success', 'Kategori penilaian berhasil diperbarui. Data evaluasi triwulan yang sudah ada tidak berubah.');
    }

    /**
     * Nonaktifkan kategori (BUKAN hapus). Kategori yang sudah pernah
     * dipakai di pertanyaan/evaluasi tidak boleh dihapus permanen agar
     * histori triwulan lama tetap aman.
     */
    public function toggle(int $id)
    {
        $kategori = KategoriPenilaian::findOrFail($id);
        $kategori->update(['is_active' => !$kategori->is_active]);

        $status = $kategori->is_active ? 'diaktifkan' : 'dinonaktifkan';
        return back()->with('success', "Kategori '{$kategori->nama}' berhasil {$status}.");
    }

    /**
     * Hapus permanen — HANYA diizinkan untuk kategori yang belum pernah
     * dipakai sama sekali (tidak ada pertanyaan & tidak ada nilai
     * evaluasi yang merujuk ke kategori ini). Ini mencegah penghapusan
     * yang bisa merusak histori grafik triwulan.
     */
    public function destroy(int $id)
    {
        $kategori = KategoriPenilaian::findOrFail($id);

        if ($kategori->sumber === 'bawaan') {
            return back()->with('error', 'Kategori bawaan tidak dapat dihapus, hanya bisa dinonaktifkan.');
        }

        $dipakaiDiPertanyaan = \App\Models\SurveyPertanyaan::where('kategori', $kategori->kode)->exists();
        $dipakaiDiEvaluasi   = $kategori->nilaiEvaluasi()->exists();

        if ($dipakaiDiPertanyaan || $dipakaiDiEvaluasi) {
            return back()->with('error', 'Kategori ini sudah pernah dipakai di pertanyaan atau data evaluasi, sehingga tidak bisa dihapus. Nonaktifkan saja agar histori triwulan tetap aman.');
        }

        $kategori->delete();
        return back()->with('success', 'Kategori penilaian berhasil dihapus.');
    }
}