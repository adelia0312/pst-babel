<?php

use App\Http\Controllers\Api\AbsensiApiController;
use Illuminate\Support\Facades\Route;

// ── Route publik (tidak perlu token) ──────────────────────
Route::post('/login',  [AbsensiApiController::class, 'login']);

// ── Route yang membutuhkan token Sanctum ──────────────────
Route::middleware('auth:sanctum')->group(function () {

    Route::post('/logout',              [AbsensiApiController::class, 'logout']);
    Route::get('/profil',               [AbsensiApiController::class, 'profil']);

    // Absensi
    Route::post('/absensi/masuk',       [AbsensiApiController::class, 'absenMasuk']);
    Route::post('/absensi/scan-qr',     [AbsensiApiController::class, 'scanQr']);      // ← BARU
    Route::post('/absensi/keluar',      [AbsensiApiController::class, 'absenKeluar']);
    Route::get('/absensi/hari-ini',     [AbsensiApiController::class, 'absensiHariIni']);
    Route::get('/absensi/riwayat',      [AbsensiApiController::class, 'riwayat']);

    // Jadwal
    Route::get('/jadwal',               [AbsensiApiController::class, 'jadwal']);

    // Info QR aktif hari ini
    Route::get('/absensi/qr-info',      [AbsensiApiController::class, 'qrInfo']);
});