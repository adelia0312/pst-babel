@extends('layouts.admin')
@section('title', 'Survey — Rekap Hasil')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">Dashboard</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('admin.survey.pertanyaan') }}">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Rekap Hasil</strong>
@endsection

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
        border: none; transition: opacity .15s;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover  { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-sm { height: 26px; padding: 0 10px; font-size: 11px; }

    .sv-filter {
        display: flex; align-items: center; gap: 10px;
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 12px 16px; margin-bottom: 20px; flex-wrap: wrap;
    }
    .sv-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); }
    .sv-filter input {
        height: 30px; padding: 0 10px; font-size: 12px;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .sv-filter input:focus { outline: none; border-color: var(--blue); }
    .sv-filter-sep { width: 1px; height: 20px; background: var(--rule); }

    .sv-stats {
        display: grid; grid-template-columns: repeat(4,1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 24px;
    }
    .sv-stat { background: var(--surface); padding: 16px 18px; }
    .sv-stat-label { font-size: 10px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; color: var(--ink3); margin-bottom: 6px; }
    .sv-stat-val { font-size: 26px; font-weight: 300; letter-spacing: -1px; font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1; }

    /* ── ACCORDION WILAYAH ───────────────────────────── */
    .sv-accordion { margin-bottom: 12px; border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }

    .sv-acc-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 13px 16px; background: var(--wash);
        cursor: pointer; user-select: none;
        transition: background .15s;
    }
    .sv-acc-head:hover { background: var(--wash2); }

    .sv-acc-left  { display: flex; align-items: center; gap: 10px; }
    .sv-acc-name  { font-size: 13px; font-weight: 600; color: var(--ink); }
    .sv-acc-right { display: flex; align-items: center; gap: 12px; }
    .sv-acc-meta  { font-size: 11px; color: var(--ink3); display: flex; gap: 10px; align-items: center; }

    /* Chevron */
    .sv-acc-chevron {
        width: 28px; height: 28px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        background: var(--surface); border: 1px solid var(--rule);
        transition: transform .25s ease, background .15s;
        flex-shrink: 0;
    }
    .sv-acc-chevron svg { transition: transform .25s ease; }
    .sv-accordion.open .sv-acc-chevron { background: var(--blue-lt); border-color: var(--blue); }
    .sv-accordion.open .sv-acc-chevron svg { transform: rotate(180deg); }

    /* Body */
    .sv-acc-body {
        max-height: 0; overflow: hidden;
        transition: max-height .3s ease;
        background: var(--surface);
        border-top: 0px solid var(--rule);
    }
    .sv-accordion.open .sv-acc-body {
        max-height: 2000px;
        border-top: 1px solid var(--rule);
    }

    .sv-table { width: 100%; border-collapse: collapse; }
    .sv-table thead th {
        text-align: left; padding: 7px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: .9px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    .sv-table tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    .sv-table tbody tr:last-child { border-bottom: none; }
    .sv-table tbody tr:hover { background: var(--wash); }
    .sv-table tbody td { padding: 10px 16px; vertical-align: middle; font-size: 12.5px; }

    .sv-pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 3px; white-space: nowrap; }
    .sv-pill-green  { background: var(--green-lt); color: var(--green); }
    .sv-pill-amber  { background: var(--amber-lt); color: var(--amber); }
    .sv-pill-red    { background: var(--red-lt);   color: var(--red); }
    .sv-pill-gray   { background: var(--wash2);    color: var(--ink3); }
    .sv-pill-blue   { background: var(--blue-lt);  color: var(--blue); }

    .sv-empty-row td { padding: 20px 16px !important; color: var(--ink3); font-size: 12px; font-style: italic; }

    /* Expand all toggle */
    .sv-expand-bar {
        display: flex; align-items: center; justify-content: space-between;
        margin-bottom: 14px;
    }
    .sv-expand-bar span { font-size: 11px; color: var(--ink3); }
    .sv-expand-toggle {
        font-size: 11px; font-weight: 500; color: var(--blue); background: none;
        border: none; cursor: pointer; padding: 0; font-family: inherit;
        display: flex; align-items: center; gap: 4px;
    }
    .sv-expand-toggle:hover { text-decoration: underline; }

    @media(max-width:768px) { .sv-stats { grid-template-columns: repeat(2,1fr); } .sv-acc-meta { display:none; } }
</style>
@endpush

@section('content')

<div class="sv-topbar">
    <div>
        <h1>Rekap Hasil Survey</h1>
        <p>Periode: <strong>{{ \App\Helpers\PeriodeHelper::label($periode) }}</strong></p>
    </div>
    <a href="{{ route('admin.survey.pertanyaan') }}" class="sv-btn sv-btn-secondary">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kelola Pertanyaan
    </a>
</div>

{{-- Filter --}}
<form method="GET" class="sv-filter">
    <label>Periode</label>
    <input type="month" name="periode" value="{{ $periode }}">
    <div class="sv-filter-sep"></div>
    <label>Cari Petugas</label>
    <input type="text" name="search" value="{{ $search }}" placeholder="Nama petugas...">
    <button type="submit" class="sv-btn sv-btn-primary">Tampilkan</button>
    @if($search)
        <a href="{{ route('admin.survey.hasil', ['periode' => $periode]) }}" class="sv-btn sv-btn-secondary">Reset</a>
    @endif
</form>

{{-- Stats global --}}
<div class="sv-stats">
    <div class="sv-stat">
        <div class="sv-stat-label">Total Survey</div>
        <div class="sv-stat-val">{{ $totalSurvey }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Total Petugas</div>
        <div class="sv-stat-val">{{ $totalPetugas }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Rata Global</div>
        <div class="sv-stat-val">{{ $rataGlobal ? number_format($rataGlobal,2) : '—' }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Tanpa Jadwal</div>
        <div class="sv-stat-val">{{ $surveyTanpaJadwal }}</div>
    </div>
</div>

{{-- Expand / Collapse all --}}
@if(count($dataPerWilayah))
<div class="sv-expand-bar">
    <span>{{ count($dataPerWilayah) }} wilayah · klik untuk expand</span>
    <button class="sv-expand-toggle" onclick="toggleAll()" id="toggleAllBtn">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
        Buka Semua
    </button>
</div>
@endif

{{-- Per Wilayah — ACCORDION --}}
@forelse($dataPerWilayah as $idx => $item)
<div class="sv-accordion" id="acc-{{ $idx }}">

    {{-- Header klik --}}
    <div class="sv-acc-head" onclick="toggleAcc({{ $idx }})">
        <div class="sv-acc-left">
            {{-- Chevron --}}
            <div class="sv-acc-chevron">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
                    <polyline points="6 9 12 15 18 9"/>
                </svg>
            </div>
            {{-- Nama wilayah --}}
            <div>
                <div class="sv-acc-name">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:inline;margin-right:4px;vertical-align:-1px">
                        <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                    </svg>
                    {{ $item['wilayah']->nama }}
                </div>
            </div>
        </div>

        <div class="sv-acc-right">
            <div class="sv-acc-meta">
                <span>{{ $item['petugas']->count() }} petugas</span>
                <span>{{ $item['total_responden'] }} responden</span>
            </div>
            @if($item['rata_kepuasan'])
            <span class="sv-pill {{ $item['rata_kepuasan'] >= 4 ? 'sv-pill-green' : ($item['rata_kepuasan'] >= 3 ? 'sv-pill-amber' : 'sv-pill-red') }}">
                ★ {{ number_format($item['rata_kepuasan'],2) }}
            </span>
            @else
            <span class="sv-pill sv-pill-gray">Belum ada data</span>
            @endif
        </div>
    </div>

    {{-- Body: tabel petugas --}}
    <div class="sv-acc-body" id="body-{{ $idx }}">
        <table class="sv-table">
            <thead>
                <tr>
                    <th>Nama Petugas</th>
                    <th style="width:120px">Responden</th>
                    <th style="width:160px">Rata Kepuasan</th>
                    <th style="width:90px">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($item['petugas'] as $row)
                <tr>
                    <td>
                        <div style="font-weight:600;color:var(--ink)">{{ $row['user']->name }}</div>
                        <div style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">{{ $row['petugas']->nip ?? '-' }}</div>
                    </td>
                    <td style="font-family:'IBM Plex Mono',monospace;font-size:12px">{{ $row['jumlah_responden'] }}</td>
                    <td>
                        @if($row['rata_kepuasan'])
                        <span class="sv-pill {{ $row['rata_kepuasan'] >= 4 ? 'sv-pill-green' : ($row['rata_kepuasan'] >= 3 ? 'sv-pill-amber' : 'sv-pill-red') }}">
                            ★ {{ number_format($row['rata_kepuasan'],2) }} / 5
                        </span>
                        @else
                        <span class="sv-pill sv-pill-gray">Belum ada data</span>
                        @endif
                    </td>
                    <td>
                        <a href="{{ route('admin.survey.hasil.detail', ['petugasId' => $row['petugas']->id, 'periode' => $periode]) }}"
                           class="sv-btn sv-btn-secondary sv-btn-sm">Detail</a>
                    </td>
                </tr>
                @empty
                <tr class="sv-empty-row"><td colspan="4">Tidak ada petugas aktif di wilayah ini.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

</div>
@empty
<div style="padding:48px 20px;text-align:center;color:var(--ink3);font-size:13px">
    Belum ada data wilayah terdaftar.
</div>
@endforelse

@endsection

@push('scripts')
<script>
function toggleAcc(idx) {
    const el = document.getElementById('acc-' + idx);
    el.classList.toggle('open');
    syncToggleAllBtn();
}

function toggleAll() {
    const accs = document.querySelectorAll('.sv-accordion');
    const anyOpen = [...accs].some(a => a.classList.contains('open'));
    accs.forEach(a => anyOpen ? a.classList.remove('open') : a.classList.add('open'));
    syncToggleAllBtn();
}

function syncToggleAllBtn() {
    const btn  = document.getElementById('toggleAllBtn');
    if (!btn) return;
    const anyOpen = [...document.querySelectorAll('.sv-accordion')].some(a => a.classList.contains('open'));
    btn.innerHTML = anyOpen
        ? `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="18 15 12 9 6 15"/></svg> Tutup Semua`
        : `<svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg> Buka Semua`;
}

// Jika ada search result, auto-open semua accordion yg match
@if($search)
document.querySelectorAll('.sv-accordion').forEach(a => a.classList.add('open'));
syncToggleAllBtn();
@endif
</script>
@endpush