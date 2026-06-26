<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'Panel Koordinator') — PST BPS Babel</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
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
        .shell { display: flex; height: 100vh; overflow: hidden; }

        /* ── SIDEBAR ─────────────────────────────────────────────────── */
        .sidebar {
            width: var(--sw);
            background: var(--ink);
            color: #c8cdd6;
            display: flex;
            flex-direction: column;
            flex-shrink: 0;
            overflow-y: auto;
            overflow-x: hidden;
            scrollbar-width: none;
            -ms-overflow-style: none;
        }
        .sidebar::-webkit-scrollbar { display: none; }
        .sb-head { padding: 20px 18px 18px; border-bottom: 1px solid rgba(255,255,255,.08); }
        .sb-brand { font-size: 21px; font-weight: 800; letter-spacing: 0.5px; color: rgba(255,255,255,.32); line-height: 1.1; margin-bottom: 7px; }
        .sb-brand .hl { color: #4d8dff; }
        .sb-wordmark {
            font-size: 9.5px; font-weight: 500; letter-spacing: 1.6px;
            text-transform: uppercase; color: rgba(255,255,255,.42); margin-bottom: 6px;
        }
        .sb-name { font-size: 14px; font-weight: 600; color: #fff; }

        .sb-nav { padding: 14px 0; flex: 1; }
        .sb-section { padding: 0 10px; margin-bottom: 4px; }
        .sb-group-label {
            font-size: 9px; font-weight: 600; letter-spacing: 1.8px;
            text-transform: uppercase; color: rgba(255,255,255,.18); padding: 10px 8px 4px;
        }
        .sb-item {
            display: flex; align-items: center; gap: 9px;
            padding: 7px 8px; border-radius: 5px;
            color: rgba(255,255,255,.48); text-decoration: none;
            font-size: 12.5px; cursor: pointer;
            transition: background .12s, color .12s;
            margin-bottom: 1px;
        }
        .sb-item:hover  { background: rgba(255,255,255,.06); color: rgba(255,255,255,.82); }
        .sb-item.active { background: rgba(26,86,219,.22); color: #6da4f7; }
        .sb-count {
            margin-left: auto;
            font-size: 10px; font-family: 'IBM Plex Mono', monospace;
            background: rgba(255,255,255,.09); color: rgba(255,255,255,.4);
            padding: 1px 6px; border-radius: 3px;
        }
        .sb-item.active .sb-count { background: rgba(26,86,219,.35); color: #6da4f7; }
        /* Badge realtime hijau (diisi JS) */
        .sb-live-count {
            margin-left: auto; font-size: 10px; font-weight: 600;
            background: #0a7c4e; color: #fff;
            padding: 1px 6px; border-radius: 10px; min-width: 16px; text-align: center;
        }

        .sb-foot { padding: 12px 10px; border-top: 1px solid rgba(255,255,255,.07); }
        .sb-user { display: flex; align-items: center; gap: 10px; padding: 8px; }
        .sb-ava {
            width: 28px; height: 28px; border-radius: 4px;
            background: rgba(26,86,219,.45);
            display: flex; align-items: center; justify-content: center;
            font-size: 10.5px; font-weight: 600; color: #6da4f7; flex-shrink: 0;
        }
        .sb-uname {
            font-size: 12px; font-weight: 500; color: rgba(255,255,255,.72);
            white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
        }
        .sb-role { font-size: 10.5px; color: rgba(255,255,255,.28); text-transform: capitalize; }
        .logout-btn {
            background: none; border: none; padding: 5px; cursor: pointer;
            color: rgba(255,255,255,.22); border-radius: 4px;
            transition: color .15s; display: flex; margin-left: auto;
        }
        .logout-btn:hover { color: rgba(220,60,60,.8); }

        /* ── MAIN AREA ───────────────────────────────────────────────── */
        .main { flex: 1; display: flex; flex-direction: column; overflow: hidden; min-width: 0; }

        .topbar {
            height: 50px;
            background: var(--surface);
            border-bottom: 1px solid var(--rule);
            display: flex; align-items: center; justify-content: space-between;
            padding: 0 28px; flex-shrink: 0;
        }
        .breadcrumb { display: flex; align-items: center; gap: 6px; font-size: 12px; color: var(--ink3); }
        .breadcrumb a    { color: var(--ink3); text-decoration: none; }
        .breadcrumb a:hover { color: var(--ink); }
        .breadcrumb strong { color: var(--ink); font-weight: 500; }
        .topbar-right { display: flex; align-items: center; gap: 12px; }
        .ts { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink3); }
        .icon-btn {
            width: 30px; height: 30px;
            border: 1px solid var(--rule); border-radius: 5px;
            background: none; cursor: pointer;
            display: flex; align-items: center; justify-content: center;
            color: var(--ink3); transition: border-color .12s, color .12s; position: relative;
        }
        .icon-btn:hover { border-color: var(--ink2); color: var(--ink); }
        .pip {
            position: absolute; top: 6px; right: 6px;
            width: 5px; height: 5px;
            background: var(--blue); border-radius: 50%;
            border: 1.5px solid var(--surface);
        }

        .body { flex: 1; overflow-y: auto; padding: 28px 28px 48px; }

        /* ── SHARED COMPONENT STYLES ───── */
        .page-head {
            display: flex; align-items: flex-end; justify-content: space-between;
            margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        }
        .page-head h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; }
        .page-head p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }
        .greeting {
            font-size: 11.5px; color: var(--blue);
            background: var(--blue-lt); border: 1px solid rgba(26,86,219,.14);
            padding: 5px 12px; border-radius: 4px; font-weight: 500;
        }

        .panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
        .ph { display: flex; align-items: center; justify-content: space-between; padding: 13px 18px; border-bottom: 1px solid var(--rule); }
        .ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
        .ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }
        .ph-link  { font-size: 11.5px; color: var(--blue); text-decoration: none; font-weight: 500; padding: 3px 8px; border-radius: 4px; }
        .ph-link:hover { background: var(--blue-lt); }

        table { width: 100%; border-collapse: collapse; }
        thead th {
            text-align: left; padding: 8px 18px;
            font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
            color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
        }
        tbody tr  { border-bottom: 1px solid var(--rule); transition: background .1s; }
        tbody tr:last-child { border-bottom: none; }
        tbody tr:hover { background: var(--wash); }
        tbody td  { padding: 10px 18px; vertical-align: middle; }
        .td-main  { font-weight: 500; color: var(--ink); font-size: 12.5px; }
        .td-id    { font-size: 10.5px; color: var(--ink3); margin-top: 2px; font-family: 'IBM Plex Mono', monospace; }
        .mava {
            display: inline-flex; align-items: center; justify-content: center;
            width: 22px; height: 22px; border-radius: 3px;
            background: var(--wash2); font-size: 9px; font-weight: 600;
            color: var(--ink2); text-transform: uppercase;
            margin-right: 7px; vertical-align: middle;
        }

        .pill   { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 7px; border-radius: 3px; }
        .p-green { background: var(--green-lt); color: var(--green); }
        .p-amber { background: var(--amber-lt); color: var(--amber); }
        .p-red   { background: var(--red-lt);   color: var(--red); }
        .p-blue  { background: var(--blue-lt);  color: var(--blue); }
        .p-gray  { background: var(--wash2);    color: var(--ink3); }
        .mono    { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; color: var(--ink2); }

        .alert {
            display: flex; align-items: center; gap: 8px;
            padding: 10px 16px; border-radius: 8px;
            margin-bottom: 16px; font-size: .875rem; font-weight: 500;
        }
        .alert-success { background: #f0fdf4; color: #166534; border: 1px solid #bbf7d0; }
        .alert-error   { background: #fef2f2; color: #991b1b; border: 1px solid #fecaca; }

        .empty {
            padding: 40px 20px; text-align: center;
            color: var(--ink3); font-size: 13px; line-height: 2;
        }
        .empty svg { display: block; margin: 0 auto 10px; color: var(--rule); }

        @keyframes up { from { opacity: 0; transform: translateY(8px); } to { opacity: 1; transform: none; } }

        /* HAMBURGER BUTTON - hanya muncul di HP */
        .menu-toggle {
            display: none;
            background: none;
            border: none;
            cursor: pointer;
            padding: 8px;
            margin-right: 12px;
            border-radius: 6px;
            color: var(--ink);
            transition: background 0.2s;
        }
        .menu-toggle:hover { background: var(--wash); }
        .menu-toggle svg {
            width: 22px; height: 22px;
            stroke: currentColor; stroke-width: 1.8; fill: none;
        }

        /* SIDEBAR OVERLAY (untuk HP) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0; left: 0; right: 0; bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.active { display: block; }

        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 768px) {
            .shell { position: relative; }

            .menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            .sidebar {
                position: fixed !important;
                left: -280px !important;
                top: 0 !important; bottom: 0 !important;
                z-index: 999 !important;
                transition: left 0.3s ease !important;
                box-shadow: 2px 0 12px rgba(0,0,0,0.1) !important;
                width: var(--sw) !important;
                background: var(--ink) !important;
                overflow-y: auto !important;
            }

            .sidebar.open { left: 0 !important; }

            .main { margin-left: 0 !important; width: 100% !important; }

            .topbar {
                padding-left: 16px !important;
                padding-right: 16px !important;
            }

            .body { padding: 16px !important; }
        }

        @media (min-width: 769px) {
            .sidebar { position: relative; left: 0 !important; }
        }
    </style>

    @stack('styles')
</head>
<body>
<div class="shell">

    {{-- Overlay untuk menutup sidebar di HP --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    <nav class="sidebar" id="sidebar">
        <div class="sb-head">
            <div class="sb-brand"><span class="hl">PE</span>S<span class="hl">TA</span>RA</div>
            <div class="sb-wordmark">Petugas Statistik Terarah</div>
            <div class="sb-name">Panel Koordinator</div>
        </div>

        <div class="sb-nav">
            <div class="sb-section">
                <div class="sb-group-label">Utama</div>

                <a href="{{ route('koordinator.dashboard') }}"
                   class="sb-item {{ request()->routeIs('koordinator.dashboard') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>

                <a href="{{ route('jadwal.index') }}"
                   class="sb-item {{ request()->routeIs('jadwal.*') ? 'active' : '' }}"
                   id="sb-jadwal-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Jadwal Petugas
                </a>

                {{-- ✅ FIX: href # diganti route yang benar --}}
                <a href="{{ route('koordinator.absensi.index') }}"
                   class="sb-item {{ request()->routeIs('koordinator.absensi.*') ? 'active' : '' }}"
                   id="sb-absensi-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                    </svg>
                    Absensi
                </a>

                <a href="{{ route('koordinator.checklist.index') }}"
                   class="sb-item {{ request()->routeIs('koordinator.checklist*') ? 'active' : '' }}"
                   id="sb-checklist-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                        <path d="M9 12l2 2 4-4"/>
                    </svg>
                    Checklist Harian
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Operasional</div>

                <a href="{{ route('koordinator.materi.index') }}"
                    class="sb-item {{ request()->routeIs('koordinator.materi*') ? 'active' : '' }}"
                    id="sb-materi-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                    </svg>
                    Materi &amp; Tugas
                </a>

                <a href="{{ route('koordinator.laporan.harian.index') }}"
                    class="sb-item {{ request()->routeIs('koordinator.laporan.harian.*') ? 'active' : '' }}"
                    id="sb-laporan-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="9" y1="13" x2="15" y2="13"/>
                        <line x1="9" y1="17" x2="12" y2="17"/>
                    </svg>
                    Laporan Harian
                    @php
                        $totalPendingLaporan = \App\Models\LaporanHarianBaru::where('wilayah_id', Auth::user()->wilayah_id)
                            ->where('status', 'submitted')->count();
                    @endphp
                    @if($totalPendingLaporan > 0)
                        <span class="sb-count">{{ $totalPendingLaporan }}</span>
                    @endif
                </a>

                <a href="{{ route('koordinator.tim-petugas') }}"
                    class="sb-item {{ request()->routeIs('koordinator.tim-petugas') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    Tim Petugas
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Penilaian</div>

                <a href="{{ route('koordinator.nilai-evaluasi.index') }}"
                   class="sb-item {{ request()->routeIs('koordinator.nilai-evaluasi.*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Nilai &amp; Evaluasi
                </a>

                <a href="{{ route('koordinator.survey.index') }}"
                   class="sb-item {{ request()->routeIs('koordinator.survey.*') || request()->routeIs('koordinator.survey-internal.*') ? 'active' : '' }}"
                   id="sb-survey-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Survey Kepuasan
                </a>
            </div>
        </div>

        <div class="sb-foot">
            <div class="sb-user">
                <div class="sb-ava">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div style="flex:1;overflow:hidden">
                    <div class="sb-uname">{{ Auth::user()->name }}</div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="logout-btn" title="Keluar">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M9 21H5a2 2 0 01-2-2V5a2 2 0 012-2h4"/>
                            <polyline points="16 17 21 12 16 7"/>
                            <line x1="21" y1="12" x2="9" y2="12"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </nav>

    <div class="main">
        <header class="topbar">
            <div style="display: flex; align-items: center;">
                {{-- HAMBURGER MENU BUTTON (hanya muncul di HP) --}}
                <button class="menu-toggle" id="menuToggle" aria-label="Menu">
                    <svg viewBox="0 0 24 24" stroke="currentColor" stroke-width="1.8" fill="none">
                        <line x1="3" y1="12" x2="21" y2="12"/>
                        <line x1="3" y1="6" x2="21" y2="6"/>
                        <line x1="3" y1="18" x2="21" y2="18"/>
                    </svg>
                </button>
                <div class="breadcrumb">
                    {!! $__env->yieldContent('breadcrumb', '<span>PST</span>') !!}
                </div>
            </div>
            <div class="topbar-right">
                <span class="ts" id="ts"></span>
                <button class="icon-btn">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/>
                    </svg>
                    <span class="pip"></span>
                </button>
            </div>
        </header>

        <div class="body">
            @yield('content')
        </div>

    </div>

    {{-- Slot tambahan (modal, FAB, dsb) --}}
    @stack('modals')

</div>{{-- end .shell --}}

<script>
// ── SIDEBAR TOGGLE (HP) ───────────────────────────────────────────────
const menuToggle = document.getElementById('menuToggle');
const sidebar    = document.getElementById('sidebar');
const overlay    = document.getElementById('sidebarOverlay');

function closeSidebar() {
    if (sidebar) sidebar.classList.remove('open');
    if (overlay) overlay.classList.remove('active');
}
function openSidebar() {
    if (sidebar) sidebar.classList.add('open');
    if (overlay) overlay.classList.add('active');
}
if (menuToggle) {
    menuToggle.addEventListener('click', function(e) {
        e.stopPropagation();
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
    });
}
if (overlay) overlay.addEventListener('click', closeSidebar);
if (sidebar) {
    sidebar.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) closeSidebar();
        });
    });
}
window.addEventListener('resize', function() {
    if (window.innerWidth > 768) closeSidebar();
});

// ── JAM ──────────────────────────────────────────────────────────────
function tick() {
    const n  = new Date();
    const tz = 'Asia/Jakarta';
    const tgl = n.toLocaleDateString('id-ID', { weekday:'short',day:'2-digit',month:'short',year:'numeric',timeZone:tz });
    const jam = n.toLocaleTimeString('id-ID', { hour:'2-digit',minute:'2-digit',second:'2-digit',timeZone:tz,hour12:false });
    const tsEl = document.getElementById('ts');
    if (tsEl) tsEl.textContent = tgl + ' · ' + jam + ' WIB';
}
tick();
setInterval(tick, 1000);

// ── SIDEBAR BADGE REALTIME ────────────────────────────────────────────
function setSidebarBadge(id, count, type, label) {
    type  = type  || 'alert';
    label = label || null;
    const link = document.getElementById(id);
    if (!link) return;
    const old = link.querySelector('.sb-rt-badge');
    if (old) old.remove();
    if (!count || count <= 0) {
        const staticCount = link.querySelector('.sb-count');
        if (staticCount) staticCount.style.display = '';
        return;
    }
    const colors = {
        alert: 'background:#dc2626;color:#fff',
        warn:  'background:#d97706;color:#fff',
        info:  'background:#0a7c4e;color:#fff',
        blue:  'background:#1a56db;color:#fff',
    };
    const style = colors[type] || colors.alert;
    const text  = label || (count > 99 ? '99+' : String(count));
    const badge = document.createElement('span');
    badge.className = 'sb-rt-badge';
    badge.style.cssText = 'margin-left:auto;font-size:10px;font-weight:700;padding:1px 7px;border-radius:10px;min-width:18px;text-align:center;flex-shrink:0;' + style;
    badge.textContent = text;
    const staticCount = link.querySelector('.sb-count');
    if (staticCount) staticCount.style.display = 'none';
    link.appendChild(badge);
}

let _prevBadges = {};

function showToast(msg) {
    let toast = document.getElementById('sb-toast');
    if (!toast) {
        toast = document.createElement('div');
        toast.id = 'sb-toast';
        toast.style.cssText = 'position:fixed;bottom:24px;right:24px;z-index:9999;max-width:320px;padding:12px 18px;border-radius:8px;font-family:"IBM Plex Sans",sans-serif;font-size:12.5px;font-weight:500;box-shadow:0 4px 16px rgba(0,0,0,.18);transition:opacity .3s,transform .3s;background:#0d1117;color:#fff;display:flex;align-items:center;gap:10px;';
        document.body.appendChild(toast);
    }
    toast.innerHTML = '<svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="flex-shrink:0;color:#4ade80"><path d="M18 8A6 6 0 006 8c0 7-3 9-3 9h18s-3-2-3-9M13.73 21a2 2 0 01-3.46 0"/></svg><span>' + msg + '</span>';
    toast.style.opacity = '1';
    toast.style.transform = 'translateY(0)';
    clearTimeout(toast._t);
    toast._t = setTimeout(function() { toast.style.opacity='0'; toast.style.transform='translateY(8px)'; }, 4500);
}

// ── HELPER: tandai halaman sudah dibuka (simpan di sessionStorage) ───
const _currentPath = window.location.pathname;
function _markVisited(key) {
    try { localStorage.setItem(key, '1'); } catch(e) {}
}
function _hasVisited(key) {
    try { return localStorage.getItem(key) === '1'; } catch(e) { return false; }
}
const _today = new Date().toISOString().slice(0,10);
// Tandai halaman yang sedang dibuka sekarang
if (_currentPath.startsWith('/koordinator/survey'))  _markVisited('visited_koor_survey_'  + _today);
if (_currentPath.startsWith('/koordinator/absensi')) _markVisited('visited_koor_absensi_' + _today);
if (_currentPath.startsWith('/koordinator/jadwal'))  _markVisited('visited_koor_jadwal_'  + _today);

function applyKoorBadges(data) {
    // ── LAPORAN HARIAN: perlu verifikasi → badge selalu tampil ──────────
    if (data.laporan_harian !== undefined) {
        if (_prevBadges.laporan_harian !== undefined && data.laporan_harian > _prevBadges.laporan_harian)
            showToast('Laporan Harian: ' + data.laporan_harian + ' laporan baru menunggu review');
        setSidebarBadge('sb-laporan-link', data.laporan_harian, 'alert');
        _prevBadges.laporan_harian = data.laporan_harian;
    }
    // ── CHECKLIST HARIAN: perlu verifikasi → badge selalu tampil ────────
    if (data.checklist_harian !== undefined) {
        if (_prevBadges.checklist_harian !== undefined && data.checklist_harian > _prevBadges.checklist_harian)
            showToast('Checklist Harian: ' + data.checklist_harian + ' checklist menunggu verifikasi');
        setSidebarBadge('sb-checklist-link', data.checklist_harian, 'warn');
        _prevBadges.checklist_harian = data.checklist_harian;
    }
    // ── MATERI & TUGAS: jawaban pending perlu dinilai ────────────────────
    if (data.materi_tugas !== undefined) {
        if (_prevBadges.materi_tugas !== undefined && data.materi_tugas > _prevBadges.materi_tugas)
            showToast('Materi & Tugas: ada jawaban baru yang perlu dinilai');
        setSidebarBadge('sb-materi-link', data.materi_tugas, 'blue');
        _prevBadges.materi_tugas = data.materi_tugas;
    }
    // ── JADWAL: ada jadwal baru/diupdate admin → hilang setelah dibuka ───
    if (data.jadwal_diupdate !== undefined) {
        const sudahBukaJadwal = _hasVisited('visited_koor_jadwal_'  + _today);
        if (!sudahBukaJadwal && _prevBadges.jadwal_diupdate !== undefined && data.jadwal_diupdate > _prevBadges.jadwal_diupdate)
            showToast('Jadwal petugas wilayah Anda baru diperbarui oleh Admin');
        setSidebarBadge('sb-jadwal-link', sudahBukaJadwal ? 0 : data.jadwal_diupdate, 'info');
        _prevBadges.jadwal_diupdate = data.jadwal_diupdate;
    }
    // ── ABSENSI: informatif, tidak perlu verifikasi ──────────────────────
    // Badge hilang begitu halaman absensi pernah dibuka (dalam sesi ini)
    if (data.absensi_hari_ini !== undefined) {
        const sudahBuka = _hasVisited('visited_koor_absensi_' + _today);
        setSidebarBadge('sb-absensi-link', sudahBuka ? 0 : data.absensi_hari_ini, 'info');
        _prevBadges.absensi_hari_ini = data.absensi_hari_ini;
    }
    // ── SURVEY KEPUASAN: informatif, tidak perlu verifikasi ─────────────
    // Badge hilang begitu halaman survey pernah dibuka (dalam sesi ini)
    if (data.survey_kepuasan !== undefined) {
        const sudahBuka = _hasVisited('visited_koor_survey_'  + _today);
        setSidebarBadge('sb-survey-link', sudahBuka ? 0 : data.survey_kepuasan, 'info');
        _prevBadges.survey_kepuasan = data.survey_kepuasan;
    }
}

async function fetchKoorBadges() {
    try {
        const res = await fetch('/api/sidebar-badges/koordinator', { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        applyKoorBadges(data);
    } catch(e) { /* silent */ }
}

fetchKoorBadges();
setInterval(fetchKoorBadges, 15000);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') fetchKoorBadges();
});

// Paksa reload jika halaman di-restore dari bfcache
window.addEventListener('pageshow', function(e) { if (e.persisted) window.location.reload(); });
</script>

@stack('scripts')

</body>
</html>