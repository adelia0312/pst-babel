@extends('layouts.koordinator')
@section('title', 'Survey Kepuasan')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Survey Kepuasan</strong>
@endsection

<div id="sv-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("koordinator.survey.polling") }}';
    const INTERVAL_MS = 7000;
    let lastId = 0;
    let unreadCount = 0;

    function showToast(s) {
        const wrap = document.getElementById('sv-toast-wrap');
        if (!wrap) return;
        const toast = document.createElement('div');
        toast.className = 'sv-rt-toast';
        toast.innerHTML = `<div class="sv-rt-toast-name">${s.nama_responden}</div>
            <div class="sv-rt-toast-detail">Survey baru &middot; ${s.petugas} &middot; ${s.diisi_pada}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'sv-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    function updateBadge() {
        const sbLink = document.getElementById('sb-survey-link');
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

    function updateStats(st) {
        function flash(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            const str = val !== null && val !== undefined ? String(val) : '—';
            if (el.textContent !== str) {
                el.textContent = str;
                const card = el.closest('.sv-stat');
                if (card) { card.classList.remove('sv-updated'); void card.offsetWidth; card.classList.add('sv-updated'); }
            }
        }
        flash('val-sv-total', st.total_semua);
        flash('val-sv-hari',  st.hari_ini);
        flash('val-sv-rata',  st.rata_hari_ini ? parseFloat(st.rata_hari_ini).toFixed(2) : '—');
    }

    async function poll() {
        try {
            const res  = await fetch(`${POLL_URL}?after=${lastId}`);
            if (!res.ok) return;
            const data = await res.json();
            if (data.new_surveys && data.new_surveys.length > 0) {
                data.new_surveys.forEach(s => {
                    if (lastId < s.id) lastId = s.id;
                    showToast(s); unreadCount++;
                });
                updateBadge();
            }
            if (data.max_id > lastId) lastId = data.max_id;
            if (data.stats) updateStats(data.stats);
        } catch (e) { /* diam */ }
    }

    const sbLink = document.getElementById('sb-survey-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateBadge(); });
    setTimeout(() => { poll(); setInterval(poll, INTERVAL_MS); }, 3000);
})();
</script>
@endpush

@push('styles')
<style>
    .sv-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .sv-topbar h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; color: var(--ink); }
    .sv-topbar p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s, background .15s;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-sm { height: 26px; padding: 0 10px; font-size: 11px; }

    /* Filter */
    .sv-filter {
        display: flex; align-items: center; gap: 10px;
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .sv-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); }
    .sv-filter input[type=month] {
        height: 30px; padding: 0 10px; font-size: 12px;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .sv-filter input:focus { outline: none; border-color: var(--blue); }

    /* Stats */
    .sv-stats {
        display: grid; grid-template-columns: repeat(4,1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 20px;
    }
    .sv-stat { background: var(--surface); padding: 16px 18px; }
    .sv-stat-label { font-size: 10px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; color: var(--ink3); margin-bottom: 6px; }
    .sv-stat-val { font-size: 26px; font-weight: 300; letter-spacing: -1px; font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1; }
    .sv-stat-warn .sv-stat-val { color: var(--amber); }

    /* Panel & table */
    .sv-panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .sv-ph { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
    .sv-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
    .sv-ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    .sv-table { width: 100%; border-collapse: collapse; }
    .sv-table thead th {
        text-align: left; padding: 8px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: .9px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    .sv-table tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    .sv-table tbody tr:last-child { border-bottom: none; }
    .sv-table tbody tr:hover { background: var(--wash); }
    .sv-table tbody td { padding: 10px 16px; vertical-align: middle; font-size: 12.5px; }

    .sv-pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 3px; }
    .sv-pill-green { background: var(--green-lt); color: var(--green); }
    .sv-pill-amber { background: var(--amber-lt); color: var(--amber); }
    .sv-pill-red   { background: var(--red-lt);   color: var(--red); }

    .sv-empty { padding: 48px 20px; text-align: center; color: var(--ink3); font-size: 13px; line-height: 1.8; }

    /* Realtime */
    #sv-toast-wrap { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
    .sv-rt-toast { background:var(--surface); border:1px solid var(--rule); border-left:3px solid var(--green); border-radius:8px; padding:10px 14px; min-width:220px; font-size:12px; box-shadow:0 4px 16px rgba(0,0,0,.12); animation:sv-toast-in .25s ease; pointer-events:auto; }
    .sv-rt-toast-name   { font-weight:600; color:var(--ink); margin-bottom:2px; }
    .sv-rt-toast-detail { color:var(--ink3); font-size:11px; }
    @keyframes sv-toast-in  { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:none} }
    @keyframes sv-toast-out { from{opacity:1} to{opacity:0;transform:translateX(16px)} }
    @keyframes sv-stat-flash { 0%{background:#d1fae5} 100%{background:var(--surface)} }
    .sv-stat.sv-updated { animation: sv-stat-flash .8s ease forwards; }
    @media(max-width:768px) { .sv-stats { grid-template-columns: repeat(2,1fr); } }

    /* Tabs */
    .sv-tabs { display:flex; gap:4px; border-bottom:1px solid var(--rule); margin-bottom:20px; }
    .sv-tab  { padding:10px 16px; font-size:12.5px; font-weight:500; color:var(--ink3); text-decoration:none; border-bottom:2px solid transparent; margin-bottom:-1px; transition:color .15s; }
    .sv-tab:hover { color:var(--ink); }
    .sv-tab.active { color:var(--ink); border-bottom-color:var(--blue); font-weight:600; }
</style>
@endpush

@section('content')

<div class="sv-topbar">
    <div>
        <h1>Survey Kepuasan Wilayah</h1>
        <p>Rekap penilaian layanan petugas di wilayah Anda.</p>
    </div>
</div>

<div class="sv-tabs">
    <a href="{{ route('koordinator.survey.index') }}" class="sv-tab active">Survey Kepuasan</a>
    <a href="{{ route('koordinator.survey-internal.hasil') }}" class="sv-tab">Survey Internal</a>
</div>

{{-- Filter periode --}}
<form method="GET" class="sv-filter">
    <label>Periode</label>
    <input type="month" name="periode" value="{{ $periode }}">
    <button type="submit" class="sv-btn sv-btn-primary">Tampilkan</button>
</form>

{{-- Stats --}}
<div class="sv-stats">
    <div class="sv-stat">
        <div class="sv-stat-label">Total Responden</div>
        <div class="sv-stat-val" id="val-sv-total">{{ $totalResponden }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Rata Kepuasan</div>
        <div class="sv-stat-val" id="val-sv-rata">{{ $rataWilayah ?? '—' }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Hari Ini</div>
        <div class="sv-stat-val" id="val-sv-hari">{{ $hariIni ?? 0 }}</div>
    </div>
    @if($jumlahTanpaJadwal > 0)
    <div class="sv-stat sv-stat-warn">
        <div class="sv-stat-label">Scan di Luar Jadwal</div>
        <div class="sv-stat-val">{{ $jumlahTanpaJadwal }}</div>
    </div>
    @endif
</div>

{{-- Tabel per petugas --}}
<div class="sv-panel">
    <div class="sv-ph">
        <div>
            <div class="sv-ph-title">Rekap per Petugas</div>
            <div class="sv-ph-sub">Periode {{ \App\Helpers\PeriodeHelper::label($periode) }}</div>
        </div>
    </div>
    @if($dataPetugas->isEmpty())
    <div class="sv-empty">Belum ada petugas aktif di wilayah ini.</div>
    @else
    <table class="sv-table">
        <thead>
            <tr>
                <th>Petugas</th>
                <th style="width:130px">Responden</th>
                <th style="width:150px">Rata Kepuasan</th>
                <th style="width:90px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($dataPetugas as $row)
            <tr>
                <td>
                    <div style="font-weight:600;font-size:12.5px;color:var(--ink)">{{ $row['user']->name }}</div>
                    <div style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">{{ $row['petugas']->nip ?? '-' }}</div>
                </td>
                <td style="font-family:'IBM Plex Mono',monospace;font-size:12px">{{ $row['jumlah_responden'] }}</td>
                <td>
                    @if($row['rata_kepuasan'])
                    <span class="sv-pill {{ $row['rata_kepuasan'] >= 4 ? 'sv-pill-green' : ($row['rata_kepuasan'] >= 3 ? 'sv-pill-amber' : 'sv-pill-red') }}">
                        ★ {{ $row['rata_kepuasan'] }} / 5
                    </span>
                    @else
                    <span style="color:var(--ink3)">—</span>
                    @endif
                </td>
                <td>
                    <a href="{{ route('koordinator.survey.detail', ['petugasId' => $row['petugas']->id, 'periode' => $periode]) }}"
                       class="sv-btn sv-btn-secondary sv-btn-sm">Detail</a>
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>

@endsection