@extends('layouts.koordinator')

@section('title', 'Checklist Harian')

@section('breadcrumb')
    <span>Koordinator</span>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Checklist Harian</strong>
@endsection

{{-- Toast container --}}
<div id="cl-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("koordinator.checklist.polling") }}';
    const FILTER_DATE = '{{ $tanggal->toDateString() }}';
    const TODAY       = '{{ now()->toDateString() }}';
    const IS_TODAY    = (FILTER_DATE === TODAY);
    const INTERVAL_MS = 7000;

    let knownIds = new Set();
    document.querySelectorAll('tbody tr[data-id]').forEach(tr => knownIds.add(parseInt(tr.dataset.id)));

    let unreadCount = 0;

    // ── Toast ────────────────────────────────────────────
    function showToast(r) {
        const wrap  = document.getElementById('cl-toast-wrap');
        const toast = document.createElement('div');
        toast.className = 'cl-toast';
        toast.innerHTML = `
            <div class="cl-toast-name">${r.nama} — Submit Checklist</div>
            <div class="cl-toast-detail">${r.sesi} &middot; ${r.jam}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'cl-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    // ── Badge sidebar ────────────────────────────────────
    function updateBadge() {
        const sbLink = document.getElementById('sb-checklist-link');
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

    // ── Update stats cards ────────────────────────────────
    function updateStats(stats) {
        function setVal(id, val) {
            const el = document.getElementById(id);
            if (el && el.textContent !== String(val)) {
                el.textContent = val;
                const card = el.closest('.cl-stat');
                if (card) { card.classList.remove('cl-updated'); void card.offsetWidth; card.classList.add('cl-updated'); }
            }
        }
        setVal('val-submit',   stats.total_submit);
        setVal('val-verified', stats.total_verified);
    }

    // ── Update status baris yang sudah ada ──────────────
    function updateRow(r) {
        const tr = document.querySelector(`tr[data-id="${r.id}"]`);
        if (!tr) return;
        if (tr.dataset.status === r.status) return;
        tr.dataset.status = r.status;

        const tdStatus = tr.querySelectorAll('td')[4];
        if (tdStatus) {
            if (r.status === 'verified')    tdStatus.innerHTML = '<span class="pill p-verified">✓ Verified</span>';
            else if (r.status === 'submit') tdStatus.innerHTML = '<span class="pill p-submit">Menunggu</span>';
            else                            tdStatus.innerHTML = '<span class="pill p-draft">Draft</span>';
        }
        // Flash baris
        tr.classList.remove('cl-row-updated'); void tr.offsetWidth; tr.classList.add('cl-row-updated');
        setTimeout(() => tr.classList.remove('cl-row-updated'), 1000);
    }

    // ── Polling ──────────────────────────────────────────
    async function poll() {
        try {
            const resp = await fetch(`${POLL_URL}?tanggal=${FILTER_DATE}`, {
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (!resp.ok) return;
            const data = await resp.json();

            if (data.stats) updateStats(data.stats);

            if (data.rows) {
                data.rows.forEach(r => {
                    if (knownIds.has(r.id)) {
                        updateRow(r);
                    } else {
                        knownIds.add(r.id);
                        showToast(r);
                        unreadCount++;
                        updateBadge();
                    }
                });
            }
        } catch (e) { /* gagal diam */ }
    }

    // Reset badge saat klik menu checklist
    const sbLink = document.getElementById('sb-checklist-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateBadge(); });

    if (IS_TODAY) {
        setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
    }
})();
</script>
@endpush

@push('styles')
<style>
.cl-topbar { display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule);flex-wrap:wrap;gap:12px; }
.cl-topbar-left h1 { font-size:19px;font-weight:600;letter-spacing:-.3px; }
.cl-topbar-left p  { font-size:12px;color:var(--ink3);margin-top:3px; }
.role-badge { display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;background:#0a7c4e18;color:var(--green);border:1px solid #0a7c4e28;padding:3px 10px;border-radius:20px; }

.cl-stats { display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
.cl-stat { background:var(--surface);padding:20px 22px;position:relative; }
.cl-stat-label { font-size:10.5px;font-weight:500;letter-spacing:.5px;text-transform:uppercase;color:var(--ink3);margin-bottom:10px; }
.cl-stat-val { font-size:30px;font-weight:300;letter-spacing:-1.2px;font-family:'IBM Plex Mono',monospace;color:var(--ink);line-height:1;margin-bottom:8px; }
.cl-stat-sub { font-size:11px;color:var(--ink3); }
.cl-stat-bar { position:absolute;bottom:0;left:0;right:0;height:2px; }
.cl-stat-fill { height:100%; }

.cl-filter { display:flex;align-items:center;gap:10px;background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:12px 16px;margin-bottom:16px;flex-wrap:wrap; }
.cl-filter label { font-size:11px;font-weight:500;color:var(--ink3); }
.cl-filter input[type=date], .cl-filter select { height:30px;padding:0 10px;font-size:12px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);color:var(--ink);font-family:'IBM Plex Sans',sans-serif;cursor:pointer; }
.cl-filter-sep { width:1px;height:20px;background:var(--rule); }
.cl-filter-btn { height:30px;padding:0 14px;font-size:12px;font-weight:500;background:var(--blue);color:#fff;border:none;border-radius:5px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif; }
.date-tag { margin-left:auto;font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace; }

/* Catatan penting banner */
.notice-banner {
    background:linear-gradient(135deg,#fff8e1 0%,#fef3cd 100%);
    border:1px solid #f59e0b44;
    border-radius:8px; padding:14px 18px; margin-bottom:18px;
    display:flex; gap:12px; align-items:flex-start;
}
.notice-icon { color:#b45309; flex-shrink:0; margin-top:1px; }
.notice-title { font-size:12px;font-weight:600;color:#92400e;margin-bottom:4px; }
.notice-list { font-size:11.5px;color:#78350f;line-height:1.7; }

table { width:100%;border-collapse:collapse; }
thead th { text-align:left;padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:var(--ink3);background:var(--wash);border-bottom:1px solid var(--rule); }
tbody tr { border-bottom:1px solid var(--rule);transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--wash); }
tbody td { padding:10px 16px;vertical-align:middle; }
.panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden; }
.ph    { display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px;font-weight:600; }
.ph-sub   { font-size:11px;color:var(--ink3);margin-top:1px; }

.ava { display:inline-flex;align-items:center;justify-content:center;width:28px;height:28px;border-radius:5px;background:var(--wash2);font-size:10px;font-weight:600;color:var(--ink2);text-transform:uppercase;flex-shrink:0; }
.prog-wrap { display:flex;align-items:center;gap:8px; }
.prog-bar  { flex:1;height:5px;background:var(--wash2);border-radius:3px;min-width:60px; }
.prog-fill { height:100%;border-radius:3px; }
.prog-pct  { font-size:11px;font-family:'IBM Plex Mono',monospace;color:var(--ink3);width:30px;text-align:right; }
.pill { display:inline-block;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px;letter-spacing:.3px; }
.p-draft    { background:var(--wash2);color:var(--ink3); }
.p-submit   { background:var(--amber-lt);color:var(--amber); }
.p-verified { background:var(--green-lt);color:var(--green); }
.sesi-chip { font-size:10px;font-weight:600;padding:1px 7px;border-radius:3px;text-transform:uppercase;letter-spacing:.5px; }
.s-pagi  { background:#fff8e1;color:#b45309; }
.s-siang { background:#e8f4fd;color:#1a56db; }
.act-btn { display:inline-flex;align-items:center;gap:5px;font-size:11px;font-weight:500;padding:4px 10px;border-radius:4px;border:1px solid var(--rule);background:var(--surface);color:var(--ink2);text-decoration:none;cursor:pointer;transition:all .12s; }
.act-btn:hover { border-color:var(--ink2);color:var(--ink); }
.act-btn.verify { background:var(--green-lt);border-color:#0a7c4e33;color:var(--green); }
.act-btn.verify:hover { background:#c9eedd; }
.flash { display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:7px;margin-bottom:16px;font-size:12.5px;font-weight:500; }
.flash-ok  { background:var(--green-lt);color:var(--green);border:1px solid #0a7c4e22; }
.flash-err { background:var(--red-lt);color:var(--red);border:1px solid #c0392b22; }
.empty-state { padding:48px 20px;text-align:center;color:var(--ink3); }

/* ── REALTIME: Toast ─────────────────────────────────── */
#cl-toast-wrap {
    position: fixed; bottom: 24px; right: 24px;
    z-index: 9999; display: flex; flex-direction: column; gap: 8px;
    pointer-events: none;
}
.cl-toast {
    background: var(--surface); border: 1px solid var(--rule);
    border-left: 3px solid var(--green);
    border-radius: 8px; padding: 10px 14px; min-width: 220px;
    font-size: 12px; box-shadow: 0 4px 16px rgba(0,0,0,.12);
    animation: cl-toast-in .25s ease;
    pointer-events: auto;
}
.cl-toast-name   { font-weight: 600; color: var(--ink); margin-bottom: 2px; }
.cl-toast-detail { color: var(--ink3); font-size: 11px; }
@keyframes cl-toast-in  { from { opacity:0; transform:translateX(16px); } to { opacity:1; transform:none; } }
@keyframes cl-toast-out { from { opacity:1; } to { opacity:0; transform:translateX(16px); } }

/* ── REALTIME: Stat flash ─────────────────────────────── */
@keyframes cl-stat-flash { 0%{background:#d1fae5} 100%{background:var(--surface)} }
.cl-stat.cl-updated { animation: cl-stat-flash .8s ease forwards; }

/* ── REALTIME: Row status update flash ───────────────── */
@keyframes cl-row-flash { 0%{background:#d1fae5} 100%{background:transparent} }
tbody tr.cl-row-updated { animation: cl-row-flash .8s ease forwards; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flash" style="background:var(--red-lt);color:var(--red);border:1px solid #c0392b22;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Page header --}}
<div class="cl-topbar">
    <div class="cl-topbar-left">
        <h1>Checklist Harian</h1>
        <p>Monitoring & verifikasi checklist SOP petugas</p>
    </div>
    <div style="display:flex;align-items:center;gap:8px">
        <a href="{{ route('koordinator.checklist.template') }}" style="height:32px;padding:0 14px;font-size:12px;font-weight:500;background:var(--surface);color:var(--ink2);border:1px solid var(--rule);border-radius:5px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
            Kelola Template
        </a>
        <span class="role-badge">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            Koordinator — Monitor & Verifikasi
        </span>
    </div>
</div>


{{-- Stats --}}
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Petugas Terjadwal</div>
        <div class="cl-stat-val">{{ $totalPetugas }}</div>
        <div class="cl-stat-sub">pada tanggal terpilih</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Sudah Submit</div>
        <div class="cl-stat-val" id="val-submit">{{ $totalSubmit }}</div>
        <div class="cl-stat-sub">menunggu verifikasi</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $totalPetugas > 0 ? round($totalSubmit/$totalPetugas*100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Terverifikasi</div>
        <div class="cl-stat-val" id="val-verified">{{ $totalVerified }}</div>
        <div class="cl-stat-sub">sudah disetujui</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $totalPetugas > 0 ? round($totalVerified/$totalPetugas*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('koordinator.checklist.index') }}" class="cl-filter">
    <label>Tanggal</label>
    <input type="date" name="tanggal" value="{{ $tanggal->toDateString() }}">
    <div class="cl-filter-sep"></div>
    <label>Sesi</label>
    <select name="sesi">
        <option value="semua" {{ $sesi === 'semua' ? 'selected' : '' }}>Semua Sesi</option>
        <option value="pagi"  {{ $sesi === 'pagi'  ? 'selected' : '' }}>Pagi</option>
        <option value="siang" {{ $sesi === 'siang' ? 'selected' : '' }}>Siang</option>
    </select>
    <button type="submit" class="cl-filter-btn">Tampilkan</button>
    
</form>

{{-- Table --}}
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Daftar Checklist Petugas</div>
            <div class="ph-sub">{{ $checklists->count() }} entri ditemukan</div>
        </div>
    </div>

    @if($checklists->isEmpty())
    <div class="empty-state">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24" style="opacity:.25;margin:0 auto 10px;display:block">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
        <p style="font-size:13px">Belum ada checklist untuk tanggal ini.</p>
    </div>
    @else
    <table>
        <thead>
            <tr>
                <th>#</th>
                <th>Petugas</th>
                <th>Sesi</th>
                <th>Progres</th>
                <th>Status</th>
                <th>Waktu</th>
                <th style="width:120px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($checklists as $i => $cl)
            <tr data-id="{{ $cl->id }}" data-status="{{ $cl->status }}">
                <td style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">{{ $i+1 }}</td>
                <td>
                    <div style="display:flex;align-items:center;gap:9px">
                        <span class="ava">{{ strtoupper(substr($cl->user->name ?? '?', 0, 2)) }}</span>
                        <span style="font-size:12.5px;font-weight:500">{{ $cl->user->name ?? '—' }}</span>
                    </div>
                </td>
                <td>
                    <span class="sesi-chip {{ $cl->sesi === 'pagi' ? 's-pagi' : 's-siang' }}">{{ ucfirst($cl->sesi) }}</span>
                </td>
                <td style="min-width:150px">
                    @php $pct = $cl->pctChecked(); @endphp
                    <div class="prog-wrap">
                        <div class="prog-bar">
                            <div class="prog-fill" style="width:{{ $pct }}%;background:{{ $pct >= 80 ? 'var(--green)' : ($pct >= 50 ? 'var(--amber)' : 'var(--red)') }}"></div>
                        </div>
                        <span class="prog-pct">{{ $pct }}%</span>
                    </div>
                    <div style="font-size:10px;color:var(--ink3);margin-top:3px">{{ $cl->totalChecked() }}/{{ $cl->totalItems() }} item</div>
                </td>
                <td>
                    @if($cl->status === 'verified')
                        <span class="pill p-verified">✓ Verified</span>
                    @elseif($cl->status === 'submit')
                        <span class="pill p-submit">Menunggu</span>
                    @else
                        <span class="pill p-draft">Draft</span>
                    @endif
                </td>
                <td style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">
                    {{ $cl->updated_at->format('H:i') }}
                </td>
                <td>
                    <div style="display:flex;gap:5px">
                        <a href="{{ route('koordinator.checklist.detail', $cl->id) }}" class="act-btn">
                            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                            Detail
                        </a>
                        @if($cl->status === 'submit')
                        <form method="POST" action="{{ route('koordinator.checklist.verify', $cl->id) }}">
                            @csrf @method('PATCH')
                            <button type="submit" class="act-btn verify" onclick="return confirm('Verifikasi checklist ini?')">
                                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                                Verif
                            </button>
                        </form>
                        @endif
                    </div>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection