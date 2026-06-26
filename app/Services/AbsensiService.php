<?php

namespace App\Services;

use Carbon\Carbon;
use App\Models\Absensi;
use App\Models\JadwalPetugas;
use App\Models\User;

class AbsensiService
{
    public function getWindowConfig(string $jenisScan, string $hariKerja): array
    {
        $isFriday = ($hariKerja === 'jumat');

        $config = [
            'masuk_pagi' => [
                'jam_ideal'  => 8 * 60 + 10,
                'toleransi'  => 0,
                'qr_mulai'   => 7 * 60,
                'qr_selesai' => 12 * 60,
                'deadline'   => 12 * 60,
                'sesi'       => 'pagi',
            ],
            'keluar_pagi' => [
                'jam_ideal'  => 12 * 60,
                'toleransi'  => 0,
                'qr_mulai'   => 12 * 60,
                'qr_selesai' => 13 * 60,
                'deadline'   => 13 * 60,
                'sesi'       => 'pagi',
            ],
            'masuk_siang' => [
                'jam_ideal'  => 12 * 60,
                'toleransi'  => 10,
                'qr_mulai'   => 12 * 60,
                'qr_selesai' => 16 * 60 + 30,
                'deadline'   => 16 * 60 + 30,
                'sesi'       => 'siang',
            ],
            'keluar_siang' => [
                'jam_ideal'  => $isFriday ? (16 * 60) : (15 * 60 + 30),
                'toleransi'  => 0,
                'qr_mulai'   => $isFriday ? (16 * 60) : (15 * 60 + 30),
                'qr_selesai' => $isFriday ? (16 * 60 + 30) : (16 * 60),
                'deadline'   => $isFriday ? (16 * 60 + 30) : (16 * 60),
                'sesi'       => 'siang',
            ],
        ];

        return $config[$jenisScan] ?? [];
    }

    public function getHariKerja(Carbon $tanggal): string
    {
        return $tanggal->dayOfWeek === Carbon::FRIDAY ? 'jumat' : 'senin_kamis';
    }

    public function generateQrToken(string $jenisScan, Carbon $tanggal, int $slotOffset = 0): string
    {
        $kode = [
            'masuk_pagi'   => 'MP',
            'keluar_pagi'  => 'KP',
            'masuk_siang'  => 'MS',
            'keluar_siang' => 'KS',
        ][$jenisScan];

        $dateStr    = $tanggal->format('Ymd');
        $totalDetik = $tanggal->hour * 3600 + $tanggal->minute * 60 + $tanggal->second;
        $slot       = floor($totalDetik / 30) + $slotOffset;
        $secret     = config('app.key');
        $raw        = "PST-BABEL:{$dateStr}:{$jenisScan}:{$slot}:{$secret}";
        $hash       = strtoupper(substr(hash('sha256', $raw), 0, 8));

        return "PST{$dateStr}{$kode}{$hash}";
    }

    public function validateQrToken(string $token, Carbon $now): array
    {
        $kodeMap = [
            'MP' => 'masuk_pagi',
            'KP' => 'keluar_pagi',
            'MS' => 'masuk_siang',
            'KS' => 'keluar_siang',
        ];

        if (! preg_match('/^PST(\d{8})([A-Z]{2})([A-F0-9]{8})$/', $token, $m)) {
            return ['valid' => false, 'error' => 'Format QR tidak dikenal.'];
        }

        $tokenDate = $m[1];
        $tokenKode = $m[2];

        if (! isset($kodeMap[$tokenKode])) {
            return ['valid' => false, 'error' => 'Jenis scan pada QR tidak valid.'];
        }
        $jenisScan = $kodeMap[$tokenKode];

        $today = $now->format('Ymd');
        if ($tokenDate !== $today) {
            return ['valid' => false, 'error' => 'QR sudah kadaluarsa (tanggal berbeda).'];
        }

        $expected     = $this->generateQrToken($jenisScan, $now);
        $expectedPrev = $this->generateQrToken($jenisScan, $now, -1);
        if ($token !== $expected && $token !== $expectedPrev) {
            return ['valid' => false, 'error' => 'QR tidak sah atau sudah kadaluarsa.'];
        }

        $hariKerja = $this->getHariKerja($now);
        $cfg       = $this->getWindowConfig($jenisScan, $hariKerja);
        $nowMenit  = $now->hour * 60 + $now->minute;

        if ($nowMenit < $cfg['qr_mulai']) {
            $mulaiStr = sprintf('%02d:%02d', intdiv($cfg['qr_mulai'], 60), $cfg['qr_mulai'] % 60);
            return ['valid' => false, 'error' => "QR belum aktif. Berlaku mulai {$mulaiStr}."];
        }

        if ($nowMenit > $cfg['qr_selesai']) {
            return ['valid' => false, 'error' => 'QR sudah kadaluarsa (waktu absensi berakhir).'];
        }

        return ['valid' => true, 'jenis_scan' => $jenisScan, 'sesi' => $cfg['sesi']];
    }

    public function hitungStatusMasuk(string $jenisScan, Carbon $now): array
    {
        $hariKerja = $this->getHariKerja($now);
        $cfg       = $this->getWindowConfig($jenisScan, $hariKerja);
        $nowMenit  = $now->hour * 60 + $now->minute;
        $ideal     = $cfg['jam_ideal'];
        $toleransi = $cfg['toleransi'];

        if ($nowMenit <= $ideal) {
            return ['status_kehadiran' => 'tepat_waktu', 'keterlambatan_menit' => 0];
        }

        $telat = $nowMenit - $ideal;

        if ($telat <= $toleransi) {
            return ['status_kehadiran' => 'toleransi', 'keterlambatan_menit' => $telat];
        }

        return ['status_kehadiran' => 'terlambat', 'keterlambatan_menit' => $telat];
    }

    public function cekJadwal(int $userId, string $sesi, Carbon $tanggal): bool
    {
        return JadwalPetugas::where('user_id', $userId)
            ->where('tanggal', $tanggal->toDateString())
            ->where('shift', $sesi)
            ->exists();
    }

    public function validasiUrutan(int $userId, string $jenisScan, Carbon $tanggal): array
    {
        $tanggalStr = $tanggal->toDateString();

        switch ($jenisScan) {
            case 'keluar_pagi':
                $masukPagi = Absensi::where('user_id', $userId)
                    ->where('tanggal', $tanggalStr)
                    ->where('jenis_scan', 'masuk_pagi')
                    ->first();
                if (! $masukPagi) {
                    return ['valid' => false, 'error' => 'Anda belum scan masuk pagi. Scan masuk terlebih dahulu.'];
                }
                break;

            case 'masuk_siang':
                $sudahMasukPagi = Absensi::where('user_id', $userId)
                    ->where('tanggal', $tanggalStr)
                    ->where('jenis_scan', 'masuk_pagi')
                    ->exists();
                $sudahKeluarPagi = Absensi::where('user_id', $userId)
                    ->where('tanggal', $tanggalStr)
                    ->where('jenis_scan', 'keluar_pagi')
                    ->exists();
                if ($sudahMasukPagi && ! $sudahKeluarPagi) {
                    return ['valid' => false, 'error' => 'Selesaikan absensi shift pagi terlebih dahulu.'];
                }
                break;

            case 'keluar_siang':
                $masukSiang = Absensi::where('user_id', $userId)
                    ->where('tanggal', $tanggalStr)
                    ->where('jenis_scan', 'masuk_siang')
                    ->first();
                if (! $masukSiang) {
                    return ['valid' => false, 'error' => 'Anda belum scan masuk siang.'];
                }
                break;
        }

        $existing = Absensi::where('user_id', $userId)
            ->where('tanggal', $tanggalStr)
            ->where('jenis_scan', $jenisScan)
            ->first();

        if ($existing) {
            $label = $this->labelJenisScan($jenisScan);
            return ['valid' => false, 'error' => "Anda sudah melakukan scan {$label} hari ini."];
        }

        return ['valid' => true];
    }

    public function prosesScan(User $user, string $token, Carbon $now, array $extra = []): array
    {
        $qrResult = $this->validateQrToken($token, $now);
        if (! $qrResult['valid']) {
            return ['success' => false, 'message' => $qrResult['error']];
        }

        $jenisScan = $qrResult['jenis_scan'];
        $sesi      = $qrResult['sesi'];

        if (! $this->cekJadwal($user->id, $sesi, $now)) {
            return [
                'success' => false,
                'message' => "Jadwal shift {$sesi} untuk hari ini tidak ditemukan.",
            ];
        }

        $urutanResult = $this->validasiUrutan($user->id, $jenisScan, $now);
        if (! $urutanResult['valid']) {
            return ['success' => false, 'message' => $urutanResult['error']];
        }

        $statusKehadiran   = null;
        $keterlambatanMnt  = 0;

        if (str_starts_with($jenisScan, 'masuk_')) {
            $statusResult    = $this->hitungStatusMasuk($jenisScan, $now);
            $statusKehadiran = $statusResult['status_kehadiran'];
            $keterlambatanMnt = $statusResult['keterlambatan_menit'];
        }

        $isKeluar  = str_starts_with($jenisScan, 'keluar_');
        $fotoPath  = $extra['foto_selfie'] ?? null;

        try {
            $absensi = Absensi::create([
                'user_id'             => $user->id,
                'wilayah_id'          => $user->wilayah_id,
                'tanggal'             => $now->toDateString(),
                'sesi'                => $sesi,
                'jenis_scan'          => $jenisScan,
                'status'              => 'hadir',
                'status_kehadiran'    => $statusKehadiran,
                'keterlambatan_menit' => $keterlambatanMnt,
                'jam_masuk'           => $isKeluar ? null : $now->format('H:i:s'),
                'jam_keluar'          => $isKeluar ? $now->format('H:i:s') : null,
                'latitude'            => $extra['latitude']    ?? null,
                'longitude'           => $extra['longitude']   ?? null,
                'foto_selfie'         => $fotoPath,
                'catatan'             => $extra['catatan']     ?? null,
                'device_info'         => $extra['device_info'] ?? null,
                'qr_token_used'       => $token,
                'verified_status'     => 'approved',
            ]);
        } catch (\Illuminate\Database\QueryException $e) {
            // Constraint unik (duplikat scan tanggal+jenis_scan) atau error DB lain.
            // Jangan lempar 500 mentah ke aplikasi mobile — beri respons yang jelas.
            return [
                'success' => false,
                'message' => 'Gagal menyimpan absensi (kemungkinan sudah pernah scan jenis ini hari ini). Silakan refresh dan coba lagi.',
            ];
        }

        $label = $this->labelJenisScan($jenisScan);
        $msg   = "Scan {$label} berhasil!";

        if ($statusKehadiran === 'toleransi') {
            $msg .= " (Terlambat {$keterlambatanMnt} menit, masih dalam toleransi)";
        } elseif ($statusKehadiran === 'terlambat') {
            $msg .= " (Terlambat {$keterlambatanMnt} menit)";
        }

        return [
            'success' => true,
            'message' => $msg,
            'data'    => [
                'id'                  => $absensi->id,
                'jenis_scan'          => $jenisScan,
                'sesi'                => $sesi,
                'jam'                 => $now->format('H:i:s'),
                'status_kehadiran'    => $statusKehadiran,
                'keterlambatan_menit' => $keterlambatanMnt,
            ],
        ];
    }

    public function labelJenisScan(string $jenisScan): string
    {
        return [
            'masuk_pagi'   => 'Masuk Pagi',
            'keluar_pagi'  => 'Keluar Pagi',
            'masuk_siang'  => 'Masuk Siang',
            'keluar_siang' => 'Keluar Siang',
        ][$jenisScan] ?? $jenisScan;
    }

    public function getStatusQrHariIni(Carbon $now): array
    {
        $hariKerja = $this->getHariKerja($now);
        $nowMenit  = $now->hour * 60 + $now->minute;
        $jenisList = ['masuk_pagi', 'keluar_pagi', 'masuk_siang', 'keluar_siang'];

        $result = [];
        foreach ($jenisList as $jenis) {
            $cfg = $this->getWindowConfig($jenis, $hariKerja);
            $aktif = $nowMenit >= $cfg['qr_mulai'] && $nowMenit <= $cfg['qr_selesai'];
            $token = $aktif ? $this->generateQrToken($jenis, $now) : null;

            $mulaiStr    = sprintf('%02d:%02d', intdiv($cfg['qr_mulai'], 60), $cfg['qr_mulai'] % 60);
            $selesaiStr  = sprintf('%02d:%02d', intdiv($cfg['qr_selesai'], 60), $cfg['qr_selesai'] % 60);
            $idealStr    = sprintf('%02d:%02d', intdiv($cfg['jam_ideal'], 60), $cfg['jam_ideal'] % 60);

            $result[$jenis] = [
                'label'       => $this->labelJenisScan($jenis),
                'sesi'        => $cfg['sesi'],
                'aktif'       => $aktif,
                'token'       => $token,
                'qr_mulai'    => $mulaiStr,
                'qr_selesai'  => $selesaiStr,
                'jam_ideal'   => $idealStr,
                'toleransi'   => $cfg['toleransi'],
            ];
        }

        return $result;
    }
}