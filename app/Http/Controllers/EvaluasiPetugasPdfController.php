<?php

namespace App\Http\Controllers;

use App\Models\EvaluasiPetugas;
use App\Models\Petugas;
use App\Models\User;
use App\Models\Wilayah;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class EvaluasiPetugasPdfController extends Controller
{
    // ── Parse periode string ───────────────────────────────────────
    private function parsePeriode(string $periode): array
    {
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            $tahun = (int) $m[1];
            $tw    = (int) $m[2];
            $bulan = match ($tw) {
                1 => [1, 2, 3],
                2 => [4, 5, 6],
                3 => [7, 8, 9],
                4 => [10, 11, 12],
            };
            return [$tahun, $bulan, $tw];
        }
        [$y, $bul] = explode('-', $periode);
        return [(int) $y, [(int) $bul], null];
    }

    // ── Label periode ──────────────────────────────────────────────
    private function labelPeriode(string $periode): string
    {
        if (preg_match('/^(\d{4})-TW([1-4])$/', $periode, $m)) {
            return "Triwulan {$m[2]} Tahun {$m[1]}";
        }
        try {
            return Carbon::createFromFormat('Y-m', $periode)->translatedFormat('F Y');
        } catch (\Exception $e) {
            return $periode;
        }
    }

    // ── Build data array untuk view PDF ───────────────────────────
    private function buildData(
        Petugas $petugas,
        ?EvaluasiPetugas $evaluasi,
        string $periode,
        ?User $koordinator,
        ?Wilayah $wilayah,
        string $cetakOleh
    ): array {
        return [
            'petugas'      => $petugas,
            'koordinator'  => $koordinator,
            'wilayah'      => $wilayah,
            'evaluasi'     => $evaluasi,
            'periode'      => $periode,
            'periodeLabel' => $this->labelPeriode($periode),
            'cetakOleh'    => $cetakOleh,
            'tanggalCetak' => now()->format('d/m/Y H:i'),
        ];
    }

    // ── Render PDF ─────────────────────────────────────────────────
    private function renderPdf(array $data, string $namaPetugas, string $periode)
    {
        $pdf = Pdf::loadView('koordinator.evaluasi.pdf', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 150,
            ]);

        $filename = 'evaluasi_' . Str::slug($namaPetugas) . '_' . $periode . '.pdf';

        return $pdf->download($filename);
    }

    // ════════════════════════════════════════════════════════════════
    //  PETUGAS — cetak PDF evaluasi milik dirinya sendiri
    //  Route: GET /petugas/penilaian/{periode}/pdf
    //  Name:  petugas.penilaian.pdf
    // ════════════════════════════════════════════════════════════════
    public function petugasPdf(Request $request, string $periode)
    {
        $user    = Auth::user();
        $petugas = $user->petugas;

        abort_if(!$petugas, 403, 'Data petugas tidak ditemukan.');

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugas->id)
            ->where('periode', $periode)
            ->firstOrFail();

        abort_if(
            $evaluasi->status !== 'selesai',
            403,
            'Evaluasi belum selesai, PDF belum bisa dicetak.'
        );

        $wilayah     = Wilayah::find($user->wilayah_id);
        $koordinator = User::find($evaluasi->koordinator_id);

        $data = $this->buildData(
            $petugas, $evaluasi, $periode,
            $koordinator, $wilayah,
            $user->name
        );

        return $this->renderPdf($data, $user->name ?? 'petugas', $periode);
    }

    // ════════════════════════════════════════════════════════════════
    //  PETUGAS — cetak PDF transkrip 1 TAHUN (rekap semua triwulan)
    //  Route: GET /petugas/penilaian/tahun/{tahun}/pdf
    //  Name:  petugas.penilaian.pdf.tahunan
    // ════════════════════════════════════════════════════════════════
    public function petugasPdfTahunan(Request $request, string $tahun)
    {
        $user    = Auth::user();
        $petugas = $user->petugas;

        abort_if(!$petugas, 403, 'Data petugas tidak ditemukan.');
        abort_unless(preg_match('/^\d{4}$/', $tahun), 404);

        $evaluasiTahun = EvaluasiPetugas::where('petugas_id', $petugas->id)
            ->where('periode', 'like', $tahun . '-TW%')
            ->where('status', 'selesai')
            ->whereNotNull('jumlah_nilai')
            ->orderBy('periode')
            ->get();

        abort_if(
            $evaluasiTahun->isEmpty(),
            404,
            'Belum ada evaluasi yang selesai pada tahun ' . $tahun . '.'
        );

        $wilayah     = Wilayah::find($user->wilayah_id);
        $koordinator = User::find($evaluasiTahun->last()->koordinator_id);

        // ── Rata-rata tahunan: rata-rata dari rata-rata 4 komponen tiap
        //    triwulan yang sudah selesai (konsisten dgn trendPerTahun
        //    di PetugasPenilaianController) ──
        $avgSikap  = $evaluasiTahun->whereNotNull('rata_sikap_kerja')->avg('rata_sikap_kerja');
        $avgHasil  = $evaluasiTahun->whereNotNull('rata_indikator_hasil')->avg('rata_indikator_hasil');
        $avgProses = $evaluasiTahun->whereNotNull('rata_indikator_proses')->avg('rata_indikator_proses');
        $avgMutu   = $evaluasiTahun->whereNotNull('rata_mutu_pelayanan')->avg('rata_mutu_pelayanan');

        $komponenTahun = collect([$avgSikap, $avgHasil, $avgProses, $avgMutu])->filter(fn ($v) => $v !== null);
        $nilaiTahun    = $komponenTahun->isNotEmpty() ? round($komponenTahun->avg(), 4) : null;
        $gradeTahun    = EvaluasiPetugas::hitungGrade($nilaiTahun);

        $data = [
            'petugas'       => $petugas,
            'koordinator'   => $koordinator,
            'wilayah'       => $wilayah,
            'tahun'         => $tahun,
            'evaluasiTahun' => $evaluasiTahun,
            'avgSikap'      => $avgSikap  !== null ? round($avgSikap, 4)  : null,
            'avgHasil'      => $avgHasil  !== null ? round($avgHasil, 4)  : null,
            'avgProses'     => $avgProses !== null ? round($avgProses, 4) : null,
            'avgMutu'       => $avgMutu   !== null ? round($avgMutu, 4)   : null,
            'nilaiTahun'    => $nilaiTahun,
            'gradeTahun'    => $gradeTahun,
            'cetakOleh'     => $user->name,
            'tanggalCetak'  => now()->format('d/m/Y H:i'),
        ];

        $pdf = Pdf::loadView('koordinator.evaluasi.pdf_tahunan', $data)
            ->setPaper('A4', 'portrait')
            ->setOptions([
                'defaultFont'          => 'DejaVu Sans',
                'isHtml5ParserEnabled' => true,
                'isRemoteEnabled'      => true,
                'dpi'                  => 150,
            ]);

        $filename = 'transkrip_tahunan_' . Str::slug($user->name ?? 'petugas') . '_' . $tahun . '.pdf';

        return $pdf->download($filename);
    }

    // ════════════════════════════════════════════════════════════════
    //  KOORDINATOR — cetak PDF evaluasi petugas di wilayahnya
    //  Route: GET /koordinator/nilai-evaluasi/{petugasId}/pdf
    //  Name:  koordinator.nilai-evaluasi.pdf
    // ════════════════════════════════════════════════════════════════
    public function koordinatorPdf(Request $request, int $petugasId)
    {
        $koordinator = Auth::user();
        $periode     = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $petugas     = Petugas::with('user')->findOrFail($petugasId);

        abort_if(
            optional($petugas->user)->wilayah_id !== (int) $koordinator->wilayah_id,
            403,
            'Anda tidak berhak mencetak evaluasi petugas ini.'
        );

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->first();

        $wilayah = Wilayah::find($koordinator->wilayah_id);

        $data = $this->buildData(
            $petugas, $evaluasi, $periode,
            $koordinator, $wilayah,
            $koordinator->name
        );

        return $this->renderPdf($data, optional($petugas->user)->name ?? 'petugas', $periode);
    }

    // ════════════════════════════════════════════════════════════════
    //  ADMIN — cetak PDF evaluasi petugas manapun
    //  Route: GET /admin/penilaian/{petugasId}/pdf
    //  Name:  admin.penilaian.pdf
    // ════════════════════════════════════════════════════════════════
    public function adminPdf(Request $request, int $petugasId)
    {
        $admin   = Auth::user();
        $periode = $request->input('periode', NilaiEvaluasiController::periodeSekarang());
        $petugas = Petugas::with('user')->findOrFail($petugasId);

        $evaluasi = EvaluasiPetugas::where('petugas_id', $petugasId)
            ->where('periode', $periode)
            ->with('koordinator')
            ->first();

        $wilayah     = Wilayah::find(optional($petugas->user)->wilayah_id);
        $koordinator = optional($evaluasi)->koordinator;

        $data = $this->buildData(
            $petugas, $evaluasi, $periode,
            $koordinator, $wilayah,
            $admin->name . ' (Admin)'
        );

        return $this->renderPdf($data, optional($petugas->user)->name ?? 'petugas', $periode);
    }
}