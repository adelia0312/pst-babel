<?php

namespace App\Http\Controllers;

use App\Models\Tugas;
use App\Models\Petugas;
use App\Models\Jawaban;
use App\Models\Wilayah;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class KoordinatorMateriController extends Controller
{
    public function index()
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;

        // Wilayah koordinator ini
        $wilayah = Wilayah::find($wilayahId);

        // Semua petugas di wilayah ini
        $petugasList = Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();

        $totalPetugas = $petugasList->count();
        $petugasIds   = $petugasList->pluck('id');

        // Semua tugas + data jawaban per tugas
        $tugasList = Tugas::with('quiz')->latest()->get()->map(function ($tugas) use ($petugasList, $petugasIds, $wilayahId) {

            // Jawaban semua petugas wilayah untuk tugas ini
            $jawabanMap = Jawaban::where('tugas_id', $tugas->id)
                ->whereIn('petugas_id', $petugasIds)
                ->get()
                ->keyBy('petugas_id');

            $jmlSudah     = 0;
            $jmlTerlambat = 0;

            foreach ($petugasList as $p) {
                $jaw = $jawabanMap->get($p->id);
                if ($jaw && $jaw->status === 'sudah') {
                    $jmlSudah++;
                    if ($tugas->deadline && $jaw->updated_at &&
                        Carbon::parse($jaw->updated_at)->startOfDay()->gt($tugas->deadline)) {
                        $jmlTerlambat++;
                    }
                }
            }

            $jmlBelum = $petugasIds->count() - $jmlSudah;
            $progress = $petugasIds->count() > 0
                ? round(($jmlSudah / $petugasIds->count()) * 100)
                : 0;

            $tugas->jawabanMap    = $jawabanMap;
            $tugas->petugasList   = $petugasList;
            $tugas->jmlSudah      = $jmlSudah;
            $tugas->jmlBelum      = $jmlBelum;
            $tugas->jmlTerlambat  = $jmlTerlambat;
            $tugas->progress      = $progress;

            return $tugas;
        });

        $totalTugas  = $tugasList->count();
        $totalSudah  = $tugasList->sum('jmlSudah');
        $totalBelum  = $tugasList->sum('jmlBelum');

        return view('koordinator.materi.index', compact(
            'tugasList',
            'wilayah',
            'totalPetugas',
            'totalTugas',
            'totalSudah',
            'totalBelum'
        ));
    }

    public function polling(\Illuminate\Http\Request $request)
    {
        $wilayahId  = \Illuminate\Support\Facades\Auth::user()->wilayah_id;
        $after      = (int) $request->input('after', 0);

        $newMateri = \App\Models\Tugas::where('id', '>', $after)
            ->orderByDesc('id')
            ->limit(10)
            ->get()
            ->map(fn($t) => [
                'id'         => $t->id,
                'judul'      => $t->judul,
                'deskripsi'  => $t->deskripsi,
                'created_at' => $t->created_at->format('d M Y'),
                'has_file'   => (bool) $t->file,
                'has_link'   => (bool) $t->link,
            ]);

        $maxId = $newMateri->max('id') ?? $after;

        // Stats wilayah koordinator ini
        $petugasList = \App\Models\Petugas::with('user')
            ->where('wilayah_id', $wilayahId)
            ->whereHas('user', fn($q) => $q->where('role', 'petugas'))
            ->get();
        $petugasIds  = $petugasList->pluck('id');
        $totalTugas  = \App\Models\Tugas::count();
        $totalSudah  = \App\Models\Jawaban::where('status', 'sudah')->whereIn('petugas_id', $petugasIds)->count();
        $totalBelum  = max(0, ($petugasIds->count() * $totalTugas) - $totalSudah);

        return response()->json([
            'new_materi' => $newMateri,
            'max_id'     => $maxId,
            'summary'    => [
                'total_materi'  => $totalTugas,
                'total_sudah'   => $totalSudah,
                'total_belum'   => $totalBelum,
                'total_petugas' => $petugasList->count(),
            ],
        ]);
    }
}