<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PST') — Portal Petugas</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">

    <style>
/* ============================================================
   PST — CSS EMBEDDED (tidak butuh asset/vite/build)
   ============================================================ */

*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

:root {
    --ink:      #0d1117;
    --ink2:     #3d4450;
    --ink3:     #7a8394;
    --rule:     #e2e5ea;
    --surface:  #ffffff;
    --wash:     #f5f6f8;
    --wash2:    #eef0f3;
    --blue:     #1a56db;
    --blue-lt:  #e8eefb;
    --green:    #0a7c4e;
    --green-lt: #e6f5ee;
    --amber:    #b45309;
    --amber-lt: #fef3e2;
    --red:      #c0392b;
    --red-lt:   #fdecea;
    --sw:       220px;
}

html, body {
    height: 100%;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 13px;
    background: var(--wash);
    color: var(--ink);
    -webkit-font-smoothing: antialiased;
}

/* SHELL */
.shell { display: flex; height: 100vh; overflow: hidden; }

/* SIDEBAR */
.sidebar {
    width: var(--sw);
    background: var(--ink);
    color: #c8cdd6;
    display: flex;
    flex-direction: column;
    flex-shrink: 0;
    overflow-y: auto;
    overflow-x: hidden;
    transition: left 0.3s ease;
    /* Sembunyikan scrollbar sidebar agar tidak muncul saat off-canvas */
    scrollbar-width: none;
    -ms-overflow-style: none;
}
.sidebar::-webkit-scrollbar { display: none; }
.sb-head { padding: 20px 18px 18px; border-bottom: 1px solid rgba(255,255,255,.08); }
.sb-brand { font-size: 21px; font-weight: 800; letter-spacing: 0.5px; color: rgba(255,255,255,.32); line-height: 1.1; margin-bottom: 7px; }
.sb-brand .hl { color: #4d8dff; }
.sb-wordmark { font-size: 9.5px; font-weight: 500; letter-spacing: 1.6px; text-transform: uppercase; color: rgba(255,255,255,.42); margin-bottom: 6px; }
.sb-name { font-size: 14px; font-weight: 600; color: #fff; }
.sb-nav { padding: 14px 0; flex: 1; }
.sb-section { padding: 0 10px; margin-bottom: 4px; }
.sb-group-label { font-size: 9px; font-weight: 600; letter-spacing: 1.8px; text-transform: uppercase; color: rgba(255,255,255,.18); padding: 10px 8px 4px; }
.sb-item {
    display: flex; align-items: center; gap: 9px;
    padding: 7px 8px; border-radius: 5px;
    color: rgba(255,255,255,.48); text-decoration: none;
    font-size: 12.5px; cursor: pointer;
    transition: background .12s, color .12s;
    margin-bottom: 1px;
}
.sb-item:hover { background: rgba(255,255,255,.06); color: rgba(255,255,255,.82); }
.sb-item.active { background: rgba(26,86,219,.22); color: #6da4f7; }
.sb-count {
    margin-left: auto; font-size: 10px;
    font-family: 'IBM Plex Mono', monospace;
    background: rgba(255,255,255,.09); color: rgba(255,255,255,.4);
    padding: 1px 6px; border-radius: 3px;
}
.sb-foot { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.07); }
.sb-user { display: flex; align-items: center; gap: 10px; padding: 8px; }
.sb-ava {
    width: 28px; height: 28px; border-radius: 4px;
    background: rgba(26,86,219,.45);
    display: flex; align-items: center; justify-content: center;
    font-size: 10.5px; font-weight: 600; color: #6da4f7; flex-shrink: 0;
}
.sb-uname { font-size: 12px; font-weight: 500; color: rgba(255,255,255,.72); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.sb-role  { font-size: 10.5px; color: rgba(255,255,255,.28); text-transform: capitalize; }
.logout-btn {
    background: none; border: none; padding: 5px;
    cursor: pointer; color: rgba(255,255,255,.22);
    border-radius: 4px; transition: color .15s;
    display: flex; margin-left: auto;
}
.logout-btn:hover { color: rgba(220,60,60,.8); }

/* MAIN */
.main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }
.topbar {
    height: 50px; background: var(--surface);
    border-bottom: 1px solid var(--rule);
    display: flex; align-items: center; justify-content: space-between;
    padding: 0 20px; flex-shrink: 0;
}
.breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink3); }
.breadcrumb a { color: var(--ink3); text-decoration: none; }
.breadcrumb a:hover { color: var(--ink); }
.breadcrumb strong { color: var(--ink); font-weight: 500; }
.topbar-right { display: flex; align-items: center; gap: 12px; }
.ts { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink3); }

/* BODY */
.body { flex: 1; overflow-y: auto; padding: 24px 24px 80px; }

/* PAGE HEAD */
.page-head {
    display: flex; align-items: flex-end; justify-content: space-between;
    margin-bottom: 24px; padding-bottom: 20px;
    border-bottom: 1px solid var(--rule);
}
.page-head h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; }
.page-head p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

/* PANEL */
.panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
.ph { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; border-bottom: 1px solid var(--rule); }
.ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
.ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }
.ph-link  { font-size: 11.5px; color: var(--blue); text-decoration: none; font-weight: 500; padding: 3px 8px; border-radius: 4px; }
.ph-link:hover { background: var(--blue-lt); }

/* TABLE */
table { width: 100%; border-collapse: collapse; }
thead th {
    text-align: left; padding: 8px 18px;
    font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
    color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
}
tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--wash); }
tbody td { padding: 11px 18px; vertical-align: middle; }
.td-main { font-weight: 500; color: var(--ink); font-size: 12.5px; }
.td-id   { font-size: 10.5px; color: var(--ink3); margin-top: 2px; font-family: 'IBM Plex Mono', monospace; }
.mono    { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: var(--ink2); }

/* PILL */
.pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 7px; border-radius: 3px; }
.p-green { background: var(--green-lt); color: var(--green); }
.p-amber { background: var(--amber-lt); color: var(--amber); }
.p-red   { background: var(--red-lt);   color: var(--red); }
.p-blue  { background: var(--blue-lt);  color: var(--blue); }
.p-gray  { background: var(--wash2);    color: var(--ink3); }

/* BTN */
.btn-detail {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 500; color: var(--blue);
    text-decoration: none; padding: 4px 10px;
    border-radius: 5px; border: 1px solid transparent;
    transition: background .12s, border-color .12s;
}
.btn-detail:hover { background: var(--blue-lt); border-color: rgba(26,86,219,.2); }
.btn-add {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12px; font-weight: 500; color: #fff;
    background: var(--blue); border: none;
    padding: 7px 14px; border-radius: 6px;
    cursor: pointer; text-decoration: none; transition: opacity .12s;
    font-family: 'IBM Plex Sans', sans-serif;
}
.btn-add:hover { opacity: .88; }
.back-link {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; color: var(--ink3);
    text-decoration: none; margin-bottom: 20px; padding: 4px 0;
}
.back-link:hover { color: var(--ink); }

/* ACT BUTTON */
.act-group { display: flex; align-items: center; gap: 4px; justify-content: flex-end; }
.act-btn {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 5px; border: 1px solid var(--rule);
    background: none; cursor: pointer; color: var(--ink3); text-decoration: none;
    transition: border-color .12s, color .12s, background .12s;
}
.act-btn:hover     { border-color: var(--ink2); color: var(--ink); background: var(--wash); }
.act-btn.del:hover { border-color: var(--red);  color: var(--red); background: var(--red-lt); }

/* EMPTY */
.empty { padding: 48px 18px; text-align: center; color: var(--ink3); font-size: 12px; }
.empty svg { margin: 0 auto 10px; display: block; opacity: .3; }

/* FORM */
.form-group { display: flex; flex-direction: column; gap: 5px; }
.form-label { font-size: 11px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: var(--ink3); }
.form-input, .form-select, .form-textarea {
    font-family: 'IBM Plex Sans', sans-serif; font-size: 12.5px; color: var(--ink);
    background: var(--wash); border: 1px solid var(--rule); border-radius: 6px;
    padding: 8px 11px; outline: none; width: 100%;
    transition: border-color .12s, background .12s;
}
.form-input:focus, .form-select:focus, .form-textarea:focus { border-color: var(--blue); background: var(--surface); }
.form-textarea { resize: vertical; min-height: 72px; line-height: 1.5; }
.form-select { cursor: pointer; }
.form-hint { font-size: 10.5px; color: var(--red); margin-top: 2px; display: none; }
.form-hint.show { display: block; }

/* STATS */
.stats {
    display: grid; grid-template-columns: repeat(4,1fr);
    gap: 1px; background: var(--rule);
    border: 1px solid var(--rule); border-radius: 8px;
    overflow: hidden; margin-bottom: 22px;
}
.stat { background: var(--surface); padding: 20px 22px; position: relative; }
.stat-label { font-size: 10.5px; font-weight: 500; letter-spacing: .5px; text-transform: uppercase; color: var(--ink3); margin-bottom: 10px; }
.stat-num { font-size: 30px; font-weight: 300; letter-spacing: -1.2px; color: var(--ink); font-family: 'IBM Plex Mono', monospace; line-height: 1; margin-bottom: 8px; }
.stat-meta { display: flex; align-items: center; gap: 6px; font-size: 11px; color: var(--ink3); }
.delta { font-size: 10.5px; font-weight: 500; padding: 1px 6px; border-radius: 3px; font-family: 'IBM Plex Mono', monospace; }
.d-up  { background: var(--green-lt); color: var(--green); }
.d-neu { background: var(--wash2); color: var(--ink3); }
.stat-bar  { position: absolute; bottom: 0; left: 0; right: 0; height: 2px; }
.stat-fill { height: 100%; }

/* GRID */
.grid2 { display: grid; grid-template-columns: 1fr 296px; gap: 18px; align-items: start; }
.side  { display: flex; flex-direction: column; gap: 14px; }

/* MODAL */
.modal-overlay {
    position: fixed; inset: 0; background: rgba(13,17,23,.55);
    backdrop-filter: blur(2px); z-index: 200;
    display: flex; align-items: center; justify-content: center;
    opacity: 0; pointer-events: none; transition: opacity .18s;
}
.modal-overlay.open { opacity: 1; pointer-events: all; }
.modal {
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 10px; width: 100%; max-width: 440px; margin: 0 16px;
    box-shadow: 0 20px 48px rgba(13,17,23,.18);
    transform: translateY(10px) scale(.98); transition: transform .2s, opacity .2s; opacity: 0;
}
.modal-overlay.open .modal { transform: none; opacity: 1; }
.modal-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--rule); }
.modal-title { font-size: 13.5px; font-weight: 600; color: var(--ink); }
.modal-close { width: 26px; height: 26px; border: 1px solid var(--rule); border-radius: 5px; background: none; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--ink3); }
.modal-body { padding: 20px; display: flex; flex-direction: column; gap: 14px; }
.modal-foot { display: flex; align-items: center; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--rule); }

/* BTN FORM */
.btn-cancel { font-family: 'IBM Plex Sans', sans-serif; font-size: 12px; font-weight: 500; color: var(--ink3); background: none; border: 1px solid var(--rule); border-radius: 6px; padding: 7px 16px; cursor: pointer; }
.btn-cancel:hover { border-color: var(--ink2); color: var(--ink); }
.btn-submit { font-family: 'IBM Plex Sans', sans-serif; font-size: 12px; font-weight: 500; color: #fff; background: var(--blue); border: none; border-radius: 6px; padding: 7px 18px; cursor: pointer; }
.btn-submit:hover { opacity: .88; }

/* FAB */
.fab {
    position: fixed; bottom: 28px; right: 28px;
    width: 44px; height: 44px; border-radius: 50%;
    background: var(--blue); color: #fff; border: none; cursor: pointer;
    display: flex; align-items: center; justify-content: center;
    box-shadow: 0 4px 14px rgba(26,86,219,.35);
    transition: transform .15s, box-shadow .15s; z-index: 100;
}
.fab:hover { transform: scale(1.08); }

/* ANIMASI */
@keyframes up { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }
.panel { animation: up .25s ease both; }
.stats { animation: up .3s ease both; }
.grid2 { animation: up .3s .08s ease both; }

/* GREETING */
.greeting { font-size: 11.5px; color: var(--blue); background: var(--blue-lt); border: 1px solid rgba(26,86,219,.14); padding: 5px 12px; border-radius: 4px; font-weight: 500; }

/* MAVA */
.mava { display: inline-flex; align-items: center; justify-content: center; width: 24px; height: 24px; border-radius: 3px; background: var(--wash2); font-size: 9.5px; font-weight: 600; color: var(--ink2); text-transform: uppercase; margin-right: 8px; vertical-align: middle; }

/* HAMBURGER */
.menu-toggle {
    display: none; background: none; border: none; cursor: pointer;
    padding: 8px; margin-right: 8px; border-radius: 6px;
    color: var(--ink); transition: background 0.2s;
}
.menu-toggle:hover { background: var(--wash); }
.menu-toggle svg { width: 20px; height: 20px; stroke: currentColor; stroke-width: 1.8; fill: none; }

/* OVERLAY HP */
.sidebar-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 998; }
.sidebar-overlay.active { display: block; }

/* RESPONSIVE */
@media (max-width: 768px) {
    .menu-toggle { display: inline-flex; align-items: center; }
    .sidebar {
        position: fixed !important;
        left: -260px !important;
        top: 0; bottom: 0;
        z-index: 999;
        width: var(--sw) !important;
        box-shadow: 2px 0 16px rgba(0,0,0,.2);
        overflow-y: auto;
    }
    .sidebar.open { left: 0 !important; }
    .main { margin-left: 0 !important; width: 100%; }
    .topbar { padding-left: 12px; padding-right: 12px; }
    .body { padding: 16px 16px 80px; }
    .stats { grid-template-columns: 1fr 1fr; }
    .grid2 { grid-template-columns: 1fr; }
}
@media (min-width: 769px) {
    .sidebar {
        position: relative;
        left: 0 !important;
        overflow-y: auto;
    }
}
@media print {
    .sidebar { display: none !important; }
    .topbar  { display: none !important; }
}
    </style>

    @stack('styles')
</head>
<body>
<div class="shell">

    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- SIDEBAR --}}
    <nav class="sidebar" id="sidebar">
        <div class="sb-head">
            <div class="sb-brand"><span class="hl">PE</span>S<span class="hl">TA</span>RA</div>
            <div class="sb-wordmark">Petugas Statistik Terarah</div>
            <div class="sb-name">Portal Petugas</div>
        </div>

        @php
            $__clStatus = null;
            if (Auth::check()) {
                $__jam = now('Asia/Jakarta')->hour;
                $__shiftNow = null;
                if ($__jam >= 7 && $__jam < 12) $__shiftNow = 'pagi';
                elseif ($__jam >= 12 && $__jam < 17) $__shiftNow = 'siang';
                if ($__shiftNow) {
                    $__cl = \App\Models\ChecklistHarian::where('user_id', Auth::id())
                        ->whereDate('tanggal', now('Asia/Jakarta')->toDateString())
                        ->where('sesi', $__shiftNow)->first();
                    $__clStatus = $__cl->status ?? null;
                }
            }
            $__siTerbuka = \App\Helpers\SurveyInternalHelper::bisaDiakses();
            $__siPeriode = \App\Helpers\SurveyInternalHelper::periodeAktif();
        @endphp

        <div class="sb-nav">
            <div class="sb-section">
                <div class="sb-group-label">Utama</div>
                <a href="{{ route('petugas.dashboard') }}" class="sb-item {{ request()->is('petugas/dashboard') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="3" width="7" height="7" rx="1"/><rect x="14" y="3" width="7" height="7" rx="1"/><rect x="3" y="14" width="7" height="7" rx="1"/><rect x="14" y="14" width="7" height="7" rx="1"/></svg>
                    Dashboard
                </a>
                <a href="{{ route('petugas.jadwal') }}" class="sb-item {{ request()->is('petugas/jadwal*') ? 'active' : '' }}" id="sb-jadwal-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    Jadwal Saya
                </a>
                <a href="{{ route('petugas.absensi.index') }}" class="sb-item {{ request()->routeIs('petugas.absensi.*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                    Absensi QR
                </a>
                <a href="{{ route('petugas.checklist') }}" class="sb-item {{ request()->is('petugas/checklist*') ? 'active' : '' }}" id="sb-checklist-link" style="justify-content:space-between">
                    <span style="display:flex;align-items:center;gap:8px">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                        Checklist Harian
                    </span>
                    @if(isset($__clStatus))
                        @if($__clStatus === 'verified')
                            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#d1fae5;color:#065f46;flex-shrink:0">✓ OK</span>
                        @elseif($__clStatus === 'submit')
                            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#fef3c7;color:#92400e;flex-shrink:0">Menunggu</span>
                        @elseif($__clStatus === 'draft')
                            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#e0e7ff;color:#3730a3;flex-shrink:0">Draft</span>
                        @else
                            <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#fee2e2;color:#991b1b;flex-shrink:0">Belum</span>
                        @endif
                    @endif
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Layanan</div>
                <a href="{{ route('petugas.survey.index') }}" class="sb-item {{ request()->routeIs('petugas.survey.*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
                    Survey Kepuasan
                </a>
                <a href="{{ route('petugas.survey-internal.index') }}" class="sb-item {{ request()->routeIs('petugas.survey-internal.*') ? 'active' : '' }}" style="justify-content:space-between" id="sb-survey-internal-link">
                    <span style="display:flex;align-items:center;gap:8px">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
                        Survey Internal
                    </span>
                    @if($__siTerbuka)
                        <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#dcfce7;color:#15803d;flex-shrink:0">Buka</span>
                    @else
                        <span style="font-size:9px;font-weight:700;padding:1px 6px;border-radius:10px;background:#f1f5f9;color:#64748b;flex-shrink:0">Terkunci</span>
                    @endif
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Laporan</div>
                <a href="{{ route('petugas.laporan.harian.index') }}" class="sb-item {{ request()->routeIs('petugas.laporan.harian.*') ? 'active' : '' }}" id="sb-laporan-link-petugas">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/></svg>
                    Laporan Harian
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Pembelajaran</div>
                <a href="{{ route('petugas.materi') }}" class="sb-item {{ request()->routeIs('petugas.materi*') ? 'active' : '' }}" id="sb-materi-link-petugas">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
                    Materi &amp; Tugas
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Kinerja</div>
                <a href="{{ route('petugas.penilaian.index') }}" class="sb-item {{ request()->is('petugas/penilaian*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Nilai Saya
                </a>
            </div>
        </div>

        <div class="sb-foot">
            <div class="sb-user">
                <div class="sb-ava">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div style="flex:1;overflow:hidden">
                    <div class="sb-uname">{{ Auth::user()->name }}</div>
                </div>
                <form id="form-logout" method="POST" action="/logout">
                    @csrf
                    <button type="submit" class="logout-btn" title="Keluar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/><polyline points="16 17 21 12 16 7"/><line x1="21" y1="12" x2="9" y2="12"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    {{-- MAIN --}}
    <div class="main">
        <header class="topbar">
            <div style="display:flex;align-items:center">
                <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                    <svg viewBox="0 0 24 24"><line x1="3" y1="6" x2="21" y2="6"/><line x1="3" y1="12" x2="21" y2="12"/><line x1="3" y1="18" x2="21" y2="18"/></svg>
                </button>
                <div class="breadcrumb">@yield('breadcrumb')</div>
            </div>
            <div class="topbar-right">
                <span class="ts" id="ts"></span>
            </div>
        </header>

        <div class="body">
            @yield('content')
        </div>
    </div>

    @stack('modals')
</div>

<script>
// SIDEBAR TOGGLE
const menuToggle = document.getElementById('menuToggle');
const sidebar    = document.getElementById('sidebar');
const overlay    = document.getElementById('sidebarOverlay');
function closeSidebar() { sidebar && sidebar.classList.remove('open'); overlay && overlay.classList.remove('active'); }
function openSidebar()  { sidebar && sidebar.classList.add('open');    overlay && overlay.classList.add('active'); }
if (menuToggle) menuToggle.addEventListener('click', function(e) { e.stopPropagation(); sidebar.classList.contains('open') ? closeSidebar() : openSidebar(); });
if (overlay) overlay.addEventListener('click', closeSidebar);
if (sidebar) sidebar.querySelectorAll('a').forEach(l => l.addEventListener('click', () => { if (window.innerWidth <= 768) closeSidebar(); }));
window.addEventListener('resize', () => { if (window.innerWidth > 768) closeSidebar(); });

// JAM
function tick() {
    const tz = 'Asia/Jakarta';
    const n = new Date();
    const tgl = n.toLocaleDateString('id-ID', {weekday:'short',day:'2-digit',month:'short',year:'numeric',timeZone:tz});
    const jam = n.toLocaleTimeString('id-ID', {hour:'2-digit',minute:'2-digit',second:'2-digit',timeZone:tz,hour12:false});
    const el = document.getElementById('ts');
    if (el) el.textContent = tgl + ' · ' + jam + ' WIB';
}
tick(); setInterval(tick, 1000);

// SIDEBAR BADGE
function setSidebarBadge(id, count, type) {
    const link = document.getElementById(id); if (!link) return;
    const old = link.querySelector('.sb-rt-badge'); if (old) old.remove();
    if (!count || count <= 0) return;
    const colors = { alert:'background:#dc2626;color:#fff', warn:'background:#d97706;color:#fff', blue:'background:#1a56db;color:#fff' };
    const badge = document.createElement('span');
    badge.className = 'sb-rt-badge';
    badge.style.cssText = 'margin-left:auto;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;min-width:18px;text-align:center;flex-shrink:0;' + (colors[type]||colors.alert);
    badge.textContent = count > 99 ? '99+' : String(count);
    link.appendChild(badge);
}
// ── VISITED TRACKER ──────────────────────────────────────────────────
const _pCurrentPath = window.location.pathname;
function _pMarkVisited(key) { try { localStorage.setItem(key, '1'); } catch(e) {} }
function _pHasVisited(key) { try { return localStorage.getItem(key) === '1'; } catch(e) { return false; } }
const _pToday = new Date().toISOString().slice(0,10);
if (_pCurrentPath.startsWith('/petugas/jadwal'))           _pMarkVisited('visited_petugas_jadwal_' + _pToday);
if (_pCurrentPath.startsWith('/petugas/laporan'))          _pMarkVisited('visited_petugas_laporan_' + _pToday);
if (_pCurrentPath.startsWith('/petugas/materi'))           _pMarkVisited('visited_petugas_materi_' + _pToday);

// ── TOAST NOTIFIKASI ──────────────────────────────────────────────────
function showToast(msg) {
    let t = document.getElementById('_sb_toast');
    if (!t) {
        t = document.createElement('div');
        t.id = '_sb_toast';
        t.style.cssText = 'position:fixed;bottom:20px;right:20px;background:#1e293b;color:#fff;padding:10px 16px;border-radius:8px;font-size:12px;font-weight:500;z-index:9999;opacity:0;transition:opacity .3s;max-width:280px;line-height:1.4;box-shadow:0 4px 12px rgba(0,0,0,.3)';
        document.body.appendChild(t);
    }
    t.textContent = msg;
    t.style.opacity = '1';
    clearTimeout(t._tid);
    t._tid = setTimeout(() => t.style.opacity = '0', 4000);
}

let _prevPetugasBadges = {};

async function fetchPetugasBadges() {
    try {
        const res = await fetch('/api/sidebar-badges/petugas', {credentials:'same-origin'});
        if (!res.ok) return;
        const data = await res.json();

        // Laporan Harian – informatif (draft hari ini), badge hilang setelah halaman dibuka
        if (data.laporan_harian !== undefined) {
            const sudahBukaLaporan = _pHasVisited('visited_petugas_laporan_' + _pToday);
            if (!sudahBukaLaporan && _prevPetugasBadges.laporan_harian !== undefined && data.laporan_harian > _prevPetugasBadges.laporan_harian)
                showToast('Ada laporan harian yang belum diselesaikan');
            setSidebarBadge('sb-laporan-link-petugas', sudahBukaLaporan ? 0 : data.laporan_harian, 'warn');
            _prevPetugasBadges.laporan_harian = data.laporan_harian;
        }

        // Materi & Tugas – informatif, badge hilang setelah halaman dibuka
        if (data.materi_tugas !== undefined) {
            const sudahBukaMateri = _pHasVisited('visited_petugas_materi_' + _pToday);
            if (!sudahBukaMateri && _prevPetugasBadges.materi_tugas !== undefined && data.materi_tugas > _prevPetugasBadges.materi_tugas)
                showToast('Ada materi atau tugas baru yang perlu dikerjakan');
            setSidebarBadge('sb-materi-link-petugas', sudahBukaMateri ? 0 : data.materi_tugas, 'blue');
            _prevPetugasBadges.materi_tugas = data.materi_tugas;
        }

        // Checklist Harian – perlu verifikasi, badge tidak hilang hanya dengan diklik
        if (data.checklist_harian !== undefined) {
            if (_prevPetugasBadges.checklist_harian !== undefined && data.checklist_harian > _prevPetugasBadges.checklist_harian)
                showToast('Checklist harian belum diselesaikan');
            setSidebarBadge('sb-checklist-link', data.checklist_harian, 'alert');
            _prevPetugasBadges.checklist_harian = data.checklist_harian;
        }

        // Jadwal Saya – informatif, badge hilang setelah halaman dibuka
        if (data.jadwal_baru !== undefined) {
            const sudahBukaJadwal = _pHasVisited('visited_petugas_jadwal_' + _pToday);
            if (!sudahBukaJadwal && _prevPetugasBadges.jadwal_baru !== undefined && data.jadwal_baru > _prevPetugasBadges.jadwal_baru)
                showToast('Jadwal Anda baru saja diperbarui, cek sekarang!');
            setSidebarBadge('sb-jadwal-link', sudahBukaJadwal ? 0 : data.jadwal_baru, 'alert');
            _prevPetugasBadges.jadwal_baru = data.jadwal_baru;
        }

    } catch(e) {}
}
fetchPetugasBadges();
const _pInterval = setInterval(fetchPetugasBadges, 15000);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') fetchPetugasBadges();
});
window.addEventListener('pageshow', e => { if (e.persisted) window.location.reload(); });
</script>

@stack('scripts')
</body>
</html>