<?php

namespace App\Http\Controllers;

use App\Models\ChecklistHarian;
use App\Models\LaporanHarianBaru;
use App\Models\Jawaban;
use App\Models\JawabanTriwulan;
use App\Models\Absensi;
use App\Models\SurveyKepuasan;
use App\Models\JadwalPetugas;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

/**
 * SidebarBadgeController
 *
 * Endpoint JSON ringan yang di-polling frontend setiap ~30 detik
 * untuk memperbarui badge/notif di sidebar tanpa full page reload.
 */
class SidebarBadgeController extends Controller
{
    /* ─────────────────────────────────────────────────────────────
     |  ADMIN  – melihat semua wilayah
     ───────────────────────────────────────────────────────────── */
    public function admin(Request $request)
    {
        $today = now('Asia/Jakarta')->toDateString();

        // Laporan Harian – total yang menunggu review
        $laporanPending = LaporanHarianBaru::where('status', 'submitted')->count();

        // Checklist Harian – yang statusnya 'submit' (perlu diverifikasi)
        $checklistPending = ChecklistHarian::whereDate('tanggal', $today)
            ->where('status', 'submit')
            ->count();

        // Absensi – hadir hari ini (informatif)
        $absensiHariIni = Absensi::whereDate('tanggal', $today)
            ->where('jenis_scan', 'masuk')
            ->count();

        // Materi – jawaban reguler yang perlu dinilai
        $jawabanPending = Jawaban::where('status', 'submitted')->count();

        // Materi Triwulan – jawaban yang perlu dinilai
        $jawabanTriwulanPending = JawabanTriwulan::where('status', 'submitted')->count();

        $materiTugas = $jawabanPending + $jawabanTriwulanPending;

        // Survey – response hari ini (informatif)
        $surveyHariIni = SurveyKepuasan::whereDate('created_at', $today)->count();

        // Jadwal – jadwal yang diupdate dalam 24 jam terakhir (informatif bagi admin)
        $jadwalBaru = JadwalPetugas::where('updated_at', '>=', now('Asia/Jakarta')->subHours(24))->count();

        return response()->json([
            'laporan_harian'   => $laporanPending,
            'checklist_harian' => $checklistPending,
            'absensi_hari_ini' => $absensiHariIni,
            'materi_tugas'     => $materiTugas,
            'survey_kepuasan'  => $surveyHariIni,
            'jadwal_diupdate'  => $jadwalBaru,
            '_ts'              => now()->timestamp,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     |  KOORDINATOR  – hanya wilayah sendiri
     ───────────────────────────────────────────────────────────── */
    public function koordinator(Request $request)
    {
        $user      = Auth::user();
        $wilayahId = $user->wilayah_id;
        $today     = now('Asia/Jakarta')->toDateString();

        // Laporan Harian – pending di wilayah ini
        $laporanPending = LaporanHarianBaru::where('wilayah_id', $wilayahId)
            ->where('status', 'submitted')
            ->count();

        // Checklist Harian – submit (menunggu verifikasi) di wilayah ini
        $checklistPending = ChecklistHarian::whereDate('tanggal', $today)
            ->where('status', 'submit')
            ->whereHas('user', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->count();

        // Absensi hari ini di wilayah ini (informatif)
        $absensiHariIni = Absensi::whereDate('tanggal', $today)
            ->where('wilayah_id', $wilayahId)
            ->where('jenis_scan', 'masuk')
            ->count();

        // Survey response hari ini di wilayah ini
        $surveyHariIni = SurveyKepuasan::where('wilayah_id', $wilayahId)
            ->whereDate('created_at', $today)
            ->count();

        // Materi – jawaban reguler pending di wilayah ini
        $jawabanPending = Jawaban::where('status', 'submitted')
            ->whereHas('petugas', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->count();

        // Materi Triwulan – jawaban pending di wilayah ini
        $jawabanTriwulanPending = JawabanTriwulan::where('status', 'submitted')
            ->whereHas('materi', fn($q) => $q->where('wilayah_id', $wilayahId))
            ->count();

        $materiTugas = $jawabanPending + $jawabanTriwulanPending;

        // Jadwal – jadwal petugas di wilayah ini yang diupdate dalam 24 jam terakhir
        $jadwalDiupdate = JadwalPetugas::where('wilayah_id', $wilayahId)
            ->where('updated_at', '>=', now('Asia/Jakarta')->subHours(24))
            ->count();

        return response()->json([
            'laporan_harian'   => $laporanPending,
            'checklist_harian' => $checklistPending,
            'absensi_hari_ini' => $absensiHariIni,
            'survey_kepuasan'  => $surveyHariIni,
            'materi_tugas'     => $materiTugas,
            'jadwal_diupdate'  => $jadwalDiupdate,
            '_ts'              => now()->timestamp,
        ]);
    }

    /* ─────────────────────────────────────────────────────────────
     |  PETUGAS  – hanya data milik user sendiri
     ───────────────────────────────────────────────────────────── */
    public function petugas(Request $request)
    {
        $user  = Auth::user();
        $today = now('Asia/Jakarta')->toDateString();

        // Status checklist shift sekarang
        $jam      = now('Asia/Jakarta')->hour;
        $shiftNow = null;
        if ($jam >= 7 && $jam < 12)  $shiftNow = 'pagi';
        elseif ($jam >= 12 && $jam < 17) $shiftNow = 'siang';

        $checklistStatus = null;
        $checklistCount  = 0;
        if ($shiftNow) {
            $cl = ChecklistHarian::where('user_id', $user->id)
                ->whereDate('tanggal', $today)
                ->where('sesi', $shiftNow)
                ->first();
            $checklistStatus = $cl->status ?? null;
            if (!$cl || !in_array($cl->status, ['verified'])) {
                $checklistCount = 1;
            }
        }

        // Laporan Harian – yang masih draft milik petugas ini hari ini
        $laporanDraft = LaporanHarianBaru::where('user_id', $user->id)
            ->whereDate('created_at', $today)
            ->where('status', 'draft')
            ->count();

        // Materi reguler – tugas yang sudah disubmit tapi belum dinilai
        $tugasPending = Jawaban::whereHas('petugas', fn($q) => $q->where('user_id', $user->id))
            ->where('status', 'submitted')
            ->count();

        // Materi Triwulan – belum dikerjakan (status null / belum ada record)
        // Hitung materi yang aktif di wilayah petugas tapi belum ada jawaban dari petugas ini
        $petugasRecord = $user->petugas;
        $materiTriwulanBelumDikerjakan = 0;
        if ($petugasRecord) {
            $periodeAktif = now('Asia/Jakarta')->quarter; // Q1-Q4
            $tahunAktif   = now('Asia/Jakarta')->year;
            $periodeStr   = $tahunAktif . '-Q' . $periodeAktif;

            $materiAktif = \App\Models\MateriTriwulan::where('wilayah_id', $petugasRecord->wilayah_id)
                ->where('periode', $periodeStr)
                ->pluck('id');

            if ($materiAktif->isNotEmpty()) {
                $sudahDikerjakan = JawabanTriwulan::where('petugas_id', $petugasRecord->id)
                    ->whereIn('materi_triwulan_id', $materiAktif)
                    ->pluck('materi_triwulan_id');

                $materiTriwulanBelumDikerjakan = $materiAktif->diff($sudahDikerjakan)->count();
            }
        }

        $totalMateri = $tugasPending + $materiTriwulanBelumDikerjakan;

        // Jadwal – jadwal milik petugas ini yang diupdate dalam 48 jam terakhir
        // (supaya petugas tahu kalau jadwalnya baru diubah/ditambah)
        $jadwalBaru = JadwalPetugas::where('user_id', $user->id)
            ->where('tanggal', '>=', $today)           // hanya jadwal mendatang
            ->where('updated_at', '>=', now('Asia/Jakarta')->subHours(48))
            ->count();

        return response()->json([
            'checklist_harian'  => $checklistCount,
            'checklist_status'  => $checklistStatus,
            'laporan_harian'    => $laporanDraft,
            'materi_tugas'      => $totalMateri,
            'jadwal_baru'       => $jadwalBaru,
            '_ts'               => now()->timestamp,
        ]);
    }
}