<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Petugas;
use App\Models\Jawaban;
use App\Models\JawabanQuiz;
use App\Models\LaporanHarianPst;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class ReviewLaporanController extends Controller
{
    public function index(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        // ── Laporan harian (tabel di halaman ini) ──
        $queryLaporan = LaporanHarianPst::where('wilayah_id', $wilayahId)
            ->orderByDesc('tanggal');

        if ($request->filled('bulan')) {
            $queryLaporan->whereMonth('tanggal', date('m', strtotime($request->bulan)))
                         ->whereYear('tanggal',  date('Y', strtotime($request->bulan)));
        }

        if ($request->filled('status')) {
            $queryLaporan->where('status', $request->status);
        }

        $laporan = $queryLaporan->with('user')->paginate(25);

        // ── Stats badge & kartu ──
        $stats = [
            'total'     => LaporanHarianPst::where('wilayah_id', $wilayahId)->count(),
            'submitted' => LaporanHarianPst::where('wilayah_id', $wilayahId)->where('status', 'submitted')->count(),
            'approved'  => LaporanHarianPst::where('wilayah_id', $wilayahId)->where('status', 'approved')->count(),
            'rejected'  => LaporanHarianPst::where('wilayah_id', $wilayahId)->where('status', 'rejected')->count(),
        ];

        // ── Tugas & Materi ──
        $petugasWilayah = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();

        $totalPetugas = $petugasWilayah->count();

        $tugasList = Tugas::with(['quiz'])
            ->latest()
            ->get()
            ->map(function ($tugas) use ($petugasWilayah, $totalPetugas) {
                $petugasIds = $petugasWilayah->pluck('id');

                $sudah = Jawaban::where('tugas_id', $tugas->id)
                    ->whereIn('petugas_id', $petugasIds)
                    ->where('status', 'sudah')
                    ->count();

                $progress = $totalPetugas > 0
                    ? round(($sudah / $totalPetugas) * 100)
                    : 0;

                $tugas->sudah        = $sudah;
                $tugas->belum        = $totalPetugas - $sudah;
                $tugas->progress     = $progress;
                $tugas->totalPetugas = $totalPetugas;

                return $tugas;
            });

        return view('koordinator.laporan.index', compact(
            'tugasList',
            'totalPetugas',
            'laporan',
            'stats',
            'user'
        ));
    }

    public function detail($tugasId)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        $tugas = Tugas::with('quiz')->findOrFail($tugasId);

        $petugasWilayah = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();

        $petugasIds = $petugasWilayah->pluck('id');

        $jawabanMap = Jawaban::where('tugas_id', $tugasId)
            ->whereIn('petugas_id', $petugasIds)
            ->get()
            ->keyBy('petugas_id');

        $jawabanQuizAll = JawabanQuiz::where('tugas_id', $tugasId)
            ->whereIn('petugas_id', $petugasIds)
            ->get()
            ->groupBy('petugas_id');

        $sudah = collect();
        $belum = collect();

        foreach ($petugasWilayah as $petugas) {
            $jawaban = $jawabanMap->get($petugas->id);

            if ($jawaban && $jawaban->status === 'sudah') {
                $terlambat = false;
                if ($tugas->deadline && $jawaban->updated_at) {
                    $terlambat = $jawaban->updated_at->startOfDay()
                        ->gt($tugas->deadline);
                }

                $petugas->jawaban        = $jawaban;
                $petugas->terlambat      = $terlambat;
                $petugas->jawabanQuizArr = $jawabanQuizAll->get($petugas->id, collect());
                $sudah->push($petugas);
            } else {
                $belum->push($petugas);
            }
        }

        $progress = $petugasWilayah->count() > 0
            ? round(($sudah->count() / $petugasWilayah->count()) * 100)
            : 0;

        return view('koordinator.laporan.detail', compact(
            'tugas',
            'sudah',
            'belum',
            'progress',
            'user'
        ));
    }
}