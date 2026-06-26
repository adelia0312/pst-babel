@extends('layouts.koordinator')

@section('title', 'Monitoring Absensi')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <a href="{{ route('koordinator.dashboard') }}">Dashboard</a>
    <span>›</span>
    <strong>Monitoring Absensi</strong>
@endsection

{{-- Toast container --}}
<div id="rt-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("koordinator.absensi.polling") }}';
    const FILTER_DATE = '{{ $filterTanggal }}';
    const TODAY       = '{{ now()->toDateString() }}';
    const IS_TODAY    = (FILTER_DATE === TODAY);
    const INTERVAL_MS = 7000;

    let lastId = 0;
    document.querySelectorAll('tbody tr[data-id]').forEach(tr => {
        const id = parseInt(tr.dataset.id);
        if (id > lastId) lastId = id;
    });

    let unreadCount = 0;

    // ── Build row HTML ──────────────────────────────────
    function buildRow(r) {
        const isPagi    = r.jenis_scan === 'masuk_pagi' || r.jenis_scan === 'keluar_pagi';
        const sesiClass = isPagi ? 'sesi-pagi' : 'sesi-siang';

        let pillHtml;
        if (r.status_kehadiran === 'tepat_waktu')    pillHtml = '<span class="pill p-green">Tepat Waktu</span>';
        else if (r.status_kehadiran === 'toleransi') pillHtml = '<span class="pill p-amber">Toleransi</span>';
        else if (r.status_kehadiran === 'terlambat') pillHtml = '<span class="pill p-red">Terlambat</span>';
        else if (r.status_kehadiran === 'alpha')     pillHtml = '<span class="pill p-red">Alpha</span>';
        else                                         pillHtml = '<span class="pill" style="background:var(--wash2);color:var(--ink3)">Keluar</span>';

        const telatHtml = r.keterlambatan_menit > 0
            ? `<span style="color:var(--red)">+${r.keterlambatan_menit} mnt</span>`
            : '—';

        return `<tr data-id="${r.id}" class="rt-new-row">
            <td>
                <div style="display:flex;align-items:center;gap:8px">
                    <span class="mava">${r.inisial}</span>
                    <div>
                        <div class="td-main">${r.nama}</div>
                        <div class="td-id">${r.username}</div>
                    </div>
                </div>
            </td>
            <td class="mono">${r.tanggal}</td>
            <td><span class="pill ${sesiClass}">${r.jenis_scan_label}</span></td>
            <td class="mono">${r.jam}</td>
            <td>${pillHtml}</td>
            <td class="mono">${telatHtml}</td>
        </tr>`;
    }

    // ── Toast ───────────────────────────────────────────
    function showToast(r) {
        const wrap  = document.getElementById('rt-toast-wrap');
        const toast = document.createElement('div');
        toast.className = 'rt-toast';
        toast.innerHTML = `
            <div class="rt-toast-name">${r.nama}</div>
            <div class="rt-toast-detail">${r.jenis_scan_label} &middot; ${r.jam}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'rt-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    // ── Update badge sidebar ────────────────────────────
    function updateSidebarBadge() {
        const sbLink = document.getElementById('sb-absensi-link');
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

    // ── Update stats cards ──────────────────────────────
    function updateStats(stats) {
        const tp = stats.total_petugas || 0;
        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el && el.textContent !== String(val)) {
                el.textContent = val;
                const card = el.closest('.stat');
                if (card) { card.classList.remove('rt-updated'); void card.offsetWidth; card.classList.add('rt-updated'); }
            }
        }
        setVal('val-hadir',     stats.total_hadir);
        setVal('val-toleransi', stats.total_toleransi);
        setVal('val-terlambat', stats.total_terlambat);

        const pct = tp > 0 ? Math.round(stats.total_hadir / tp * 100) : 0;
        const barH = document.getElementById('bar-hadir');
        if (barH) barH.style.width = pct + '%';
        const thi  = stats.total_hari_ini || 0;
        const barTol = document.getElementById('bar-toleransi');
        if (barTol) barTol.style.width = (thi > 0 ? Math.round(stats.total_toleransi / thi * 100) : 0) + '%';
        const barTel = document.getElementById('bar-terlambat');
        if (barTel) barTel.style.width = (thi > 0 ? Math.round(stats.total_terlambat / thi * 100) : 0) + '%';
    }

    // ── Polling ─────────────────────────────────────────
    async function poll() {
        try {
            const resp = await fetch(`${POLL_URL}?after=${lastId}&tanggal=${FILTER_DATE}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) return;
            const data = await resp.json();

            if (data.max_id > lastId) lastId = data.max_id;
            if (data.stats) updateStats(data.stats);

            if (data.rows && data.rows.length > 0) {
                const tbody = document.querySelector('tbody');
                if (!tbody) return;

                data.rows.forEach(r => {
                    if (tbody.querySelector(`tr[data-id="${r.id}"]`)) return;
                    tbody.insertAdjacentHTML('afterbegin', buildRow(r));
                    showToast(r);
                    unreadCount++;
                });
                updateSidebarBadge();

                // Hapus empty state jika ada
                const empty = tbody.querySelector('.empty');
                const emptyTr = empty ? empty.closest('tr') : null;
                if (emptyTr) emptyTr.remove();

                setTimeout(() => {
                    document.querySelectorAll('tbody tr.rt-new-row').forEach(tr => tr.classList.remove('rt-new-row'));
                }, 1500);
            }
        } catch (e) { /* gagal diam */ }
    }

    // Reset badge saat klik menu absensi
    const sbLink = document.getElementById('sb-absensi-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateSidebarBadge(); });

    if (IS_TODAY) {
        setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
    }
})();
</script>
@endpush

@push('styles')
<style>
    /* ── FILTER BAR ─────────────────────────────────── */
    .filter-bar {
        display: flex; align-items: center; gap: 10px;
        margin-bottom: 20px; flex-wrap: wrap;
    }
    .filter-bar label { font-size: 11px; font-weight: 600; color: var(--ink3); text-transform: uppercase; letter-spacing: .8px; }
    .filter-select, .filter-input {
        height: 32px; border: 1px solid var(--rule); border-radius: 5px;
        background: var(--surface); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif; font-size: 12.5px;
        padding: 0 10px; outline: none;
        transition: border-color .12s;
    }
    .filter-select:focus, .filter-input:focus { border-color: var(--blue); }
    .filter-input { min-width: 130px; }
    .btn-filter {
        height: 32px; padding: 0 14px;
        background: var(--blue); color: #fff;
        border: none; border-radius: 5px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12px; font-weight: 500; cursor: pointer;
        transition: opacity .12s;
    }
    .btn-filter:hover { opacity: .88; }
    .btn-reset {
        height: 32px; padding: 0 12px;
        background: none; color: var(--ink3);
        border: 1px solid var(--rule); border-radius: 5px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12px; cursor: pointer;
        transition: border-color .12s, color .12s;
    }
    .btn-reset:hover { border-color: var(--ink2); color: var(--ink); }
    .spacer { flex: 1; }

    /* ── STATS — mengikuti style admin dashboard ─────── */
    .stats {
        display: grid; grid-template-columns: repeat(4, 1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 20px;
    }
    .stat { background: var(--surface); padding: 20px 22px; position: relative; }
    .stat-label {
        font-size: 10.5px; font-weight: 500; letter-spacing: .5px;
        text-transform: uppercase; color: var(--ink3); margin-bottom: 10px;
    }
    .stat-num {
        font-size: 30px; font-weight: 300; letter-spacing: -1.2px;
        color: var(--ink); font-family: 'IBM Plex Mono', monospace;
        line-height: 1; margin-bottom: 8px;
    }
    .stat-meta  { display: flex; gap: 6px; align-items: center; font-size: 11px; color: var(--ink3); }
    .delta { padding: 1px 6px; border-radius: 3px; font-size: 10.5px; font-weight: 500; font-family: 'IBM Plex Mono', monospace; }
    .d-up   { background: var(--green-lt); color: var(--green); }
    .d-down { background: var(--red-lt);   color: var(--red); }
    .d-amber{ background: var(--amber-lt); color: var(--amber); }
    .stat-bar  { position: absolute; bottom: 0; left: 0; right: 0; height: 2px; }
    .stat-fill { height: 100%; }

    /* ── GRID 2 KOLOM ───────────────────────────────── */
    .grid2 { display: grid; grid-template-columns: 1fr 340px; gap: 16px; }

    /* ── TABLE PANEL ────────────────────────────────── */
    .action-btn {
        display: inline-flex; align-items: center; gap: 4px;
        padding: 3px 9px; border-radius: 4px; font-size: 10.5px; font-weight: 500;
        cursor: pointer; border: none; font-family: 'IBM Plex Sans', sans-serif;
        transition: opacity .12s;
    }
    .action-btn:hover { opacity: .78; }
    .btn-approve { background: var(--green-lt); color: var(--green); }
    .btn-reject  { background: var(--red-lt);   color: var(--red); }

    /* ── REKAP SIDEBAR ──────────────────────────────── */
    .rekap-list { padding: 4px 0; }
    .rekap-item {
        display: flex; align-items: center; gap: 10px;
        padding: 9px 18px; border-bottom: 1px solid var(--rule);
    }
    .rekap-item:last-child { border-bottom: none; }
    .rekap-ava {
        width: 28px; height: 28px; border-radius: 4px;
        background: var(--wash2); color: var(--ink2);
        display: flex; align-items: center; justify-content: center;
        font-size: 9.5px; font-weight: 700; flex-shrink: 0; text-transform: uppercase;
    }
    .rekap-name  { font-size: 12px; font-weight: 500; color: var(--ink); }
    .rekap-sesi  { font-size: 10.5px; color: var(--ink3); }
    .rekap-right { margin-left: auto; text-align: right; }
    .rekap-jam   { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink2); }

    /* ── SESI BADGE ─────────────────────────────────── */
    .sesi-pagi  { background: #fff8e1; color: #e65100; }
    .sesi-siang { background: #e3f2fd; color: #0277bd; }
    .sesi-malam { background: #ede7f6; color: #4527a0; }

    /* ── MODAL VERIFY ───────────────────────────────── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 500;
        background: rgba(0,0,0,.35); align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
        background: var(--surface); border-radius: 10px;
        width: 440px; max-width: 95vw; overflow: hidden;
        box-shadow: 0 20px 60px rgba(0,0,0,.18);
    }
    .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 14px 18px; border-bottom: 1px solid var(--rule);
    }
    .modal-title { font-size: 13.5px; font-weight: 600; }
    .modal-close {
        background: none; border: none; cursor: pointer;
        color: var(--ink3); padding: 2px; border-radius: 3px;
        transition: color .12s; display: flex;
    }
    .modal-close:hover { color: var(--ink); }
    .modal-body { padding: 18px; }
    .modal-row { display: flex; gap: 10px; margin-bottom: 10px; }
    .modal-kv { flex: 1; }
    .modal-k { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .7px; color: var(--ink3); margin-bottom: 3px; }
    .modal-v { font-size: 12.5px; color: var(--ink); font-weight: 500; }
    .modal-foto {
        width: 100%; max-height: 200px; object-fit: cover;
        border-radius: 6px; border: 1px solid var(--rule);
        margin-bottom: 12px;
    }
    .modal-foot {
        display: flex; gap: 8px; justify-content: flex-end;
        padding: 12px 18px; border-top: 1px solid var(--rule);
    }
    .btn-lg {
        height: 34px; padding: 0 16px; border-radius: 5px;
        font-family: 'IBM Plex Sans', sans-serif; font-size: 12.5px;
        font-weight: 500; cursor: pointer; border: none;
        transition: opacity .12s;
    }
    .btn-lg:hover { opacity: .85; }
    .btn-approve-lg { background: var(--green); color: #fff; }
    .btn-reject-lg  { background: var(--red);   color: #fff; }
    .btn-cancel-lg  { background: var(--wash2); color: var(--ink2); }

    /* ── QR ICON TRIGGER (pojok kanan atas panel) ───────── */
    .qr-trigger-btn {
        display: inline-flex; align-items: center; gap: 5px;
        padding: 5px 10px; border-radius: 6px;
        background: var(--blue-lt); color: var(--blue);
        border: 1px solid var(--blue);
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 11px; font-weight: 600; cursor: pointer;
        text-decoration: none;
        transition: background .12s, transform .1s;
        position: relative;
    }
    .qr-trigger-btn:hover { background: var(--blue); color: #fff; transform: scale(1.04); }
    .qr-trigger-dot {
        position: absolute; top: -4px; right: -4px;
        width: 8px; height: 8px; border-radius: 50%;
        background: var(--green); border: 1.5px solid var(--surface);
        animation: qr-pulse 1.8s ease-in-out infinite;
    }
    @keyframes qr-pulse {
        0%, 100% { opacity: 1; transform: scale(1); }
        50%       { opacity: .5; transform: scale(1.3); }
    }

    @media (max-width: 900px) {
        .grid2 { grid-template-columns: 1fr; }
        .stats { grid-template-columns: repeat(2, 1fr); }
    }

    /* ── REALTIME: Toast ─────────────────────────────────── */
    #rt-toast-wrap {
        position: fixed; bottom: 24px; right: 24px;
        z-index: 9999; display: flex; flex-direction: column; gap: 8px;
        pointer-events: none;
    }
    .rt-toast {
        background: var(--surface); border: 1px solid var(--rule);
        border-left: 3px solid var(--green);
        border-radius: 8px; padding: 10px 14px; min-width: 220px;
        font-size: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.12);
        animation: rt-toast-in .25s ease;
        pointer-events: auto;
    }
    .rt-toast-name   { font-weight: 600; color: var(--ink); margin-bottom: 2px; }
    .rt-toast-detail { color: var(--ink3); font-size: 11px; }
    @keyframes rt-toast-in  { from { opacity:0; transform: translateX(16px); } to { opacity:1; transform: none; } }
    @keyframes rt-toast-out { from { opacity:1; } to { opacity:0; transform: translateX(16px); } }

    /* ── REALTIME: New row flash ─────────────────────────── */
    @keyframes rt-row-in {
        from { background: #d1fae5; opacity: 0; }
        to   { background: transparent; opacity: 1; }
    }
    tbody tr.rt-new-row { animation: rt-row-in 1.2s ease forwards; }

    /* ── REALTIME: Stat card flash ───────────────────────── */
    @keyframes rt-stat-flash { 0%{background:#d1fae5} 100%{background:var(--surface)} }
    .stat.rt-updated { animation: rt-stat-flash .8s ease forwards; }
    /* ── QR SHIFT BUTTONS ─────────────────────────────────── */
.btn-qr-shift {
    padding: 6px 14px;
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 5px;
    font-family: 'IBM Plex Sans', sans-serif;
    font-size: 12px;
    font-weight: 500;
    cursor: pointer;
    transition: opacity .12s;
}
.btn-qr-shift:hover {
    opacity: .85;
}

/* ── MODAL QR ────────────────────────────────────────── */
.modal-qr {
    width: 560px;
    max-width: 95vw;
}
.qr-grid-modal {
    display: flex;
    flex-direction: column;
    gap: 20px;
    max-height: 60vh;
    overflow-y: auto;
    padding: 4px;
}
.qr-card-modal {
    border: 1px solid var(--rule);
    border-radius: 10px;
    padding: 16px;
    background: white;
    text-align: center;
}
.qr-card-modal .qr-label {
    font-weight: 600;
    font-size: 13px;
    margin-bottom: 8px;
}
.qr-card-modal .qr-time {
    font-size: 11px;
    color: var(--ink3);
    margin-top: 8px;
}
</style>
@endpush

@section('content')

{{-- ── PAGE HEAD ─────────────────────────────────────────────── --}}
<div class="page-head">
    <div>
        <h1>Monitoring Absensi</h1>
        <p><strong>{{ $wilayah->nama ?? 'Wilayah' }}</strong> — Data kehadiran petugas dari aplikasi mobile</p>
    </div>
    
</div>

{{-- ── ALERT ────────────────────────────────────────────────── --}}
@if(session('success'))
<div class="alert alert-success">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4"/><circle cx="12" cy="12" r="9"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="9"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- ── STATS ─────────────────────────────────────────────────── --}}
<div class="stats">
    <div class="stat">
        <div class="stat-label">Petugas Terjadwal</div>
        <div class="stat-num">{{ $totalPetugas }}</div>
        <div class="stat-meta"><span>{{ $wilayah->nama ?? '-' }} · tanggal terpilih</span></div>
        <div class="stat-bar"><div class="stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label">Hadir Hari Ini</div>
        <div class="stat-num" style="color:var(--green)" id="val-hadir">{{ $totalHadir }}</div>
        <div class="stat-meta">
            <span class="delta d-up">{{ $totalPetugas > 0 ? round($totalHadir / $totalPetugas * 100) : 0 }}%</span>
            <span>kehadiran</span>
        </div>
        <div class="stat-bar"><div class="stat-fill" id="bar-hadir" style="width:{{ $totalPetugas > 0 ? round($totalHadir / $totalPetugas * 100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label">Toleransi</div>
        <div class="stat-num" style="color:var(--amber)" id="val-toleransi">{{ $totalTolerasi ?? 0 }}</div>
        <div class="stat-meta"><span class="delta d-amber">≤10 menit</span></div>
        <div class="stat-bar"><div class="stat-fill" id="bar-toleransi" style="width:{{ $totalHariIni > 0 ? round(($totalTolerasi ?? 0) / $totalHariIni * 100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label">Terlambat</div>
        <div class="stat-num" style="color:var(--red)" id="val-terlambat">{{ $totalTerlambat ?? 0 }}</div>
        <div class="stat-meta"><span class="delta d-down">>10 menit</span></div>
        <div class="stat-bar"><div class="stat-fill" id="bar-terlambat" style="width:{{ $totalHariIni > 0 ? round(($totalTerlambat ?? 0) / $totalHariIni * 100) : 0 }}%;background:var(--red)"></div></div>
    </div>
</div>

{{-- ── FILTER BAR ────────────────────────────────────────────── --}}
<form method="GET" action="{{ route('koordinator.absensi.index') }}" class="filter-bar" id="filterForm">
    <label>Tanggal</label>
    <input type="date" name="tanggal" class="filter-input" value="{{ $filterTanggal }}" max="{{ now()->toDateString() }}">

    <label>Sesi</label>
    <select name="sesi" class="filter-select">
        <option value="">Semua Sesi</option>
        <option value="pagi"   {{ $filterSesi === 'pagi'   ? 'selected' : '' }}>Pagi</option>
        <option value="siang"  {{ $filterSesi === 'siang'  ? 'selected' : '' }}>Siang</option>
    </select>

    <label>Status</label>
    <select name="status" class="filter-select">
        <option value="">Semua Status</option>
        <option value="tepat_waktu"  {{ $filterStatus === 'tepat_waktu'  ? 'selected' : '' }}>Tepat Waktu</option>
        <option value="toleransi"    {{ $filterStatus === 'toleransi'    ? 'selected' : '' }}>Toleransi</option>
        <option value="terlambat"    {{ $filterStatus === 'terlambat'    ? 'selected' : '' }}>Terlambat</option>
        <option value="alpha"        {{ $filterStatus === 'alpha'        ? 'selected' : '' }}>Alpha</option>
    </select>

    <button type="submit" class="btn-filter">Terapkan</button>
    <a href="{{ route('koordinator.absensi.index') }}" class="btn-reset">Reset</a>

    <div class="spacer"></div>

{{-- Tombol QR Code --}}
<div style="display:flex; gap:8px;">
    <button type="button" class="btn-qr-shift" data-sesi="pagi" onclick="openQrModal('pagi')">
        QR Shift Pagi
    </button>
    <button type="button" class="btn-qr-shift" data-sesi="siang" onclick="openQrModal('siang')">
        QR Shift Siang
</button>
</div>
</form>

{{-- ── GRID 2 KOLOM ─────────────────────────────────────────── --}}
<div class="grid2">

    {{-- ── TABEL ABSENSI ──────────────────────────────────────── --}}
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Data Absensi Petugas</div>
                <div class="ph-sub">{{ $absensi->total() }} record · {{ $filterTanggal }}</div>
            </div>
            {{-- Bulk actions --}}
            <div style="display:flex;gap:6px;align-items:center">
                <span id="selectedCount" style="font-size:11px;color:var(--ink3);display:none">
                    <span id="selNum">0</span> dipilih
                </span>
                <button id="btnBulkApprove" onclick="bulkAction('approve')"
                    class="action-btn btn-approve" style="display:none">
                    ✓ Approve Semua
                </button>
            </div>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Petugas</th>
                    <th>Tanggal</th>
                    <th>Jenis Scan</th>
                    <th>Jam</th>
                    <th>Status Kehadiran</th>
                    <th>Keterlambatan</th>
                </tr>
            </thead>
            <tbody>
                @forelse($absensi as $a)
                <tr data-id="{{ $a->id }}">
                    <td>
                        <div style="display:flex;align-items:center;gap:8px">
                            <span class="mava">{{ strtoupper(substr($a->user->name, 0, 2)) }}</span>
                            <div>
                                <div class="td-main">{{ $a->user->name }}</div>
                                <div class="td-id">{{ $a->user->username }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="mono">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
                    <td>
                        @php
                            $jenisCls = match($a->jenis_scan) {
                                'masuk_pagi','keluar_pagi'   => 'sesi-pagi',
                                'masuk_siang','keluar_siang' => 'sesi-siang',
                                default => '',
                            };
                        @endphp
                        <span class="pill {{ $jenisCls }}">{{ $a->label_jenis_scan }}</span>
                    </td>
                    <td class="mono">{{ $a->jam_masuk ?? $a->jam_keluar ?? '—' }}</td>
                    <td>
                        @php
                            $skCls = match($a->status_kehadiran) {
                                'tepat_waktu' => 'p-green',
                                'toleransi'   => 'p-amber',
                                'terlambat'   => 'p-red',
                                'alpha'       => 'p-red',
                                default       => '',
                            };
                        @endphp
                        @if($a->status_kehadiran)
                            <span class="pill {{ $skCls }}">{{ $a->label_status_kehadiran }}</span>
                        @else
                            <span class="pill" style="background:var(--wash2);color:var(--ink3)">Keluar</span>
                        @endif
                    </td>
                    <td class="mono">
                        @if($a->keterlambatan_menit > 0)
                            <span style="color:var(--red)">+{{ $a->keterlambatan_menit }} mnt</span>
                        @else
                            —
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9">
                        <div class="empty">
                            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                                <path d="M9 11l3 3L22 4"/>
                                <path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                            </svg>
                            Belum ada data absensi<br>
                            <span style="font-size:11px">untuk tanggal {{ $filterTanggal }}
                            @if($filterSesi) · sesi {{ $filterSesi }} @endif
                            @if($filterStatus) · status {{ $filterStatus }} @endif
                            </span>
                        </div>
                    </td>
                </tr>
                @endforelse
            </tbody>
        </table>

        {{-- Pagination --}}
        @if($absensi->hasPages())
        <div style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--rule)">
            <div style="font-size:11px;color:var(--ink3)">
                Menampilkan {{ $absensi->firstItem() }}–{{ $absensi->lastItem() }} dari {{ $absensi->total() }} data
            </div>
            {{ $absensi->appends(request()->query())->links('vendor.pagination.simple-default') }}
        </div>
        @endif
    </div>

    {{-- ── SIDEBAR KANAN ────────────────────────────────────────── --}}
    <div style="display:flex;flex-direction:column;gap:16px">



        {{-- Absen masuk terbaru --}}
        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Absen Masuk Terbaru</div>
                    <div class="ph-sub">Hari ini · real-time</div>
                </div>
            </div>
            <div class="rekap-list">
                @forelse($absensiTerbaru as $t)
                <div class="rekap-item">
                    <div class="rekap-ava">{{ strtoupper(substr($t->user->name, 0, 2)) }}</div>
                    <div>
                        <div class="rekap-name">{{ $t->user->name }}</div>
                        <div class="rekap-sesi">Sesi {{ ucfirst($t->sesi) }}</div>
                    </div>
                    <div class="rekap-right">
                        <div class="rekap-jam">{{ $t->jam_masuk }}</div>
                        <div style="font-size:10px;color:var(--ink3);margin-top:2px">masuk</div>
                    </div>
                </div>
                @empty
                <div class="empty" style="padding:24px">Belum ada yang absen hari ini</div>
                @endforelse
            </div>
        </div>

    </div>{{-- /sidebar kanan --}}
</div>{{-- /grid2 --}}

{{-- ══ MODAL DETAIL / VERIFIKASI ═══════════════════════════════ --}}
<div class="modal-overlay" id="modalOverlay" onclick="closeModalOutside(event)">
    <div class="modal" id="modal">
        <div class="modal-head">
            <div class="modal-title">Detail Absensi</div>
            <button class="modal-close" onclick="closeModal()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
            </button>
        </div>
        <div class="modal-body">
            <img id="modalFoto" class="modal-foto" src="" alt="Foto Selfie" style="display:none">
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Petugas</div>
                    <div class="modal-v" id="modalNama">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Tanggal</div>
                    <div class="modal-v" id="modalTanggal">—</div>
                </div>
            </div>
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Sesi</div>
                    <div class="modal-v" id="modalSesi">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Status Hadir</div>
                    <div class="modal-v" id="modalStatus">—</div>
                </div>
            </div>
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Jam Masuk</div>
                    <div class="modal-v mono" id="modalMasuk">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Jam Keluar</div>
                    <div class="modal-v mono" id="modalKeluar">—</div>
                </div>
            </div>
            <div class="modal-row" id="lokasiRow" style="display:none">
                <div class="modal-kv" style="flex:1">
                    <div class="modal-k">Lokasi GPS</div>
                    <div class="modal-v" id="modalLokasi">—</div>
                </div>
                <a id="modalMapLink" href="#" target="_blank"
                   style="align-self:flex-end;display:inline-flex;align-items:center;gap:4px;
                          font-size:11px;color:var(--blue);text-decoration:none;white-space:nowrap">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    Buka Maps
                </a>
            </div>
            <div id="catatanRow" style="display:none;margin-top:4px">
                <div class="modal-k">Catatan</div>
                <div class="modal-v" id="modalCatatan" style="margin-top:3px;color:var(--ink2)">—</div>
            </div>

            <div id="statusVerifRow" style="margin-top:10px;padding-top:10px;border-top:1px solid var(--rule)">
                <div class="modal-k">Status Verifikasi</div>
                <div id="modalVerifStatus" style="margin-top:4px">—</div>
            </div>
        </div>
        <div class="modal-foot" id="modalFoot">
            <button class="btn-lg btn-cancel-lg" onclick="closeModal()">Tutup</button>
            <button class="btn-lg btn-reject-lg" id="btnRejectModal" onclick="submitVerify('rejected')">✕ Tolak</button>
            <button class="btn-lg btn-approve-lg" id="btnApproveModal" onclick="submitVerify('approved')">✓ Setujui</button>
        </div>
    </div>
</div>

{{-- Hidden form for verify actions --}}
<form id="verifyForm" method="POST" style="display:none">
    @csrf
    @method('PATCH')
    <input type="hidden" name="action" id="verifyAction">
</form>
{{-- ══ MODAL QR ABSENSI ═══════════════════════════════════ --}}
<div class="modal-overlay" id="qrModalOverlay" onclick="closeQrModalOutside(event)">
    <div class="modal modal-qr">
        <div class="modal-head">
            <div class="modal-title" id="qrModalTitle">QR Absensi</div>
            <button class="modal-close" onclick="closeQrModal()">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/>
                    <line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body" id="qrModalBody" style="text-align:center;">
            <div id="qrCountdown" style="font-size:12px; color:var(--green); margin-bottom:12px;"></div>
            <div id="qrContentContainer" style="max-height:50vh; overflow-y:auto;"></div>
            <div id="qrTokenContainer" style="font-family:monospace; font-size:10px; color:var(--ink3); word-break:break-all; margin-top:12px; text-align:left;"></div>
        </div>
        <div class="modal-foot">
            <button class="btn-lg btn-cancel-lg" onclick="closeQrModal()">Tutup</button>
            <button class="btn-lg btn-approve-lg" onclick="refreshQrModal()">Refresh QR</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
{{-- Library QR Code – wajib dimuat sebelum script modal QR --}}
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"
        integrity="sha512-CNgIRecGo7nphbeZ04Sc13ka07paqdeTu0WR1IM4kNcpmBAUSHSE1FTdKQLpf7CV2L3Rh/QbBpVL4gg2dHv/w=="
        crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
// Fallback: jika CDN gagal, muat ulang dari unpkg
window.addEventListener('load', function() {
    if (typeof QRCode === 'undefined') {
        var s = document.createElement('script');
        s.src = 'https://unpkg.com/qrcodejs@1.0.0/qrcode.min.js';
        document.head.appendChild(s);
        console.warn('QRCode CDN fallback: memuat dari unpkg');
    }
});
</script>

<script>
// ── MODAL DETAIL ──────────────────────────────────────────────
let currentAbsensiId = null;

function openModal(id, nama, tanggal, sesi, masuk, keluar, status, verifStatus, foto, catatan, lat, lng) {
    currentAbsensiId = id;
    document.getElementById('modalNama').textContent    = nama;
    document.getElementById('modalTanggal').textContent = tanggal;
    document.getElementById('modalSesi').textContent    = sesi;
    document.getElementById('modalStatus').textContent  = status.charAt(0).toUpperCase() + status.slice(1);
    document.getElementById('modalMasuk').textContent   = masuk;
    document.getElementById('modalKeluar').textContent  = keluar;

    const fotoEl = document.getElementById('modalFoto');
    if (foto) { fotoEl.src = foto; fotoEl.style.display = 'block'; }
    else       { fotoEl.style.display = 'none'; }

    const lokasiRow = document.getElementById('lokasiRow');
    if (lat && lng) {
        lokasiRow.style.display = 'flex';
        document.getElementById('modalLokasi').textContent = `${parseFloat(lat).toFixed(6)}, ${parseFloat(lng).toFixed(6)}`;
        document.getElementById('modalMapLink').href = `https://maps.google.com/?q=${lat},${lng}`;
    } else {
        lokasiRow.style.display = 'none';
    }

    const catatanRow = document.getElementById('catatanRow');
    if (catatan) {
        catatanRow.style.display = 'block';
        document.getElementById('modalCatatan').textContent = catatan;
    } else {
        catatanRow.style.display = 'none';
    }

    const verifEl = document.getElementById('modalVerifStatus');
    const pillMap  = { approved: 'p-green', rejected: 'p-red', pending: 'p-amber' };
    const labelMap = { approved: 'Approved', rejected: 'Rejected', pending: 'Pending' };
    verifEl.innerHTML = `<span class="pill ${pillMap[verifStatus]}">${labelMap[verifStatus]}</span>`;

    const btnA = document.getElementById('btnApproveModal');
    const btnR = document.getElementById('btnRejectModal');
    btnA.style.display = verifStatus === 'pending' ? 'block' : 'none';
    btnR.style.display = verifStatus === 'pending' ? 'block' : 'none';

    document.getElementById('modalOverlay').classList.add('open');
}

function closeModal() {
    document.getElementById('modalOverlay').classList.remove('open');
    currentAbsensiId = null;
}
function closeModalOutside(e) {
    if (e.target === document.getElementById('modalOverlay')) closeModal();
}

function submitVerify(action) {
    if (!currentAbsensiId) return;
    const form = document.getElementById('verifyForm');
    form.action = `/koordinator/absensi/${currentAbsensiId}/verify`;
    document.getElementById('verifyAction').value = action;
    form.submit();
}

// ── BULK ACTION ───────────────────────────────────────────────
function toggleAll(cb) {
    document.querySelectorAll('.row-check').forEach(c => c.checked = cb.checked);
    updateBulkBar();
}
function updateBulkBar() {
    const checked = document.querySelectorAll('.row-check:checked');
    const countEl = document.getElementById('selectedCount');
    const btnBulk = document.getElementById('btnBulkApprove');
    if (checked.length > 0) {
        document.getElementById('selNum').textContent = checked.length;
        countEl.style.display = 'inline';
        btnBulk.style.display = 'inline-flex';
    } else {
        countEl.style.display = 'none';
        btnBulk.style.display = 'none';
    }
}
function bulkAction(action) {
    const ids = [...document.querySelectorAll('.row-check:checked')].map(c => c.value);
    if (!ids.length) return;
    if (!confirm(`Approve ${ids.length} absensi?`)) return;
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = '{{ route("koordinator.absensi.bulkVerify") }}';
    form.innerHTML = `@csrf <input name="_method" value="PATCH">
        <input name="action" value="${action}">
        ${ids.map(id => `<input name="ids[]" value="${id}">`).join('')}`;
    document.body.appendChild(form);
    form.submit();
}
// ── QR MODAL ──────────────────────────────────────────────
let qrModalSesi   = '';
let qrTickTimer   = null;
let lastSlotFetch = -1; // slot terakhir kali fetch dilakukan

// Hitung slot saat ini (berubah tiap 30 detik, sinkron ke jam)
function getCurrentSlot() {
    const now = new Date();
    return Math.floor((now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds()) / 30);
}

// Sisa detik sampai slot berikutnya
function sisaDetikSlot() {
    const now = new Date();
    const totalDetik = now.getHours() * 3600 + now.getMinutes() * 60 + now.getSeconds();
    return 30 - (totalDetik % 30);
}

async function openQrModal(sesi) {
    qrModalSesi = sesi;
    const title = sesi === 'pagi'  ? 'QR Absensi - Shift Pagi'
                : sesi === 'siang' ? 'QR Absensi - Shift Siang'
                :                    'QR Absensi - Semua Shift Aktif';
    document.getElementById('qrModalTitle').textContent = title;
    document.getElementById('qrModalOverlay').classList.add('open');

    // Stop timer lama kalau ada
    if (qrTickTimer) clearInterval(qrTickTimer);
    lastSlotFetch = -1;

    // Langsung fetch pertama kali
    await loadQrData();

    // Tick tiap 1 detik — fetch hanya saat slot berganti
    qrTickTimer = setInterval(qrTick, 1000);
}

function qrTick() {
    const slot = getCurrentSlot();
    const sisa = sisaDetikSlot();

    // Update countdown
    const cdEl = document.getElementById('qrCountdown');
    if (cdEl) {
        cdEl.textContent = sisa === 30
            ? 'Memperbarui QR...'
            : 'QR akan diperbarui dalam ' + sisa + ' detik';
    }

    // Fetch baru hanya kalau slot sudah berganti
    if (slot !== lastSlotFetch) {
        lastSlotFetch = slot;
        loadQrData();
    }
}

function closeQrModal() {
    document.getElementById('qrModalOverlay').classList.remove('open');
    if (qrTickTimer) clearInterval(qrTickTimer);
    qrTickTimer   = null;
    lastSlotFetch = -1;
}

function closeQrModalOutside(e) {
    if (e.target === document.getElementById('qrModalOverlay')) closeQrModal();
}

async function loadQrData() {
    try {
        const url = '{{ route("koordinator.absensi.qrJson") }}' + (qrModalSesi ? '?sesi=' + qrModalSesi : '');
        const response = await fetch(url);
        const data     = await response.json();

        if (!data.success) {
            document.getElementById('qrContentContainer').innerHTML = '<p style="color:var(--red)">Gagal memuat QR</p>';
            return;
        }

        const qrStatus        = data.qr_status;
        const contentContainer = document.getElementById('qrContentContainer');
        const tokenContainer   = document.getElementById('qrTokenContainer');

        if (Object.keys(qrStatus).length === 0) {
            contentContainer.innerHTML = '<p style="color:var(--amber); padding:20px;">Tidak ada QR yang aktif untuk shift yang dipilih saat ini.</p>';
            tokenContainer.innerHTML   = '';
            const cdEl = document.getElementById('qrCountdown');
            if (cdEl) cdEl.textContent = 'Tidak ada QR aktif';
            return;
        }

        let html      = '<div class="qr-grid-modal">';
        let tokenHtml = '';

        for (const [jenis, info] of Object.entries(qrStatus)) {
            const sesiLabel     = info.sesi === 'pagi' ? 'Pagi' : 'Siang';
            const toleransiText = info.toleransi > 0 ? ' (Toleransi ' + info.toleransi + ' menit)' : '';
            html += `
                <div class="qr-card-modal">
                    <div class="qr-label">${info.label} (Shift ${sesiLabel})${toleransiText}</div>
                    <div id="qr-img-${jenis}" style="display:flex; justify-content:center; margin:10px 0;"></div>
                    <div class="qr-time">Berlaku: ${info.qr_mulai} - ${info.qr_selesai}</div>
                </div>`;
            tokenHtml += `${info.label}: ${info.token}\n`;
        }
        html += '</div>';

        contentContainer.innerHTML = html;
        tokenContainer.innerHTML   = '<strong>Token QR:</strong><br>' + tokenHtml.replace(/\n/g, '<br>');

        renderAllQr(qrStatus);

    } catch (e) {
        console.error('Load QR gagal:', e);
        document.getElementById('qrContentContainer').innerHTML = '<p style="color:var(--red)">Gagal memuat QR</p>';
    }
}

// Render QR — tunggu library siap jika belum ter-load
function renderAllQr(qrStatus, attempt) {
    attempt = attempt || 0;
    if (typeof QRCode === 'undefined') {
        if (attempt >= 10) {
            for (const [jenis] of Object.entries(qrStatus)) {
                const qrDiv = document.getElementById(`qr-img-${jenis}`);
                if (qrDiv) qrDiv.innerHTML = `<div style="width:160px;height:160px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid #ccc;gap:6px;font-size:11px;color:#888;padding:8px;text-align:center;">⚠️ Library QR tidak dapat dimuat.<br>Periksa koneksi internet.</div>`;
            }
            return;
        }
        setTimeout(() => renderAllQr(qrStatus, attempt + 1), 300);
        return;
    }
    for (const [jenis, info] of Object.entries(qrStatus)) {
        if (!info.token) continue;
        const qrDiv = document.getElementById(`qr-img-${jenis}`);
        if (!qrDiv) continue;
        qrDiv.innerHTML = '';
        try {
            new QRCode(qrDiv, {
                text: info.token,
                width: 160,
                height: 160,
                colorDark: '#0d1117',
                colorLight: '#ffffff',
                correctLevel: QRCode.CorrectLevel.M,
            });
        } catch (e) {
            console.error('QR render error:', e);
            qrDiv.innerHTML = `<div style="width:160px;height:160px;display:flex;flex-direction:column;align-items:center;justify-content:center;border:1px solid #ccc;gap:4px;font-size:11px;color:#888;">⚠️ Gagal render QR<br><small>${e.message||''}</small></div>`;
        }
    }
}

function refreshQrModal() {
    loadQrData();
}
</script>
@endpush