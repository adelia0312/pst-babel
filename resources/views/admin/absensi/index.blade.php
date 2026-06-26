@extends('layouts.admin')

@section('title', 'Monitoring Absensi — Semua Wilayah')

@section('breadcrumb')
    <span>Admin</span>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Monitoring Absensi</strong>

{{-- Toast container --}}
<div id="rt-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("admin.absensi.polling") }}';
    const FILTER_DATE = '{{ $filterTanggal }}';
    const TODAY       = '{{ now()->toDateString() }}';
    const IS_TODAY    = (FILTER_DATE === TODAY);
    const INTERVAL_MS = 7000;

    // Ambil max id dari baris yang sudah ada
    let lastId = 0;
    document.querySelectorAll('tbody tr[data-id]').forEach(tr => {
        const id = parseInt(tr.dataset.id);
        if (id > lastId) lastId = id;
    });

    let unreadCount = 0;

    // ── Build row HTML ──────────────────────────────────
    function buildRow(r) {
        const isPagi     = r.jenis_scan === 'masuk_pagi' || r.jenis_scan === 'keluar_pagi';
        const sesiClass  = isPagi ? 's-pagi' : 's-siang';

        let pillHtml;
        if (r.status_kehadiran === 'tepat_waktu')      pillHtml = '<span class="pill p-green">Tepat Waktu</span>';
        else if (r.status_kehadiran === 'toleransi')   pillHtml = '<span class="pill p-amber">Toleransi</span>';
        else if (r.status_kehadiran === 'terlambat')   pillHtml = '<span class="pill p-red">Terlambat</span>';
        else if (r.status_kehadiran === 'alpha')       pillHtml = '<span class="pill p-red">Alpha</span>';
        else                                           pillHtml = '<span class="pill" style="background:var(--wash2);color:var(--ink3)">Keluar</span>';

        const telatHtml = r.keterlambatan_menit > 0
            ? `<span style="color:var(--red)">+${r.keterlambatan_menit} mnt</span>`
            : '—';

        return `<tr data-id="${r.id}" class="new-row">
            <td>
                <div style="display:flex;align-items:center;gap:9px">
                    <span class="ava">${r.inisial}</span>
                    <div>
                        <div class="td-main">${r.nama}</div>
                        <div class="td-id">${r.username}</div>
                    </div>
                </div>
            </td>
            <td><span class="wilayah-badge">${r.wilayah}</span></td>
            <td class="mono">${r.tanggal}</td>
            <td><span class="sesi-chip ${sesiClass}">${r.jenis_scan_label}</span></td>
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
            <div class="rt-toast-detail">${r.jenis_scan_label} &middot; ${r.jam} &middot; ${r.wilayah}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    // ── Update badge sidebar ────────────────────────────
    function updateSidebarBadge() {
        const sbAbsensi = document.getElementById('sb-absensi-link');
        if (!sbAbsensi) return;
        let badge = sbAbsensi.querySelector('.sb-live-count');
        if (unreadCount <= 0) { if (badge) badge.remove(); return; }
        if (!badge) {
            badge = document.createElement('span');
            badge.className = 'sb-live-count';
            sbAbsensi.appendChild(badge);
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
                const card = el.closest('.cl-stat');
                if (card) { card.classList.remove('updated'); void card.offsetWidth; card.classList.add('updated'); }
            }
        }
        setVal('val-hadir',     stats.total_hadir);
        setVal('val-toleransi', stats.total_toleransi);
        setVal('val-terlambat', stats.total_terlambat);

        const pct   = tp > 0 ? Math.round(stats.total_hadir / tp * 100) : 0;
        const pctEl = document.getElementById('pct-hadir');
        if (pctEl) pctEl.textContent = pct + '% kehadiran';
        const barH = document.getElementById('bar-hadir');
        if (barH) barH.style.width = pct + '%';
        const thi = stats.total_hari_ini || 0;
        const barT = document.getElementById('bar-toleransi');
        if (barT) barT.style.width = (thi > 0 ? Math.round(stats.total_toleransi/thi*100) : 0) + '%';
        const barR = document.getElementById('bar-terlambat');
        if (barR) barR.style.width = (thi > 0 ? Math.round(stats.total_terlambat/thi*100) : 0) + '%';
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
                const panel = document.querySelector('.panel');
                const empty = document.querySelector('.empty-state');

                if (empty) empty.style.display = 'none';
                if (panel) {
                    let table = panel.querySelector('table');
                    if (!table) {
                        panel.querySelector('.ph').insertAdjacentHTML('afterend',
                            '<div class="table-responsive"><table><thead><tr><th>Petugas</th><th>Wilayah</th><th>Tanggal</th><th>Jenis Scan</th><th>Jam</th><th>Status Kehadiran</th><th>Keterlambatan</th></tr></thead><tbody></tbody></table></div>');
                    }
                }

                const tb = document.querySelector('tbody');
                data.rows.forEach(r => {
                    if (tb.querySelector(`tr[data-id="${r.id}"]`)) return;
                    tb.insertAdjacentHTML('afterbegin', buildRow(r));
                    showToast(r);
                    unreadCount++;
                });

                updateSidebarBadge();

                setTimeout(() => {
                    document.querySelectorAll('tbody tr.new-row').forEach(tr => tr.classList.remove('new-row'));
                }, 1500);
            }
        } catch (e) { /* gagal diam */ }
    }

    // Reset badge saat klik menu absensi
    const sbLink = document.getElementById('sb-absensi-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateSidebarBadge(); });

    // Mulai polling hanya untuk hari ini
    if (IS_TODAY) {
        setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
    }
})();
</script>
@endpush

@endsection

@push('styles')
<style>
/* ── Page-specific styles (RESPONSIF UNTUK HP & KOMPUTER) ── */
.cl-topbar {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
    flex-wrap: wrap; gap: 12px;
}
.cl-topbar-left h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; }
.cl-topbar-left p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

/* Role badge */
.role-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 10.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase;
    background: #1a56db18; color: var(--blue);
    border: 1px solid #1a56db28; padding: 3px 10px; border-radius: 20px;
}
.role-badge svg { flex-shrink: 0; }

/* Stats row - GRID RESPONSIF */
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

/* Filter toolbar - WRAP & FLEXIBLE */
.cl-filter {
    display: flex; align-items: center; gap: 10px;
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;
    flex-wrap: wrap;
}
.cl-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); white-space: nowrap; }
.cl-filter input[type=date],
.cl-filter select {
    height: 30px; padding: 0 10px; font-size: 12px;
    border: 1px solid var(--rule); border-radius: 5px;
    background: var(--wash); color: var(--ink);
    font-family: 'IBM Plex Sans', sans-serif;
    cursor: pointer; transition: border-color .15s;
}
.cl-filter input[type=date]:focus,
.cl-filter select:focus { outline: none; border-color: var(--blue); }
.cl-filter-sep { width: 1px; height: 20px; background: var(--rule); }
.cl-filter-btn {
    height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
    background: var(--blue); color: #fff; border: none;
    border-radius: 5px; cursor: pointer; transition: opacity .15s;
    font-family: 'IBM Plex Sans', sans-serif;
}
.cl-filter-btn:hover { opacity: .88; }

/* Info tag hari ini */
.date-tag {
    margin-left: auto; font-size: 11px; color: var(--ink3);
    font-family: 'IBM Plex Mono', monospace;
}

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

/* Table panel - WITH RESPONSIVE OVERFLOW */
.panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
.ph    { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); flex-wrap: wrap; gap: 8px; }
.ph-title { font-size: 12.5px; font-weight: 600; }
.ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

/* TABLE RESPONSIF - SCROLL HORIZONTAL DI HP */
.table-responsive {
    overflow-x: auto;
    -webkit-overflow-scrolling: touch;
}
table { 
    width: 100%; 
    border-collapse: collapse; 
    min-width: 700px; /* Minimal lebar agar tabel tidak terlalu sempit di HP */
}
thead th {
    text-align: left; padding: 8px 16px;
    font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
    color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
}
tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--wash); }
tbody td { padding: 10px 16px; vertical-align: middle; }

/* Avatar */
.ava {
    display: inline-flex; align-items: center; justify-content: center;
    width: 28px; height: 28px; border-radius: 5px;
    background: var(--wash2); font-size: 10px; font-weight: 600;
    color: var(--ink2); text-transform: uppercase; flex-shrink: 0;
}

/* Typography for table */
.td-main { font-size: 12.5px; font-weight: 500; color: var(--ink); }
.td-id { font-size: 10.5px; color: var(--ink3); margin-top: 1px; }
.mono { font-family: 'IBM Plex Mono', monospace; font-size: 11px; color: var(--ink2); }

/* Wilayah badge */
.wilayah-badge {
    display: inline-block;
    font-size: 11px; font-weight: 600;
    padding: 2px 8px; border-radius: 4px;
    background: var(--wash2); color: var(--ink2);
}

/* Pill status */
.pill { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
.p-green { background: var(--green-lt); color: var(--green); }
.p-amber { background: var(--amber-lt); color: var(--amber); }
.p-red   { background: var(--red-lt);   color: var(--red); }

/* Sesi chip */
.sesi-chip {
    font-size: 10px; font-weight: 600; padding: 1px 7px; border-radius: 3px;
    text-transform: uppercase; letter-spacing: .5px;
}
.s-pagi  { background: #fff8e1; color: #b45309; }
.s-siang { background: #e8f4fd; color: #1a56db; }

/* Empty state */
.empty-state { padding: 48px 20px; text-align: center; color: var(--ink3); }
.empty-state svg { margin: 0 auto 12px; display: block; opacity: .3; }
.empty-state p { font-size: 13px; }

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

/* Pagination */
.pagination {
    display: flex; gap: 5px; align-items: center; flex-wrap: wrap;
}
.pagination .page-link {
    padding: 4px 8px; font-size: 11px; border-radius: 4px;
    background: var(--wash); color: var(--ink2); text-decoration: none;
}
.pagination .active .page-link { background: var(--blue); color: white; }

/* RESPONSIVE BREAKPOINTS */
@media (max-width: 900px) {
    .cl-stats { grid-template-columns: repeat(2,1fr); }
}

@media (max-width: 700px) {
    .cl-stats { grid-template-columns: 1fr; gap: 1px; }
    .cl-stat { padding: 14px 16px; }
    .cl-stat-val { font-size: 24px; }
    .cl-topbar { flex-direction: column; align-items: flex-start; }
    .cl-topbar > div:last-child { width: 100%; justify-content: space-between; }
    .ph { flex-direction: column; align-items: flex-start; }
}

@media (max-width: 550px) {
    .cl-filter select,
    .cl-filter input[type=date],
    .cl-filter-btn {
        flex: 1 0 auto;
        min-width: 120px;
    }
    .cl-filter-sep { display: none; }
    .btn-export { font-size: 10px; padding: 0 10px; }
    .role-badge { font-size: 9px; }
    .cl-topbar-left h1 { font-size: 17px; }
}

/* ── TOAST NOTIFIKASI ── */
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
    animation: toast-in .25s ease;
    pointer-events: auto;
}
.rt-toast-name   { font-weight: 600; color: var(--ink); margin-bottom: 2px; }
.rt-toast-detail { color: var(--ink3); font-size: 11px; }
@keyframes toast-in  { from { opacity:0; transform: translateX(16px); } to { opacity:1; transform: none; } }
@keyframes toast-out { from { opacity:1; } to { opacity:0; transform: translateX(16px); } }

/* badge live di sidebar */
.sb-live-count {
    margin-left: auto; font-size: 10px; font-weight: 600;
    background: var(--green); color: #fff;
    padding: 1px 6px; border-radius: 10px; min-width: 16px; text-align: center;
}

/* new row animation */
@keyframes row-in {
    from { background: #d1fae5; opacity: 0; }
    to   { background: transparent; opacity: 1; }
}
tbody tr.new-row { animation: row-in 1.2s ease forwards; }

/* stat flash */
@keyframes stat-flash { 0%{background:var(--green-lt)} 100%{background:var(--surface)} }
.cl-stat.updated { animation: stat-flash .8s ease forwards; }

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
        <h1>Monitoring Absensi</h1>
        <p>
            
            &nbsp;Data kehadiran seluruh petugas
        </p>
    </div>
    <div style="display:flex; align-items:center; gap:8px; flex-wrap:wrap;">
       
        <span class="role-badge">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
            Admin — Akses Penuh
        </span>
    </div>
</div>

{{-- Stats cards --}}
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Petugas Terjadwal</div>
        <div class="cl-stat-val">{{ $totalPetugas }}</div>
        <div class="cl-stat-sub">pada tanggal terpilih</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Hadir Hari Ini</div>
        <div class="cl-stat-val" style="color:var(--green)" id="val-hadir">{{ $totalHadir }}</div>
        <div class="cl-stat-sub">
            <span class="role-badge" style="background:var(--green-lt); color:var(--green); padding:2px 8px;">
                <span id="pct-hadir">{{ $totalPetugas > 0 ? round($totalHadir/$totalPetugas*100) : 0 }}% kehadiran</span>
            </span>
        </div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" id="bar-hadir" style="width:{{ $totalPetugas > 0 ? round($totalHadir/$totalPetugas*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Toleransi</div>
        <div class="cl-stat-val" style="color:var(--amber)" id="val-toleransi">{{ $totalTolerasi }}</div>
        <div class="cl-stat-sub"><span class="role-badge" style="background:var(--amber-lt); color:var(--amber); padding:2px 8px;">≤10 menit</span></div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" id="bar-toleransi" style="width:{{ $totalHariIni > 0 ? round($totalTolerasi/$totalHariIni*100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Terlambat</div>
        <div class="cl-stat-val" style="color:var(--red)" id="val-terlambat">{{ $totalTerlambat }}</div>
        <div class="cl-stat-sub"><span class="role-badge" style="background:var(--red-lt); color:var(--red); padding:2px 8px;">>10 menit</span></div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" id="bar-terlambat" style="width:{{ $totalHariIni > 0 ? round($totalTerlambat/$totalHariIni*100) : 0 }}%;background:var(--red)"></div></div>
    </div>
</div>

{{-- Filter bar --}}
<form method="GET" action="{{ route('admin.absensi.index') }}" class="cl-filter">
    <label>Tanggal</label>
    <input type="date" name="tanggal" value="{{ $filterTanggal }}" max="{{ now()->toDateString() }}">

    <div class="cl-filter-sep"></div>
    <label>Wilayah</label>
    <select name="wilayah_id">
        <option value="">Semua Wilayah</option>
        @foreach($wilayahList as $w)
            <option value="{{ $w->id }}" {{ $filterWilayah == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
        @endforeach
    </select>

    <div class="cl-filter-sep"></div>
    <label>Sesi</label>
    <select name="sesi">
        <option value="">Semua Sesi</option>
        <option value="pagi"  {{ $filterSesi === 'pagi'  ? 'selected' : '' }}>Pagi</option>
        <option value="siang" {{ $filterSesi === 'siang' ? 'selected' : '' }}>Siang</option>
    </select>

    <div class="cl-filter-sep"></div>
    <label>Status</label>
    <select name="status">
        <option value="">Semua Status</option>
        <option value="tepat_waktu" {{ $filterStatus === 'tepat_waktu' ? 'selected' : '' }}>Tepat Waktu</option>
        <option value="toleransi"   {{ $filterStatus === 'toleransi'   ? 'selected' : '' }}>Toleransi</option>
        <option value="terlambat"   {{ $filterStatus === 'terlambat'   ? 'selected' : '' }}>Terlambat</option>
        <option value="alpha"       {{ $filterStatus === 'alpha'       ? 'selected' : '' }}>Alpha</option>
    </select>

    <button type="submit" class="cl-filter-btn">Tampilkan</button>
    <a href="{{ route('admin.absensi.index') }}" class="cl-filter-btn" style="background:var(--wash);color:var(--ink2);border:1px solid var(--rule);">Reset</a>
</form>

{{-- Table --}}
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Data Absensi Petugas</div>
            <div class="ph-sub">{{ $absensi->total() }} record · {{ $filterTanggal }}</div>
        </div>
    </div>

    @if($absensi->isEmpty())
        <div class="empty-state">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
                <line x1="9" y1="12" x2="15" y2="12"/>
                <line x1="9" y1="16" x2="13" y2="16"/>
            </svg>
            <p>Belum ada data absensi untuk tanggal ini.</p>
        </div>
    @else
        {{-- Responsive wrapper untuk scroll horizontal di HP --}}
        <div class="table-responsive">
            <table>
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Wilayah</th>
                        <th>Tanggal</th>
                        <th>Jenis Scan</th>
                        <th>Jam</th>
                        <th>Status Kehadiran</th>
                        <th>Keterlambatan</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($absensi as $a)
                    <tr data-id="{{ $a->id }}">
                        <td>
                            <div style="display:flex;align-items:center;gap:9px">
                                <span class="ava">{{ strtoupper(substr($a->user->name, 0, 2)) }}</span>
                                <div>
                                    <div class="td-main">{{ $a->user->name }}</div>
                                    <div class="td-id">{{ $a->user->username }}</div>
                                </div>
                            </div>
                        </td>
                        <td><span class="wilayah-badge">{{ $a->user->wilayah->nama ?? '-' }}</span></td>
                        <td class="mono">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
                        <td>
                            <span class="sesi-chip {{ $a->jenis_scan == 'masuk_pagi' || $a->jenis_scan == 'keluar_pagi' ? 's-pagi' : 's-siang' }}">
                                {{ $a->label_jenis_scan }}
                            </span>
                        </td>
                        <td class="mono">{{ $a->jam_masuk ?? $a->jam_keluar ?? '—' }}</td>
                        <td>
                            @php
                                $statusClass = match($a->status_kehadiran) {
                                    'tepat_waktu' => 'p-green',
                                    'toleransi'   => 'p-amber',
                                    'terlambat', 'alpha' => 'p-red',
                                    default => '',
                                };
                                $statusLabel = match($a->status_kehadiran) {
                                    'tepat_waktu' => 'Tepat Waktu',
                                    'toleransi'   => 'Toleransi',
                                    'terlambat'   => 'Terlambat',
                                    'alpha'       => 'Alpha',
                                    default => 'Keluar',
                                };
                            @endphp
                            @if($a->status_kehadiran)
                                <span class="pill {{ $statusClass }}">{{ $statusLabel }}</span>
                            @else
                                <span class="pill" style="background:var(--wash2);color:var(--ink3)">Keluar</span>
                            @endif
                        </td>
                        <td class="mono">
                            @if($a->keterlambatan_menit > 0)
                                <span style="color:var(--red)">+{{ $a->keterlambatan_menit }} mnt</span>
                            @else —
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    @if($absensi->hasPages())
    <div style="padding:12px 18px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--rule);flex-wrap:wrap;gap:12px;">
        <div style="font-size:11px;color:var(--ink3)">
            Menampilkan {{ $absensi->firstItem() }}–{{ $absensi->lastItem() }} dari {{ $absensi->total() }} data
        </div>
        {{ $absensi->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

@endsection