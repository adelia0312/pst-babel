<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'PST') — Panel Administrasi</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=IBM+Plex+Sans:wght@300;400;500;600&family=IBM+Plex+Mono:wght@400;500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('css/admin.css') }}">
    
    {{-- Additional responsive styles for sidebar --}}
    <style>
        /* CSS VARIABLES & RESET */
        :root {
            --sidebar-width: 260px;
            --topbar-height: 60px;
        }
        
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
        .menu-toggle:hover {
            background: var(--wash);
        }
        .menu-toggle svg {
            width: 22px;
            height: 22px;
            stroke: currentColor;
            stroke-width: 1.8;
            fill: none;
        }
        
        /* SIDEBAR OVERLAY (untuk HP) */
        .sidebar-overlay {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.5);
            z-index: 998;
            backdrop-filter: blur(2px);
        }
        .sidebar-overlay.active {
            display: block;
        }
        
        /* RESPONSIVE BREAKPOINTS */
        @media (max-width: 768px) {
            /* Tombol menu muncul */
            .menu-toggle {
                display: inline-flex;
                align-items: center;
                justify-content: center;
            }

            /* Sidebar off-canvas */
            .sidebar {
                position: fixed !important;
                left: -260px !important;
                top: 0 !important;
                bottom: 0 !important;
                z-index: 999 !important;
                transition: left 0.3s ease !important;
                box-shadow: 2px 0 16px rgba(0,0,0,.2) !important;
                width: var(--sidebar-width) !important;
                background: var(--ink) !important;
                overflow-y: auto !important;
            }

            .sidebar.open {
                left: 0 !important;
            }

            /* Main content full width */
            .main {
                margin-left: 0 !important;
                width: 100% !important;
            }

            /* Topbar adjustment */
            .topbar {
                padding-left: 12px !important;
                padding-right: 12px !important;
            }

            /* Body padding lebih kecil di HP */
            .body {
                padding: 16px !important;
            }
        }

        /* Komputer: sidebar tetap di kiri */
        @media (min-width: 769px) {
            .sidebar {
                position: relative;
                left: 0 !important;
            }
        }
    </style>
    
    @stack('styles')
</head>
<body>
<div class="shell">

    {{-- Overlay untuk menutup sidebar di HP --}}
    <div class="sidebar-overlay" id="sidebarOverlay"></div>

    {{-- ── SIDEBAR ── --}}
    <nav class="sidebar" id="sidebar">
        <div class="sb-head">
            <div class="sb-brand"><span class="hl">PE</span>S<span class="hl">TA</span>RA</div>
            <div class="sb-wordmark">Petugas Statistik Terarah</div>
            <div class="sb-name">Panel Administrasi</div>
        </div>
        <div class="sb-nav">
            <div class="sb-section">
                <div class="sb-group-label">Utama</div>
                <a href="{{ url('/admin/dashboard') }}"
                   class="sb-item {{ request()->is('admin/dashboard') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7" rx="1"/>
                        <rect x="14" y="3" width="7" height="7" rx="1"/>
                        <rect x="3" y="14" width="7" height="7" rx="1"/>
                        <rect x="14" y="14" width="7" height="7" rx="1"/>
                    </svg>
                    Dashboard
                </a>
                <a href="{{ route('admin.jadwal.index') }}" class="sb-item {{ request()->is('admin/jadwal*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/>
                        <line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    Jadwal Petugas
                </a>
                <a href="{{ route('admin.absensi.index') }}" class="sb-item {{ request()->is('admin/absensi*') ? 'active' : '' }}" id="sb-absensi-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 11l3 3L22 4"/>
                        <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                    </svg>
                    Absensi
                </a>
                <a href="{{ route('admin.checklist.index') }}" class="sb-item {{ request()->is('admin/checklist*') ? 'active' : '' }}" id="sb-checklist-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                        <line x1="9" y1="12" x2="15" y2="12"/>
                        <line x1="9" y1="16" x2="13" y2="16"/>
                    </svg>
                    Checklist Harian
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Manajemen</div>
                @php $pendingAdmin = \App\Models\LaporanHarianBaru::where('status', 'submitted')->count(); @endphp
                <a href="{{ route('admin.laporanharian.index') }}"
                   class="sb-item {{ request()->routeIs('admin.laporanharian.*') ? 'active' : '' }}" id="sb-laporan-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                        <line x1="9" y1="13" x2="15" y2="13"/>
                        <line x1="9" y1="17" x2="12" y2="17"/>
                    </svg>
                    Laporan Harian
                    @if($pendingAdmin > 0)
                        <span class="sb-count">{{ $pendingAdmin }}</span>
                    @endif
                </a>
                <a href="{{ route('admin.tim-petugas') }}"
                   class="sb-item {{ request()->is('admin/tim-petugas*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/>
                        <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                    </svg>
                    Tim Petugas
                </a>
                <a href="{{ route('admin.materi') }}"
                   class="sb-item {{ request()->is('admin/materi*') || request()->is('admin/tugas*') ? 'active' : '' }}" id="sb-materi-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                        <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                    </svg>
                    Materi &amp; Pembelajaran
                </a>
                <a href="{{ route('admin.survey.pertanyaan') }}"
                   class="sb-item {{ request()->is('admin/survey*') ? 'active' : '' }}" id="sb-survey-link">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
                    </svg>
                    Survey Kepuasan
                </a>

                <a href="{{ route('admin.penilaian.index') }}"
                   class="sb-item {{ request()->routeIs('admin.penilaian.*') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
                    </svg>
                    Rekap Penilaian
                </a>
            </div>

            <div class="sb-section">
                <div class="sb-group-label">Sistem</div>
                <a href="{{ route('admin.pengaturan') }}" 
                class="sb-item {{ request()->routeIs('admin.pengaturan') ? 'active' : '' }}">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="3"/>
                        <path d="M19.07 4.93l-1.41 1.41M19.07 19.07l-1.41-1.41M4.93 19.07l1.41-1.41M4.93 4.93l1.41 1.41M21 12h-2M5 12H3M12 21v-2M12 5V3"/>
                    </svg>
                    Pengaturan
                </a>
            </div>
        </div>

        <div class="sb-foot">
            <div class="sb-user">
                <div class="sb-ava">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div style="flex:1;overflow:hidden">
                    <div class="sb-uname">{{ Auth::user()->name }}</div>
                </div>
                <form method="POST" action="/logout">
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

    {{-- ── MAIN ── --}}
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
                    @yield('breadcrumb')
                </div>
            </div>
            <div class="topbar-right">
                <span class="ts" id="ts"></span>
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
if (_currentPath.startsWith('/admin/survey'))  _markVisited('visited_admin_survey_'  + _today);
if (_currentPath.startsWith('/admin/absensi')) _markVisited('visited_admin_absensi_' + _today);

function applyAdminBadges(data) {
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
    // ── MATERI / TUGAS: perlu penilaian → badge selalu tampil ───────────
    if (data.materi_tugas !== undefined) {
        if (_prevBadges.materi_tugas !== undefined && data.materi_tugas > _prevBadges.materi_tugas)
            showToast('Materi & Tugas: ada jawaban baru yang perlu dinilai');
        setSidebarBadge('sb-materi-link', data.materi_tugas, 'blue');
        _prevBadges.materi_tugas = data.materi_tugas;
    }
    // ── SURVEY KEPUASAN: informatif, tidak perlu verifikasi ─────────────
    // Badge hilang begitu halaman survey pernah dibuka (dalam sesi ini)
    if (data.survey_kepuasan !== undefined) {
        const sudahBuka = _hasVisited('visited_admin_survey_' + _today);
        setSidebarBadge('sb-survey-link', sudahBuka ? 0 : data.survey_kepuasan, 'info');
        _prevBadges.survey_kepuasan = data.survey_kepuasan;
    }
    // ── ABSENSI: informatif, tidak perlu verifikasi ──────────────────────
    // Badge hilang begitu halaman absensi pernah dibuka (dalam sesi ini)
    if (data.absensi_hari_ini !== undefined) {
        const sudahBuka = _hasVisited('visited_admin_absensi_' + _today);
        setSidebarBadge('sb-absensi-link', sudahBuka ? 0 : data.absensi_hari_ini, 'info');
        _prevBadges.absensi_hari_ini = data.absensi_hari_ini;
    }
}

async function fetchAdminBadges() {
    try {
        const res = await fetch('/api/sidebar-badges/admin', { credentials: 'same-origin' });
        if (!res.ok) return;
        const data = await res.json();
        applyAdminBadges(data);
    } catch(e) { /* silent */ }
}

fetchAdminBadges();
setInterval(fetchAdminBadges, 15000);
document.addEventListener('visibilitychange', function() {
    if (document.visibilityState === 'visible') fetchAdminBadges();
});

// Paksa reload jika halaman di-restore dari bfcache
window.addEventListener('pageshow', function(e) { if (e.persisted) window.location.reload(); });
</script>

@stack('scripts')
</body>
</html>