@extends('layouts.admin')

@section('title', 'Materi & Pembelajaran')

@section('breadcrumb')
    <span>PST</span><span>›</span><span>Admin</span><span>›</span><strong>Materi & Pembelajaran</strong>
@endsection

{{-- Toast container --}}
<div id="rt-toast-wrap"></div>

@push('styles')
<style>
    /* ── STATS MATERI ─────────────────────────────────── */
    .mt-stats {
        display: grid; grid-template-columns: repeat(3,1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 20px;
    }
    .mt-stat { background: var(--surface); padding: 16px 18px; position: relative; overflow: hidden; }
    .mt-stat-label { font-size: 10px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; color: var(--ink3); margin-bottom: 6px; }
    .mt-stat-val { font-size: 26px; font-weight: 300; letter-spacing: -1px; font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1; }
    .mt-stat-sub { font-size: 11px; color: var(--ink3); margin-top: 4px; }

    /* ── TAB BAR ─────────────────────────────────────── */
    .tab-bar { display: flex; gap: 0; border-bottom: 2px solid var(--rule); margin-bottom: 0; }
    .tab-btn {
        padding: 9px 20px; font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12.5px; font-weight: 500; color: var(--ink3); background: none;
        border: none; border-bottom: 2px solid transparent; margin-bottom: -2px;
        cursor: pointer; display: flex; align-items: center; gap: 7px;
        transition: color .12s, border-color .12s;
    }
    .tab-btn:hover { color: var(--ink); }
    .tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); font-weight: 600; }
    .tab-count { font-size: 10px; font-family: 'IBM Plex Mono', monospace; background: var(--wash2); color: var(--ink3); padding: 1px 6px; border-radius: 3px; }
    .tab-btn.active .tab-count { background: var(--blue-lt); color: var(--blue); }
    .tab-panel { display: none; padding: 20px; }
    .tab-panel.active { display: block; }

    /* ── MATERI CARD ─────────────────────────────────── */
    .materi-card {
        border: 1px solid var(--rule); border-radius: 8px;
        padding: 14px 16px; background: var(--surface); margin-bottom: 12px;
        transition: box-shadow .2s, transform .15s;
    }
    .materi-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.07); transform: translateY(-1px); }
    .materi-card:last-child { margin-bottom: 0; }
    .materi-meta { display: flex; align-items: center; gap: 6px; font-size: 10.5px; color: var(--ink3); margin-bottom: 6px; }
    .materi-ava { width: 20px; height: 20px; border-radius: 50%; background: var(--blue-lt); display: flex; align-items: center; justify-content: center; font-size: 7px; font-weight: 700; color: var(--blue); flex-shrink: 0; }
    .materi-title { font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 5px; line-height: 1.4; }
    .materi-desc { font-size: 11.5px; color: var(--ink2); line-height: 1.6; margin-bottom: 10px; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
    .materi-badges { display: flex; flex-wrap: wrap; gap: 6px; margin-bottom: 10px; }
    .badge { display: inline-flex; align-items: center; gap: 4px; padding: 3px 8px; border-radius: 4px; font-size: 10.5px; text-decoration: none; background: var(--wash); color: var(--ink2); transition: background .15s; }
    .badge:hover { background: var(--wash2); }

    /* ── TUGAS CARD ──────────────────────────────────── */
    .tugas-card { border: 1px solid var(--rule); border-radius: 8px; padding: 14px 16px; background: var(--surface); margin-bottom: 12px; transition: box-shadow .2s; }
    .tugas-card:hover { box-shadow: 0 3px 12px rgba(0,0,0,.07); }
    .tugas-card:last-child { margin-bottom: 0; }
    .tugas-title { font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 8px; line-height: 1.4; }
    .tugas-stat { font-size: 11px; color: var(--ink3); margin-bottom: 6px; display: flex; align-items: center; gap: 4px; }
    .progress-label { display: flex; justify-content: space-between; font-size: 10.5px; color: var(--ink3); margin-bottom: 4px; }
    .progress-label b { color: var(--blue); font-weight: 600; }
    .progress-bar-wrap { height: 5px; background: var(--wash2); border-radius: 3px; overflow: hidden; margin-bottom: 10px; }
    .progress-bar-fill { height: 100%; background: var(--blue); border-radius: 3px; transition: width .5s; }
    .act-btn-sm { padding: 4px 9px; border: 1px solid var(--rule); background: var(--surface); border-radius: 4px; font-size: 10.5px; font-weight: 500; cursor: pointer; transition: all .15s; color: var(--ink2); display: inline-flex; align-items: center; gap: 3px; text-decoration: none; }
    .act-btn-sm:hover { border-color: var(--blue); color: var(--blue); }

    /* ── BUTTONS ─────────────────────────────────────── */
    .btn-primary { display: inline-flex; align-items: center; gap: 5px; padding: 7px 13px; background: var(--blue); color: white; border: none; border-radius: 6px; font-size: 11.5px; font-weight: 600; cursor: pointer; text-decoration: none; transition: opacity .2s; white-space: nowrap; }
    .btn-primary:hover { opacity: .88; color: white; }
    .btn-amber { display: inline-flex; align-items: center; gap: 5px; padding: 7px 13px; background: #9a6200; color: white; border: none; border-radius: 6px; font-size: 11.5px; font-weight: 600; cursor: pointer; text-decoration: none; transition: opacity .2s; white-space: nowrap; }
    .btn-amber:hover { opacity: .88; color: white; }

    .empty-state { text-align: center; padding: 40px 16px; color: var(--ink3); font-size: 12px; }

    /* ── RT INDICATOR ─────────────────────────────────── */
    .rt-dot {
        display: inline-block; width: 7px; height: 7px; border-radius: 50%;
        background: var(--green); margin-right: 6px; flex-shrink: 0;
        animation: pulse-dot 2s ease infinite;
    }
    @keyframes pulse-dot {
        0%,100% { opacity: 1; transform: scale(1); }
        50%      { opacity: .5; transform: scale(.7); }
    }
    .rt-label { display: flex; align-items: center; font-size: 10.5px; color: var(--ink3); }
    .rt-label.paused .rt-dot { background: var(--ink3); animation: none; }

    /* ── TOAST ─────────────────────────────────────────── */
    #rt-toast-wrap { position: fixed; bottom: 24px; right: 24px; z-index: 9999; display: flex; flex-direction: column; gap: 8px; pointer-events: none; }
    .rt-toast { background: var(--surface); border: 1px solid var(--rule); border-left: 3px solid var(--green); border-radius: 8px; padding: 10px 14px; min-width: 230px; font-size: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.12); animation: toast-in .25s ease; pointer-events: auto; }
    .rt-toast-name   { font-weight: 600; color: var(--ink); margin-bottom: 2px; }
    .rt-toast-detail { color: var(--ink3); font-size: 11px; }
    @keyframes toast-in  { from { opacity:0; transform: translateX(16px); } to { opacity:1; transform: none; } }
    @keyframes toast-out { from { opacity:1; } to { opacity:0; transform: translateX(16px); } }

    /* badge live di sidebar */
    .sb-live-count { margin-left: auto; font-size: 10px; font-weight: 600; background: var(--green); color: #fff; padding: 1px 6px; border-radius: 10px; min-width: 16px; text-align: center; }

    /* new card animation */
    @keyframes card-in { from { background: #d1fae5; opacity: 0; transform: translateY(-4px); } to { background: var(--surface); opacity: 1; transform: none; } }
    .materi-card.new-card { animation: card-in 1.4s ease forwards; }

    /* stat flash */
    @keyframes stat-flash { 0%{background:var(--green-lt)} 100%{background:var(--surface)} }
    .mt-stat.updated { animation: stat-flash .8s ease forwards; }

    @media (max-width: 700px) { .mt-stats { grid-template-columns: 1fr 1fr; } }
</style>
@endpush

@section('content')

{{-- ── PAGE HEAD ─────────────────────────────────────────────── --}}
<div class="page-head">
    <div>
        <h1>Materi &amp; Pembelajaran</h1>
        <p>Kelola materi dan tugas untuk petugas PST</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center">
        <span class="rt-label" id="mt-rt-label">
            <span class="rt-dot"></span>Live
        </span>
        <a href="{{ route('admin.materi.create-tugas') }}" class="btn-amber">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Tugas
        </a>
    </div>
</div>

{{-- ── FILTER TOOLBAR ─────────────────────────────────────────── --}}
<style>
    .adm-toolbar {
        display:flex; align-items:center; gap:10px;
        background:var(--surface); border:1px solid var(--rule);
        border-radius:10px; padding:10px 14px; margin-bottom:18px; flex-wrap:wrap;
    }
    .adm-search-wrap {
        display:flex; align-items:center; gap:8px;
        flex:1; min-width:200px;
        background:var(--wash); border:1px solid var(--rule);
        border-radius:7px; padding:7px 11px; transition:border-color .15s;
    }
    .adm-search-wrap:focus-within { border-color:var(--blue); background:#fff; }
    .adm-search-input { flex:1; border:none; background:none; outline:none; font-size:12.5px; color:var(--ink); font-family:'IBM Plex Sans',sans-serif; }
    .adm-search-input::placeholder { color:var(--ink3); }
    .adm-search-clear { background:none; border:none; cursor:pointer; padding:0; color:var(--ink3); display:none; line-height:1; }
    .adm-search-clear:hover { color:var(--ink); }
    .adm-sep { width:1px; height:24px; background:var(--rule); flex-shrink:0; }
    /* Kalender custom */
    .adm-cal-btn {
        display:inline-flex; align-items:center; gap:7px;
        height:34px; padding:0 13px;
        border:1px solid var(--rule); border-radius:7px;
        background:var(--wash); color:var(--ink2);
        font-size:12px; font-weight:500; font-family:'IBM Plex Sans',sans-serif;
        cursor:pointer; white-space:nowrap; transition:all .12s;
    }
    .adm-cal-btn:hover { border-color:var(--blue); color:var(--blue); }
    .adm-cal-btn.has-date { background:var(--blue-lt); border-color:var(--blue); color:var(--blue); }
    .adm-cal-popup {
        position:absolute; z-index:999;
        background:var(--surface); border:1px solid var(--rule);
        border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,.13);
        padding:14px; display:none; min-width:240px; top:calc(100% + 6px); left:0;
    }
    .adm-cal-popup.open { display:block; }
    .amc-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .amc-month { font-size:12px; font-weight:600; color:var(--ink); }
    .amc-nav { background:none; border:none; cursor:pointer; color:var(--ink3); font-size:16px; padding:0 4px; line-height:1; transition:color .1s; }
    .amc-nav:hover { color:var(--ink); }
    .amc-days-hdr { display:grid; grid-template-columns:repeat(7,1fr); margin-bottom:4px; }
    .amc-days-hdr span { text-align:center; font-size:9.5px; font-weight:600; color:var(--ink3); letter-spacing:.4px; padding:2px 0; }
    .amc-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
    .amc-day { text-align:center; font-size:11px; padding:5px 2px; border-radius:5px; cursor:pointer; color:var(--ink2); font-family:'IBM Plex Mono',monospace; transition:all .1s; line-height:1.3; }
    .amc-day:hover { background:var(--blue-lt); color:var(--blue); }
    .amc-day.today { background:var(--wash2); font-weight:700; }
    .amc-day.selected { background:var(--blue); color:#fff; font-weight:700; }
    .amc-day.other-month { color:var(--rule); cursor:default; }
    .amc-day.other-month:hover { background:none; }
    /* Chip tanggal aktif */
    .adm-date-chip {
        display:none; align-items:center; gap:5px;
        background:var(--blue-lt); border:1px solid rgba(26,86,219,.2);
        color:var(--blue); border-radius:20px;
        font-size:11px; font-weight:600; padding:3px 10px; white-space:nowrap;
    }
    .adm-date-chip.visible { display:inline-flex; }
    .adm-date-chip-clear { background:none; border:none; cursor:pointer; padding:0; color:var(--blue); line-height:1; opacity:.7; }
    .adm-date-chip-clear:hover { opacity:1; }
    /* Filter toggle btn */
    .adm-filter-btn {
        height:34px; padding:0 13px; border:1px solid var(--rule); border-radius:7px;
        background:var(--wash); color:var(--ink2); font-size:12px; font-weight:500;
        font-family:'IBM Plex Sans',sans-serif; cursor:pointer; transition:all .12s; white-space:nowrap;
        display:inline-flex; align-items:center; gap:5px;
    }
    .adm-filter-btn:hover { border-color:var(--blue); color:var(--blue); }
    .adm-filter-btn.active { background:var(--blue-lt); border-color:var(--blue); color:var(--blue); }
    .adm-result-count { margin-left:auto; font-size:11px; color:var(--ink3); font-family:'IBM Plex Mono',monospace; white-space:nowrap; }
    .adm-clear-all { background:none; border:none; cursor:pointer; font-size:11px; color:var(--ink3); padding:0; transition:color .12s; white-space:nowrap; }
    .adm-clear-all:hover { color:var(--red); }
    .adm-no-result { text-align:center; padding:48px 20px; color:var(--ink3); display:none; }
    .adm-no-result p { font-size:13px; }
</style>

<div class="adm-toolbar">
    {{-- Search --}}
    <div class="adm-search-wrap">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <input type="text" id="adm-search" class="adm-search-input" placeholder="Cari judul materi atau tugas…" autocomplete="off">
        <button class="adm-search-clear" id="adm-search-clear">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </div>

    <div class="adm-sep"></div>

    {{-- Kalender filter tanggal --}}
    <div style="position:relative">
        <button class="adm-cal-btn" id="adm-cal-btn" onclick="admToggleCal(event)">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span id="adm-cal-label">Filter Tanggal</span>
        </button>
        <div class="adm-cal-popup" id="adm-cal-popup">
            <div class="amc-head">
                <button class="amc-nav" onclick="admNavCal(-1)">&#8249;</button>
                <span class="amc-month" id="adm-cal-month"></span>
                <button class="amc-nav" onclick="admNavCal(1)">&#8250;</button>
            </div>
            <div class="amc-days-hdr"><span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span></div>
            <div class="amc-grid" id="adm-cal-grid"></div>
        </div>
    </div>

    <button class="adm-clear-all" id="adm-clear-all" onclick="admClearAll()">Reset</button>
    <span class="adm-result-count" id="adm-count"></span>
</div>

<div class="adm-no-result" id="adm-no-result">
    <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;opacity:.3">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <p>Tidak ada materi yang cocok dengan filter.</p>
</div>

{{-- ── STATS ─────────────────────────────────────────────────── --}}
<div class="mt-stats">
    <div class="mt-stat" id="mt-stat-materi">
        <div class="mt-stat-label">Total Materi</div>
        <div class="mt-stat-val" id="val-materi">{{ count($tugas) }}</div>
        <div class="mt-stat-sub">dibuat admin</div>
    </div>
    <div class="mt-stat" id="mt-stat-sudah">
        <div class="mt-stat-label">Sudah Selesai</div>
        @php $totalSudah = \App\Models\Jawaban::where('status','sudah')->count(); @endphp
        <div class="mt-stat-val" id="val-sudah">{{ $totalSudah }}</div>
        <div class="mt-stat-sub">pengumpulan petugas</div>
    </div>
    <div class="mt-stat" id="mt-stat-petugas">
        <div class="mt-stat-label">Total Petugas</div>
        <div class="mt-stat-val" id="val-petugas">{{ \App\Models\Petugas::count() }}</div>
        <div class="mt-stat-sub">terdaftar</div>
    </div>
</div>

{{-- ── TOGGLE AKSES QUIZ TRIWULAN ────────────────────────────────── --}}
<div class="panel" style="padding:14px 20px;margin-bottom:16px;display:flex;align-items:center;justify-content:space-between;gap:16px;">
    <div style="font-size:13px;font-weight:600;color:var(--ink);">
        Quiz Triwulan
        <span style="font-size:11.5px;font-weight:400;color:var(--ink3);margin-left:8px;">
            Akses petugas untuk mengerjakan quiz triwulan
        </span>
    </div>
    <form method="POST" action="{{ route('admin.materi-triwulan.toggle') }}" style="display:flex;align-items:center;gap:10px;">
        @csrf
        <span style="font-size:12px;color:{{ $materiTriwulanOpen ? '#16a34a' : '#dc2626' }};font-weight:600;">
            {{ $materiTriwulanOpen ? 'ON' : 'OFF' }}
        </span>
        <button type="submit" style="
            position:relative;width:44px;height:24px;border-radius:99px;border:none;cursor:pointer;
            background:{{ $materiTriwulanOpen ? '#16a34a' : '#d1d5db' }};
            transition:background .2s;padding:0;flex-shrink:0;">
            <span style="
                position:absolute;top:3px;
                left:{{ $materiTriwulanOpen ? '23px' : '3px' }};
                width:18px;height:18px;border-radius:50%;background:white;
                transition:left .2s;display:block;"></span>
        </button>
    </form>
</div>

{{-- ── PANEL DENGAN TAB ─────────────────────────────────────── --}}
<div class="panel" style="padding:0">

    {{-- Tab Bar --}}
    <div style="padding:0 18px">
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab('materi', this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                    <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
                </svg>
                Materi Pembelajaran
                <span class="tab-count" id="tc-materi">{{ count($tugas) }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('tugas', this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                    <rect x="9" y="3" width="6" height="4" rx="1"/>
                    <line x1="9" y1="12" x2="15" y2="12"/>
                    <line x1="9" y1="16" x2="13" y2="16"/>
                </svg>
                Tugas &amp; Monitoring
                <span class="tab-count" id="tc-tugas">{{ count($wilayah) }}</span>
            </button>
            <button class="tab-btn" onclick="switchTab('triwulan', this)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                Materi &amp; Kuis Triwulan
                <span class="tab-count" id="tc-triwulan">{{ $monitoringTriwulan['rekapWilayah']->sum('totalMateri') }}</span>
            </button>
        </div>
    </div>

    {{-- Tab 1: Materi --}}
    <div class="tab-panel active" id="tab-materi">
        <div id="materi-list">
        @forelse($tugas as $item)
        <div class="materi-card" data-id="{{ $item->id }}"
             data-judul="{{ strtolower($item->judul) }}"
             data-date="{{ $item->created_at->format('Y-m-d') }}"
             data-has-file="{{ ($item->file || $item->files->isNotEmpty()) ? '1' : '0' }}"
             data-has-link="{{ $item->link ? '1' : '0' }}"
        >
            <div class="materi-meta">
                <div class="materi-ava">AD</div>
                <span>Admin</span>
                <span>·</span>
                <span>{{ $item->created_at->format('d M Y') }}</span>
            </div>
            <div class="materi-title">{{ $item->judul }}</div>
            <div class="materi-desc">{{ $item->deskripsi }}</div>
            <div class="materi-badges">
                @if($item->file || $item->files->isNotEmpty()) <span class="badge">File</span> @endif
                @if($item->link) <a href="{{ $item->link }}" target="_blank" class="badge">Link</a> @endif
            </div>
            <a href="{{ route('admin.tugas.show', $item->id) }}" class="btn-primary">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                Lihat Detail
            </a>
        </div>
        @empty
        <div class="empty-state" id="materi-empty">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 8px">
                <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
                <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
            </svg>
            <p>Belum ada materi pembelajaran</p>
        </div>
        @endforelse
        </div>
    </div>

    {{-- Tab 2: Tugas & Monitoring --}}
    <div class="tab-panel" id="tab-tugas">
        @forelse($wilayah as $w)
        @php
            $sudah    = \App\Models\Jawaban::where('status','sudah')
                            ->whereIn('petugas_id', $w->petugas->pluck('id'))->count();
            $total    = $w->petugas->count();
            $progress = $total > 0 ? ($sudah / $total) * 100 : 0;
        @endphp
        <div class="tugas-card" id="wcard-{{ $w->id }}">
            <div class="tugas-title">Wilayah {{ $w->nama }}</div>
            <div class="tugas-stat">
                <span style="color:var(--green)" id="ws-sudah-{{ $w->id }}">✔ {{ $sudah }} sudah</span>
                &nbsp;&nbsp;
                <span style="color:var(--red)" id="ws-belum-{{ $w->id }}">✖ {{ $total - $sudah }} belum</span>
            </div>
            <div class="progress-label">
                <span>Progres</span>
                <b id="ws-pct-{{ $w->id }}">{{ round($progress) }}%</b>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" id="ws-bar-{{ $w->id }}" style="width:{{ $progress }}%"></div>
            </div>
            <a href="{{ route('admin.materi.detail', $w->id) }}" class="act-btn-sm">Lihat Detail</a>
        </div>
        @empty
        <div class="empty-state"><p>Tidak ada wilayah</p></div>
        @endforelse
    </div>

    {{-- Tab 3: Materi & Kuis Triwulan --}}
    <div class="tab-panel" id="tab-triwulan">

        {{-- Filter periode --}}
        <form method="GET" action="{{ route('admin.materi') }}" id="triwulan-periode-form" style="margin-bottom:16px; display:flex; align-items:center; gap:10px;">
            <label style="font-size:12px; font-weight:600; color:var(--ink);">Periode:</label>
            <select name="periode_triwulan" class="adm-cal-btn" style="background:var(--wash);" onchange="document.getElementById('triwulan-periode-form').submit()">
                @foreach($periodeOptions as $val => $label)
                    <option value="{{ $val }}" {{ $monitoringTriwulan['periode'] === $val ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <span style="font-size:11.5px;color:var(--ink3);">Rekap materi & kuis triwulan yang dibuat koordinator per wilayah</span>
        </form>

        @php
            $rekapWilayah = $monitoringTriwulan['rekapWilayah'];
            $grandMateri  = $rekapWilayah->sum('totalMateri');
            $grandSudah   = $rekapWilayah->sum('jmlSudah');
            $grandBelum   = $rekapWilayah->sum('jmlBelum');
        @endphp

        @if($grandMateri === 0)
        <div class="empty-state">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 8px">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <p>Belum ada materi & kuis triwulan untuk periode ini.</p>
        </div>
        @else

        <div class="mt-stats" style="margin-bottom:16px;">
            <div class="mt-stat">
                <div class="mt-stat-label">Total Materi Triwulan</div>
                <div class="mt-stat-val">{{ $grandMateri }}</div>
                <div class="mt-stat-sub">dibuat koordinator</div>
            </div>
            <div class="mt-stat">
                <div class="mt-stat-label">Jawaban Masuk</div>
                <div class="mt-stat-val" style="color:var(--green)">{{ $grandSudah }}</div>
                <div class="mt-stat-sub">petugas sudah mengisi</div>
            </div>
            <div class="mt-stat">
                <div class="mt-stat-label">Belum Mengisi</div>
                <div class="mt-stat-val" style="color:var(--red)">{{ $grandBelum }}</div>
                <div class="mt-stat-sub">petugas belum mengisi</div>
            </div>
        </div>

        @foreach($rekapWilayah as $rw)
        @continue($rw->totalMateri === 0)
        <div class="tugas-card">
            <div class="tugas-title" style="display:flex; align-items:center; justify-content:space-between; gap:8px;">
                <span>Wilayah {{ $rw->wilayah->nama }}</span>
                <span style="font-size:10.5px; font-weight:500; color:var(--ink3);">Koordinator: {{ $rw->koordinatorNama }}</span>
            </div>
            <div class="tugas-stat">
                <span>{{ $rw->totalMateri }} materi · {{ $rw->totalSoal }} soal</span>
            </div>
            <div class="tugas-stat">
                <span style="color:var(--green)">✔ {{ $rw->jmlSudah }} sudah</span>
                &nbsp;&nbsp;
                <span style="color:var(--red)">✖ {{ $rw->jmlBelum }} belum</span>
            </div>
            <div class="progress-label">
                <span>Progres</span>
                <b>{{ $rw->progres }}%</b>
            </div>
            <div class="progress-bar-wrap">
                <div class="progress-bar-fill" style="width:{{ $rw->progres }}%"></div>
            </div>

            {{-- Daftar materi di wilayah ini --}}
            <div style="margin-top:10px; display:flex; flex-direction:column; gap:6px;">
                @foreach($rw->materiList as $m)
                <div style="display:flex; align-items:center; justify-content:space-between; gap:8px; padding:7px 10px; background:var(--wash); border-radius:6px; font-size:11.5px;">
                    <span style="color:var(--ink2); overflow:hidden; text-overflow:ellipsis; white-space:nowrap;">{{ $m->judul }}</span>
                    <span style="color:var(--ink3); flex-shrink:0;">{{ $m->quiz->count() }} soal</span>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
        @endif
    </div>

</div>{{-- /panel --}}

@endsection

@push('scripts')
<script>
function switchTab(name, btn) {
    document.querySelectorAll('.tab-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('tab-' + name).classList.add('active');
    btn.classList.add('active');
}

// ── REAL-TIME POLLING ────────────────────────────────────────────
(function () {
    const POLL_URL    = '{{ route("admin.materi.polling") }}';
    const TODAY       = '{{ now()->toDateString() }}';
    const INTERVAL_MS = 7000;

    // Track max ID dari materi yang sudah ada
    let lastId = 0;
    document.querySelectorAll('#materi-list .materi-card[data-id]').forEach(el => {
        const id = parseInt(el.dataset.id);
        if (id > lastId) lastId = id;
    });

    let unreadCount = 0;

    // ── Build materi card HTML ──────────────────────────────────
    function buildMateriCard(m) {
        const badges = (m.has_file ? '<span class="badge">File</span>' : '') +
                       (m.has_link ? '<a href="#" class="badge">Link</a>' : '');
        return `<div class="materi-card new-card" data-id="${m.id}">
            <div class="materi-meta">
                <div class="materi-ava">AD</div>
                <span>Admin</span>
                <span>·</span>
                <span>${m.created_at}</span>
            </div>
            <div class="materi-title">${m.judul}</div>
            <div class="materi-desc">${m.deskripsi}</div>
            <div class="materi-badges">${badges}</div>
            <a href="${m.detail_url}" class="btn-primary">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/>
                    <circle cx="12" cy="12" r="3"/>
                </svg>
                Lihat Detail
            </a>
        </div>`;
    }

    // ── Toast ────────────────────────────────────────────────────
    function showToast(m) {
        const wrap  = document.getElementById('rt-toast-wrap');
        if (!wrap) return;
        const toast = document.createElement('div');
        toast.className = 'rt-toast';
        toast.innerHTML = `<div class="rt-toast-name">${m.judul}</div>
            <div class="rt-toast-detail">Materi baru ditambahkan · ${m.created_at}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    // ── Sidebar badge ────────────────────────────────────────────
    function updateSidebarBadge() {
        const sbMateri = document.querySelector('a[href*="admin/materi"]');
        if (!sbMateri) return;
        let badge = sbMateri.querySelector('.sb-live-count');
        if (unreadCount <= 0) { if (badge) badge.remove(); return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'sb-live-count';
            sbMateri.appendChild(badge);
        }
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
    }

    // ── Update stats ─────────────────────────────────────────────
    function updateStats(s) {
        function flash(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.textContent !== String(val)) {
                el.textContent = val;
                const card = el.closest('.mt-stat');
                if (card) { card.classList.remove('updated'); void card.offsetWidth; card.classList.add('updated'); }
            }
        }
        flash('val-materi', s.total_materi);
        flash('val-sudah',  s.total_sudah);
        flash('val-petugas', s.total_petugas);
    }

    // ── Update monitoring tugas ───────────────────────────────────
    function updateWilayah(statsArr) {
        statsArr.forEach(w => {
            const sudahEl = document.getElementById('ws-sudah-' + w.wilayah_id);
            const belumEl = document.getElementById('ws-belum-' + w.wilayah_id);
            const pctEl   = document.getElementById('ws-pct-'   + w.wilayah_id);
            const barEl   = document.getElementById('ws-bar-'   + w.wilayah_id);
            if (sudahEl) sudahEl.textContent = '✔ ' + w.sudah + ' sudah';
            if (belumEl) belumEl.textContent = '✖ ' + w.belum + ' belum';
            if (pctEl)   pctEl.textContent   = w.progress_pct + '%';
            if (barEl)   barEl.style.width   = w.progress_pct + '%';
        });
    }

    // ── Poll ─────────────────────────────────────────────────────
    async function poll() {
        try {
            const res  = await fetch(`${POLL_URL}?after=${lastId}`);
            const data = await res.json();

            if (data.new_materi && data.new_materi.length > 0) {
                const list = document.getElementById('materi-list');
                const empty = document.getElementById('materi-empty');
                if (empty) empty.remove();

                data.new_materi.forEach(m => {
                    const existing = list.querySelector(`.materi-card[data-id="${m.id}"]`);
                    if (!existing) {
                        list.insertAdjacentHTML('afterbegin', buildMateriCard(m));
                        showToast(m);
                        unreadCount++;
                    }
                });

                if (data.max_id > lastId) lastId = data.max_id;
                updateSidebarBadge();

                // Update tab count
                const allCards = document.querySelectorAll('#materi-list .materi-card').length;
                const tc = document.getElementById('tc-materi');
                if (tc) tc.textContent = allCards;

                // Clear badge saat klik tab materi
                document.querySelectorAll('.tab-btn').forEach(btn => {
                    btn.addEventListener('click', () => {
                        if (btn.textContent.includes('Materi')) {
                            unreadCount = 0;
                            updateSidebarBadge();
                        }
                    }, { once: true });
                });
            }

            if (data.summary)       updateStats(data.summary);
            if (data.stats_wilayah) updateWilayah(data.stats_wilayah);

        } catch (e) {
            console.warn('Polling materi error', e);
        }
    }

    // Polling hanya aktif hari ini (tanggal server = hari ini selalu,
    // materi/tugas tidak berhubungan tanggal, tapi ikuti pola absensi)
    setInterval(poll, INTERVAL_MS);
})();
/* ── ADMIN MATERI FILTER ─────────────────────────────────── */
(function() {
    const BULAN   = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    const BULAN_S = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

    let calCur      = null;
    let calSelected = null;

    function todayLocal() {
        return new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
    }

    function initCur() {
        if (!calCur) {
            const [y, m] = todayLocal().split('-').map(Number);
            calCur = { y, m: m - 1 };
        }
    }

    function renderCal() {
        initCur();
        const { y, m } = calCur;
        const today     = todayLocal();

        document.getElementById('adm-cal-month').textContent = BULAN[m] + ' ' + y;
        const grid = document.getElementById('adm-cal-grid');
        grid.innerHTML = '';

        const firstDay    = new Date(y, m, 1).getDay();
        const daysInMonth = new Date(y, m + 1, 0).getDate();
        const daysInPrev  = new Date(y, m, 0).getDate();

        for (let i = firstDay - 1; i >= 0; i--) {
            const el = document.createElement('div');
            el.className = 'amc-day other-month';
            el.textContent = daysInPrev - i;
            grid.appendChild(el);
        }
        for (let d = 1; d <= daysInMonth; d++) {
            const key = y + '-' + String(m + 1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
            const el  = document.createElement('div');
            el.className = 'amc-day';
            el.textContent = d;
            if (key === today)       el.classList.add('today');
            if (key === calSelected) el.classList.add('selected');
            el.addEventListener('click', () => selectDate(key, d, m + 1, y));
            grid.appendChild(el);
        }
        const rem = (firstDay + daysInMonth) % 7 === 0 ? 0 : 7 - ((firstDay + daysInMonth) % 7);
        for (let d = 1; d <= rem; d++) {
            const el = document.createElement('div');
            el.className = 'amc-day other-month';
            el.textContent = d;
            grid.appendChild(el);
        }
    }

    function selectDate(key, d, mo, y) {
        calSelected = key;
        renderCal();
        closeCal();
        const label = d + ' ' + BULAN_S[mo - 1] + ' ' + y;
        document.getElementById('adm-cal-label').textContent = label;
        document.getElementById('adm-cal-btn').classList.add('has-date');
        admApplyFilter();
    }

    window.admNavCal = function(dir) {
        initCur();
        calCur.m += dir;
        if (calCur.m > 11) { calCur.m = 0;  calCur.y++; }
        if (calCur.m < 0)  { calCur.m = 11; calCur.y--; }
        renderCal();
    };

    window.admToggleCal = function(e) {
        e.stopPropagation();
        const popup = document.getElementById('adm-cal-popup');
        const isOpen = popup.classList.contains('open');
        closeCal();
        if (!isOpen) { popup.classList.add('open'); renderCal(); }
    };

    function closeCal() {
        document.getElementById('adm-cal-popup')?.classList.remove('open');
    }

    document.addEventListener('click', e => {
        if (!e.target.closest('#adm-cal-popup') && !e.target.closest('#adm-cal-btn')) closeCal();
    });

    function admApplyFilter() {
        const q     = (document.getElementById('adm-search')?.value || '').toLowerCase().trim();
        const cards = Array.from(document.querySelectorAll('#materi-list .materi-card[data-id]'));
        let visible = 0;

        cards.forEach(c => {
            const judul = c.dataset.judul || '';
            const date  = c.dataset.date  || '';
            const matchQ = !q            || judul.includes(q);
            const matchD = !calSelected  || date === calSelected;
            const show   = matchQ && matchD;
            c.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const cnt = document.getElementById('adm-count');
        if (cnt) cnt.textContent = visible + ' materi';
        const nr = document.getElementById('adm-no-result');
        if (nr) nr.style.display = visible === 0 && cards.length > 0 ? 'block' : 'none';
    }

    window.admClearAll = function() {
        const inp = document.getElementById('adm-search');
        if (inp) inp.value = '';
        document.getElementById('adm-search-clear').style.display = 'none';
        calSelected = null;
        document.getElementById('adm-cal-label').textContent = 'Filter Tanggal';
        document.getElementById('adm-cal-btn').classList.remove('has-date');
        admApplyFilter();
    };

    document.addEventListener('DOMContentLoaded', function() {
        const inp = document.getElementById('adm-search');
        const clr = document.getElementById('adm-search-clear');
        if (!inp) return;

        const total = document.querySelectorAll('#materi-list .materi-card[data-id]').length;
        const cnt   = document.getElementById('adm-count');
        if (cnt) cnt.textContent = total + ' materi';

        inp.addEventListener('input', function() {
            clr.style.display = this.value ? 'block' : 'none';
            admApplyFilter();
        });
        inp.addEventListener('keydown', e => {
            if (e.key === 'Escape') { inp.value = ''; clr.style.display = 'none'; admApplyFilter(); }
        });
        clr.addEventListener('click', () => { inp.value = ''; clr.style.display = 'none'; admApplyFilter(); inp.focus(); });
    });
})();
</script>
@endpush