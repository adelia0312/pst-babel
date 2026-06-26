@extends('layouts.koordinator')

@section('title', 'Materi & Tugas')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <strong>Materi & Tugas</strong>
@endsection

{{-- Toast container --}}
<div id="mt-toast-wrap"></div>

@push('scripts')
<script>
(function () {
    const POLL_URL    = '{{ route("koordinator.materi.polling") }}';
    const INTERVAL_MS = 7000;

    let lastId = 0;
    document.querySelectorAll('.tugas-card[data-id]').forEach(el => {
        const id = parseInt(el.dataset.id);
        if (id > lastId) lastId = id;
    });

    let unreadCount = 0;

    function showToast(m) {
        const wrap = document.getElementById('mt-toast-wrap');
        if (!wrap) return;
        const toast = document.createElement('div');
        toast.className = 'mt-toast';
        toast.innerHTML = `<div class="mt-toast-name">${m.judul}</div>
            <div class="mt-toast-detail">Materi baru ditambahkan &middot; ${m.created_at}</div>`;
        wrap.appendChild(toast);
        setTimeout(() => {
            toast.style.animation = 'mt-toast-out .3s ease forwards';
            setTimeout(() => toast.remove(), 320);
        }, 4500);
    }

    function updateBadge() {
        const sbLink = document.getElementById('sb-materi-link');
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

    function updateStats(s) {
        function flash(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            if (el.textContent !== String(val)) {
                el.textContent = val;
                const card = el.closest('.stat-box');
                if (card) { card.classList.remove('mt-updated'); void card.offsetWidth; card.classList.add('mt-updated'); }
            }
        }
        flash('val-total-tugas', s.total_materi);
        flash('val-sudah',       s.total_sudah);
        flash('val-belum',       s.total_belum);
    }

    async function poll() {
        try {
            const res  = await fetch(`${POLL_URL}?after=${lastId}`);
            if (!res.ok) return;
            const data = await res.json();

            if (data.new_materi && data.new_materi.length > 0) {
                data.new_materi.forEach(m => {
                    if (!document.querySelector(`.tugas-card[data-id="${m.id}"]`)) {
                        showToast(m);
                        unreadCount++;
                    }
                });
                if (data.max_id > lastId) lastId = data.max_id;
                updateBadge();
            }

            if (data.summary) updateStats(data.summary);
        } catch (e) { /* gagal diam */ }
    }

    const sbLink = document.getElementById('sb-materi-link');
    if (sbLink) sbLink.addEventListener('click', () => { unreadCount = 0; updateBadge(); });

    setInterval(poll, INTERVAL_MS);
})();
</script>
@endpush

@section('content')
<style>
    /* ── Page head ── */
    .page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; flex-wrap:wrap; gap:12px; }
    .page-head h1 { font-size:19px; font-weight:600; color:var(--ink); letter-spacing:-.3px; }
    .page-head p  { font-size:12px; color:var(--ink3); margin-top:3px; }

    /* ── TAB UTAMA (Reguler / Triwulan) ── */
    .main-tab-bar {
        display:flex; gap:0;
        border-bottom:2px solid var(--rule);
        margin-bottom:24px;
    }
    .main-tab-btn {
        display:flex; align-items:center; gap:8px;
        padding:10px 20px; font-size:13px; font-weight:500;
        color:var(--ink3); background:none; border:none;
        border-bottom:2px solid transparent; margin-bottom:-2px;
        cursor:pointer; font-family:'IBM Plex Sans',sans-serif;
        transition:color .15s, border-color .15s;
        white-space:nowrap;
    }
    .main-tab-btn:hover { color:var(--ink); }
    .main-tab-btn.active {
        color:var(--blue); border-bottom-color:var(--blue); font-weight:600;
    }
    .main-tab-badge {
        font-size:10px; font-family:'IBM Plex Mono',monospace;
        padding:1px 7px; border-radius:3px;
        background:var(--wash2); color:var(--ink3);
    }
    .main-tab-btn.active .main-tab-badge {
        background:var(--blue-lt); color:var(--blue);
    }
    .tw-open-dot {
        width:6px; height:6px; border-radius:50%; background:var(--green);
        animation:pulse-dot 2s ease infinite;
    }
    @keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.7)} }

    /* ── Panel switching ── */
    .main-panel { display:none; }
    .main-panel.active { display:block; }

    /* ── Stat strip ── */
    .stat-strip { display:flex; gap:1px; margin-bottom:20px; background:var(--rule); border:1px solid var(--rule); border-radius:8px; overflow:hidden; }
    .stat-box { flex:1; min-width:100px; background:var(--surface); padding:20px 22px; display:flex; flex-direction:column-reverse; }
    .stat-lbl  { font-size:10.5px; font-weight:500; letter-spacing:.5px; text-transform:uppercase; color:var(--ink3); margin-bottom:10px; }
    .stat-num  { font-size:30px; font-weight:300; letter-spacing:-1.2px; font-family:'IBM Plex Mono',monospace; color:var(--ink); line-height:1; margin-top:8px; }
    .num-blue  { color:var(--blue); }
    .num-green { color:var(--green); }
    .num-red   { color:var(--red); }

    /* ── Tugas card reguler ── */
    .tugas-wrap { display:flex; flex-direction:column; gap:14px; }
    .tugas-card { background:var(--surface); border:1px solid var(--rule); border-radius:10px; overflow:hidden; transition:box-shadow .15s; }
    .tugas-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.07); }
    .tc-head { display:flex; align-items:center; gap:14px; padding:14px 18px; cursor:pointer; user-select:none; }
    .tc-head:hover { background:var(--wash); }
    .tc-icon { width:36px; height:36px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:var(--blue-lt); color:var(--blue); }
    .tc-info { flex:1; min-width:0; }
    .tc-judul { font-size:13.5px; font-weight:600; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
    .tc-sub   { font-size:11px; color:var(--ink3); margin-top:2px; }
    .tc-prog { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .prog-track { width:110px; height:6px; background:var(--wash2); border-radius:3px; overflow:hidden; }
    .prog-fill  { height:100%; border-radius:3px; transition:width .4s; }
    .prog-label { font-size:11px; font-weight:700; font-family:'IBM Plex Mono',monospace; min-width:36px; text-align:right; }
    .tc-chips { display:flex; gap:6px; flex-shrink:0; }
    .chip { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:600; padding:3px 9px; border-radius:20px; white-space:nowrap; }
    .chip-green { background:var(--green-lt); color:var(--green); }
    .chip-red   { background:var(--red-lt);   color:var(--red); }
    .chip-amber { background:var(--amber-lt); color:var(--amber); }
    .dl-badge { font-size:10.5px; font-family:'IBM Plex Mono',monospace; padding:2px 8px; border-radius:4px; white-space:nowrap; flex-shrink:0; }
    .dl-ok   { background:var(--green-lt); color:var(--green); }
    .dl-warn { background:var(--amber-lt); color:var(--amber); }
    .dl-late { background:var(--red-lt);   color:var(--red); }
    .chevron { color:var(--ink3); flex-shrink:0; transition:transform .2s; }
    .tugas-card.open .chevron { transform:rotate(180deg); }
    .tc-body { display:none; border-top:1px solid var(--rule); }
    .tugas-card.open .tc-body { display:block; }

    /* ── Tab dalam card (semua/sudah/belum) ── */
    .tab-bar { display:flex; gap:0; border-bottom:1px solid var(--rule); padding:0 18px; background:var(--wash); }
    .tab-btn { padding:9px 14px; font-size:12px; font-weight:500; cursor:pointer; border:none; background:none; color:var(--ink3); border-bottom:2px solid transparent; margin-bottom:-1px; transition:color .15s; font-family:inherit; }
    .tab-btn.active { color:var(--blue); border-bottom-color:var(--blue); font-weight:600; }

    /* ── Petugas list ── */
    .petugas-section { padding:10px 18px 14px; }
    .petugas-item { display:flex; align-items:center; gap:12px; padding:10px 12px; border-radius:7px; transition:background .12s; }
    .petugas-item:hover { background:var(--wash); }
    .p-avatar { width:32px; height:32px; border-radius:7px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
    .ava-green { background:var(--green-lt); color:var(--green); }
    .ava-red   { background:var(--red-lt);   color:var(--red); }
    .ava-amber { background:var(--amber-lt); color:var(--amber); }
    .p-info { flex:1; min-width:0; }
    .p-name { font-size:12.5px; font-weight:500; color:var(--ink); }
    .p-meta { font-size:11px; color:var(--ink3); }
    .skor-badge { font-size:11px; font-weight:700; font-family:'IBM Plex Mono',monospace; padding:2px 8px; border-radius:4px; flex-shrink:0; }
    .skor-high { background:var(--green-lt); color:var(--green); }
    .skor-mid  { background:var(--amber-lt); color:var(--amber); }
    .skor-low  { background:var(--red-lt);   color:var(--red); }
    .skor-none { background:var(--wash2); color:var(--ink3); }
    .submit-time { font-size:10.5px; color:var(--ink3); flex-shrink:0; font-family:'IBM Plex Mono',monospace; white-space:nowrap; }
    .p-file { display:inline-flex; align-items:center; gap:4px; font-size:11px; color:var(--blue); text-decoration:none; font-weight:500; flex-shrink:0; }
    .p-file:hover { text-decoration:underline; }
    .empty-msg { text-align:center; padding:28px; color:var(--ink3); font-size:12.5px; }
    .full-empty { text-align:center; padding:60px 20px; color:var(--ink3); }
    .full-empty svg { margin-bottom:12px; opacity:.35; }
    .full-empty p { font-size:13px; }

    /* ── Search + Kalender toolbar ── */
    .mt-toolbar { display:flex; align-items:center; gap:10px; background:var(--surface); border:1px solid var(--rule); border-radius:10px; padding:10px 14px; margin-bottom:16px; flex-wrap:wrap; }
    .mt-search-wrap { display:flex; align-items:center; gap:8px; flex:1; min-width:200px; background:var(--wash); border:1px solid var(--rule); border-radius:7px; padding:7px 11px; transition:border-color .15s; }
    .mt-search-wrap:focus-within { border-color:var(--blue); background:#fff; }
    .mt-search-wrap svg { color:var(--ink3); flex-shrink:0; }
    .mt-search-input { flex:1; border:none; background:none; outline:none; font-size:12.5px; color:var(--ink); font-family:'IBM Plex Sans',sans-serif; }
    .mt-search-input::placeholder { color:var(--ink3); }
    .mt-search-clear { background:none; border:none; cursor:pointer; padding:0; color:var(--ink3); display:none; line-height:1; transition:color .12s; }
    .mt-search-clear:hover { color:var(--ink); }
    .mt-toolbar-sep { width:1px; height:24px; background:var(--rule); flex-shrink:0; }
    .mt-cal-btn { display:inline-flex; align-items:center; gap:7px; height:34px; padding:0 13px; border:1px solid var(--rule); border-radius:7px; background:var(--wash); color:var(--ink2); font-size:12px; font-weight:500; font-family:'IBM Plex Sans',sans-serif; cursor:pointer; white-space:nowrap; transition:all .12s; position:relative; }
    .mt-cal-btn:hover { border-color:var(--blue); color:var(--blue); }
    .mt-cal-btn.has-date { background:var(--blue-lt); border-color:var(--blue); color:var(--blue); }
    .mt-cal-popup { position:absolute; z-index:999; background:var(--surface); border:1px solid var(--rule); border-radius:10px; box-shadow:0 8px 32px rgba(0,0,0,.13); padding:14px; display:none; min-width:240px; }
    .mt-cal-popup.open { display:block; }
    .mc-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
    .mc-month { font-size:12px; font-weight:600; color:var(--ink); }
    .mc-nav { background:none; border:none; cursor:pointer; color:var(--ink3); font-size:16px; padding:0 4px; line-height:1; transition:color .1s; }
    .mc-nav:hover { color:var(--ink); }
    .mc-days-hdr { display:grid; grid-template-columns:repeat(7,1fr); margin-bottom:4px; }
    .mc-days-hdr span { text-align:center; font-size:9.5px; font-weight:600; color:var(--ink3); letter-spacing:.4px; padding:2px 0; }
    .mc-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
    .mc-day { text-align:center; font-size:11px; padding:5px 2px; border-radius:5px; cursor:pointer; color:var(--ink2); font-family:'IBM Plex Mono',monospace; transition:all .1s; line-height:1.3; }
    .mc-day:hover { background:var(--blue-lt); color:var(--blue); }
    .mc-day.today { background:var(--wash2); font-weight:700; }
    .mc-day.has-deadline { background:var(--amber-lt); color:var(--amber); font-weight:600; position:relative; }
    .mc-day.has-deadline::after { content:''; position:absolute; bottom:2px; left:50%; transform:translateX(-50%); width:3px; height:3px; border-radius:50%; background:var(--amber); }
    .mc-day.selected { background:var(--blue); color:#fff; font-weight:700; }
    .mc-day.other-month { color:var(--rule); cursor:default; }
    .mc-day.other-month:hover { background:none; }
    .mt-date-chip { display:none; align-items:center; gap:5px; background:var(--blue-lt); border:1px solid rgba(26,86,219,.2); color:var(--blue); border-radius:20px; font-size:11px; font-weight:600; padding:3px 10px; white-space:nowrap; }
    .mt-date-chip.visible { display:inline-flex; }
    .mt-date-chip-clear { background:none; border:none; cursor:pointer; padding:0; color:var(--blue); line-height:1; opacity:.7; }
    .mt-date-chip-clear:hover { opacity:1; }
    .mt-result-count { margin-left:auto; font-size:11px; color:var(--ink3); font-family:'IBM Plex Mono',monospace; white-space:nowrap; }
    .mt-no-result { text-align:center; padding:48px 20px; color:var(--ink3); display:none; }
    .mt-no-result svg { margin:0 auto 12px; display:block; opacity:.3; }
    .mt-no-result p { font-size:13px; }

    /* ── Toast realtime ── */
    #mt-toast-wrap { position:fixed; bottom:24px; right:24px; z-index:9999; display:flex; flex-direction:column; gap:8px; pointer-events:none; }
    .mt-toast { background:var(--surface); border:1px solid var(--rule); border-left:3px solid var(--green); border-radius:8px; padding:10px 14px; min-width:220px; font-size:12px; box-shadow:0 4px 16px rgba(0,0,0,.12); animation:mt-toast-in .25s ease; pointer-events:auto; }
    .mt-toast-name   { font-weight:600; color:var(--ink); margin-bottom:2px; }
    .mt-toast-detail { color:var(--ink3); font-size:11px; }
    @keyframes mt-toast-in  { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:none} }
    @keyframes mt-toast-out { from{opacity:1} to{opacity:0;transform:translateX(16px)} }
    @keyframes mt-stat-flash { 0%{background:#d1fae5} 100%{background:var(--surface)} }
    .stat-box.mt-updated { animation:mt-stat-flash .8s ease forwards; }

    /* ── Triwulan panel styles ── */
    .tw-periode-bar { display:flex; align-items:center; gap:10px; background:var(--surface); border:1px solid var(--rule); border-radius:8px; padding:10px 14px; margin-bottom:20px; flex-wrap:wrap; }
    .tw-periode-select { height:32px; padding:0 10px; border:1px solid var(--rule); border-radius:6px; font-size:12px; font-family:'IBM Plex Sans',sans-serif; color:var(--ink); background:var(--wash); cursor:pointer; }
    .tw-periode-select:focus { outline:none; border-color:var(--blue); }
    .btn-tw-create { display:inline-flex; align-items:center; gap:6px; padding:7px 13px; background:var(--green); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; transition:opacity .2s; white-space:nowrap; margin-left:auto; }
    .btn-tw-create:hover { opacity:.88; color:#fff; }
    .tw-info-banner { display:flex; align-items:flex-start; gap:10px; padding:11px 14px; border-radius:7px; font-size:12px; color:#1e40af; background:#eff6ff; border:1px solid #bfdbfe; margin-bottom:18px; line-height:1.7; }
    .tw-card { background:var(--surface); border:1px solid var(--rule); border-radius:10px; overflow:hidden; margin-bottom:14px; transition:box-shadow .15s; }
    .tw-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.07); }
    .tw-card-head { display:flex; align-items:center; gap:14px; padding:14px 18px; cursor:pointer; user-select:none; }
    .tw-card-head:hover { background:var(--wash); }
    .tw-card-icon { width:36px; height:36px; border-radius:8px; flex-shrink:0; display:flex; align-items:center; justify-content:center; background:#f0fdf4; color:var(--green); }
    .tw-card-info { flex:1; min-width:0; }
    .tw-card-title { font-size:13.5px; font-weight:600; color:var(--ink); }
    .tw-card-sub   { font-size:11px; color:var(--ink3); margin-top:2px; }
    .tw-prog { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .tw-chevron { color:var(--ink3); flex-shrink:0; transition:transform .2s; }
    .tw-card.open .tw-chevron { transform:rotate(180deg); }
    .tw-card-body { display:none; border-top:1px solid var(--rule); }
    .tw-card.open .tw-card-body { display:block; }
    .btn-del { background:none; border:1px solid var(--rule); border-radius:5px; padding:4px 9px; font-size:10.5px; color:var(--red); cursor:pointer; transition:all .12s; flex-shrink:0; }
    .btn-del:hover { background:var(--red-lt); border-color:var(--red); }
    .p-item { display:flex; align-items:center; gap:12px; padding:9px 10px; border-radius:7px; transition:background .12s; }
    .p-item:hover { background:var(--wash); }
    .p-ava { width:30px; height:30px; border-radius:7px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
    .ava-g { background:var(--green-lt); color:var(--green); }
    .ava-r { background:var(--red-lt);   color:var(--red); }
    .tw-empty { text-align:center; padding:60px 20px; background:var(--surface); border:1px solid var(--rule); border-radius:10px; color:var(--ink3); }
    .tw-empty svg { margin:0 auto 12px; display:block; opacity:.3; }
    .tw-empty p { font-size:13px; margin-bottom:16px; }
    .flash-alert { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:12.5px; font-weight:500; }
    .flash-success { background:var(--green-lt); color:var(--green); border:1px solid #bbf7d0; }
</style>

{{-- Flash --}}
@if(session('success'))
<div class="flash-alert flash-success">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── Page head ── --}}
<div class="page-head">
    <div>
        <h1>Materi & Tugas</h1>
        <p>Pantau progres pengerjaan tugas petugas wilayah <strong>{{ $wilayah->nama ?? '-' }}</strong></p>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════
     TAB UTAMA — JS switching, tidak reload halaman
     ══════════════════════════════════════════════════════════════ --}}
@php
    $periodeSekarang = \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang();
    // Pass data triwulan
    $wilayahId   = auth()->user()->wilayah_id;
    $petugasListTw = \App\Models\Petugas::with('user')
        ->where('wilayah_id', $wilayahId)
        ->whereHas('user', fn($q) => $q->where('role','petugas'))->get();
    $periodeFilter = request('periode', $periodeSekarang);
    $materiTriwulan = \App\Models\MateriTriwulan::with(['quiz', 'files'])
        ->where('wilayah_id', $wilayahId)
        ->where('periode', $periodeFilter)
        ->latest()->get();
    $materiTriwulan = $materiTriwulan->map(function($m) use ($petugasListTw) {
        $ids = $petugasListTw->pluck('id');
        $jMap = \App\Models\JawabanTriwulan::where('materi_triwulan_id', $m->id)
            ->whereIn('petugas_id', $ids)->get()->keyBy('petugas_id');
        $sudah = $jMap->where('status','sudah')->count();
        $m->jawabanMap  = $jMap;
        $m->petugasList = $petugasListTw;
        $m->jmlSudah    = $sudah;
        $m->jmlBelum    = $ids->count() - $sudah;
        $m->progres     = $ids->count() > 0 ? round($sudah/$ids->count()*100) : 0;
        return $m;
    });
    $periodeOptions = [];
    for($y=2026; $y<=now()->year+1; $y++) for($tw=1;$tw<=4;$tw++) $periodeOptions["{$y}-TW{$tw}"]="Triwulan {$tw} Tahun {$y}";
    preg_match('/^(\d{4})-TW(\d)$/', $periodeFilter, $pm);
    $periodeLabel = isset($pm[1]) ? "Triwulan {$pm[2]} Tahun {$pm[1]}" : $periodeFilter;
    $bisaSurvey = \App\Helpers\SurveyInternalHelper::bisaDiakses();
@endphp

<div class="main-tab-bar">
    <button class="main-tab-btn active" id="mtab-reguler" onclick="switchMainTab('reguler')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
            <rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
        Materi & Tugas Reguler
        <span class="main-tab-badge">{{ $totalTugas }}</span>
    </button>

    <button class="main-tab-btn" id="mtab-triwulan" onclick="switchMainTab('triwulan')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Quiz Triwulan
        <span class="main-tab-badge">{{ count($materiTriwulan) }}</span>
        @if($bisaSurvey)
            <span class="tw-open-dot" title="Survey Internal terbuka"></span>
        @endif
    </button>
</div>

{{-- ══════════════════════════════════════════════════════════════
     PANEL 1: MATERI & TUGAS REGULER
     ══════════════════════════════════════════════════════════════ --}}
<div class="main-panel active" id="panel-reguler">

    {{-- Stat strip --}}
    <div class="stat-strip">
        <div class="stat-box">
            <div class="stat-num num-blue" id="val-total-tugas">{{ $totalTugas }}</div>
            <div class="stat-lbl">Total Tugas</div>
        </div>
        <div class="stat-box">
            <div class="stat-num">{{ $totalPetugas }}</div>
            <div class="stat-lbl">Petugas Wilayah</div>
        </div>
        <div class="stat-box">
            <div class="stat-num num-green" id="val-sudah">{{ $totalSudah }}</div>
            <div class="stat-lbl">Sudah Selesai</div>
        </div>
        <div class="stat-box">
            <div class="stat-num num-red" id="val-belum">{{ $totalBelum }}</div>
            <div class="stat-lbl">Belum Dikerjakan</div>
        </div>
    </div>

    {{-- Search + Kalender toolbar --}}
    @if(!$tugasList->isEmpty())
    <div class="mt-toolbar">
        <div class="mt-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="mt-search" class="mt-search-input" placeholder="Cari nama tugas…" autocomplete="off">
            <button class="mt-search-clear" id="mt-search-clear" title="Hapus">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="mt-toolbar-sep"></div>
        <div style="position:relative;">
            <button class="mt-cal-btn" id="mt-cal-btn" onclick="toggleCal(event)">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span id="mt-cal-label">Filter Deadline</span>
            </button>
            <div class="mt-cal-popup" id="mt-cal-popup">
                <div class="mc-head">
                    <button class="mc-nav" id="mc-prev">&#8249;</button>
                    <span class="mc-month" id="mc-month-label"></span>
                    <button class="mc-nav" id="mc-next">&#8250;</button>
                </div>
                <div class="mc-days-hdr">
                    <span>Min</span><span>Sen</span><span>Sel</span><span>Rab</span><span>Kam</span><span>Jum</span><span>Sab</span>
                </div>
                <div class="mc-grid" id="mc-grid"></div>
            </div>
        </div>
        <span class="mt-date-chip" id="mt-date-chip">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            <span id="mt-date-chip-label"></span>
            <button class="mt-date-chip-clear" onclick="clearDateFilter()" title="Hapus">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </span>
        <span class="mt-result-count" id="mt-count"></span>
    </div>
    @endif

    <div class="mt-no-result" id="mt-no-result">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p>Tidak ada tugas yang cocok.</p>
    </div>

    @if($tugasList->isEmpty())
        <div class="full-empty">
            <svg width="42" height="42" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <p>Belum ada tugas yang dibuat oleh admin.</p>
        </div>
    @else
    <div class="tugas-wrap">
        @foreach($tugasList as $i => $tugas)
        @php
            $deadline  = $tugas->deadline;
            $dlClass   = 'dl-ok';
            $dlLabel   = $deadline ? $deadline->format('d M Y') : '—';
            if ($deadline) {
                $diff = now()->diffInDays($deadline, false);
                if ($diff < 0)      $dlClass = 'dl-late';
                elseif ($diff <= 3) $dlClass = 'dl-warn';
            }
            $fillColor = $tugas->progress >= 80 ? 'var(--green)' : ($tugas->progress >= 40 ? 'var(--amber)' : 'var(--red)');
            $progLabelColor = $fillColor;
            $openClass = $i === 0 ? 'open' : '';
            $dlTimestamp = $deadline ? $deadline->timestamp : 9999999999;
        @endphp
        <div class="tugas-card {{ $openClass }}" id="tc-{{ $tugas->id }}"
             data-judul="{{ strtolower($tugas->judul) }}"
             data-deadline="{{ $dlTimestamp }}">

            <div class="tc-head" onclick="toggleCard({{ $tugas->id }})">
                <div class="tc-icon">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                    </svg>
                </div>
                <div class="tc-info">
                    <div class="tc-judul">{{ $tugas->judul }}</div>
                    <div class="tc-sub">{{ $tugas->quiz->count() }} soal quiz &nbsp;·&nbsp; Deadline: {{ $dlLabel }}</div>
                </div>
                <span class="dl-badge {{ $dlClass }}">{{ $dlLabel }}</span>
                <div class="tc-chips">
                    <span class="chip chip-green">✔ {{ $tugas->jmlSudah }} selesai</span>
                    <span class="chip chip-red">✖ {{ $tugas->jmlBelum }} belum</span>
                    @if($tugas->jmlTerlambat > 0)
                        <span class="chip chip-amber">⚠ {{ $tugas->jmlTerlambat }} terlambat</span>
                    @endif
                </div>
                <div class="tc-prog">
                    <div class="prog-track">
                        <div class="prog-fill" style="width:{{ $tugas->progress }}%; background:{{ $fillColor }}"></div>
                    </div>
                    <span class="prog-label" style="color:{{ $progLabelColor }}">{{ $tugas->progress }}%</span>
                </div>
                <svg class="chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>

            <div class="tc-body">
                <div class="tab-bar">
                    <button class="tab-btn active" onclick="switchTab({{ $tugas->id }}, 'semua', this)">Semua ({{ $totalPetugas }})</button>
                    <button class="tab-btn" onclick="switchTab({{ $tugas->id }}, 'sudah', this)">✔ Selesai ({{ $tugas->jmlSudah }})</button>
                    <button class="tab-btn" onclick="switchTab({{ $tugas->id }}, 'belum', this)">✖ Belum ({{ $tugas->jmlBelum }})</button>
                </div>
                <div class="petugas-section">

                    <div data-tab="{{ $tugas->id }}-semua">
                        @forelse($tugas->petugasList as $p)
                        @php
                            $jaw = $tugas->jawabanMap->get($p->id);
                            $status = 'belum';
                            if ($jaw && $jaw->status === 'sudah') {
                                $status = ($deadline && $jaw->updated_at && \Carbon\Carbon::parse($jaw->updated_at)->startOfDay()->gt($deadline)) ? 'terlambat' : 'sudah';
                            }
                            $skor = $jaw?->skor;
                            $skorClass = $skor === null ? 'skor-none' : ($skor >= 80 ? 'skor-high' : ($skor >= 50 ? 'skor-mid' : 'skor-low'));
                            $avaClass  = $status === 'sudah' ? 'ava-green' : ($status === 'terlambat' ? 'ava-amber' : 'ava-red');
                        @endphp
                        <div class="petugas-item">
                            <div class="p-avatar {{ $avaClass }}">{{ strtoupper(substr($p->user->name ?? '?', 0, 2)) }}</div>
                            <div class="p-info">
                                <div class="p-name">{{ $p->user->name ?? '—' }}</div>
                                <div class="p-meta">Shift {{ $p->shift ?? '—' }}</div>
                            </div>
                            @if($status==='sudah') <span class="chip chip-green" style="font-size:10px">✔ Selesai</span>
                            @elseif($status==='terlambat') <span class="chip chip-amber" style="font-size:10px">⚠ Terlambat</span>
                            @else <span class="chip chip-red" style="font-size:10px">✖ Belum</span>
                            @endif
                            <span class="skor-badge {{ $skorClass }}">{{ $skor !== null ? $skor.'/100' : '—' }}</span>
                            @if($jaw?->file) <a href="{{ asset('storage/'.$jaw->file) }}" target="_blank" class="p-file"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>File</a> @endif
                            @if($jaw?->link) <a href="{{ $jaw->link }}" target="_blank" class="p-file"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>Link</a> @endif
                            @if($jaw?->updated_at && $status!=='belum') <span class="submit-time">{{ \Carbon\Carbon::parse($jaw->updated_at)->format('d M, H:i') }}</span> @endif
                        </div>
                        @empty
                        <div class="empty-msg">Belum ada petugas di wilayah ini.</div>
                        @endforelse
                    </div>

                    <div data-tab="{{ $tugas->id }}-sudah" style="display:none">
                        @php $sudahList = $tugas->petugasList->filter(fn($p) => ($tugas->jawabanMap->get($p->id)?->status ?? '') === 'sudah'); @endphp
                        @forelse($sudahList as $p)
                        @php $jaw=$tugas->jawabanMap->get($p->id); $tl=$deadline&&$jaw->updated_at&&\Carbon\Carbon::parse($jaw->updated_at)->startOfDay()->gt($deadline); $sk=$jaw->skor; $skc=$sk===null?'skor-none':($sk>=80?'skor-high':($sk>=50?'skor-mid':'skor-low')); @endphp
                        <div class="petugas-item">
                            <div class="p-avatar {{ $tl?'ava-amber':'ava-green' }}">{{ strtoupper(substr($p->user->name??'?',0,2)) }}</div>
                            <div class="p-info"><div class="p-name">{{ $p->user->name??'—' }}</div><div class="p-meta">Shift {{ $p->shift??'—' }}</div></div>
                            @if($tl) <span class="chip chip-amber" style="font-size:10px">⚠ Terlambat</span>
                            @else <span class="chip chip-green" style="font-size:10px">✔ Tepat Waktu</span> @endif
                            <span class="skor-badge {{ $skc }}">{{ $sk!==null?$sk.'/100':'—' }}</span>
                            @if($jaw->file) <a href="{{ asset('storage/'.$jaw->file) }}" target="_blank" class="p-file"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>File</a> @endif
                            @if($jaw->link) <a href="{{ $jaw->link }}" target="_blank" class="p-file"><svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>Link</a> @endif
                            <span class="submit-time">{{ \Carbon\Carbon::parse($jaw->updated_at)->format('d M, H:i') }}</span>
                        </div>
                        @empty <div class="empty-msg">Belum ada yang menyelesaikan.</div>
                        @endforelse
                    </div>

                    <div data-tab="{{ $tugas->id }}-belum" style="display:none">
                        @php $belumList = $tugas->petugasList->filter(fn($p) => ($tugas->jawabanMap->get($p->id)?->status??'belum')!=='sudah'); @endphp
                        @forelse($belumList as $p)
                        <div class="petugas-item">
                            <div class="p-avatar ava-red">{{ strtoupper(substr($p->user->name??'?',0,2)) }}</div>
                            <div class="p-info"><div class="p-name">{{ $p->user->name??'—' }}</div><div class="p-meta">Shift {{ $p->shift??'—' }}</div></div>
                            <span class="chip chip-red" style="font-size:10px">✖ Belum Dikerjakan</span>
                            <span class="skor-badge skor-none">—</span>
                        </div>
                        @empty <div class="empty-msg">🎉 Semua petugas sudah mengerjakan!</div>
                        @endforelse
                    </div>

                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif

</div>{{-- /panel-reguler --}}


{{-- ══════════════════════════════════════════════════════════════
     PANEL 2: QUIZ TRIWULAN
     ══════════════════════════════════════════════════════════════ --}}
<div class="main-panel" id="panel-triwulan">

    {{-- Periode selector + tombol buat --}}
    <div class="tw-periode-bar">
        <span style="font-size:12px;font-weight:600;color:var(--ink)">Periode:</span>
        <form method="GET" action="{{ route('koordinator.materi.index') }}" id="tw-periode-form" style="display:inline">
            <input type="hidden" name="panel" value="triwulan">
            <select name="periode" class="tw-periode-select" onchange="document.getElementById('tw-periode-form').submit()">
                @foreach($periodeOptions as $val => $lbl)
                    <option value="{{ $val }}" {{ $periodeFilter===$val?'selected':'' }}>{{ $lbl }}</option>
                @endforeach
            </select>
        </form>
        <span style="font-size:11.5px;color:var(--ink3)">Petugas bisa mengisi saat Survey Internal aktif</span>
        <a href="{{ route('koordinator.materi.triwulan.create') }}" class="btn-tw-create">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Buat Materi & Quiz
        </a>
    </div>

    {{-- Info banner --}}
    <div class="tw-info-banner">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            Periode <strong>{{ $periodeLabel }}</strong> —
            @if($bisaSurvey)
                <strong style="color:var(--green)">Survey Internal sedang terbuka</strong>, petugas bisa mengisi quiz sekarang.
            @else
                Survey Internal belum dibuka. Aktifkan di <strong>Admin → Survey → Toggle Override</strong> untuk membuka pengisian.
            @endif
        </div>
    </div>

    {{-- Stats triwulan --}}
    <div class="stat-strip" style="margin-bottom:20px">
        <div class="stat-box">
            <div class="stat-num num-blue">{{ count($materiTriwulan) }}</div>
            <div class="stat-lbl">Total Materi</div>
        </div>
        <div class="stat-box">
            <div class="stat-num">{{ $petugasListTw->count() }}</div>
            <div class="stat-lbl">Petugas Wilayah</div>
        </div>
        <div class="stat-box">
            <div class="stat-num num-green">{{ $materiTriwulan->sum('jmlSudah') }}</div>
            <div class="stat-lbl">Jawaban Masuk</div>
        </div>
        <div class="stat-box">
            <div class="stat-num num-red">{{ $materiTriwulan->sum('jmlBelum') }}</div>
            <div class="stat-lbl">Belum Mengisi</div>
        </div>
    </div>

    @if($materiTriwulan->isEmpty())
    <div class="tw-empty">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <p>Belum ada materi untuk <strong>{{ $periodeLabel }}</strong></p>
        <a href="{{ route('koordinator.materi.triwulan.create') }}" class="btn-tw-create" style="margin:0 auto;display:inline-flex">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Buat Materi & Quiz Sekarang
        </a>
    </div>
    @else

    {{-- Search toolbar — supaya materi/file lama gampang ditemukan --}}
    <div class="mt-toolbar">
        <div class="mt-search-wrap">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
            </svg>
            <input type="text" id="mt-search-tw" class="mt-search-input" placeholder="Cari judul materi triwulan…" autocomplete="off">
            <button class="mt-search-clear" id="mt-search-tw-clear" title="Hapus">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="mt-toolbar-sep"></div>
        <label style="display:inline-flex;align-items:center;gap:6px;font-size:12px;color:var(--ink2);cursor:pointer;white-space:nowrap">
            <input type="checkbox" id="mt-filter-has-file-tw" style="cursor:pointer">
            Hanya yang ada file
        </label>
        <span class="mt-result-count" id="mt-count-tw"></span>
    </div>

    <div class="mt-no-result" id="mt-no-result-tw">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
        </svg>
        <p>Tidak ada materi yang cocok.</p>
    </div>

    @foreach($materiTriwulan as $i => $mt)
    @php
        $fillColor = $mt->progres >= 80 ? 'var(--green)' : ($mt->progres >= 40 ? 'var(--amber)' : 'var(--red)');
        $openClass = $i === 0 ? 'open' : '';
        $twHasFile = ($mt->file || $mt->files->isNotEmpty()) ? '1' : '0';
    @endphp
    <div class="tw-card {{ $openClass }}" id="twc-{{ $mt->id }}"
         data-judul-tw="{{ strtolower($mt->judul) }}"
         data-has-file-tw="{{ $twHasFile }}">
        <div class="tw-card-head" onclick="toggleTwCard({{ $mt->id }})">
            <div class="tw-card-icon">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
            </div>
            <div class="tw-card-info">
                <div class="tw-card-title">{{ $mt->judul }}</div>
                <div class="tw-card-sub">{{ $mt->quiz->count() }} soal · {{ $periodeLabel }} · Dibuat {{ $mt->created_at->format('d M Y') }}</div>
            </div>
            @if($twHasFile === '1')
            <span class="chip" style="background:var(--blue-lt);color:var(--blue)" title="Ada file materi">
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                File
            </span>
            @endif
            <div class="chip chip-green">✔ {{ $mt->jmlSudah }} sudah</div>
            <div class="chip chip-red">✖ {{ $mt->jmlBelum }} belum</div>
            <div class="tw-prog">
                <div class="prog-track"><div class="prog-fill" style="width:{{ $mt->progres }}%;background:{{ $fillColor }}"></div></div>
                <span class="prog-label" style="color:{{ $fillColor }}">{{ $mt->progres }}%</span>
            </div>
            <a href="{{ route('koordinator.materi.triwulan.edit', $mt->id) }}" class="btn-del" style="color:var(--blue);text-decoration:none" onclick="event.stopPropagation()">Edit</a>
            <form method="POST" action="{{ route('koordinator.materi.triwulan.destroy', $mt->id) }}"
                  onsubmit="return confirm('Hapus materi ini?')" onclick="event.stopPropagation()" style="flex-shrink:0">
                @csrf @method('DELETE')
                <button type="submit" class="btn-del">Hapus</button>
            </form>
            <svg class="tw-chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>

        <div class="tw-card-body">
            @php $twFiles = $mt->semuaFile(); @endphp
            @if($twFiles->isNotEmpty() || $mt->link)
            <div style="padding:12px 18px;border-bottom:1px solid var(--rule);display:flex;flex-wrap:wrap;gap:8px;background:var(--wash)">
                @foreach($twFiles as $f)
                <a href="{{ asset('storage/' . $f->file) }}" target="_blank" class="p-file" style="border:1px solid var(--rule);border-radius:5px;padding:5px 10px;background:var(--surface)">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    {{ $f->nama_asli }}
                </a>
                @endforeach
                @if($mt->link)
                <a href="{{ $mt->link }}" target="_blank" class="p-file" style="border:1px solid var(--rule);border-radius:5px;padding:5px 10px;background:var(--surface)">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                    Link Referensi
                </a>
                @endif
            </div>
            @endif
            <div class="tab-bar" style="padding:0 18px;background:var(--wash)">
                <button class="tab-btn active" onclick="switchTwTab({{ $mt->id }},'semua',this)">Semua ({{ $petugasListTw->count() }})</button>
                <button class="tab-btn" onclick="switchTwTab({{ $mt->id }},'sudah',this)">✔ Sudah ({{ $mt->jmlSudah }})</button>
                <button class="tab-btn" onclick="switchTwTab({{ $mt->id }},'belum',this)">✖ Belum ({{ $mt->jmlBelum }})</button>
            </div>
            <div style="padding:10px 18px 14px">

                <div data-twtab="{{ $mt->id }}-semua">
                    @forelse($mt->petugasList as $p)
                    @php $jaw=$mt->jawabanMap->get($p->id); $sdh=$jaw&&$jaw->status==='sudah'; $sk=$jaw?->skor; $skc=$sk===null?'skor-none':($sk>=80?'skor-high':($sk>=50?'skor-mid':'skor-low')); @endphp
                    <div class="p-item">
                        <div class="p-ava {{ $sdh?'ava-g':'ava-r' }}">{{ strtoupper(substr($p->user->name??'?',0,2)) }}</div>
                        <div style="flex:1;min-width:0"><div class="p-name">{{ $p->user->name??'—' }}</div><div class="p-meta">Shift {{ $p->shift??'—' }}</div></div>
                        @if($sdh) <span class="chip chip-green" style="font-size:10px">✔ Sudah</span>
                        @else <span class="chip chip-red" style="font-size:10px">✖ Belum</span> @endif
                        <span class="skor-badge {{ $skc }}">{{ $sk!==null?$sk.'/100':'—' }}</span>
                        @if($jaw?->dikerjakan_at) <span class="submit-time">{{ $jaw->dikerjakan_at->format('d M, H:i') }}</span> @endif
                    </div>
                    @empty <div class="empty-msg">Belum ada petugas.</div>
                    @endforelse
                </div>

                <div data-twtab="{{ $mt->id }}-sudah" style="display:none">
                    @php $sl=$mt->petugasList->filter(fn($p)=>($mt->jawabanMap->get($p->id)?->status??'')=='sudah'); @endphp
                    @forelse($sl as $p)
                    @php $jaw=$mt->jawabanMap->get($p->id); $sk=$jaw?->skor; $skc=$sk===null?'skor-none':($sk>=80?'skor-high':($sk>=50?'skor-mid':'skor-low')); @endphp
                    <div class="p-item">
                        <div class="p-ava ava-g">{{ strtoupper(substr($p->user->name??'?',0,2)) }}</div>
                        <div style="flex:1;min-width:0"><div class="p-name">{{ $p->user->name??'—' }}</div><div class="p-meta">Shift {{ $p->shift??'—' }}</div></div>
                        <span class="chip chip-green" style="font-size:10px">✔ Selesai</span>
                        <span class="skor-badge {{ $skc }}">{{ $sk!==null?$sk.'/100':'—' }}</span>
                        @if($jaw?->dikerjakan_at) <span class="submit-time">{{ $jaw->dikerjakan_at->format('d M, H:i') }}</span> @endif
                    </div>
                    @empty <div class="empty-msg">Belum ada yang mengerjakan.</div>
                    @endforelse
                </div>

                <div data-twtab="{{ $mt->id }}-belum" style="display:none">
                    @php $bl=$mt->petugasList->filter(fn($p)=>($mt->jawabanMap->get($p->id)?->status??'belum')!=='sudah'); @endphp
                    @forelse($bl as $p)
                    <div class="p-item">
                        <div class="p-ava ava-r">{{ strtoupper(substr($p->user->name??'?',0,2)) }}</div>
                        <div style="flex:1;min-width:0"><div class="p-name">{{ $p->user->name??'—' }}</div><div class="p-meta">Shift {{ $p->shift??'—' }}</div></div>
                        <span class="chip chip-red" style="font-size:10px">✖ Belum</span>
                        <span class="skor-badge skor-none">—</span>
                    </div>
                    @empty <div class="empty-msg">🎉 Semua sudah mengerjakan!</div>
                    @endforelse
                </div>

            </div>
        </div>
    </div>
    @endforeach
    @endif

</div>{{-- /panel-triwulan --}}

@endsection

@push('scripts')
<script>
/* ══════════════════════════════════════════════════
   TAB UTAMA — smooth JS switch, zero reload
   ══════════════════════════════════════════════════ */
function switchMainTab(tab) {
    // Panel
    document.querySelectorAll('.main-panel').forEach(p => p.classList.remove('active'));
    document.getElementById('panel-' + tab).classList.add('active');
    // Tombol
    document.querySelectorAll('.main-tab-btn').forEach(b => b.classList.remove('active'));
    document.getElementById('mtab-' + tab).classList.add('active');
    // Simpan state
    sessionStorage.setItem('koor_materi_tab', tab);
    // Update hidden input periode form agar tetap di panel yang benar
    const hid = document.querySelector('#tw-periode-form input[name="panel"]');
    if (hid) hid.value = tab;
}

// Restore tab dari sessionStorage atau dari query param
document.addEventListener('DOMContentLoaded', function() {
    const fromUrl  = new URLSearchParams(location.search).get('panel');
    const fromSess = sessionStorage.getItem('koor_materi_tab');
    const active   = fromUrl || fromSess || 'reguler';
    if (active === 'triwulan') switchMainTab('triwulan');
});

/* ── Tugas reguler: buka/tutup card ── */
function toggleCard(id) { document.getElementById('tc-' + id).classList.toggle('open'); }

/* ── Tab dalam card reguler (semua/sudah/belum) ── */
function switchTab(tugasId, tab, btn) {
    document.querySelectorAll('[data-tab^="' + tugasId + '-"]').forEach(el => el.style.display = 'none');
    document.querySelector('[data-tab="' + tugasId + '-' + tab + '"]').style.display = 'block';
    btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

/* ── Triwulan: buka/tutup card ── */
function toggleTwCard(id) { document.getElementById('twc-' + id).classList.toggle('open'); }

/* ── Tab dalam card triwulan ── */
function switchTwTab(id, tab, btn) {
    document.querySelectorAll('[data-twtab^="' + id + '-"]').forEach(el => el.style.display = 'none');
    document.querySelector('[data-twtab="' + id + '-' + tab + '"]').style.display = 'block';
    btn.closest('.tab-bar').querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}

/* ══════════════════════════════════════════════════
   KALENDER DEADLINE PICKER (tab reguler)
   ══════════════════════════════════════════════════ */
const BULAN_ID = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const BULAN_SHORT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

function getDeadlineDates() {
    const dates = new Set();
    document.querySelectorAll('.tugas-card[data-deadline]').forEach(c => {
        const ts = Number(c.dataset.deadline);
        if (ts && ts < 9999999999) dates.add(new Date(ts*1000).toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'}));
    });
    return dates;
}

let calCur = (function() {
    const s = new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'});
    const [y,m] = s.split('-').map(Number);
    return {y, m:m-1};
})();
const calToday = new Date().toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'});
let calSelected = null;

function renderCal() {
    const deadlines = getDeadlineDates();
    document.getElementById('mc-month-label').textContent = BULAN_ID[calCur.m]+' '+calCur.y;
    const grid = document.getElementById('mc-grid'); grid.innerHTML='';
    const firstDay = new Date(calCur.y,calCur.m,1).getDay();
    const daysInMonth = new Date(calCur.y,calCur.m+1,0).getDate();
    const daysInPrev  = new Date(calCur.y,calCur.m,0).getDate();
    for(let i=firstDay-1;i>=0;i--){const el=document.createElement('div');el.className='mc-day other-month';el.textContent=daysInPrev-i;grid.appendChild(el);}
    for(let d=1;d<=daysInMonth;d++){
        const key=calCur.y+'-'+String(calCur.m+1).padStart(2,'0')+'-'+String(d).padStart(2,'0');
        const el=document.createElement('div'); el.className='mc-day'; el.textContent=d;
        if(key===calToday) el.classList.add('today');
        if(deadlines.has(key)) el.classList.add('has-deadline');
        if(key===calSelected) el.classList.add('selected');
        el.addEventListener('click',()=>selectDate(key,d));
        grid.appendChild(el);
    }
    const rem=(firstDay+daysInMonth)%7===0?0:7-((firstDay+daysInMonth)%7);
    for(let d=1;d<=rem;d++){const el=document.createElement('div');el.className='mc-day other-month';el.textContent=d;grid.appendChild(el);}
}

function selectDate(key,d) {
    calSelected=key; renderCal(); closeCal();
    const [y,m]=key.split('-').map(Number);
    const label=d+' '+BULAN_SHORT[m-1]+' '+y;
    document.getElementById('mt-date-chip-label').textContent=label;
    document.getElementById('mt-date-chip').classList.add('visible');
    document.getElementById('mt-cal-btn').classList.add('has-date');
    document.getElementById('mt-cal-label').textContent=label;
    applyFilter();
}
function clearDateFilter() {
    calSelected=null; renderCal();
    document.getElementById('mt-date-chip').classList.remove('visible');
    document.getElementById('mt-cal-btn').classList.remove('has-date');
    document.getElementById('mt-cal-label').textContent='Filter Deadline';
    applyFilter();
}
function toggleCal(e) {
    e.stopPropagation();
    const p=document.getElementById('mt-cal-popup'); p.classList.toggle('open');
    if(p.classList.contains('open')) renderCal();
}
function closeCal(){document.getElementById('mt-cal-popup').classList.remove('open');}
document.getElementById('mc-prev')?.addEventListener('click',()=>{calCur.m--;if(calCur.m<0){calCur.m=11;calCur.y--;}renderCal();});
document.getElementById('mc-next')?.addEventListener('click',()=>{calCur.m++;if(calCur.m>11){calCur.m=0;calCur.y++;}renderCal();});
document.addEventListener('click',e=>{if(!e.target.closest('.mt-cal-popup')&&!e.target.closest('#mt-cal-btn'))closeCal();});

function applyFilter() {
    const q=(document.getElementById('mt-search')?.value||'').toLowerCase().trim();
    const cards=Array.from(document.querySelectorAll('.tugas-card')); let visible=0;
    cards.forEach(c=>{
        const judul=c.dataset.judul||''; const ts=Number(c.dataset.deadline);
        const matchQ=!q||judul.includes(q);
        let matchD=true;
        if(calSelected){matchD=ts&&ts<9999999999&&new Date(ts*1000).toLocaleDateString('en-CA',{timeZone:'Asia/Jakarta'})===calSelected;}
        const show=matchQ&&matchD; c.style.display=show?'':'none'; if(show)visible++;
    });
    const cnt=document.getElementById('mt-count'); if(cnt)cnt.textContent=visible+' tugas';
    const nr=document.getElementById('mt-no-result'); if(nr)nr.style.display=visible===0&&cards.length>0?'block':'none';
    document.querySelectorAll('.tugas-card .tc-judul').forEach(el=>{
        const orig=el.dataset.orig||el.textContent.trim(); el.dataset.orig=orig;
        if(!q){el.innerHTML=orig;return;}
        const re=new RegExp('('+q.replace(/[\-\[\]{}()*+?.,\\^$|#\s]/g,'\\$&')+')','gi');
        el.innerHTML=orig.replace(re,'<mark style="background:#fef08a;color:#713f12;border-radius:2px;padding:0 1px">$1</mark>');
    });
}

document.addEventListener('DOMContentLoaded',function(){
    const inp=document.getElementById('mt-search');
    const clr=document.getElementById('mt-search-clear');
    if(!inp)return;
    const total=document.querySelectorAll('.tugas-card').length;
    const cnt=document.getElementById('mt-count'); if(cnt)cnt.textContent=total+' tugas';
    inp.addEventListener('input',function(){clr.style.display=this.value?'block':'none';applyFilter();});
    inp.addEventListener('keydown',e=>{if(e.key==='Escape'){inp.value='';clr.style.display='none';applyFilter();}});
    clr.addEventListener('click',()=>{inp.value='';clr.style.display='none';applyFilter();inp.focus();});
});

/* ══════════════════════════════════════════════════
   SEARCH — Quiz Triwulan (cari judul materi / file lama)
   ══════════════════════════════════════════════════ */
function applyFilterTw() {
    const q = (document.getElementById('mt-search-tw')?.value || '').toLowerCase().trim();
    const onlyFile = document.getElementById('mt-filter-has-file-tw')?.checked || false;
    const cards = Array.from(document.querySelectorAll('.tw-card[data-judul-tw]'));
    let visible = 0;

    cards.forEach(c => {
        const judul = c.dataset.judulTw || '';
        const hasFile = c.dataset.hasFileTw === '1';
        const matchQ = !q || judul.includes(q);
        const matchFile = !onlyFile || hasFile;
        const show = matchQ && matchFile;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });

    const cnt = document.getElementById('mt-count-tw');
    if (cnt) cnt.textContent = visible + ' materi';

    const nr = document.getElementById('mt-no-result-tw');
    if (nr) nr.style.display = (visible === 0 && cards.length > 0) ? 'block' : 'none';

    // Highlight kata yang cocok di judul
    document.querySelectorAll('.tw-card .tw-card-title').forEach(el => {
        const orig = el.dataset.orig || el.textContent.trim();
        el.dataset.orig = orig;
        if (!q) { el.innerHTML = orig; return; }
        const re = new RegExp('(' + q.replace(/[\-\[\]{}()*+?.,\\^$|#\s]/g, '\\$&') + ')', 'gi');
        el.innerHTML = orig.replace(re, '<mark style="background:#fef08a;color:#713f12;border-radius:2px;padding:0 1px">$1</mark>');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const inpTw = document.getElementById('mt-search-tw');
    const clrTw = document.getElementById('mt-search-tw-clear');
    const fileChk = document.getElementById('mt-filter-has-file-tw');
    if (!inpTw) return;

    const totalTw = document.querySelectorAll('.tw-card[data-judul-tw]').length;
    const cntTw = document.getElementById('mt-count-tw');
    if (cntTw) cntTw.textContent = totalTw + ' materi';

    inpTw.addEventListener('input', function() {
        clrTw.style.display = this.value ? 'block' : 'none';
        applyFilterTw();
    });
    inpTw.addEventListener('keydown', e => {
        if (e.key === 'Escape') { inpTw.value = ''; clrTw.style.display = 'none'; applyFilterTw(); }
    });
    clrTw.addEventListener('click', () => {
        inpTw.value = ''; clrTw.style.display = 'none'; applyFilterTw(); inpTw.focus();
    });
    fileChk?.addEventListener('change', applyFilterTw);
});
</script>
@endpush