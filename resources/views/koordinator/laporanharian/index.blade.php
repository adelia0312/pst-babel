@extends('layouts.koordinator')

@section('title', 'Laporan Harian PST')

@push('styles')
<style>
    .cl-topbar {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .cl-topbar-left h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; }
    .cl-topbar-left p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

    .role-badge {
        display: inline-flex; align-items: center; gap: 5px;
        font-size: 10.5px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase;
        background: #1a56db18; color: var(--blue);
        border: 1px solid #1a56db28; padding: 3px 10px; border-radius: 20px;
    }

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
    .cl-stat-fill { height: 100%; border-radius: 1px; }

    .cl-filter {
        display: flex; align-items: center; gap: 10px;
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 12px 16px; margin-bottom: 16px;
        flex-wrap: wrap;
    }
    .cl-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); white-space: nowrap; }
    .cl-filter input[type=month],
    .cl-filter select {
        height: 30px; padding: 0 10px; font-size: 12px;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif; cursor: pointer;
    }
    .cl-filter input:focus, .cl-filter select:focus { outline: none; border-color: var(--blue); }
    .cl-filter-sep { width: 1px; height: 20px; background: var(--rule); }
    .cl-filter-btn {
        height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
        background: var(--blue); color: #fff; border: none;
        border-radius: 5px; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .cl-filter-btn:hover { opacity: .88; }
    .cl-filter-btn-reset {
        height: 30px; padding: 0 12px; font-size: 12px; font-weight: 500;
        background: var(--wash); color: var(--ink2);
        border: 1px solid var(--rule); border-radius: 5px;
        cursor: pointer; font-family: 'IBM Plex Sans', sans-serif;
        text-decoration: none; display: inline-flex; align-items: center;
    }
    .cl-filter-btn-reset:hover { background: var(--wash2); color: var(--ink); }

    .btn-export {
        height: 30px; padding: 0 14px; font-size: 12px; font-weight: 500;
        background: var(--green-lt); color: var(--green);
        border: 1px solid #0a7c4e44; border-radius: 5px;
        text-decoration: none; display: inline-flex; align-items: center; gap: 6px;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .btn-export:hover { opacity: .85; }

    .panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .ph    { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
    .ph-title { font-size: 12.5px; font-weight: 600; }
    .ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    thead th {
        text-align: left; padding: 8px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--wash); }
    tbody td { padding: 10px 16px; vertical-align: middle; color: var(--ink2); }

    .mono { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; }

    .pill { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
    .pill-submitted { background: var(--amber-lt); color: var(--amber); }
    .pill-approved  { background: var(--green-lt);  color: var(--green); }
    .pill-rejected  { background: var(--red-lt);    color: var(--red); }
    .pill-draft     { background: var(--wash2);     color: var(--ink3); }

    .btn-detail {
        font-size: 11px; font-weight: 500; padding: 4px 10px;
        border-radius: 4px; background: var(--blue-lt); color: var(--blue);
        text-decoration: none; white-space: nowrap;
    }
    .btn-detail:hover { background: #d4e3f9; }

    .empty-state { padding: 48px 20px; text-align: center; color: var(--ink3); }
    .empty-state svg { margin: 0 auto 12px; display: block; opacity: .3; }
    .empty-state p { font-size: 13px; }

    .pagination-wrap { padding: 12px 18px; border-top: 1px solid var(--rule); font-size: 12px; }

    .flash {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 16px; border-radius: 7px; margin-bottom: 16px;
        font-size: 12.5px; font-weight: 500;
    }
    .flash-ok  { background: var(--green-lt); color: var(--green); border: 1px solid #0a7c4e22; }
    .flash-err { background: var(--red-lt);   color: var(--red);   border: 1px solid #c0392b22; }

    /* ── REALTIME ──────────────────────────────────── */
    #lh-toast-wrap { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
    .lh-toast { background:var(--surface); border:1px solid var(--rule); border-left:3px solid var(--green); border-radius:8px; padding:10px 14px; min-width:220px; font-size:12px; box-shadow:0 4px 16px rgba(0,0,0,.12); animation:lh-toast-in .25s ease; pointer-events:auto; }
    .lh-toast-name   { font-weight:600; color:var(--ink); margin-bottom:2px; }
    .lh-toast-detail { color:var(--ink3); font-size:11px; }
    @keyframes lh-toast-in  { from { opacity:0; transform:translateX(16px); } to { opacity:1; transform:none; } }
    @keyframes lh-toast-out { from { opacity:1; } to { opacity:0; transform:translateX(16px); } }
    @keyframes lh-stat-flash { 0%{background:#d1fae5} 100%{background:var(--surface)} }
    .cl-stat.lh-updated { animation: lh-stat-flash .8s ease forwards; }
    @keyframes lh-row-flash { 0%{background:#d1fae5} 100%{background:transparent} }
    tr.lh-row-flash { animation: lh-row-flash .8s ease forwards; }
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Laporan Harian</strong>
@endsection

{{-- Toast container --}}
<div id="lh-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("koordinator.laporan.harian.polling") }}';
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
            <div class="lh-toast-detail">${r.sesi} &middot; ${r.tanggal}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'lh-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    function updateBadge() {
        const sbLink = document.getElementById('sb-laporan-link');
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
                if (card) { card.classList.remove('lh-updated'); void card.offsetWidth; card.classList.add('lh-updated'); }
            }
        }
        setVal('val-lh-total',     stats.total);
        setVal('val-lh-submitted', stats.submitted);
        setVal('val-lh-approved',  stats.approved);
        setVal('val-lh-rejected',  stats.rejected);
    }

    function updateRowStatus(r) {
        const tr = document.querySelector(`tr[data-id="${r.id}"]`);
        if (!tr || tr.dataset.status === r.status) return;
        tr.dataset.status = r.status;
        const tds = tr.querySelectorAll('td');
        const tdStatus = tds[3]; // kolom ke-4 (0-indexed)
        if (!tdStatus) return;
        const map = {
            submitted: '<span class="pill pill-submitted">Menunggu Review</span>',
            approved:  '<span class="pill pill-approved">Disetujui</span>',
            rejected:  '<span class="pill pill-rejected">Dikembalikan</span>',
            draft:     '<span class="pill pill-draft">Draft</span>',
        };
        if (map[r.status]) {
            tdStatus.innerHTML = map[r.status];
            tdStatus.style.textAlign = 'center';
            tr.classList.remove('lh-row-flash'); void tr.offsetWidth; tr.classList.add('lh-row-flash');
            setTimeout(() => tr.classList.remove('lh-row-flash'), 900);
        }
    }

    async function poll() {
        try {
            const resp = await fetch(POLL_URL, { headers: { 'X-Requested-With': 'XMLHttpRequest' } });
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

    const sbLink = document.getElementById('sb-laporan-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateBadge(); });

    setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
})();
</script>
@endpush

@section('content')

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

<!-- Page Header -->
<div class="cl-topbar">
    <div class="cl-topbar-left">
        <h1>Laporan Harian PST</h1>
        <p>
        
            &nbsp;Pantau dan review laporan harian petugas wilayah Anda
        </p>
    </div>
</div>

<!-- Stats -->
@php
    $total     = $stats['total'];
    $submitted = $stats['submitted'];
    $approved  = $stats['approved'];
    $rejected  = $stats['rejected'];
    $pctApproved  = $total > 0 ? round($approved  / $total * 100) : 0;
    $pctSubmitted = $total > 0 ? round($submitted / $total * 100) : 0;
    $pctRejected  = $total > 0 ? round($rejected  / $total * 100) : 0;
@endphp
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Total Laporan</div>
        <div class="cl-stat-val" id="val-lh-total">{{ $total }}</div>
        <div class="cl-stat-sub">Semua periode</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Menunggu Review</div>
        <div class="cl-stat-val" style="color:var(--amber)" id="val-lh-submitted">{{ $submitted }}</div>
        <div class="cl-stat-sub">{{ $pctSubmitted }}% dari total</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $pctSubmitted }}%;background:var(--amber)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Disetujui</div>
        <div class="cl-stat-val" style="color:var(--green)" id="val-lh-approved">{{ $approved }}</div>
        <div class="cl-stat-sub">{{ $pctApproved }}% dari total</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $pctApproved }}%;background:var(--green)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Dikembalikan</div>
        <div class="cl-stat-val" style="color:var(--red)" id="val-lh-rejected">{{ $rejected }}</div>
        <div class="cl-stat-sub">{{ $pctRejected }}% dari total</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $pctRejected }}%;background:var(--red)"></div></div>
    </div>
</div>

<!-- Filter + Export -->
<form method="GET" action="{{ route('koordinator.laporan.harian.index') }}">
    <div class="cl-filter">
        <label>Bulan</label>
        <input type="month" name="bulan" value="{{ request('bulan') }}">

        <div class="cl-filter-sep"></div>

        <label>Status</label>
        <select name="status">
            <option value="">Semua</option>
            <option value="draft"     {{ request('status') === 'draft'     ? 'selected' : '' }}>Draft</option>
            <option value="submitted" {{ request('status') === 'submitted' ? 'selected' : '' }}>Menunggu Review</option>
            <option value="approved"  {{ request('status') === 'approved'  ? 'selected' : '' }}>Disetujui</option>
            <option value="rejected"  {{ request('status') === 'rejected'  ? 'selected' : '' }}>Dikembalikan</option>
        </select>

        <div class="cl-filter-sep"></div>

        <button type="submit" class="cl-filter-btn">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-right:4px"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
            Filter
        </button>
        <a href="{{ route('koordinator.laporan.harian.index') }}" class="cl-filter-btn-reset">Reset</a>

        <div style="margin-left:auto">
            <a href="{{ route('koordinator.laporan.harian.export', request()->only('bulan','status')) }}"
               class="btn-export">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export
            </a>
        </div>
    </div>
</form>

{{-- ── TAB BAR ── --}}
<style>
.tab-bar { display:flex; gap:2px; margin-bottom:16px; border-bottom:1px solid var(--rule); padding-bottom:0; }
.tab-btn {
    font-size:13px; font-weight:500; padding:9px 18px;
    border:none; background:none; cursor:pointer; color:var(--ink3);
    border-bottom:2px solid transparent; margin-bottom:-1px;
    transition:color .15s,border-color .15s; border-radius:4px 4px 0 0;
    font-family:'IBM Plex Sans',sans-serif; display:inline-flex; align-items:center; gap:6px;
}
.tab-btn:hover { color:var(--ink); background:var(--wash); }
.tab-btn.active { color:var(--blue); border-bottom-color:var(--blue); font-weight:600; }
.tab-content { display:none; }
.tab-content.active { display:block; }
.tab-badge-pending { background:#fef3c7; color:#b45309; font-size:10px; font-weight:700; padding:1px 7px; border-radius:20px; animation: pulse-badge 2s ease-in-out infinite; }
@keyframes pulse-badge { 0%,100%{opacity:1} 50%{opacity:.6} }

/* Table detail jawaban */
.table-scroll-outer { overflow-x:auto; }
.table-scroll-outer::-webkit-scrollbar { height:6px; }
.table-scroll-outer::-webkit-scrollbar-thumb { background:var(--rule); border-radius:3px; }
table { width:100%; border-collapse:collapse; font-size:12.5px; }
thead th { text-align:left; padding:8px 14px; font-size:10px; font-weight:600; letter-spacing:1px; text-transform:uppercase; color:var(--ink3); background:var(--wash); border-bottom:1px solid var(--rule); white-space:nowrap; }
tbody tr { border-bottom:1px solid var(--rule); transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--wash); }
tbody td { padding:10px 14px; vertical-align:middle; color:var(--ink2); }
.jawaban-cell { max-width:200px; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; font-size:12px; color:var(--ink); }

/* Quick approve/reject buttons */
.btn-approve { font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px; background:var(--green-lt); color:var(--green); border:1px solid #0a7c4e44; text-decoration:none; white-space:nowrap; cursor:pointer; }
.btn-approve:hover { background:#c6f6e0; }
.btn-reject  { font-size:11px; font-weight:600; padding:4px 10px; border-radius:4px; background:var(--red-lt); color:var(--red); border:1px solid #c0392b44; text-decoration:none; white-space:nowrap; cursor:pointer; }
.btn-reject:hover { background:#fee2e2; }
</style>

<div class="tab-bar">
    {{-- Tab 1: Daftar semua laporan (tabel detail dengan jawaban) --}}
    <button class="tab-btn active" onclick="switchTab(this,'tab-semua')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
        Daftar Laporan
        <span style="font-size:10px;background:var(--wash2);color:var(--ink3);padding:1px 6px;border-radius:3px">{{ $laporan->total() }}</span>
    </button>
    {{-- Tab 2: Verifikasi — hanya laporan submitted --}}
    <button class="tab-btn" onclick="switchTab(this,'tab-verifikasi')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
        Review & Verifikasi
        @if($laporanPending->count() > 0)
            <span class="tab-badge-pending">{{ $laporanPending->count() }} menunggu</span>
        @endif
    </button>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- TAB 1 — DAFTAR LAPORAN (tabel lengkap dengan isi jawaban)     --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div id="tab-semua" class="tab-content active">
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Daftar Laporan Harian Wilayah</div>
                <div class="ph-sub">
                    {{ $laporan->total() }} laporan
                    @if(request('bulan')) · {{ \App\Helpers\PeriodeHelper::isoLabel(request('bulan')) }} @endif
                    @if(request('status')) · {{ ucfirst(request('status')) }} @endif
                </div>
            </div>
        </div>

        <div class="table-scroll-outer">
            <table>
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Sesi</th>
                        @foreach($templates as $tpl)
                            <th title="{{ $tpl->judul }}">{{ \Illuminate\Support\Str::limit($tpl->judul, 22) }}</th>
                        @endforeach
                        <th style="text-align:center">Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $item)
                    <tr data-id="{{ $item->id }}" data-status="{{ $item->status }}">
                        <td>
                            <span class="mava">{{ strtoupper(substr($item->user->name ?? '-', 0, 2)) }}</span>
                            <span style="font-weight:500;color:var(--ink)">{{ $item->user->name ?? '-' }}</span>
                        </td>
                        <td class="mono">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td style="color:var(--ink3)">{{ $item->hari ?? '-' }}</td>
                        <td style="color:var(--ink3)">{{ ucfirst($item->sesi ?? '-') }}</td>
                        @foreach($templates as $tpl)
                            @php
                                $berlakuPadaTgl = is_null($tpl->berlaku_mulai) || $tpl->berlaku_mulai->lte($item->tanggal);
                                $jawaban = $item->jawabUntuk($tpl->id);
                            @endphp
                            @if($berlakuPadaTgl)
                                <td class="jawaban-cell" title="{{ $jawaban ?? '-' }}">{{ $jawaban ?? '-' }}</td>
                            @else
                                <td class="jawaban-cell" style="color:var(--ink3);font-style:italic;text-align:center" title="Pertanyaan belum ada saat laporan ini dibuat">-</td>
                            @endif
                        @endforeach
                        <td style="text-align:center">
                            @php
                                $pillClass = match($item->status) {
                                    'submitted' => 'pill-submitted',
                                    'approved'  => 'pill-approved',
                                    'rejected'  => 'pill-rejected',
                                    default     => 'pill-draft',
                                };
                                $pillLabel = match($item->status) {
                                    'submitted' => 'Menunggu',
                                    'approved'  => 'Disetujui',
                                    'rejected'  => 'Dikembalikan',
                                    default     => 'Draft',
                                };
                            @endphp
                            <span class="pill {{ $pillClass }}">{{ $pillLabel }}</span>
                        </td>
                        <td style="text-align:right">
                            <a href="{{ route('koordinator.laporan.harian.detail', $item->id) }}" class="btn-detail">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 4 + $templates->count() + 2 }}">
                            <div class="empty-state">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="9" y1="13" x2="15" y2="13"/>
                                    <line x1="9" y1="17" x2="12" y2="17"/>
                                </svg>
                                <p>Belum ada laporan harian di wilayah ini.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporan->hasPages())
        <div class="pagination-wrap">{{ $laporan->links() }}</div>
        @endif
    </div>
</div>

{{-- ═══════════════════════════════════════════════════════════════ --}}
{{-- TAB 2 — REVIEW & VERIFIKASI (laporan submitted)              --}}
{{-- ═══════════════════════════════════════════════════════════════ --}}
<div id="tab-verifikasi" class="tab-content">
    @if($laporanPending->isEmpty())
    <div class="panel" style="padding:64px 20px;text-align:center">
        <svg width="48" height="48" fill="none" stroke="var(--green)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block;opacity:.6">
            <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
        </svg>
        <div style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:6px">Tidak ada laporan yang menunggu review</div>
        <p style="font-size:12.5px;color:var(--ink3)">Semua laporan di wilayah ini sudah diproses.</p>
    </div>
    @else
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Laporan Menunggu Verifikasi</div>
                <div class="ph-sub">{{ $laporanPending->count() }} laporan perlu ditinjau dan diverifikasi</div>
            </div>
        </div>
        <div class="table-scroll-outer">
            <table>
                <thead>
                    <tr>
                        <th>Petugas</th>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Sesi</th>
                        @foreach($templates as $tpl)
                            <th title="{{ $tpl->judul }}">{{ \Illuminate\Support\Str::limit($tpl->judul, 22) }}</th>
                        @endforeach
                        <th>Dikirim</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($laporanPending as $item)
                    <tr data-id="{{ $item->id }}" style="background:#fffbf0">
                        <td>
                            <span class="mava">{{ strtoupper(substr($item->user->name ?? '-', 0, 2)) }}</span>
                            <span style="font-weight:500;color:var(--ink)">{{ $item->user->name ?? '-' }}</span>
                        </td>
                        <td class="mono">{{ $item->tanggal ? \Carbon\Carbon::parse($item->tanggal)->format('d/m/Y') : '-' }}</td>
                        <td style="color:var(--ink3)">{{ $item->hari ?? '-' }}</td>
                        <td style="color:var(--ink3)">{{ ucfirst($item->sesi ?? '-') }}</td>
                        @foreach($templates as $tpl)
                            @php
                                $berlakuPadaTgl = is_null($tpl->berlaku_mulai) || $tpl->berlaku_mulai->lte($item->tanggal);
                                $jawaban = $item->jawabUntuk($tpl->id);
                            @endphp
                            @if($berlakuPadaTgl)
                                <td class="jawaban-cell" title="{{ $jawaban ?? '-' }}">{{ $jawaban ?? '-' }}</td>
                            @else
                                <td class="jawaban-cell" style="color:var(--ink3);font-style:italic;text-align:center">-</td>
                            @endif
                        @endforeach
                        <td class="mono" style="color:var(--ink3);font-size:11px">
                            {{ $item->created_at ? $item->created_at->format('d M, H:i') : '-' }}
                        </td>
                        <td style="text-align:right">
                            <div style="display:flex;gap:6px;justify-content:flex-end;align-items:center">
                                <a href="{{ route('koordinator.laporan.harian.detail', $item->id) }}" class="btn-detail">Review</a>
                                {{-- Quick approve langsung dari tabel --}}
                                <form method="POST" action="{{ route('koordinator.laporan.harian.approve', $item->id) }}" style="display:inline" onsubmit="return confirm('Setujui laporan dari {{ $item->user->name }}?')">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn-approve">✓ Setujui</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>

@push('scripts')
<script>
function switchTab(btn, id) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}

// Buka tab verifikasi otomatis jika ada di URL hash
if (window.location.hash === '#verifikasi') {
    var tabBtns = document.querySelectorAll('.tab-btn');
    if (tabBtns.length >= 2) switchTab(tabBtns[1], 'tab-verifikasi');
}
</script>
@endpush

@endsection