<?php

namespace App\Http\Controllers;

use App\Models\SurveySetting;
use App\Models\JawabanTriwulan;
use App\Models\MateriTriwulan;
use App\Models\Petugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class PetugasMateriTriwulanController extends Controller
{
    public function show($id)
    {
        $petugas = Petugas::where('user_id', Auth::id())->firstOrFail();
        $materi  = MateriTriwulan::with(['quiz', 'files'])
            ->where('wilayah_id', $petugas->wilayah_id)
            ->findOrFail($id);

        $periode = $materi->periode;
        $bisaIsi = SurveySetting::get('materi_triwulan_open', 'false') === 'true';

        $jawabanSudah = JawabanTriwulan::where('materi_triwulan_id', $id)
            ->where('petugas_id', $petugas->id)
            ->where('periode', $periode)
            ->first();

        return view('petugas.materi.triwulan_show', compact(
            'materi', 'petugas', 'bisaIsi', 'jawabanSudah', 'periode'
        ));
    }

    public function submit(Request $request, $id)
    {
        $petugas = Petugas::where('user_id', Auth::id())->firstOrFail();
        $materi  = MateriTriwulan::with('quiz')
            ->where('wilayah_id', $petugas->wilayah_id)
            ->findOrFail($id);

        // Cek apakah materi triwulan sudah dibuka oleh admin
        if (SurveySetting::get('materi_triwulan_open', 'false') !== 'true') {
            return back()->with('error', 'Materi & quiz triwulan belum dibuka oleh admin.');
        }

        $periode = $materi->periode;

        // Cek sudah mengisi
        $existing = JawabanTriwulan::where('materi_triwulan_id', $id)
            ->where('petugas_id', $petugas->id)
            ->where('periode', $periode)
            ->first();

        if ($existing && $existing->status === 'sudah') {
            return back()->with('info', 'Anda sudah mengerjakan quiz ini.');
        }

        // Hitung skor
        $quiz   = $materi->quiz;
        $total  = $quiz->count();
        $benar  = 0;
        $detail = [];

        foreach ($quiz as $soal) {
            $jawaban = $request->input('jawaban.' . $soal->id);
            $detail[$soal->id] = $jawaban;
            if ($jawaban && strtolower($jawaban) === strtolower($soal->jawaban)) {
                $benar++;
            }
        }

        $skor = $total > 0 ? round($benar / $total * 100) : 0;

        JawabanTriwulan::updateOrCreate(
            [
                'materi_triwulan_id' => $id,
                'petugas_id'         => $petugas->id,
                'periode'            => $periode,
            ],
            [
                'status'         => 'sudah',
                'skor'           => $skor,
                'jawaban_detail' => $detail,
                'dikerjakan_at'  => now(),
            ]
        );

        return redirect()
            ->route('petugas.materi.triwulan.show', $id)
            ->with('success', "Quiz selesai! Skor Anda: {$skor}/100 ({$benar}/{$total} benar)");
    }
}