<?php
// app/Http/Controllers/PetugasAbsensiController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Models\Absensi;
use Carbon\Carbon;

class PetugasAbsensiController extends Controller
{
    public function index(Request $request)
    {
        $user        = Auth::user();
        $filterBulan = (int) ($request->bulan ?? now()->month);
        $filterTahun = (int) ($request->tahun ?? now()->year);
        $filterSesi  = $request->sesi ?? '';

        // ── Absensi hari ini ───────────────────────────────
        $absensiHariIni = Absensi::where('user_id', $user->id)
            ->whereDate('tanggal', now()->toDateString())
            ->latest()
            ->first();

        // ── Riwayat bulan ini (dengan pagination) ─────────
        $query = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $filterBulan)
            ->whereYear('tanggal', $filterTahun)
            ->orderByDesc('tanggal')
            ->orderByDesc('jam_masuk');

        if ($filterSesi) {
            $query->where('sesi', $filterSesi);
        }

        $absensi = $query->paginate(15)->appends($request->query());

        // ── Statistik bulan ini ───────────────────────────
        $semuaBulanIni = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $filterBulan)
            ->whereYear('tanggal', $filterTahun)
            ->get();

        $totalHadir  = $semuaBulanIni->where('status', 'hadir')->count();
        $totalTepat  = $semuaBulanIni->where('status_kehadiran', 'tepat_waktu')->count();
        $totalAlpha  = $semuaBulanIni->where('status', 'alpha')->count();
        $totalIzin   = $semuaBulanIni->whereIn('status', ['izin', 'sakit'])->count();
        $totalRecord = $semuaBulanIni->count();

        $pctHadir  = $totalRecord > 0 ? round($totalHadir / $totalRecord * 100) : 0;
        $pctTepat  = $totalRecord > 0 ? round($totalTepat / $totalRecord * 100) : 0;
        $pctAlpha  = $totalRecord > 0 ? round($totalAlpha / $totalRecord * 100) : 0;

        // ── Rekap per minggu ──────────────────────────────
        $rekapMinggu  = [];
        $startOfMonth = Carbon::create($filterTahun, $filterBulan, 1);
        $endOfMonth   = $startOfMonth->copy()->endOfMonth();

        for ($week = 1; $week <= 5; $week++) {
            $weekStart = $startOfMonth->copy()->addWeeks($week - 1)->startOfWeek(Carbon::MONDAY);
            $weekEnd   = $weekStart->copy()->endOfWeek(Carbon::SUNDAY);

            // Pastikan dalam range bulan
            if ($weekStart->gt($endOfMonth)) break;
            $weekEnd = $weekEnd->gt($endOfMonth) ? $endOfMonth : $weekEnd;

            $weekData = $semuaBulanIni->filter(function ($a) use ($weekStart, $weekEnd) {
                return Carbon::parse($a->tanggal)->between($weekStart, $weekEnd);
            });

            if ($weekData->isEmpty()) continue;

            $rekapMinggu[] = [
                'hadir' => $weekData->where('status', 'hadir')->count(),
                'total' => $weekData->count(),
            ];
        }

        return view('petugas.absensi.index', compact(
            'absensi',
            'absensiHariIni',
            'filterBulan',
            'filterTahun',
            'filterSesi',
            'totalHadir',
            'totalTepat',
            'totalAlpha',
            'totalIzin',
            'pctHadir',
            'pctTepat',
            'pctAlpha',
            'rekapMinggu'
        ));
    }
}