@extends('layouts.admin')

@section('title', 'Laporan Harian PST')

@section('breadcrumb')
    <span>Admin</span>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Laporan Harian</strong>

<div id="lh-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("admin.laporanharian.polling") }}';
    const INTERVAL_MS = 7000;

    let knownIds = new Set();
    document.querySelectorAll('tbody tr[data-id]').forEach(tr => knownIds.add(parseInt(tr.dataset.id)));

    let unreadCount = 0;

    function showToast(r) {
        const wrap  = document.getElementById('lh-toast-wrap');
        const toast = document.createElement('div');
        toast.className = 'lh-toast';
        toast.innerHTML = `
            <div class="lh-toast-name">${r.nama} — Laporan Baru</div>
            <div class="lh-toast-detail">${r.sesi} &middot; ${r.wilayah} &middot; ${r.tanggal}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'lh-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    function updateBadge() {
        const sbLink = document.querySelector('.sb-item[href*="laporan-harian"]');
        if (!sbLink) return;
        let badge = sbLink.querySelector('.sb-live-count');
        if (unreadCount <= 0) { if (badge) badge.remove(); return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'sb-live-count';
            sbLink.appendChild(badge);
        }
        badge.textContent = unreadCount > 99 ? '99+' : unreadCount;
    }

    function updateStats(stats) {
        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el && el.textContent !== String(val)) {
                el.textContent = val;
                const card = el.closest('.cl-stat');
                if (card) { card.classList.remove('updated'); void card.offsetWidth; card.classList.add('updated'); }
            }
        }
        setVal('val-total',     stats.total);
        setVal('val-submitted', stats.submitted);
        setVal('val-approved',  stats.approved);
        setVal('val-rejected',  stats.rejected);
    }

    function updateRowStatus(r) {
        const tr = document.querySelector(`tr[data-id="${r.id}"]`);
        if (!tr || tr.dataset.status === r.status) return;
        tr.dataset.status = r.status;
        const tdStatus = tr.querySelectorAll('td')[tr.querySelectorAll('td').length - 2];
        if (!tdStatus) return;
        const map = {
            submitted: '<span class="pill pill-submitted">Menunggu</span>',
            approved:  '<span class="pill pill-approved">Disetujui</span>',
            rejected:  '<span class="pill pill-rejected">Dikembalikan</span>',
            draft:     '<span class="pill pill-draft">Draft</span>',
        };
        if (map[r.status]) tdStatus.innerHTML = map[r.status];
    }

    async function poll() {
        try {
            const resp = await fetch(POLL_URL, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) return;
            const data = await resp.json();

            if (data.stats) updateStats(data.stats);

            if (data.rows) {
                data.rows.forEach(r => {
                    if (knownIds.has(r.id)) {
                        updateRowStatus(r);
                    } else {
                        knownIds.add(r.id);
                        showToast(r);
                        unreadCount++;
                        updateBadge();
                    }
                });
            }
        } catch (e) {}
    }

    document.querySelectorAll('.sb-item[href*="laporan-harian"]').forEach(el => {
        el.addEventListener('click', () => { unreadCount = 0; updateBadge(); });
    });

    setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
})();
</script>
@endpush

@endsection

@push('styles')
<style>
    /* ── Page-specific styles (SAMA PERSIS DENGAN CHECKLIST HARIAN) ── */
    .cl-topbar {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .cl-topbar-left h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; font-family: 'IBM Plex Sans', sans-serif; }
    .cl-topbar-left p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

    /* Role badge */
    .role-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 10.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase;
        background: #1a56db18; color: var(--blue);
        border: 1px solid #1a56db28; padding: 3px 10px; border-radius: 20px;
    }
    .role-badge svg { flex-shrink: 0; }

    /* Stats row - SAMA PERSIS DENGAN CHECKLIST */
    .cl-stats {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 20px;
    }
    .cl-stat { background: var(--surface); padding: 18px 20px; }
    .cl-stat-label {
        font-size: 10px; font-weight: 600; letter-spacing: .8px;
        text-transform: uppercase; color: var(--ink3); margin-bottom: 8px;
    }
    .cl-stat-val {
        font-size: 28px; font-weight: 300; letter-spacing: -1px;
        font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1;
        margin-bottom: 6px;
    }
    .cl-stat-sub { font-size: 11px; color: var(--ink3); }
    .cl-stat-bar { height: 2px; background: var(--wash2); border-radius: 1px; margin-top: 10px; }
    .cl-stat-fill { height: 100%; border-radius: 1px; transition: width .5s; }

    /* Filter toolbar - SAMA PERSIS */
    .cl-filter {
        display: flex; align-items: center; gap: 10px;
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .cl-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); white-space: nowrap; }
    .cl-filter input[type=month],
    .cl-filter input[type=date],
    .cl-filter select {
        height: 30px; padding: 0 10px; font-size: 12px;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif;
        cursor: pointer; transition: border-color .15s;
    }
    .cl-filter input:focus,
    .cl-filter select:focus { outline: none; border-color: var(--blue); }
    .cl-filter-sep { width: 1px; height: 20px; background: var(--rule); }
    .cl-filter-btn {
        height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
        background: var(--blue); color: #fff; border: none;
        border-radius: 5px; cursor: pointer; transition: opacity .15s;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .cl-filter-btn:hover { opacity: .88; }
    .cl-filter-btn-reset {
        height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
        background: var(--wash); color: var(--ink2);
        border: 1px solid var(--rule); border-radius: 5px;
        cursor: pointer; transition: all .15s;
        font-family: 'IBM Plex Sans', sans-serif;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }
    .cl-filter-btn-reset:hover { background: var(--wash2); color: var(--ink); }

    /* Export button */
    .btn-export {
        height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
        background: var(--green-lt); color: var(--green);
        border: 1px solid var(--green); border-radius: 5px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        transition: opacity .15s;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .btn-export:hover { opacity: .85; }

    /* Tab bar */
    .tab-bar {
        display: flex;
        gap: 0;
        border-bottom: 2px solid var(--rule);
        margin-bottom: 22px;
    }
    .tab-btn {
        padding: 9px 18px;
        font-size: 13px;
        font-weight: 500;
        color: var(--ink3);
        cursor: pointer;
        border: none;
        background: none;
        border-bottom: 2px solid transparent;
        margin-bottom: -2px;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .tab-btn.active {
        color: var(--blue);
        border-bottom-color: var(--blue);
    }
    .tab-content {
        display: none;
    }
    .tab-content.active {
        display: block;
    }

    /* Table panel - SAMA PERSIS */
    .panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .ph    { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
    .ph-title { font-size: 12.5px; font-weight: 600; }
    .ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    .table-scroll-outer {
        overflow-x: auto;
        border-radius: 8px;
    }
    .table-scroll-outer::-webkit-scrollbar { height: 7px; }
    .table-scroll-outer::-webkit-scrollbar-track { background: var(--wash); border-radius: 0 0 8px 8px; }
    .table-scroll-outer::-webkit-scrollbar-thumb { background: var(--rule); border-radius: 4px; }
    .table-scroll-outer::-webkit-scrollbar-thumb:hover { background: var(--ink3); }

    table {
        width: 100%;
        border-collapse: collapse;
        font-size: 12.5px;
        min-width: 800px;
    }
    thead th {
        text-align: left; padding: 8px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--wash); }
    tbody td { padding: 10px 16px; vertical-align: middle; color: var(--ink2); }

    /* Typography */
    .mono { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; }
    .jawaban-cell {
        max-width: 180px;
        overflow: hidden;
        text-overflow: ellipsis;
        white-space: nowrap;
    }

    /* Pill status - SAMA PERSIS */
    .pill { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
    .pill-submitted { background: var(--amber-lt); color: var(--amber); }
    .pill-approved  { background: var(--green-lt);  color: var(--green); }
    .pill-rejected  { background: var(--red-lt);    color: var(--red); }
    .pill-draft     { background: var(--wash2);     color: var(--ink3); }

    /* Button detail */
    .btn-detail {
        font-size: 11px; font-weight: 500; padding: 4px 10px;
        border-radius: 4px; background: var(--blue-lt); color: var(--blue);
        text-decoration: none; white-space: nowrap;
    }
    .btn-detail:hover { background: #d4e3f9; }

    /* Empty state */
    .empty-state { padding: 48px 20px; text-align: center; color: var(--ink3); }
    .empty-state svg { margin: 0 auto 12px; display: block; opacity: .3; }
    .empty-state p { font-size: 13px; }

    /* Pagination */
    .pagination-wrap { padding: 12px 18px; border-top: 1px solid var(--rule); font-size: 12px; }
    .pagination { display: flex; gap: 5px; align-items: center; flex-wrap: wrap; }
    .pagination .page-link {
        padding: 4px 8px; font-size: 11px; border-radius: 4px;
        background: var(--wash); color: var(--ink2); text-decoration: none;
    }
    .pagination .active .page-link { background: var(--blue); color: white; }

    /* Flash messages */
    .flash {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 7px; margin-bottom: 16px;
        font-size: 12.5px; font-weight: 500;
        animation: fadeUp .3s ease;
    }
    .flash-ok  { background: var(--green-lt); color: var(--green); border: 1px solid #0a7c4e22; }
    .flash-err { background: var(--red-lt);   color: var(--red);   border: 1px solid #c0392b22; }
    @keyframes fadeUp { from { opacity: 0; transform: translateY(6px); } to { opacity: 1; transform: none; } }

    /* Template cards - SAMA PERSIS DENGAN CHECKLIST */
    .tpl-card { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; margin-bottom: 12px; overflow: hidden; }
    .tpl-row { display: flex; align-items: center; gap: 12px; padding: 12px 16px; }
    .tpl-num { width: 24px; height: 24px; border-radius: 50%; background: var(--blue); color: #fff; font-size: 11px; font-weight: 600; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
    .tpl-info { flex: 1; min-width: 0; }
    .tpl-judul { font-size: 13px; font-weight: 600; color: var(--ink); }
    .tpl-badge { font-size: 10px; padding: 2px 7px; border-radius: 3px; font-weight: 500; }
    .tpl-teks    { background: var(--wash2); color: var(--ink3); }
    .tpl-pilihan { background: var(--blue-lt); color: var(--blue); }
    .tpl-wajib   { background: var(--red-lt); color: var(--red); }
    .tpl-opsi-list { display: flex; flex-wrap: wrap; gap: 5px; padding: 0 16px 12px 52px; }
    .tpl-opsi-tag { font-size: 11px; padding: 2px 8px; border-radius: 3px; background: var(--wash); color: var(--ink2); border: 1px solid var(--rule); }
    .tpl-actions { display: flex; gap: 6px; flex-shrink: 0; }
    .btn-sm { font-size: 11px; padding: 4px 10px; border-radius: 4px; border: 1px solid var(--rule); background: var(--surface); color: var(--ink2); cursor: pointer; font-family: 'IBM Plex Sans', sans-serif; }
    .btn-sm-del { color: var(--red); border-color: #c0392b22; background: var(--red-lt); }
    .btn-sm:hover { background: var(--wash); }
    .btn-tambah {
        display: inline-flex; align-items: center; gap: 6px;
        font-size: 12px; font-weight: 500; padding: 6px 14px;
        border-radius: 5px; background: var(--blue); color: #fff;
        border: none; cursor: pointer; font-family: 'IBM Plex Sans', sans-serif;
    }
    .btn-tambah:hover { opacity: .88; }

    /* Modal */
    .tpl-modal-bg { position: fixed; inset: 0; background: rgba(0,0,0,.45); z-index: 9999; display: none; align-items: center; justify-content: center; }
    .tpl-modal-bg.open { display: flex; }
    .tpl-modal { background: var(--surface); border-radius: 10px; width: 480px; max-width: 92vw; overflow: hidden; box-shadow: 0 8px 32px rgba(0,0,0,.18); }
    .tpl-modal-head { display: flex; align-items: center; justify-content: space-between; padding: 16px 20px; border-bottom: 1px solid var(--rule); }
    .tpl-modal-head h3 { font-size: 15px; font-weight: 600; margin: 0; }
    .tpl-modal-close { background: none; border: none; font-size: 18px; color: var(--ink3); cursor: pointer; padding: 2px 6px; border-radius: 4px; }
    .tpl-modal-close:hover { background: var(--wash); }
    .tpl-modal-body { padding: 20px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: 11.5px; font-weight: 600; color: var(--ink2); margin-bottom: 5px; }
    .field input[type=text], .field select { width: 100%; padding: 8px 10px; font-size: 12.5px; border: 1px solid var(--rule); border-radius: 5px; background: var(--wash); color: var(--ink); font-family: 'IBM Plex Sans', sans-serif; outline: none; box-sizing: border-box; }
    .field input:focus, .field select:focus { border-color: var(--blue); }
    .opsi-list { display: flex; flex-direction: column; gap: 6px; margin-top: 6px; }
    .opsi-row { display: flex; gap: 6px; align-items: center; }
    .opsi-row input { flex: 1; }
    .btn-del-opsi { background: none; border: none; color: var(--red); cursor: pointer; font-size: 16px; padding: 0 4px; line-height: 1; }
    .btn-add-opsi { font-size: 11.5px; color: var(--blue); background: none; border: none; cursor: pointer; font-family: 'IBM Plex Sans', sans-serif; padding: 4px 0; font-weight: 500; }
    .tpl-modal-foot { display: flex; justify-content: flex-end; gap: 8px; padding: 14px 20px; border-top: 1px solid var(--rule); background: var(--wash); }
    .btn-save { height: 34px; padding: 0 20px; font-size: 12.5px; font-weight: 600; background: var(--blue); color: #fff; border: none; border-radius: 5px; cursor: pointer; font-family: 'IBM Plex Sans', sans-serif; }
    .btn-cancel-sm { height: 34px; padding: 0 14px; font-size: 12.5px; background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); border-radius: 5px; cursor: pointer; font-family: 'IBM Plex Sans', sans-serif; }
    .opsi-section { display: none; }
    .opsi-section.show { display: block; }

/* ── TOAST & BADGE ── */
#lh-toast-wrap {
    position: fixed; bottom: 24px; right: 24px;
    z-index: 9999; display: flex; flex-direction: column; gap: 8px;
    pointer-events: none;
}
.lh-toast {
    background: var(--surface); border: 1px solid var(--rule);
    border-left: 3px solid var(--green);
    border-radius: 8px; padding: 10px 14px; min-width: 220px;
    font-size: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.12);
    animation: lh-toast-in .25s ease; pointer-events: auto;
}
.lh-toast-name   { font-weight: 600; color: var(--ink); margin-bottom: 2px; }
.lh-toast-detail { color: var(--ink3); font-size: 11px; }
@keyframes lh-toast-in  { from { opacity:0; transform:translateX(16px); } to { opacity:1; transform:none; } }
@keyframes lh-toast-out { from { opacity:1; } to { opacity:0; transform:translateX(16px); } }
.sb-live-count {
    margin-left: auto; font-size: 10px; font-weight: 600;
    background: var(--green); color: #fff;
    padding: 1px 6px; border-radius: 10px; min-width: 16px; text-align: center;
}
@keyframes lh-stat-flash { 0%{background:var(--green-lt)} 100%{background:var(--surface)} }
.cl-stat.updated { animation: lh-stat-flash .8s ease forwards; }
@keyframes lh-row-in { from { background:#d1fae5; } to { background:transparent; } }
tbody tr.new-row { animation: lh-row-in 1.5s ease forwards; }
</style>
@endpush

@section('content')

{{-- Flash messages --}}
@if(session('success'))
    <div class="flash flash-ok">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div class="flash flash-err">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
@endif

{{-- Page header --}}
<div class="cl-topbar">
    <div class="cl-topbar-left">
        <h1>Laporan Harian PST</h1>
        <p>
            <span class="role-badge">
                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="2" y1="12" x2="22" y2="12"/>
                    <path d="M12 2a15.3 15.3 0 010 20M12 2a15.3 15.3 0 000 20"/>
                </svg>
                Semua Wilayah
            </span>
            &nbsp;Monitoring laporan harian seluruh petugas
        </p>
    </div>
    <div style="display:flex; align-items:center; gap:8px;">
        <span class="role-badge">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Admin — Akses Penuh
        </span>
    </div>
</div>

{{-- Stats cards --}}
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Total Laporan</div>
        <div class="cl-stat-val" id="val-total">{{ $stats['total'] }}</div>
        <div class="cl-stat-sub">seluruh laporan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Menunggu Review</div>
        <div class="cl-stat-val" style="color:var(--amber)" id="val-submitted">{{ $stats['submitted'] }}</div>
        <div class="cl-stat-sub">perlu diverifikasi</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $stats['total'] > 0 ? round($stats['submitted']/$stats['total']*100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Disetujui</div>
        <div class="cl-stat-val" style="color:var(--green)" id="val-approved">{{ $stats['approved'] }}</div>
        <div class="cl-stat-sub">sudah diverifikasi</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $stats['total'] > 0 ? round($stats['approved']/$stats['total']*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Dikembalikan</div>
        <div class="cl-stat-val" style="color:var(--red)" id="val-rejected">{{ $stats['rejected'] }}</div>
        <div class="cl-stat-sub">perlu perbaikan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $stats['total'] > 0 ? round($stats['rejected']/$stats['total']*100) : 0 }}%;background:var(--red)"></div></div>
    </div>
</div>

{{-- Tab bar --}}
<div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab(this,'tab-laporan')">Daftar Laporan</button>
    <button class="tab-btn" onclick="switchTab(this,'tab-template')">
        Kelola Pertanyaan
        @if($templates->count())
            <span style="font-size:10px;margin-left:6px;background:var(--wash2);color:var(--ink3);padding:1px 6px;border-radius:3px">{{ $templates->count() }}</span>
        @endif
    </button>
</div>

{{-- TAB 1 — DAFTAR LAPORAN --}}
<div id="tab-laporan" class="tab-content active">
    <form method="GET" class="cl-filter">
        <input type="hidden" name="tab" value="laporan">
        <label>Bulan</label>
        <input type="month" name="bulan" value="{{ request('bulan') }}">
        <div class="cl-filter-sep"></div>
        <label>Wilayah</label>
        <select name="wilayah_id">
            <option value="">Semua Wilayah</option>
            @foreach($wilayahs as $w)
                <option value="{{ $w->id }}" {{ request('wilayah_id') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
            @endforeach
        </select>
        <div class="cl-filter-sep"></div>
        <label>Status</label>
        <select name="status">
            <option value="">Semua Status</option>
            <option value="submitted" {{ request('status') == 'submitted' ? 'selected' : '' }}>Menunggu</option>
            <option value="approved"  {{ request('status') == 'approved'  ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected"  {{ request('status') == 'rejected'  ? 'selected' : '' }}>Dikembalikan</option>
            <option value="draft"     {{ request('status') == 'draft'     ? 'selected' : '' }}>Draft</option>
        </select>
        <button type="submit" class="cl-filter-btn">Tampilkan</button>
        @if(request()->hasAny(['bulan','wilayah_id','status']))
            <a href="{{ route('admin.laporanharian.index') }}" class="cl-filter-btn-reset">Reset</a>
        @endif
        <div style="margin-left:auto">
            <a href="{{ route('admin.laporanharian.export', request()->query()) }}" class="btn-export">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="7 10 12 15 17 10"/>
                    <line x1="12" y1="15" x2="12" y2="3"/>
                </svg>
                Export
            </a>
        </div>
    </form>

    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Data Laporan Petugas</div>
                <div class="ph-sub">{{ $laporan->total() }} laporan ditemukan</div>
            </div>
        </div>

        <div class="table-scroll-outer">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Sesi</th>
                        <th>Petugas</th>
                        <th>Wilayah</th>
                      @foreach($templates as $tpl)
                            <th title="{{ $tpl->judul }}">
                                {{ \Illuminate\Support\Str::limit($tpl->judul, 22) }}
                                {{-- Tampilkan ikon kalender kecil jika pertanyaan punya berlaku_mulai --}}
                                @if($tpl->berlaku_mulai)
                                    <span title="Berlaku mulai: {{ \Carbon\Carbon::parse($tpl->berlaku_mulai)->format('d/m/Y') }}"
                                          style="font-size:9px;color:var(--ink3);margin-left:2px;font-weight:400">
                                        ({{ \Carbon\Carbon::parse($tpl->berlaku_mulai)->format('d/m') }})
                                    </span>
                                @endif
                            </th>
                        @endforeach
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $lap)
                    <tr data-id="{{ $lap->id }}" data-status="{{ $lap->status }}">
                        <td class="mono">{{ $lap->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $lap->hari }}</td>
                        <td>{{ ucfirst($lap->sesi) }}</td>
                        <td>{{ $lap->nama_petugas }}</td>
                        <td>{{ $lap->wilayah?->nama ?? '-' }}</td>
 @foreach($templates as $tpl)
                            @php
                                
                                $berlakuPadaTgl = is_null($tpl->berlaku_mulai)
                                    || $tpl->berlaku_mulai->lte($lap->tanggal);
                                $jawaban = $lap->jawabUntuk($tpl->id);
                            @endphp
                            @if($berlakuPadaTgl)
                                <td class="jawaban-cell" title="{{ $jawaban ?? '-' }}">
                                    {{ $jawaban ?? '-' }}
                                </td>
                            @else
                                {{-- Pertanyaan ini ditambahkan setelah laporan dibuat --}}
                                <td class="jawaban-cell"
                                    title="Pertanyaan belum ada saat laporan ini dibuat (berlaku mulai {{ \Carbon\Carbon::parse($tpl->berlaku_mulai)->format('d/m/Y') }})"
                                    style="color:var(--ink3);font-style:italic;text-align:center">
                                    -
                                </td>
                            @endif
                        @endforeach
                        <td>
                            @php
                                $pillClass = match($lap->status) {
                                    'submitted' => 'pill-submitted',
                                    'approved'  => 'pill-approved',
                                    'rejected'  => 'pill-rejected',
                                    default     => 'pill-draft',
                                };
                                $pillLabel = match($lap->status) {
                                    'submitted' => 'Menunggu',
                                    'approved'  => 'Disetujui',
                                    'rejected'  => 'Dikembalikan',
                                    default     => 'Draft',
                                };
                            @endphp
                            <span class="pill {{ $pillClass }}">{{ $pillLabel }}</span>
                        </td>
                        <td>
                            <a href="{{ route('admin.laporanharian.detail', $lap->id) }}" class="btn-detail">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 5 + $templates->count() + 2 }}" class="empty-state">
                            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                                <rect x="9" y="3" width="6" height="4" rx="1"/>
                                <line x1="9" y1="12" x2="15" y2="12"/>
                                <line x1="9" y1="16" x2="13" y2="16"/>
                            </svg>
                            <p>Belum ada data laporan untuk periode ini.</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($laporan->hasPages())
            <div class="pagination-wrap">
                {{ $laporan->appends(request()->query())->links('vendor.pagination.simple-default') }}
            </div>
            @endif
        </div>
    </div>
</div>

{{-- TAB 2 — KELOLA PERTANYAAN --}}
<div id="tab-template" class="tab-content">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px">
        <div>
            <div class="ph-title">Daftar Pertanyaan Laporan</div>
            <div class="ph-sub" style="margin-top:2px">Pertanyaan ini akan muncul di form laporan harian petugas</div>
        </div>
        <button class="btn-tambah" id="btn-tambah-pertanyaan">+ Tambah Pertanyaan</button>
    </div>

    @if($templates->isEmpty())
        <div class="tpl-card" style="padding:48px 20px;text-align:center;color:var(--ink3)">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="margin:0 auto 12px;opacity:.3">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="13" y2="16"/>
            </svg>
            <p>Belum ada pertanyaan. Klik tombol di atas untuk menambah.</p>
        </div>
    @else
        @foreach($templates as $idx => $tpl)
        <div class="tpl-card">
            <div class="tpl-row">
                <div class="tpl-num">{{ $idx + 1 }}</div>
                <div class="tpl-info">
                    <div class="tpl-judul">{{ $tpl->judul }}</div>
                    <div style="display:flex;gap:6px;margin-top:4px;flex-wrap:wrap">
                        <span class="tpl-badge {{ $tpl->tipe === 'pilihan' ? 'tpl-pilihan' : 'tpl-teks' }}">
                            {{ $tpl->tipe === 'pilihan' ? 'Pilihan / Dropdown' : 'Teks Bebas' }}
                        </span>
                        @if($tpl->wajib)<span class="tpl-badge tpl-wajib">Wajib</span>@endif
                        @if(!$tpl->aktif)<span class="tpl-badge" style="background:var(--wash2);color:var(--ink3)">Nonaktif</span>@endif
                    </div>
                    @if($tpl->deskripsi)
                        <div style="font-size:11.5px;color:var(--ink3);margin-top:4px;font-style:italic">{{ $tpl->deskripsi }}</div>
                    @endif
                </div>
                <div class="tpl-actions">
                    <button class="btn-sm btn-edit-tpl" data-tpl='@json($tpl)'>Edit</button>
                    <form method="POST" action="{{ route('admin.laporanharian.template.destroy', $tpl->id) }}"
                          onsubmit="return confirm('Hapus pertanyaan ini?')">
                        @csrf @method('DELETE')
                        <button type="submit" class="btn-sm btn-sm-del">Hapus</button>
                    </form>
                </div>
            </div>
            @if($tpl->tipe === 'pilihan' && $tpl->opsi)
            <div class="tpl-opsi-list">
                @foreach($tpl->opsi as $opsi)
                    <span class="tpl-opsi-tag">{{ $opsi }}</span>
                @endforeach
            </div>
            @endif
        </div>
        @endforeach
    @endif
</div>

{{-- ── MODAL TAMBAH / EDIT ── --}}
<div class="tpl-modal-bg" id="tpl-modal-bg">
    <div class="tpl-modal">
        <div class="tpl-modal-head">
            <h3 id="modal-title">Tambah Pertanyaan</h3>
            <button class="tpl-modal-close" id="btn-close-modal">✕</button>
        </div>
        <form method="POST" id="modal-form" action="{{ route('admin.laporanharian.template.store') }}">
            @csrf
            <span id="method-field"></span>
            <div class="tpl-modal-body">
                <div class="field">
                    <label>Judul Pertanyaan <span style="color:var(--red)">*</span></label>
                    <input type="text" name="judul" id="f-judul" placeholder="Contoh: Tamu Kunjungan Langsung" required>
                </div>
                <div class="field">
                    <label>Deskripsi / Petunjuk <span style="color:var(--ink3);font-weight:400">(opsional)</span></label>
                    <input type="text" name="deskripsi" id="f-deskripsi" placeholder="Penjelasan singkat untuk petugas">
                </div>
                <div class="field">
                    <label>Tipe Jawaban <span style="color:var(--red)">*</span></label>
                    <select name="tipe" id="f-tipe">
                        <option value="teks">Teks Bebas</option>
                        <option value="pilihan">Pilihan / Dropdown</option>
                    </select>
                </div>
                <div class="field opsi-section" id="opsi-section">
                    <label>Opsi Jawaban <span style="color:var(--red)">*</span></label>
                    <div class="opsi-list" id="opsi-list">
                        <div class="opsi-row">
                            <input type="text" name="opsi[]" placeholder="Contoh: Tidak ada kunjungan langsung">
                            <button type="button" class="btn-del-opsi" onclick="hapusOpsi(this)">×</button>
                        </div>
                    </div>
                    <button type="button" class="btn-add-opsi" onclick="tambahOpsi()">+ Tambah opsi</button>
                </div>
                <div class="field" style="display:flex;align-items:center;gap:8px">
                    <input type="checkbox" name="wajib" id="f-wajib" value="1" checked style="width:auto">
                    <label for="f-wajib" style="margin:0;font-size:12.5px;font-weight:400;color:var(--ink)">Wajib diisi petugas</label>
                </div>
            </div>
            <div class="tpl-modal-foot">
                <button type="button" class="btn-cancel-sm" id="btn-batal-modal">Batal</button>
                <button type="submit" class="btn-save">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
// ── Tab switching ──────────────────────────────────
function switchTab(btn, id) {
    document.querySelectorAll('.tab-btn').forEach(function(b) { b.classList.remove('active'); });
    document.querySelectorAll('.tab-content').forEach(function(c) { c.classList.remove('active'); });
    btn.classList.add('active');
    var target = document.getElementById(id);
    if (target) target.classList.add('active');
}

// ── Modal helpers ──────────────────────────────────
function bukaModal() {
    document.getElementById('tpl-modal-bg').classList.add('open');
}
function tutupModal() {
    document.getElementById('tpl-modal-bg').classList.remove('open');
}
function toggleOpsi(tipe) {
    var sec = document.getElementById('opsi-section');
    if (tipe === 'pilihan') sec.classList.add('show');
    else sec.classList.remove('show');
}
function tambahOpsi() {
    var list = document.getElementById('opsi-list');
    var row = document.createElement('div');
    row.className = 'opsi-row';
    row.innerHTML = '<input type="text" name="opsi[]" placeholder="Ketik opsi..."><button type="button" class="btn-del-opsi" onclick="hapusOpsi(this)">×</button>';
    list.appendChild(row);
}
function hapusOpsi(btn) {
    var list = document.getElementById('opsi-list');
    if (list.children.length > 1) btn.closest('.opsi-row').remove();
}

// ── Reset form untuk Tambah ────────────────────────
function resetFormTambah() {
    document.getElementById('modal-title').textContent = 'Tambah Pertanyaan';
    document.getElementById('modal-form').action = '{{ route("admin.laporanharian.template.store") }}';
    document.getElementById('method-field').innerHTML = '';
    document.getElementById('f-judul').value = '';
    document.getElementById('f-deskripsi').value = '';
    document.getElementById('f-tipe').value = 'teks';
    document.getElementById('f-wajib').checked = true;
    document.getElementById('opsi-list').innerHTML =
        '<div class="opsi-row"><input type="text" name="opsi[]" placeholder="Contoh: opsi 1"><button type="button" class="btn-del-opsi" onclick="hapusOpsi(this)">×</button></div>';
    toggleOpsi('teks');
}

// ── Isi form untuk Edit ────────────────────────────
function isiFormEdit(tpl) {
    document.getElementById('modal-title').textContent = 'Edit Pertanyaan';
    document.getElementById('modal-form').action = '/admin/laporan-template/' + tpl.id;
    document.getElementById('method-field').innerHTML = '<input type="hidden" name="_method" value="PATCH">';
    document.getElementById('f-judul').value = tpl.judul || '';
    document.getElementById('f-deskripsi').value = tpl.deskripsi || '';
    document.getElementById('f-tipe').value = tpl.tipe;
    document.getElementById('f-wajib').checked = !!tpl.wajib;
    toggleOpsi(tpl.tipe);
    var list = document.getElementById('opsi-list');
    list.innerHTML = '';
    var opsi = tpl.opsi || [];
    if (opsi.length === 0) opsi = [''];
    opsi.forEach(function(o) {
        var row = document.createElement('div');
        row.className = 'opsi-row';
        row.innerHTML = '<input type="text" name="opsi[]" value="' + (o || '').replace(/"/g,'&quot;') + '"><button type="button" class="btn-del-opsi" onclick="hapusOpsi(this)">×</button>';
        list.appendChild(row);
    });
}

// ── Event listeners setelah DOM siap ──────────────
document.addEventListener('DOMContentLoaded', function() {
    var modalEl = document.getElementById('tpl-modal-bg');
    if (modalEl) document.body.appendChild(modalEl);

    document.getElementById('btn-tambah-pertanyaan').addEventListener('click', function() {
        resetFormTambah();
        bukaModal();
    });

    document.getElementById('btn-close-modal').addEventListener('click', tutupModal);
    document.getElementById('btn-batal-modal').addEventListener('click', tutupModal);

    document.getElementById('tpl-modal-bg').addEventListener('click', function(e) {
        if (e.target === this) tutupModal();
    });

    document.querySelectorAll('.btn-edit-tpl').forEach(function(btn) {
        btn.addEventListener('click', function() {
            var tpl = JSON.parse(this.getAttribute('data-tpl'));
            isiFormEdit(tpl);
            bukaModal();
        });
    });

    document.getElementById('f-tipe').addEventListener('change', function() {
        toggleOpsi(this.value);
    });

    var urlParams = new URLSearchParams(window.location.search);
if (urlParams.get('tab') === 'template' || window.location.hash === '#template') {
    var tabBtns = document.querySelectorAll('.tab-btn');
    if (tabBtns.length >= 2) switchTab(tabBtns[1], 'tab-template');
    history.replaceState(null, '', window.location.pathname);
}
});
</script>

@endsection