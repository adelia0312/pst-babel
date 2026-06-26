@extends('layouts.admin')
@section('title', 'Survey — Pertanyaan')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">Dashboard</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Survey Kepuasan</strong>
@endsection

<div id="rt-toast-wrap"></div>

@push('styles')
<style>
    /* ── Base ── */
    .sv-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .sv-topbar h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; color: var(--ink); }
    .sv-topbar p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }
    .sv-actions   { display: flex; gap: 8px; align-items: center; flex-shrink: 0; }

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
    .sv-btn-sm    { height: 26px; padding: 0 10px; font-size: 11px; }
    .sv-btn-danger{ background: #fdecea; color: #c0392b; border: 1px solid #fbd5d5; }
    .sv-btn-danger:hover { background: #fbd5d5; }

    .sv-alert {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;
        font-size: 12.5px; font-weight: 500;
        background: var(--green-lt); color: var(--green); border: 1px solid #b7e4ce;
    }

    /* ── TAB ── */
    .sv-tab-bar {
        display: flex; gap: 2px; margin-bottom: 22px;
        border-bottom: 1px solid var(--rule); padding-bottom: 0;
    }
    .sv-tab-btn {
        display: inline-flex; align-items: center; gap: 7px;
        height: 36px; padding: 0 16px;
        font-size: 12.5px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; background: transparent; color: var(--ink3);
        border-bottom: 2px solid transparent; margin-bottom: -1px;
        transition: color .15s, border-color .15s;
    }
    .sv-tab-btn:hover { color: var(--ink); }
    .sv-tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); font-weight: 600; }
    .sv-tab-btn.active-int { color: #7c3aed; border-bottom-color: #7c3aed; font-weight: 600; }
    .sv-tab-pane { display: none; }
    .sv-tab-pane.active { display: block; }

    /* ── Jadwal ── */
    .sv-jadwal-grid { display: flex; flex-wrap: wrap; gap: 10px; margin-bottom: 20px; }
    .sv-jadwal-card {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 6px; padding: 11px 14px; font-size: 12px; min-width: 170px;
    }
    .sv-jadwal-card-title { font-weight: 600; color: var(--ink); margin-bottom: 5px; font-size: 12.5px; }
    .sv-jadwal-petugas { color: var(--ink2); line-height: 1.7; }
    .sv-jadwal-empty   { color: var(--ink3); font-style: italic; }
    .sv-shift { color: var(--ink3); font-size: 11px; }

    /* ── Panel ── */
    .sv-panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .sv-ph {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; border-bottom: 1px solid var(--rule);
    }
    .sv-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
    .sv-ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    /* ── Table ── */
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

    .sv-pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 3px; letter-spacing: .2px; }
    .sv-pill-blue   { background: var(--blue-lt);  color: var(--blue); }
    .sv-pill-amber  { background: var(--amber-lt); color: var(--amber); }
    .sv-pill-gray   { background: var(--wash2);    color: var(--ink3); }
    .sv-pill-green  { background: var(--green-lt); color: var(--green); }
    .sv-pill-red    { background: var(--red-lt);   color: var(--red); }
    .sv-pill-purple { background: #f5f3ff;         color: #7c3aed; }

    .sv-drag { color: var(--ink3); cursor: grab; font-size: 13px; padding-right: 4px; }

    /* ── Pengelompokan pertanyaan internal per kategori ── */
    .sv-kategori-block { border-top: 1px solid var(--rule); }
    .sv-kategori-block:first-of-type { border-top: none; }
    .sv-kategori-head {
        display: flex; align-items: center; gap: 10px;
        padding: 10px 18px; background: var(--wash);
        border-bottom: 1px solid var(--rule);
    }
    .sv-opsi-tag {
        display: inline-block; font-size: 10px; padding: 1px 7px;
        background: var(--wash2); color: var(--ink3); border-radius: 3px; margin: 2px 2px 0 0;
    }
    .sv-empty { padding: 48px 20px; text-align: center; color: var(--ink3); font-size: 13px; line-height: 1.8; }

    /* ── Mini-form "Tambah kategori baru" inline di modal pertanyaan ── */
    .sv-kategori-baru-box {
        margin-top: 10px; padding: 12px; border: 1px dashed var(--rule);
        border-radius: 6px; background: var(--wash);
    }
    .sv-kategori-baru-box .sv-form-group:last-of-type { margin-bottom: 8px; }
    .sv-kategori-baru-error { font-size: 11px; color: var(--red); margin-left: 8px; }

    /* ── RT Live ── */
    #rt-toast-wrap { position:fixed;bottom:24px;right:24px;z-index:9999;display:flex;flex-direction:column;gap:8px;pointer-events:none; }
    .rt-toast { background:var(--surface);border:1px solid var(--rule);border-left:3px solid var(--green);border-radius:8px;padding:10px 14px;min-width:230px;font-size:12px;box-shadow:0 4px 16px rgba(0,0,0,.12);animation:toast-in .25s ease;pointer-events:auto; }
    .rt-toast-name   { font-weight:600;color:var(--ink);margin-bottom:2px; }
    .rt-toast-detail { color:var(--ink3);font-size:11px; }
    @keyframes toast-in  { from{opacity:0;transform:translateX(16px)} to{opacity:1;transform:none} }
    @keyframes toast-out { from{opacity:1} to{opacity:0;transform:translateX(16px)} }
    .sb-live-count { margin-left:auto;font-size:10px;font-weight:600;background:var(--green);color:#fff;padding:1px 6px;border-radius:10px;min-width:16px;text-align:center; }
    .sv-rt-stats { display:grid;grid-template-columns:repeat(3,1fr);gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
    .sv-rt-stat { background:var(--surface);padding:14px 16px; }
    .sv-rt-stat-label { font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);margin-bottom:5px; }
    .sv-rt-stat-val { font-size:24px;font-weight:300;letter-spacing:-1px;font-family:'IBM Plex Mono',monospace;color:var(--ink);line-height:1; }
    .sv-rt-stat-sub { font-size:11px;color:var(--ink3);margin-top:3px; }
    @keyframes stat-flash { 0%{background:var(--green-lt)} 100%{background:var(--surface)} }
    .sv-rt-stat.updated { animation:stat-flash .8s ease forwards; }
    @keyframes row-in { from{background:#d1fae5;opacity:0} to{background:transparent;opacity:1} }
    .sv-new-row { animation:row-in 1.2s ease forwards; }
    @media(max-width:768px){.sv-rt-stats{grid-template-columns:repeat(2,1fr)}}

    /* ── Modal ── */
    .sv-modal-bg { display:none;position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:1000;align-items:center;justify-content:center; }
    .sv-modal { background:var(--surface);border-radius:8px;padding:24px 28px;width:100%;max-width:520px;max-height:90vh;overflow-y:auto;box-shadow:0 8px 32px rgba(0,0,0,.18); }
    .sv-modal h3 { font-size:14px;font-weight:600;color:var(--ink);margin:0 0 18px; }
    .sv-modal-footer { display:flex;gap:8px;justify-content:flex-end;margin-top:20px;padding-top:16px;border-top:1px solid var(--rule); }
    .sv-form-group { margin-bottom:14px; }
    .sv-form-label { display:block;font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--ink3);margin-bottom:5px; }
    .sv-form-control { width:100%;padding:7px 10px;font-size:12.5px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);color:var(--ink);font-family:'IBM Plex Sans',sans-serif;transition:border-color .15s; }
    .sv-form-control:focus { outline:none;border-color:var(--blue);background:var(--surface); }
    textarea.sv-form-control { resize:vertical; }
    .sv-required { color:var(--red); }

    /* ── Panel Internal Setting ── */

    .si-form-row   { display:flex;flex-direction:column;gap:5px;margin-bottom:10px; }
    .si-form-label { font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--ink3); }
    .si-form-ctrl  { height:32px;padding:0 10px;border:1px solid var(--rule);border-radius:5px;font-size:12.5px;background:var(--surface);color:var(--ink);font-family:'IBM Plex Sans',sans-serif; }
    .si-form-ctrl:focus { outline:none;border-color:var(--blue); }
    .si-hint { font-size:11px;color:var(--ink3);margin-top:3px; }

    .si-toggle-row { display:flex;align-items:center;justify-content:space-between;gap:12px;margin-bottom:12px; }
    .si-switch { position:relative;display:inline-block;width:40px;height:22px;flex-shrink:0; }
    .si-switch input { opacity:0;width:0;height:0; }
    .si-slider { position:absolute;cursor:pointer;inset:0;background:#cbd5e1;border-radius:99px;transition:.25s; }
    .si-slider:before { content:'';position:absolute;height:16px;width:16px;left:3px;bottom:3px;background:#fff;border-radius:50%;transition:.25s; }
    input:checked+.si-slider { background:var(--blue); }
    input:checked+.si-slider:before { transform:translateX(18px); }

    .si-status-bar {
        padding:10px 20px;border-bottom:1px solid var(--rule);
        display:flex;align-items:center;gap:10px;flex-wrap:wrap;
        background:var(--wash);font-size:12px;color:var(--ink2);
    }
    .si-badge-open   { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d; }
    .si-badge-closed { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:#fee2e2;color:#dc2626; }
    .si-badge-dot    { width:6px;height:6px;border-radius:50%;background:currentColor; }
    .si-override-chip { font-size:10.5px;font-weight:600;padding:2px 8px;border-radius:99px;background:#fef3c7;color:#92400e;border:1px solid #fde68a; }

    .sv-info-box {
        display:flex;gap:8px;align-items:flex-start;
        background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;
        padding:9px 12px;font-size:11.5px;color:#1e40af;line-height:1.6;margin-top:8px;
    }
    .sv-warn-box {
        display:flex;gap:8px;align-items:flex-start;
        background:#fffbeb;border:1px solid #fde68a;border-radius:6px;
        padding:9px 12px;font-size:11.5px;color:#92400e;line-height:1.6;margin-top:8px;
    }
</style>
@endpush

@section('content')

<div class="sv-topbar">
    <div>
        <h1>Survey Kepuasan</h1>
        <p>Kelola pertanyaan, pengaturan periode, dan pantau respon masuk.</p>
    </div>
    <div class="sv-actions">
        <a href="{{ route('admin.survey.template') }}" class="sv-btn sv-btn-secondary">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
            Template &amp; Link
        </a>
    </div>
</div>

@if(session('success'))
<div class="sv-alert">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- ── TAB BAR ── --}}
<div class="sv-tab-bar">
    <button class="sv-tab-btn active" id="tab-btn-ext" onclick="switchTab('ext')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87"/></svg>
        Survey Eksternal
        <span style="font-size:10px;background:var(--blue-lt);color:var(--blue);padding:1px 6px;border-radius:99px;font-weight:600">
            {{ $pertanyaan->where('jenis','!=','internal')->count() }}
        </span>
    </button>
    <button class="sv-tab-btn" id="tab-btn-int" onclick="switchTab('int')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
        Survey Internal
        <span style="font-size:10px;background:#f5f3ff;color:#7c3aed;padding:1px 6px;border-radius:99px;font-weight:600">
            {{ $pertanyaan->whereIn('jenis',['internal','semua'])->count() }}
        </span>
        @if($internalSetting['bisa_diakses'])
            <span style="font-size:9px;background:#dcfce7;color:#15803d;padding:1px 6px;border-radius:99px;font-weight:700">Buka</span>
        @endif
    </button>
</div>

{{-- ════════════════════════════════════════════════════════════
     TAB EKSTERNAL
════════════════════════════════════════════════════════════ --}}
<div class="sv-tab-pane active" id="tab-ext">

    {{-- Jadwal petugas hari ini --}}
    <div style="margin-bottom:20px">
        <div style="font-size:11px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:var(--ink3);margin-bottom:10px">
            Petugas Bertugas Hari Ini
            <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink3)">&nbsp;— otomatis dari jadwal</span>
        </div>
        <div class="sv-jadwal-grid">
            @foreach($jadwalHariIni as $entry)
            <div class="sv-jadwal-card">
                <div class="sv-jadwal-card-title">{{ $entry['wilayah']->nama }}</div>
                @if($entry['petugas']->isEmpty())
                    <div class="sv-jadwal-empty">Belum ada jadwal hari ini</div>
                @else
                    <div class="sv-jadwal-petugas">
                        @foreach($entry['petugas'] as $p)
                        {{ $p['nama'] }} <span class="sv-shift">({{ $p['shift'] }})</span><br>
                        @endforeach
                    </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>

    @php
        $today          = now()->toDateString();
        $selesaiHariIni = \App\Models\SurveyKepuasan::whereDate('created_at', $today)->where('status','selesai')->where('jenis','eksternal')->count();
        $totalSemua     = \App\Models\SurveyKepuasan::where('status','selesai')->where('jenis','eksternal')->count();
        $rataHariIni    = \App\Models\SurveyKepuasan::whereDate('created_at', $today)->where('status','selesai')->where('jenis','eksternal')->get()->map(fn($s)=>$s->rataRating())->filter()->avg();
    @endphp

    {{-- Live panel --}}
    <div class="sv-panel" style="margin-bottom:20px">
        <div class="sv-ph">
            <div style="display:flex;align-items:center;gap:10px">
                <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="color:var(--blue)"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                <div><div class="sv-ph-title">Respon Survey Eksternal Hari Ini</div></div>
            </div>
            <a href="{{ route('admin.survey.hasil') }}" class="sv-btn sv-btn-secondary sv-btn-sm">Semua Rekap →</a>
        </div>
        <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:0;border-bottom:1px solid var(--rule)">
            <div class="sv-rt-stat" style="border-right:1px solid var(--rule)">
                <div class="sv-rt-stat-label">Selesai Hari Ini</div>
                <div class="sv-rt-stat-val" id="val-sv-masuk">{{ $selesaiHariIni }}</div>
                <div class="sv-rt-stat-sub">sudah mengisi &amp; submit</div>
            </div>
            <div class="sv-rt-stat" style="border-right:1px solid var(--rule)">
                <div class="sv-rt-stat-label">Rata Rating Hari Ini</div>
                <div class="sv-rt-stat-val" id="val-sv-rata">{{ $rataHariIni ? number_format($rataHariIni,2) : '—' }}</div>
                <div class="sv-rt-stat-sub">dari responden selesai</div>
            </div>
            <div class="sv-rt-stat">
                <div class="sv-rt-stat-label">Total Semua Waktu</div>
                <div class="sv-rt-stat-val" id="val-sv-total">{{ $totalSemua }}</div>
                <div class="sv-rt-stat-sub">responden selesai</div>
            </div>
        </div>
        <div style="overflow-x:auto">
        <table class="sv-table">
            <thead><tr>
                <th>Responden</th><th>Petugas</th><th>Wilayah</th>
                <th style="width:70px">Waktu</th><th style="width:70px">Rating</th>
            </tr></thead>
            <tbody id="sv-live-tbody">
                @php $surveyToday = \App\Models\SurveyKepuasan::with(['petugas.user','wilayah'])->whereDate('created_at',$today)->where('status','selesai')->where('jenis','eksternal')->orderByDesc('id')->limit(15)->get(); @endphp
                @forelse($surveyToday as $sv)
                <tr data-id="{{ $sv->id }}">
                    <td style="font-size:12.5px">{{ $sv->nama_responden ?: 'Anonim' }}</td>
                    <td style="font-size:12px;color:var(--ink2)">{{ optional(optional($sv->petugas)->user)->name ?? '—' }}</td>
                    <td>@if($sv->wilayah)<span class="sv-pill sv-pill-blue">{{ $sv->wilayah->nama }}</span>@else<span style="color:var(--ink3)">—</span>@endif</td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:11px">{{ $sv->diisi_pada?->format('H:i') ?? '—' }}</td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:700;color:var(--blue)">{{ $sv->rataRating() ?? '—' }}</td>
                </tr>
                @empty
                <tr id="sv-empty-row"><td colspan="5" style="text-align:center;padding:28px;color:var(--ink3);font-size:12px">Belum ada responden yang menyelesaikan survey hari ini</td></tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Tabel pertanyaan eksternal --}}
    <div class="sv-panel">
        <div class="sv-ph">
            <div>
                <div class="sv-ph-title">Daftar Pertanyaan</div>
                <div class="sv-ph-sub">Ditampilkan ke pengunjung saat scan QR atau buka link survey</div>
            </div>
            <button class="sv-btn sv-btn-primary sv-btn-sm" onclick="openTambah('eksternal')">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pertanyaan
            </button>
        </div>
        @php $pEksternal = $pertanyaan->where('jenis','!=','internal')->sortBy('urutan')->values(); @endphp
        @if($pEksternal->isEmpty())
        <div class="sv-empty">Belum ada pertanyaan.<br>Klik <strong>Tambah Pertanyaan</strong> untuk memulai.</div>
        @else
        <table class="sv-table">
            <thead><tr>
                <th style="width:36px"></th>
                <th style="width:36px">#</th>
                <th>Pertanyaan</th>
                <th style="width:90px">Tipe</th>
                <th style="width:70px">Status</th>
                <th style="width:130px">Aksi</th>
            </tr></thead>
            <tbody id="sortableListExt">
                @foreach($pEksternal as $idx => $p)
                @php $jenis = $p->jenis ?? 'eksternal'; @endphp
                <tr data-id="{{ $p->id }}">
                    <td><span class="sv-drag">⠿</span></td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-size:12.5px;color:var(--ink)">{{ $p->pertanyaan }}</div>
                        @if($p->tipe==='pilihan'&&$p->opsi_pilihan)<div style="margin-top:5px">@foreach($p->opsi_pilihan as $o)<span class="sv-opsi-tag">{{$o}}</span>@endforeach</div>@endif
                    </td>
                    <td><span class="sv-pill {{ $p->tipe==='rating'?'sv-pill-blue':($p->tipe==='pilihan'?'sv-pill-amber':'sv-pill-gray') }}">{{ ucfirst($p->tipe) }}</span></td>
                    <td>
                        <form method="POST" action="{{ route('admin.survey.pertanyaan.toggle',$p->id) }}">@csrf @method('PATCH')
                            <button type="submit" class="sv-pill {{ $p->is_active?'sv-pill-green':'sv-pill-red' }}" style="border:none;cursor:pointer;font-family:inherit">{{ $p->is_active?'Aktif':'Nonaktif' }}</button>
                        </form>
                    </td>
                    <td>
                        <div style="display:flex;gap:5px">
                            <button class="sv-btn sv-btn-secondary sv-btn-sm" onclick="openEdit({{ $p->id }},{{ json_encode($p->pertanyaan) }},'{{ $p->tipe }}',{{ json_encode($p->opsi_pilihan??[]) }},{{ $p->urutan }},{{ $p->is_active?1:0 }},'{{ $jenis }}',{{ json_encode($p->kategori) }})">Edit</button>
                            <form method="POST" action="{{ route('admin.survey.pertanyaan.destroy',$p->id) }}" onsubmit="return confirm('Hapus pertanyaan ini?')">@csrf @method('DELETE')
                                <button type="submit" class="sv-btn sv-btn-danger sv-btn-sm">Hapus</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @endif
    </div>

</div>{{-- /tab-ext --}}

{{-- ════════════════════════════════════════════════════════════
     TAB INTERNAL
════════════════════════════════════════════════════════════ --}}
<div class="sv-tab-pane" id="tab-int">

    {{-- ── Pengaturan Survey Internal ── --}}
    <div class="sv-panel" style="margin-bottom:16px">

        {{-- Header status --}}
        <div class="sv-ph">
            <div>
                <div class="sv-ph-title" style="display:flex;align-items:center;gap:8px">
                    Pengaturan Survey Internal
                    @if($internalSetting['bisa_diakses'])
                        <span class="si-badge-open"><span class="si-badge-dot"></span>Terbuka</span>
                    @else
                        <span class="si-badge-closed"><span class="si-badge-dot"></span>Tertutup</span>
                    @endif
                </div>
                <div class="sv-ph-sub">
                    Periode berjalan: <strong>{{ $internalSetting['label_periode'] ?: '—' }}</strong>
                    @if($internalSetting['override_aktif'])
                        &nbsp;·&nbsp;<span style="color:#92400e;font-weight:600">⚡ Override aktif{{ $internalSetting['override_label'] ? ': '.$internalSetting['override_label'] : '' }}</span>
                    @endif
                </div>
            </div>
            <div style="display:flex;gap:8px;flex-shrink:0">
                <a href="{{ route('admin.kategori-penilaian') }}" class="sv-btn sv-btn-secondary sv-btn-sm">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" style="margin-right:4px"><path d="M3 7l9-4 9 4-9 4-9-4z"/><path d="M3 12l9 4 9-4"/><path d="M3 17l9 4 9-4"/></svg>
                    Kelola Kategori Penilaian
                </a>
                <a href="{{ route('admin.survey.internal.hasil') }}" class="sv-btn sv-btn-secondary sv-btn-sm">Lihat Hasil Penilaian →</a>
            </div>
        </div>

        {{-- Toggle buka paksa — satu baris, tanpa mode manual/otomatis --}}
        <div style="padding:14px 20px;border-top:1px solid var(--rule)">
            <form action="{{ route('admin.survey.internal.toggle-override') }}" method="POST"
                  style="display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                @csrf
                <input type="hidden" name="override_aktif" id="siOverrideVal"
                       value="{{ $internalSetting['override_aktif']?'true':'false' }}">
                <label class="si-switch" style="flex-shrink:0">
                    <input type="checkbox" id="siOverrideToggle"
                           {{ $internalSetting['override_aktif']?'checked':'' }}
                           onchange="siHandleOverrideChange(this)">
                    <span class="si-slider"></span>
                </label>
                <span style="font-size:12.5px;color:var(--ink);flex-shrink:0">
                    @if($internalSetting['override_aktif'])
                        Survey sedang <strong style="color:var(--green)">dibuka untuk pengujian</strong>
                    @else
                        Buka paksa untuk pengujian
                    @endif
                </span>
            </form>
            @if($internalSetting['override_aktif'])
            <div style="margin-top:9px;display:flex;align-items:center;gap:7px;font-size:11.5px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;border-radius:5px;padding:7px 11px">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
                Semua pegawai saat ini dapat mengisi. Matikan setelah pengujian selesai.
            </div>
            @endif
        </div>
    </div>


    {{-- Tabel pertanyaan internal — dikelompokkan per kategori --}}
    <div class="sv-panel">
        <div class="sv-ph">
            <div>
                <div class="sv-ph-title">Daftar Pertanyaan</div>
                <div class="sv-ph-sub">Ditampilkan ke pegawai saat menilai rekan satu wilayah, dikelompokkan per kategori penilaian</div>
            </div>
            <button class="sv-btn sv-btn-primary sv-btn-sm" onclick="openTambah('internal')">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
                Tambah Pertanyaan
            </button>
        </div>
        @php $pInternal = $pertanyaan->whereIn('jenis',['internal','semua'])->sortBy('urutan')->values(); @endphp
        @if($pInternal->isEmpty())
        <div class="sv-empty">
            Belum ada pertanyaan.<br>
            <span style="font-size:12px;color:var(--ink3)">Klik <strong>Tambah Pertanyaan</strong> di atas untuk menambahkan.</span>
        </div>
        @else
            @php
                // Kelompokkan berdasarkan kategori. Pertanyaan lama yang belum
                // diberi kategori (kategori = null) ditampilkan di grup terpisah
                // di paling bawah supaya admin tahu masih ada yang harus dilengkapi.
                $grupKategori = $pInternal->groupBy(fn($p) => $p->kategori ?? '__belum__');
            @endphp

            @foreach($kategoriList as $kategoriKey => $kategoriLabel)
                @php $grup = $grupKategori->get($kategoriKey, collect()); @endphp
                <div class="sv-kategori-block">
                    <div class="sv-kategori-head">
                        <span class="sv-pill sv-pill-blue">{{ $kategoriLabel }}</span>
                        <span style="font-size:11.5px;color:var(--ink3)">{{ $grup->count() }} pertanyaan</span>
                    </div>
                    @if($grup->isEmpty())
                        <div class="sv-empty" style="padding:14px 18px;font-size:12px">
                            Belum ada pertanyaan untuk kategori ini.
                        </div>
                    @else
                    <table class="sv-table">
                        <thead><tr>
                            <th style="width:36px">#</th>
                            <th>Pertanyaan</th>
                            <th style="width:90px">Tipe</th>
                            <th style="width:70px">Status</th>
                            <th style="width:130px">Aksi</th>
                        </tr></thead>
                        <tbody>
                            @foreach($grup as $idx => $p)
                            @php $jenis = $p->jenis ?? 'internal'; @endphp
                            <tr data-id="{{ $p->id }}">
                                <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">{{ $idx + 1 }}</td>
                                <td>
                                    <div style="font-size:12.5px;color:var(--ink)">{{ $p->pertanyaan }}</div>
                                    @if($p->tipe==='pilihan'&&$p->opsi_pilihan)<div style="margin-top:5px">@foreach($p->opsi_pilihan as $o)<span class="sv-opsi-tag">{{$o}}</span>@endforeach</div>@endif
                                </td>
                                <td><span class="sv-pill {{ $p->tipe==='rating'?'sv-pill-blue':($p->tipe==='pilihan'?'sv-pill-amber':'sv-pill-gray') }}">{{ ucfirst($p->tipe) }}</span></td>
                                <td>
                                    <form method="POST" action="{{ route('admin.survey.pertanyaan.toggle',$p->id) }}">@csrf @method('PATCH')
                                        <button type="submit" class="sv-pill {{ $p->is_active?'sv-pill-green':'sv-pill-red' }}" style="border:none;cursor:pointer;font-family:inherit">{{ $p->is_active?'Aktif':'Nonaktif' }}</button>
                                    </form>
                                </td>
                                <td>
                                    <div style="display:flex;gap:5px">
                                        <button class="sv-btn sv-btn-secondary sv-btn-sm" onclick="openEdit({{ $p->id }},{{ json_encode($p->pertanyaan) }},'{{ $p->tipe }}',{{ json_encode($p->opsi_pilihan??[]) }},{{ $p->urutan }},{{ $p->is_active?1:0 }},'{{ $jenis }}',{{ json_encode($p->kategori) }})">Edit</button>
                                        <form method="POST" action="{{ route('admin.survey.pertanyaan.destroy',$p->id) }}" onsubmit="return confirm('Hapus pertanyaan ini?')">@csrf @method('DELETE')
                                            <button type="submit" class="sv-btn sv-btn-danger sv-btn-sm">Hapus</button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @endif
                </div>
            @endforeach

            {{-- Pertanyaan lama yang belum diberi kategori — perlu dilengkapi admin --}}
            @php $grupBelum = $grupKategori->get('__belum__', collect()); @endphp
            @if($grupBelum->isNotEmpty())
            <div class="sv-kategori-block">
                <div class="sv-kategori-head" style="background:#fffbeb;border-color:#fde68a">
                    <span class="sv-pill" style="background:#fef3c7;color:#92400e">⚠ Belum Ada Kategori</span>
                    <span style="font-size:11.5px;color:#92400e">{{ $grupBelum->count() }} pertanyaan — klik Edit untuk melengkapi kategorinya</span>
                </div>
                <table class="sv-table">
                    <thead><tr>
                        <th style="width:36px">#</th>
                        <th>Pertanyaan</th>
                        <th style="width:90px">Tipe</th>
                        <th style="width:70px">Status</th>
                        <th style="width:130px">Aksi</th>
                    </tr></thead>
                    <tbody>
                        @foreach($grupBelum as $idx => $p)
                        @php $jenis = $p->jenis ?? 'internal'; @endphp
                        <tr data-id="{{ $p->id }}">
                            <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">{{ $idx + 1 }}</td>
                            <td>
                                <div style="font-size:12.5px;color:var(--ink)">{{ $p->pertanyaan }}</div>
                                @if($p->tipe==='pilihan'&&$p->opsi_pilihan)<div style="margin-top:5px">@foreach($p->opsi_pilihan as $o)<span class="sv-opsi-tag">{{$o}}</span>@endforeach</div>@endif
                            </td>
                            <td><span class="sv-pill {{ $p->tipe==='rating'?'sv-pill-blue':($p->tipe==='pilihan'?'sv-pill-amber':'sv-pill-gray') }}">{{ ucfirst($p->tipe) }}</span></td>
                            <td>
                                <form method="POST" action="{{ route('admin.survey.pertanyaan.toggle',$p->id) }}">@csrf @method('PATCH')
                                    <button type="submit" class="sv-pill {{ $p->is_active?'sv-pill-green':'sv-pill-red' }}" style="border:none;cursor:pointer;font-family:inherit">{{ $p->is_active?'Aktif':'Nonaktif' }}</button>
                                </form>
                            </td>
                            <td>
                                <div style="display:flex;gap:5px">
                                    <button class="sv-btn sv-btn-secondary sv-btn-sm" onclick="openEdit({{ $p->id }},{{ json_encode($p->pertanyaan) }},'{{ $p->tipe }}',{{ json_encode($p->opsi_pilihan??[]) }},{{ $p->urutan }},{{ $p->is_active?1:0 }},'{{ $jenis }}',{{ json_encode($p->kategori) }})">Edit</button>
                                    <form method="POST" action="{{ route('admin.survey.pertanyaan.destroy',$p->id) }}" onsubmit="return confirm('Hapus pertanyaan ini?')">@csrf @method('DELETE')
                                        <button type="submit" class="sv-btn sv-btn-danger sv-btn-sm">Hapus</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @endif
        @endif
    </div>

</div>{{-- /tab-int --}}

{{-- ── MODAL TAMBAH ── --}}
<div id="modalTambah" class="sv-modal-bg">
    <div class="sv-modal">
        <h3 id="modalTambahTitle">Tambah Pertanyaan</h3>
        <form method="POST" action="{{ route('admin.survey.pertanyaan.store') }}" onsubmit="return cegahSubmitJikaKategoriBelumDibuat('tambah')">
            @csrf
            <input type="hidden" name="jenis" id="tambahJenis" value="eksternal">
            <div class="sv-form-group">
                <label class="sv-form-label">Pertanyaan <span class="sv-required">*</span></label>
                <textarea name="pertanyaan" class="sv-form-control" rows="3" required maxlength="500" placeholder="Tulis pertanyaan survey..."></textarea>
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Tipe Jawaban</label>
                <input type="hidden" name="tipe" value="rating">
                <div style="height:34px;padding:0 10px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);color:var(--ink2);font-size:12.5px;display:flex;align-items:center;gap:7px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Rating (bintang 1–5)
                </div>
            </div>
            <div id="opsiSection-tambah" style="display:none">
                <div class="sv-form-group">
                    <label class="sv-form-label">Opsi Pilihan</label>
                    <div id="opsiList-tambah"></div>
                    <button type="button" class="sv-btn sv-btn-secondary sv-btn-sm" style="margin-top:8px" onclick="tambahOpsi('tambah')">+ Tambah Opsi</button>
                </div>
            </div>
            <div class="sv-form-group" id="kategoriSection-tambah" style="display:none">
                <label class="sv-form-label">Kategori Penilaian <span class="sv-required">*</span></label>
                <select name="kategori" class="sv-form-control" id="kategoriSelectTambah" onchange="cekOpsiBaruKategori(this.value,'tambah')">
                    <option value="">— Pilih kategori —</option>
                    @foreach($kategoriList as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                    <option value="__baru__">+ Tambah kategori baru…</option>
                </select>
                <div class="sv-textarea-hint" style="margin-top:5px">
                    Tentukan kategori penilaian rekan kerja yang diwakili pertanyaan ini. Wajib dipilih untuk pertanyaan Internal.
                </div>

                {{-- Mini-form muncul hanya saat admin pilih "+ Tambah kategori baru…" --}}
                <div id="kategoriBaruBox-tambah" class="sv-kategori-baru-box" style="display:none">
                    <div class="sv-form-group">
                        <label class="sv-form-label">Nama Kategori Baru</label>
                        <input type="text" id="kategoriBaruNama-tambah" class="sv-form-control" maxlength="100" placeholder="Contoh: Kepemimpinan">
                    </div>
                    <div class="sv-form-group">
                        <label class="sv-form-label">Masuk Komponen</label>
                        <select id="kategoriBaruKomponen-tambah" class="sv-form-control">
                            <option value="">— Pilih komponen —</option>
                            @foreach(\App\Models\KategoriPenilaian::KOMPONEN_LIST as $kKey => $kLabel)
                            <option value="{{ $kKey }}">{{ $kLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="sv-btn sv-btn-secondary sv-btn-sm" onclick="buatKategoriBaru('tambah')">Buat Kategori</button>
                    <span id="kategoriBaruError-tambah" class="sv-kategori-baru-error"></span>
                </div>
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Urutan</label>
                <input type="number" name="urutan" class="sv-form-control" min="0" placeholder="Otomatis jika kosong">
            </div>
            <div class="sv-modal-footer">
                <button type="button" class="sv-btn sv-btn-secondary" onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
                <button type="submit" class="sv-btn sv-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT ── --}}
<div id="modalEdit" class="sv-modal-bg">
    <div class="sv-modal">
        <h3>Edit Pertanyaan</h3>
        <form method="POST" id="editForm" onsubmit="return cegahSubmitJikaKategoriBelumDibuat('edit')">
            @csrf @method('PUT')
            <div class="sv-form-group">
                <label class="sv-form-label">Pertanyaan <span class="sv-required">*</span></label>
                <textarea name="pertanyaan" id="editPertanyaan" class="sv-form-control" rows="3" required maxlength="500"></textarea>
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Tipe Jawaban</label>
                <input type="hidden" name="tipe" id="editTipe" value="rating">
                <div style="height:34px;padding:0 10px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);color:var(--ink2);font-size:12.5px;display:flex;align-items:center;gap:7px;">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    Rating (bintang 1–5)
                </div>
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Berlaku untuk Jenis Survey <span class="sv-required">*</span></label>
                <select name="jenis" id="editJenis" class="sv-form-control" onchange="toggleKategori(this.value,'edit')">
                    <option value="eksternal">Eksternal (pengunjung)</option>
                    <option value="internal">Internal (antar pegawai)</option>
                    <option value="semua">Semua jenis</option>
                </select>
            </div>
            <div class="sv-form-group" id="kategoriSection-edit" style="display:none">
                <label class="sv-form-label">Kategori Penilaian <span class="sv-required">*</span></label>
                <select name="kategori" class="sv-form-control" id="editKategori" onchange="cekOpsiBaruKategori(this.value,'edit')">
                    <option value="">— Pilih kategori —</option>
                    @foreach($kategoriList as $key => $label)
                    <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                    <option value="__baru__">+ Tambah kategori baru…</option>
                </select>

                <div id="kategoriBaruBox-edit" class="sv-kategori-baru-box" style="display:none">
                    <div class="sv-form-group">
                        <label class="sv-form-label">Nama Kategori Baru</label>
                        <input type="text" id="kategoriBaruNama-edit" class="sv-form-control" maxlength="100" placeholder="Contoh: Kepemimpinan">
                    </div>
                    <div class="sv-form-group">
                        <label class="sv-form-label">Masuk Komponen</label>
                        <select id="kategoriBaruKomponen-edit" class="sv-form-control">
                            <option value="">— Pilih komponen —</option>
                            @foreach(\App\Models\KategoriPenilaian::KOMPONEN_LIST as $kKey => $kLabel)
                            <option value="{{ $kKey }}">{{ $kLabel }}</option>
                            @endforeach
                        </select>
                    </div>
                    <button type="button" class="sv-btn sv-btn-secondary sv-btn-sm" onclick="buatKategoriBaru('edit')">Buat Kategori</button>
                    <span id="kategoriBaruError-edit" class="sv-kategori-baru-error"></span>
                </div>
            </div>
            <div id="opsiSection-edit" style="display:none">
                <div class="sv-form-group">
                    <label class="sv-form-label">Opsi Pilihan</label>
                    <div id="opsiList-edit"></div>
                    <button type="button" class="sv-btn sv-btn-secondary sv-btn-sm" style="margin-top:8px" onclick="tambahOpsi('edit')">+ Tambah Opsi</button>
                </div>
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Urutan</label>
                <input type="number" name="urutan" id="editUrutan" class="sv-form-control" min="0">
            </div>
            <div class="sv-form-group">
                <label class="sv-form-label">Status</label>
                <select name="is_active" id="editIsActive" class="sv-form-control">
                    <option value="1">Aktif</option>
                    <option value="0">Nonaktif</option>
                </select>
            </div>
            <div class="sv-modal-footer">
                <button type="button" class="sv-btn sv-btn-secondary" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
                <button type="submit" class="sv-btn sv-btn-primary">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('scripts')
<script>
// ── Tab switch ──────────────────────────────────────────────────────
function switchTab(tab) {
    document.querySelectorAll('.sv-tab-pane').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.sv-tab-btn').forEach(b => b.classList.remove('active','active-int'));
    document.getElementById('tab-' + tab).classList.add('active');
    if (tab === 'ext') {
        document.getElementById('tab-btn-ext').classList.add('active');
    } else {
        document.getElementById('tab-btn-int').classList.add('active-int');
    }
    localStorage.setItem('sv_active_tab', tab);
}
// Restore tab dari localStorage
const savedTab = localStorage.getItem('sv_active_tab');
if (savedTab) switchTab(savedTab);

// ── Tambah pertanyaan dengan jenis preset ──────────────────────────
function openTambah(jenis) {
    document.getElementById('tambahJenis').value = jenis;
    document.getElementById('modalTambahTitle').textContent =
        jenis === 'internal' ? 'Tambah Pertanyaan Internal' : 'Tambah Pertanyaan Eksternal';
    // Kategori hanya relevan untuk pertanyaan internal/semua
    const kategoriSection = document.getElementById('kategoriSection-tambah');
    const kategoriSelect  = document.getElementById('kategoriSelectTambah');
    if (jenis === 'internal' || jenis === 'semua') {
        kategoriSection.style.display = 'block';
        kategoriSelect.required = true;
    } else {
        kategoriSection.style.display = 'none';
        kategoriSelect.required = false;
        kategoriSelect.value = '';
        document.getElementById('kategoriBaruBox-tambah').style.display = 'none';
    }
    document.getElementById('modalTambah').style.display = 'flex';
}

// ── Cegah submit form pertanyaan jika kategori masih "+ Tambah kategori
//    baru…" (admin lupa klik tombol "Buat Kategori" dulu) ──
function cegahSubmitJikaKategoriBelumDibuat(ctx) {
    const select = ctx === 'edit' ? document.getElementById('editKategori') : document.getElementById('kategoriSelectTambah');
    if (select.value === '__baru__') {
        const errorEl = document.getElementById('kategoriBaruError-' + ctx);
        errorEl.textContent = 'Klik "Buat Kategori" dulu sebelum menyimpan pertanyaan, atau pilih kategori yang sudah ada.';
        return false;
    }
    return true;
}

// ── Tampilkan/sembunyikan dropdown kategori sesuai jenis (modal Edit) ──
function toggleKategori(jenis, ctx) {
    const section = document.getElementById('kategoriSection-' + ctx);
    const select  = ctx === 'edit' ? document.getElementById('editKategori') : document.getElementById('kategoriSelectTambah');
    if (jenis === 'internal' || jenis === 'semua') {
        section.style.display = 'block';
        select.required = true;
    } else {
        section.style.display = 'none';
        select.required = false;
        select.value = '';
        // Pastikan mini-form "tambah kategori baru" juga ikut tertutup
        document.getElementById('kategoriBaruBox-' + ctx).style.display = 'none';
    }
}

// ── Munculkan mini-form "Tambah kategori baru" saat opsi itu dipilih ──
// ctx: 'tambah' atau 'edit' — dipakai di kedua modal (Tambah & Edit Pertanyaan)
function cekOpsiBaruKategori(value, ctx) {
    const box = document.getElementById('kategoriBaruBox-' + ctx);
    box.style.display = (value === '__baru__') ? 'block' : 'none';
}

// ── Buat kategori baru langsung dari modal pertanyaan (tanpa pindah halaman) ──
// Memanggil endpoint yang sama dengan halaman "Kategori Penilaian"
// (admin.kategori-penilaian.store), lalu menambahkan <option> baru ke
// dropdown kategori yang sedang dibuka dan langsung memilihnya.
function buatKategoriBaru(ctx) {
    const namaInput     = document.getElementById('kategoriBaruNama-' + ctx);
    const komponenInput = document.getElementById('kategoriBaruKomponen-' + ctx);
    const errorEl       = document.getElementById('kategoriBaruError-' + ctx);
    const select         = ctx === 'edit' ? document.getElementById('editKategori') : document.getElementById('kategoriSelectTambah');

    const nama     = namaInput.value.trim();
    const komponen = komponenInput.value;
    errorEl.textContent = '';

    if (!nama) {
        errorEl.textContent = 'Nama kategori wajib diisi.';
        return;
    }
    if (!komponen) {
        errorEl.textContent = 'Pilih komponen induknya.';
        return;
    }

    fetch('{{ route('admin.kategori-penilaian.store') }}', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content
                || document.querySelector('input[name="_token"]').value,
        },
        body: JSON.stringify({ nama: nama, komponen: komponen }),
    })
        .then(res => res.json().then(data => ({ status: res.status, body: data })))
        .then(({ status, body }) => {
            if (status !== 200 || !body.ok) {
                const pesan = body?.errors
                    ? Object.values(body.errors).flat().join(' ')
                    : (body?.message || 'Gagal membuat kategori baru.');
                errorEl.textContent = pesan;
                return;
            }
            // Sisipkan <option> baru tepat sebelum opsi "+ Tambah kategori baru…"
            const opt = document.createElement('option');
            opt.value = body.kode;
            opt.textContent = body.nama;
            const opsiTambahBaru = select.querySelector('option[value="__baru__"]');
            select.insertBefore(opt, opsiTambahBaru);
            select.value = body.kode;

            // Bersihkan & sembunyikan mini-form
            namaInput.value = '';
            komponenInput.value = '';
            document.getElementById('kategoriBaruBox-' + ctx).style.display = 'none';
        })
        .catch(() => {
            errorEl.textContent = 'Gagal terhubung ke server. Coba lagi.';
        });
}

// ── Modal helpers ──────────────────────────────────────────────────
function toggleOpsi(tipe, ctx) {
    document.getElementById('opsiSection-' + ctx).style.display = tipe === 'pilihan' ? 'block' : 'none';
}
function tambahOpsi(ctx) {
    const list = document.getElementById('opsiList-' + ctx);
    const idx  = list.children.length;
    const div  = document.createElement('div');
    div.style.cssText = 'display:flex;gap:6px;margin-bottom:6px';
    div.innerHTML = `<input type="text" name="opsi_pilihan[]" class="sv-form-control" placeholder="Opsi ${idx+1}" required>
        <button type="button" onclick="this.parentElement.remove()" class="sv-btn sv-btn-danger sv-btn-sm">×</button>`;
    list.appendChild(div);
}
function openEdit(id, pertanyaan, tipe, opsi, urutan, isActive, jenis, kategori) {
    document.getElementById('editForm').action = '/admin/survey/pertanyaan/' + id;
    document.getElementById('editPertanyaan').value = pertanyaan;
    document.getElementById('editUrutan').value = urutan;
    document.getElementById('editIsActive').value = isActive;
    document.getElementById('editJenis').value = jenis || 'eksternal';
    document.getElementById('editKategori').value = kategori || '';
    document.getElementById('kategoriBaruBox-edit').style.display = 'none';
    toggleKategori(jenis || 'eksternal', 'edit');
    toggleOpsi('rating', 'edit');
    const list = document.getElementById('opsiList-edit');
    list.innerHTML = '';
    (opsi||[]).forEach(o => {
        const div = document.createElement('div');
        div.style.cssText = 'display:flex;gap:6px;margin-bottom:6px';
        div.innerHTML = `<input type="text" name="opsi_pilihan[]" class="sv-form-control" value="${o}" required>
            <button type="button" onclick="this.parentElement.remove()" class="sv-btn sv-btn-danger sv-btn-sm">×</button>`;
        list.appendChild(div);
    });
    document.getElementById('modalEdit').style.display = 'flex';
}
['modalTambah','modalEdit'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.style.display = 'none';
    });
});

// ── Override toggle ────────────────────────────────────────────────
function siHandleOverrideChange(cb) {
    document.getElementById('siOverrideVal').value = cb.checked ? 'true' : 'false';
    // Auto-submit langsung tanpa harus klik tombol Simpan
    cb.closest('form').submit();
}

// ── RT Polling ─────────────────────────────────────────────────────
(function () {
    const POLL_URL    = '{{ route("admin.survey.polling") }}';
    const INTERVAL_MS = 7000;
    let lastId = 0;
    document.querySelectorAll('#sv-live-tbody tr[data-id]').forEach(tr => {
        const id = parseInt(tr.dataset.id);
        if (id > lastId) lastId = id;
    });
    let unreadCount = 0;
    function buildRow(s) {
        const w = s.wilayah !== '—' ? `<span class="sv-pill sv-pill-blue">${s.wilayah}</span>` : '<span style="color:var(--ink3)">—</span>';
        const r = s.rata_rating ?? '—';
        return `<tr data-id="${s.id}" class="sv-new-row">
            <td style="font-size:12.5px">${s.nama_responden}</td>
            <td style="font-size:12px;color:var(--ink2)">${s.petugas}</td>
            <td>${w}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:11px">${s.diisi_pada}</td>
            <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;font-weight:700;color:var(--blue)">${r}</td>
        </tr>`;
    }
    function showToast(s) {
        const wrap = document.getElementById('rt-toast-wrap');
        if (!wrap) return;
        const t = document.createElement('div');
        t.className = 'rt-toast';
        t.innerHTML = `<div class="rt-toast-name">${s.nama_responden}</div><div class="rt-toast-detail">Survey baru &middot; ${s.wilayah} &middot; ${s.diisi_pada}</div>`;
        wrap.appendChild(t);
        setTimeout(() => { t.style.animation='toast-out .3s ease forwards'; setTimeout(()=>t.remove(),320); }, 4500);
    }
    function updateStats(st) {
        function flash(id, val) {
            const el = document.getElementById(id);
            if (!el) return;
            const str = val != null ? String(val) : '—';
            if (el.textContent !== str) {
                el.textContent = str;
                const card = el.closest('.sv-rt-stat');
                if (card) { card.classList.remove('updated'); void card.offsetWidth; card.classList.add('updated'); }
            }
        }
        flash('val-sv-masuk', st.selesai_hari_ini);
        flash('val-sv-rata',  st.rata_hari_ini ? parseFloat(st.rata_hari_ini).toFixed(2) : '—');
        flash('val-sv-total', st.total_semua);
    }
    async function poll() {
        try {
            const res  = await fetch(`${POLL_URL}?after=${lastId}`);
            const data = await res.json();
            if (data.new_surveys?.length > 0) {
                const tbody  = document.getElementById('sv-live-tbody');
                const emptyR = document.getElementById('sv-empty-row');
                if (emptyR) emptyR.remove();
                data.new_surveys.forEach(s => {
                    if (!tbody.querySelector(`tr[data-id="${s.id}"]`)) {
                        tbody.insertAdjacentHTML('afterbegin', buildRow(s));
                        showToast(s); unreadCount++;
                    }
                });
                if (data.max_id > lastId) lastId = data.max_id;
            }
            if (data.stats) updateStats(data.stats);
        } catch(e) { console.warn('Polling error', e); }
    }
    setInterval(poll, INTERVAL_MS);
})();
</script>
@endpush