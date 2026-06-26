<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Absensi;
use App\Models\JadwalPetugas;
use App\Models\User;
use App\Services\AbsensiService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AbsensiApiController extends Controller
{
    public function __construct(protected AbsensiService $svc) {}

    // POST /api/login
    public function login(Request $request)
    {
        $request->validate(['username' => 'required', 'password' => 'required']);

        $user = User::where('username', $request->username)->where('is_active', true)->first();
        if (! $user || ! Hash::check($request->password, $user->password)) {
            return response()->json(['success' => false, 'message' => 'Username atau password salah'], 401);
        }
        if ($user->role !== 'petugas') {
            return response()->json(['success' => false, 'message' => 'Akun ini tidak memiliki akses aplikasi mobile'], 403);
        }

        $user->tokens()->delete();
        $token = $user->createToken('mobile-app')->plainTextToken;

        return response()->json([
            'success' => true, 'message' => 'Login berhasil', 'token' => $token,
            'user' => ['id' => $user->id, 'name' => $user->name, 'username' => $user->username,
                       'role' => $user->role, 'no_hp' => $user->no_hp, 'wilayah' => $user->wilayah?->nama],
        ]);
    }

    // POST /api/logout
    public function logout(Request $request)
    {
        $request->user()->tokens()->delete();
        return response()->json(['success' => true, 'message' => 'Logout berhasil']);
    }

    // GET /api/profil
    public function profil(Request $request)
    {
        $user = $request->user()->load('wilayah', 'petugas');
        return response()->json(['success' => true, 'data' => [
            'id' => $user->id, 'name' => $user->name, 'username' => $user->username,
            'no_hp' => $user->no_hp, 'wilayah' => $user->wilayah?->nama ?? '-',
            'shift' => $user->petugas?->shift ?? '-',
        ]]);
    }

    // POST /api/absensi/scan-qr  — UTAMA: scan QR otomatis
    public function scanQr(Request $request)
    {
        $request->validate([
            'qr_token'    => 'required|string',
            'latitude'    => 'required|numeric',
            'longitude'   => 'required|numeric',
            'foto_selfie' => 'nullable|image|max:5120',
        ]);

        $user = $request->user();
        $now  = Carbon::now('Asia/Jakarta');

        $fotoPath = null;
        if ($request->hasFile('foto_selfie')) {
            $fotoPath = $request->file('foto_selfie')->store('absensi/selfie', 'public');
        }

        $result = $this->svc->prosesScan($user, $request->qr_token, $now, [
            'latitude'    => $request->latitude,
            'longitude'   => $request->longitude,
            'foto_selfie' => $fotoPath,
            'device_info' => $request->device_info,
            'catatan'     => $request->catatan,
        ]);

        return response()->json($result, $result['success'] ? 200 : 422);
    }

    // POST /api/absensi/masuk  — legacy, gunakan scan-qr
    public function absenMasuk(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Gunakan endpoint /api/absensi/scan-qr untuk absensi dengan QR Code.',
        ], 410);
    }

    // POST /api/absensi/keluar — legacy
    public function absenKeluar(Request $request)
    {
        return response()->json([
            'success' => false,
            'message' => 'Gunakan endpoint /api/absensi/scan-qr untuk scan keluar.',
        ], 410);
    }

    // GET /api/absensi/hari-ini
    public function absensiHariIni(Request $request)
    {
        $user    = $request->user();
        $tanggal = now()->toDateString();

        $absensi = Absensi::where('user_id', $user->id)->where('tanggal', $tanggal)->get()
            ->map(fn($a) => [
                'id'                  => $a->id,
                'sesi'                => $a->sesi,
                'jenis_scan'          => $a->jenis_scan,
                'status'              => $a->status,
                'status_kehadiran'    => $a->status_kehadiran,
                'keterlambatan_menit' => $a->keterlambatan_menit,
                'jam_masuk'           => $a->jam_masuk,
                'jam_keluar'          => $a->jam_keluar,
            ]);

        // Sertakan juga info QR aktif saat ini
        $now      = Carbon::now('Asia/Jakarta');
        $qrStatus = $this->svc->getStatusQrHariIni($now);

        return response()->json([
            'success'     => true,
            'tanggal'     => $tanggal,
            'server_time' => $now->format('H:i:s'),
            'data'        => $absensi,
            'qr_aktif'    => collect($qrStatus)->filter(fn($q) => $q['aktif'])->keys()->values(),
        ]);
    }

    // GET /api/absensi/riwayat
    //
    // PERUBAHAN: sekarang riwayat juga menyertakan hari yang DIJADWALKAN (jadwal_petugas)
    // tapi TIDAK ADA absensi "masuk" yang cocok -> ditandai sebagai status_kehadiran = 'alpa'.
    // Hari ini & hari di masa depan TIDAK dihitung alpa (jamnya belum lewat / belum tentu absen).
    public function riwayat(Request $request)
    {
        $user  = $request->user();
        $bulan = (int) $request->input('bulan', now()->month);
        $tahun = (int) $request->input('tahun', now()->year);
        $today = Carbon::now('Asia/Jakarta')->toDateString();

        // 1) Data absensi asli pada bulan/tahun yang diminta
        $absensiList = Absensi::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->get();

        // 2) Gabungkan baris masuk_* dan keluar_* dari shift yang sama jadi SATU baris
        //    per (tanggal|sesi). Status kehadiran & keterlambatan diambil dari baris "masuk".
        $gabungan = []; // key: "tanggal|sesi" => data gabungan

        foreach ($absensiList as $a) {
            $tgl = Carbon::parse($a->tanggal)->format('Y-m-d');
            $key = "{$tgl}|{$a->sesi}";

            if (! isset($gabungan[$key])) {
                $gabungan[$key] = [
                    'id'                  => $a->id,
                    'tanggal'             => $tgl,
                    'sesi'                => $a->sesi,
                    'status'              => $a->status,
                    'status_kehadiran'    => null,
                    'keterlambatan_menit' => null,
                    'jam_masuk'           => null,
                    'jam_keluar'          => null,
                    'is_alpa'             => false,
                ];
            }

            if (str_starts_with($a->jenis_scan, 'masuk_')) {
                $gabungan[$key]['id']                  = $a->id; // pakai id baris masuk sebagai id utama
                $gabungan[$key]['jam_masuk']           = $a->jam_masuk;
                $gabungan[$key]['status_kehadiran']    = $a->status_kehadiran;
                $gabungan[$key]['keterlambatan_menit'] = $a->keterlambatan_menit;
            } else {
                $gabungan[$key]['jam_keluar'] = $a->jam_keluar;
            }
        }

        // Index cepat untuk cek "apakah tanggal+sesi ini sudah ada absensi masuk"
        $sudahAbsenMasuk = [];
        foreach ($absensiList as $a) {
            if (str_starts_with($a->jenis_scan, 'masuk_')) {
                $tgl = Carbon::parse($a->tanggal)->format('Y-m-d');
                $sudahAbsenMasuk["{$tgl}|{$a->sesi}"] = true;
            }
        }

        // 3) Cek jadwal bulan/tahun yang sama, untuk cari hari+sesi yang TIDAK ada absensi masuk
        $jadwalList = JadwalPetugas::where('user_id', $user->id)
            ->whereMonth('tanggal', $bulan)->whereYear('tanggal', $tahun)
            ->get();

        foreach ($jadwalList as $j) {
            $tgl  = Carbon::parse($j->tanggal)->format('Y-m-d');
            $sesi = $j->shift; // di skema ini shift bernilai 'pagi' / 'siang', sama seperti kolom sesi pada absensi
            $key  = "{$tgl}|{$sesi}";

            // Lewati hari ini & masa depan — belum bisa dinilai alpa karena jam absen mungkin belum lewat
            if ($tgl >= $today) {
                continue;
            }

            if (isset($sudahAbsenMasuk[$key])) {
                continue; // sudah ada absensi masuk untuk jadwal ini, tidak alpa
            }

            // Tidak ada absensi masuk untuk jadwal ini -> tandai sebagai alpa
            // (jika user sempat scan keluar tanpa masuk -- kasus aneh -- tetap ditimpa jadi alpa
            //  karena yang menentukan kehadiran adalah scan masuk)
            $gabungan[$key] = [
                'id'                  => null,
                'tanggal'             => $tgl,
                'sesi'                => $sesi,
                'status'              => 'alpa',
                'status_kehadiran'    => 'alpa',
                'keterlambatan_menit' => null,
                'jam_masuk'           => null,
                'jam_keluar'          => null,
                'is_alpa'             => true,
            ];
        }

        // 4) Urutkan tanggal terbaru dulu, lalu sesi (pagi sebelum siang) agar rapi
        $data = collect(array_values($gabungan))
            ->sortBy(fn($r) => $r['tanggal'] . ($r['sesi'] === 'pagi' ? '0' : '1'))
            ->reverse()
            ->values();

        return response()->json([
            'success' => true,
            'bulan'   => $bulan,
            'tahun'   => $tahun,
            'total'   => $data->count(),
            'data'    => $data,
        ]);
    }

    // GET /api/jadwal
    public function jadwal(Request $request)
    {
        $user   = $request->user();
        $jadwal = \App\Models\JadwalPetugas::where('user_id', $user->id)
            ->where('tanggal', '>=', now()->toDateString())
            ->where('tanggal', '<=', now()->addDays(7)->toDateString())
            ->orderBy('tanggal')->get()
            ->map(fn($j) => ['tanggal' => $j->tanggal, 'shift' => $j->shift, 'keterangan' => $j->keterangan]);

        return response()->json(['success' => true, 'data' => $jadwal]);
    }

    // GET /api/absensi/qr-info  — info QR aktif hari ini untuk tampilan di app
    public function qrInfo(Request $request)
    {
        $now      = Carbon::now('Asia/Jakarta');
        $qrStatus = $this->svc->getStatusQrHariIni($now);

        return response()->json([
            'success'     => true,
            'server_time' => $now->format('H:i:s'),
            'tanggal'     => $now->format('Y-m-d'),
            'hari'        => $now->isoFormat('dddd'),
            'qr_status'   => $qrStatus,
        ]);
    }
}