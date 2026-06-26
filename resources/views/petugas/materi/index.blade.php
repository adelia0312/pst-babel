@extends('layouts.petugas')

@section('title', 'Materi & Tugas')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <strong>Materi &amp; Tugas</strong>
@endsection

@section('content')

<style>
/* ── Page header — sama dengan Laporan Harian ── */
.page-head {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
    gap: 12px; flex-wrap: wrap;
}
.page-head h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; color: var(--ink); }
.page-head p  { font-size: 12.5px; color: var(--ink3); margin-top: 4px; }

/* ── Progress bar ── */
.progress-bar-wrap {
    background: var(--surface); border: 1px solid var(--rule); border-radius: 8px;
    padding: 14px 18px; margin-bottom: 20px;
    display: flex; align-items: center; gap: 14px; flex-wrap: wrap;
}
.progress-label { font-size: 12px; font-weight: 500; color: var(--ink); flex-shrink: 0; }
.progress-track { flex: 1; min-width: 100px; height: 7px; background: var(--wash2); border-radius: 4px; overflow: hidden; }
.progress-fill  { height: 100%; background: var(--green); border-radius: 4px; transition: width .6s ease; }
.progress-pct   { font-size: 13px; font-weight: 700; font-family: 'IBM Plex Mono', monospace; color: var(--green); flex-shrink: 0; }

/* ── Stats — SAMA PERSIS dengan Laporan Harian ── */
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

/* ── Toolbar search + kalender — sama dengan koordinator ── */
.mt-toolbar {
    display: flex; align-items: center; gap: 10px;
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 10px; padding: 10px 14px;
    margin-bottom: 16px; flex-wrap: wrap;
}
.mt-search-wrap {
    display: flex; align-items: center; gap: 8px;
    flex: 1; min-width: 200px;
    background: var(--wash); border: 1px solid var(--rule);
    border-radius: 7px; padding: 7px 11px;
    transition: border-color .15s;
}
.mt-search-wrap:focus-within { border-color: var(--blue); background: #fff; }
.mt-search-wrap svg { color: var(--ink3); flex-shrink:0; }
.mt-search-input {
    flex:1; border:none; background:none; outline:none;
    font-size: 12.5px; color: var(--ink);
    font-family: 'IBM Plex Sans', sans-serif;
}
.mt-search-input::placeholder { color: var(--ink3); }
.mt-search-clear {
    background:none; border:none; cursor:pointer; padding:0;
    color: var(--ink3); display:none; line-height:1; transition: color .12s;
}
.mt-search-clear:hover { color: var(--ink); }
.mt-toolbar-sep { width:1px; height:24px; background: var(--rule); flex-shrink:0; }

/* Filter status */
.mt-status-btn {
    display: inline-flex; align-items: center; gap: 5px;
    height: 34px; padding: 0 13px;
    border: 1px solid var(--rule); border-radius: 7px;
    background: var(--wash); color: var(--ink2);
    font-size: 12px; font-weight: 500; font-family: 'IBM Plex Sans', sans-serif;
    cursor: pointer; white-space: nowrap; transition: all .12s;
}
.mt-status-btn:hover { border-color: var(--blue); color: var(--blue); }
.mt-status-btn.active { background: var(--blue-lt); border-color: var(--blue); color: var(--blue); }

/* Kalender picker trigger */
.mt-cal-btn {
    display: inline-flex; align-items: center; gap: 7px;
    height: 34px; padding: 0 13px;
    border: 1px solid var(--rule); border-radius: 7px;
    background: var(--wash); color: var(--ink2);
    font-size: 12px; font-weight: 500; font-family: 'IBM Plex Sans', sans-serif;
    cursor: pointer; white-space: nowrap; transition: all .12s; position: relative;
}
.mt-cal-btn:hover { border-color: var(--blue); color: var(--blue); }
.mt-cal-btn.has-date { background: var(--blue-lt); border-color: var(--blue); color: var(--blue); }

/* Kalender dropdown */
.mt-cal-popup {
    position: absolute; z-index: 999;
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 10px; box-shadow: 0 8px 32px rgba(0,0,0,.13);
    padding: 14px; display: none; min-width: 240px;
}
.mt-cal-popup.open { display: block; }
.mc-head { display:flex; align-items:center; justify-content:space-between; margin-bottom:10px; }
.mc-month { font-size: 12px; font-weight: 600; color: var(--ink); }
.mc-nav {
    background:none; border:none; cursor:pointer;
    color: var(--ink3); font-size:16px; padding:0 4px; line-height:1; transition: color .1s;
}
.mc-nav:hover { color: var(--ink); }
.mc-days-hdr { display:grid; grid-template-columns:repeat(7,1fr); margin-bottom:4px; }
.mc-days-hdr span { text-align:center; font-size:9.5px; font-weight:600; color: var(--ink3); letter-spacing:.4px; padding:2px 0; }
.mc-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; }
.mc-day {
    text-align:center; font-size:11px; padding:5px 2px;
    border-radius:5px; cursor:pointer; color: var(--ink2);
    font-family:'IBM Plex Mono',monospace; transition:all .1s; line-height:1.3;
}
.mc-day:hover { background: var(--blue-lt); color: var(--blue); }
.mc-day.today { background: var(--wash2); font-weight:700; }
.mc-day.has-deadline { background: var(--amber-lt); color: var(--amber); font-weight: 600; position:relative; }
.mc-day.has-deadline::after { content:''; position:absolute; bottom:2px; left:50%; transform:translateX(-50%); width:3px; height:3px; border-radius:50%; background: var(--amber); }
.mc-day.selected { background: var(--blue); color: #fff; font-weight:700; }
.mc-day.selected.has-deadline { background: var(--blue); color:#fff; }
.mc-day.other-month { color: var(--rule); cursor:default; }
.mc-day.other-month:hover { background:none; }

/* Date chip */
.mt-date-chip {
    display:none; align-items:center; gap:5px;
    background: var(--blue-lt); border: 1px solid rgba(26,86,219,.2);
    color: var(--blue); border-radius:20px;
    font-size: 11px; font-weight:600; padding: 3px 10px; white-space:nowrap;
}
.mt-date-chip.visible { display:inline-flex; }
.mt-date-chip-clear { background:none; border:none; cursor:pointer; padding:0; color: var(--blue); line-height:1; opacity:.7; }
.mt-date-chip-clear:hover { opacity:1; }
.mt-result-count { margin-left: auto; font-size: 11px; color: var(--ink3); font-family: 'IBM Plex Mono', monospace; white-space:nowrap; }

/* ── Tugas list — compact rows seperti Laporan Harian ── */
.tugas-list { display: flex; flex-direction: column; gap: 6px; }

.tugas-card {
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 7px; overflow: hidden;
    transition: border-color .15s, box-shadow .15s;
}
.tugas-card:hover { border-color: #c4c9d4; box-shadow: 0 1px 8px rgba(0,0,0,.05); }

/* Aksen kiri per status */
.card-sudah    { border-left: 3px solid var(--green); }
.card-belum    { border-left: 3px solid var(--red); }
.card-terlambat{ border-left: 3px solid var(--amber); }

/* Row utama */
.card-main { display: flex; align-items: stretch; }

/* Blok tanggal deadline — seperti date block laporan harian */
.card-date-block {
    flex-shrink: 0; width: 62px;
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 14px 6px; border-right: 1px solid var(--rule); gap: 0;
}
.cdb-day { font-size: 22px; font-weight: 300; letter-spacing: -1.5px; font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1; }
.cdb-mon { font-size: 10px; font-weight: 500; color: var(--ink3); margin-top: 3px; text-align: center; }
.cdb-no-dl { font-size: 10px; color: var(--ink3); text-align: center; line-height: 1.3; }

/* Konten tengah */
.card-info { flex: 1; min-width: 0; padding: 12px 16px; display: flex; flex-direction: column; justify-content: center; gap: 5px; }
.card-info-row1 { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
.card-title { font-size: 13px; font-weight: 600; color: var(--ink); }
.card-meta  { font-size: 11.5px; color: var(--ink3); display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
.meta-tag {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; background: var(--wash2); color: var(--ink3);
    padding: 2px 7px; border-radius: 4px; border: 1px solid var(--rule);
}
.meta-tag.dl-lewat { background: var(--red-lt); color: var(--red); border-color: #fca5a544; }

/* Skor inline */
.skor-badge {
    display: inline-flex; align-items: center; gap: 6px;
    background: var(--green-lt); border: 1px solid #86efac44;
    border-radius: 5px; padding: 3px 10px;
    font-size: 11px; color: var(--green); font-weight: 500;
}
.skor-badge strong { font-family: 'IBM Plex Mono', monospace; font-size: 13px; font-weight: 600; }

/* Status pill — sama dengan laporan harian */
.status-pill {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 600; padding: 2px 9px;
    border-radius: 3px; white-space: nowrap;
}
.pill-sudah     { background: var(--green-lt); color: var(--green); border: 1px solid #86efac66; }
.pill-belum     { background: var(--red-lt);   color: var(--red);   border: 1px solid #fca5a466; }
.pill-terlambat { background: #fffbf0; color: #b45309; border: 1px solid #fde68a; }

/* Aksi kanan */
.card-actions {
    flex-shrink: 0; display: flex; align-items: center;
    padding: 0 16px; border-left: 1px solid var(--rule); gap: 6px;
}
.btn-sm-act {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 500; padding: 6px 12px;
    border-radius: 5px; text-decoration: none; white-space: nowrap;
    transition: opacity .12s; cursor: pointer; border: 1px solid var(--rule);
}
.btn-act-kerjakan { background: var(--blue); color: #fff; border-color: var(--blue); }
.btn-act-lihat    { background: var(--wash); color: var(--ink2); }
.btn-sm-act:hover { opacity: .82; }

/* No result */
.mt-no-result { text-align:center; padding: 48px 20px; color: var(--ink3); display: none; }
.mt-no-result svg { margin: 0 auto 12px; display:block; opacity:.3; }
.mt-no-result p { font-size: 13px; }

/* Empty state */
.empty-card {
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 12px; padding: 64px 20px; text-align: center;
}
.empty-icon { width: 64px; height: 64px; background: var(--wash); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 16px; }
.empty-card h3 { font-size: 15px; font-weight: 600; color: var(--ink); margin-bottom: 6px; }
.empty-card p  { font-size: 12.5px; color: var(--ink3); max-width: 300px; margin: 0 auto; line-height: 1.6; }
</style>

@php
    $totalSudah    = $tugasList->where('statusPetugas', 'sudah')->count();
    $totalBelum    = $tugasList->where('statusPetugas', 'belum')->count();
    $totalTerlambat= $tugasList->where('statusPetugas', 'terlambat')->count();
    $totalAll      = $tugasList->count();
    $pctSelesai    = $totalAll > 0 ? round(($totalSudah / $totalAll) * 100) : 0;
@endphp

{{-- PAGE HEADER --}}
<div class="page-head">
    <div>
        <h1>Materi &amp; Tugas</h1>
        <p>Kerjakan tugas yang diberikan Admin sebelum batas waktu (deadline)</p>
    </div>
</div>

{{-- TAB: Reguler vs Triwulan --}}
<div style="display:flex;gap:0;border-bottom:2px solid var(--rule);margin-bottom:22px">
    <button class="main-tw-tab active" id="tab-btn-reguler" onclick="switchMainTab('reguler', this)">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
        Tugas Reguler
        <span style="font-size:10px;background:var(--wash2);color:var(--ink3);padding:1px 6px;border-radius:3px">{{ $tugasList->count() }}</span>
    </button>
    <button class="main-tw-tab" id="tab-btn-triwulan" onclick="switchMainTab('triwulan', this)">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Quiz Triwulan
        @php $twCount = $materiTriwulanList->count(); @endphp
        <span style="font-size:10px;background:{{ $twCount > 0 ? 'var(--blue-lt)' : 'var(--wash2)' }};color:{{ $twCount > 0 ? 'var(--blue)' : 'var(--ink3)' }};padding:1px 6px;border-radius:3px">{{ $twCount }}</span>
        @if($bisaIsiTriwulan && $twCount > 0)
            <span style="font-size:9.5px;background:#dcfce7;color:#15803d;padding:1px 7px;border-radius:99px;font-weight:600;display:flex;align-items:center;gap:3px">
                <span style="width:5px;height:5px;border-radius:50%;background:#15803d;animation:pulse-dot 2s ease infinite;display:inline-block"></span>
                Terbuka
            </span>
        @endif
    </button>
</div>

<style>
.main-tw-tab {
    padding:9px 20px; font-size:12.5px; font-weight:500; color:var(--ink3);
    background:none; border:none; border-bottom:2px solid transparent;
    margin-bottom:-2px; cursor:pointer; display:flex; align-items:center; gap:7px;
    transition:color .12s,border-color .12s; font-family:'IBM Plex Sans',sans-serif;
}
.main-tw-tab:hover { color:var(--ink); }
.main-tw-tab.active { color:var(--blue); border-bottom-color:var(--blue); font-weight:600; }
@keyframes pulse-dot { 0%,100%{opacity:1;transform:scale(1)} 50%{opacity:.5;transform:scale(.7)} }
</style>

{{-- ─── PANEL TRIWULAN ─────────────────────── --}}
<div id="panel-triwulan" style="display:none">
    @php
        preg_match('/^(\d{4})-TW(\d)$/', $periodeTriwulanSekarang ?? '', $pm);
        $twLabel = isset($pm[1]) ? "Triwulan {$pm[2]} Tahun {$pm[1]}" : ($periodeTriwulanSekarang ?? '');
    @endphp

    {{-- Status banner --}}
    @if($bisaIsiTriwulan)
    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:7px;font-size:12px;color:#1e40af;background:#eff6ff;border:1px solid #bfdbfe;margin-bottom:16px;line-height:1.7">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div>
            <strong>Quiz Triwulan Terbuka!</strong> — Periode <strong>{{ $twLabel }}</strong><br>
            Kerjakan quiz yang tersedia sebelum periode ditutup oleh admin.
        </div>
    </div>
    @else
    <div style="display:flex;align-items:flex-start;gap:10px;padding:12px 14px;border-radius:7px;font-size:12px;color:#92400e;background:#fffbeb;border:1px solid #fde68a;margin-bottom:16px;line-height:1.7">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:2px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        <div>
            <strong>Quiz Triwulan Belum Dibuka.</strong> — Periode <strong>{{ $twLabel }}</strong><br>
            Quiz akan bisa diisi setelah Admin mengaktifkan Survey Internal. Materi tetap bisa dibaca.
        </div>
    </div>
    @endif

    @if($materiTriwulanList->isEmpty())
    <div style="text-align:center;padding:48px 20px;background:var(--surface);border:1px solid var(--rule);border-radius:10px;color:var(--ink3)">
        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24" style="margin:0 auto 10px;display:block;opacity:.3">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <p style="font-size:13px">Belum ada materi triwulan untuk periode ini.<br>Koordinator akan menambahkan materi segera.</p>
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:8px">
        @foreach($materiTriwulanList as $mt)
        @php
            $jaw      = $jawabanTriwulanMap->get($mt->id);
            $sudah    = $jaw && $jaw->status === 'sudah';
            $skor     = $jaw?->skor;
        @endphp
        <div style="background:var(--surface);border:1px solid var(--rule);border-left:3px solid {{ $sudah ? 'var(--green)' : 'var(--rule)' }};border-radius:7px;overflow:hidden;transition:box-shadow .15s" class="tw-card-item">
            <div style="display:flex;align-items:stretch">
                {{-- Icon --}}
                <div style="flex-shrink:0;width:56px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:14px 6px;border-right:1px solid var(--rule);gap:4px">
                    <svg width="18" height="18" fill="none" stroke="{{ $sudah ? 'var(--green)' : 'var(--ink3)' }}" stroke-width="1.6" viewBox="0 0 24 24">
                        <rect x="3" y="4" width="18" height="18" rx="2"/>
                        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                        <line x1="3" y1="10" x2="21" y2="10"/>
                    </svg>
                    @if($sudah)
                    <span style="font-size:9px;color:var(--green);font-weight:700;text-align:center">Selesai</span>
                    @else
                    <span style="font-size:9px;color:var(--ink3);text-align:center">{{ $mt->quiz->count() }} soal</span>
                    @endif
                </div>
                {{-- Info --}}
                <div style="flex:1;min-width:0;padding:12px 16px;display:flex;flex-direction:column;justify-content:center;gap:5px">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-size:13px;font-weight:600;color:var(--ink)">{{ $mt->judul }}</span>
                        @if($sudah)
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:2px 9px;border-radius:3px;background:var(--green-lt);color:var(--green);border:1px solid #86efac66">
                                <svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                Selesai
                            </span>
                        @elseif($bisaIsiTriwulan)
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:2px 9px;border-radius:3px;background:#eff6ff;color:var(--blue);border:1px solid #bfdbfe">Bisa Dikerjakan</span>
                        @else
                            <span style="display:inline-flex;align-items:center;gap:4px;font-size:11px;font-weight:600;padding:2px 9px;border-radius:3px;background:var(--wash2);color:var(--ink3)">Belum Dibuka</span>
                        @endif
                    </div>
                    <div style="font-size:11.5px;color:var(--ink3);display:flex;align-items:center;gap:10px;flex-wrap:wrap">
                        <span>{{ $mt->quiz->count() }} soal</span>
                        @if($sudah && $skor !== null)
                        <span style="display:inline-flex;align-items:center;gap:4px;background:var(--green-lt);color:var(--green);padding:2px 8px;border-radius:4px;font-size:11px;font-weight:600">
                            Skor: {{ $skor }}/100
                        </span>
                        @endif
                        @if($mt->file || $mt->files->isNotEmpty())
                        <span>· Ada file materi</span>
                        @endif
                    </div>
                </div>
                {{-- Aksi --}}
                <div style="flex-shrink:0;display:flex;align-items:center;padding:0 16px;border-left:1px solid var(--rule)">
                    <a href="{{ route('petugas.materi.triwulan.show', $mt->id) }}"
                       style="display:inline-flex;align-items:center;gap:5px;font-size:11.5px;font-weight:500;padding:6px 12px;border-radius:5px;text-decoration:none;transition:opacity .12s;border:1px solid {{ $sudah ? 'var(--rule)' : 'var(--blue)' }};background:{{ $sudah ? 'var(--wash)' : 'var(--blue)' }};color:{{ $sudah ? 'var(--ink2)' : '#fff' }}">
                        @if($sudah)
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat Hasil
                        @elseif($bisaIsiTriwulan)
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                        Kerjakan
                        @else
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat Materi
                        @endif
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ─── PANEL REGULER ─────────────────────── --}}
<div id="panel-reguler">

{{-- PROGRESS BAR --}}
@if($totalAll > 0)
<div class="progress-bar-wrap">
    <span class="progress-label">Progress Penyelesaian</span>
    <div class="progress-track">
        <div class="progress-fill" style="width:{{ $pctSelesai }}%"></div>
    </div>
    <span class="progress-pct">{{ $pctSelesai }}%</span>
    <span style="font-size:11.5px;color:var(--ink3)">{{ $totalSudah }} dari {{ $totalAll }} tugas selesai</span>
</div>
@endif

{{-- STATS — SAMA dengan Laporan Harian --}}
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Total Tugas</div>
        <div class="cl-stat-val">{{ $totalAll }}</div>
        <div class="cl-stat-sub">seluruh tugas</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Selesai</div>
        <div class="cl-stat-val" style="color:var(--green)">{{ $totalSudah }}</div>
        <div class="cl-stat-sub">sudah dikerjakan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $totalAll > 0 ? round($totalSudah/$totalAll*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Belum Dikerjakan</div>
        <div class="cl-stat-val" style="color:var(--red)">{{ $totalBelum }}</div>
        <div class="cl-stat-sub">perlu diselesaikan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $totalAll > 0 ? round($totalBelum/$totalAll*100) : 0 }}%;background:var(--red)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Terlambat</div>
        <div class="cl-stat-val" style="color:var(--amber)">{{ $totalTerlambat }}</div>
        <div class="cl-stat-sub">lewat batas waktu</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $totalAll > 0 ? round($totalTerlambat/$totalAll*100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
</div>

{{-- TOOLBAR SEARCH + FILTER --}}
@if(!$tugasList->isEmpty())
<div class="mt-toolbar">
    {{-- Search --}}
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

    {{-- Kalender filter deadline --}}
    <div style="position:relative;">
        <button class="mt-cal-btn" id="mt-cal-btn" onclick="toggleCal(event)">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/>
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

    {{-- Chip tanggal aktif --}}
    <span class="mt-date-chip" id="mt-date-chip">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        <span id="mt-date-chip-label"></span>
        <button class="mt-date-chip-clear" onclick="clearDateFilter()" title="Hapus filter">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
            </svg>
        </button>
    </span>

    <span class="mt-result-count" id="mt-count"></span>
</div>
@endif

{{-- No result --}}
<div class="mt-no-result" id="mt-no-result">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
        <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
    </svg>
    <p>Tidak ada tugas yang cocok dengan pencarian atau filter.</p>
</div>

{{-- TUGAS LIST --}}
@if($tugasList->isEmpty())
<div class="empty-card">
    <div class="empty-icon">
        <svg width="28" height="28" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="9" y1="13" x2="15" y2="13"/>
            <line x1="9" y1="17" x2="12" y2="17"/>
        </svg>
    </div>
    <h3>Belum ada tugas dari Admin</h3>
    <p>Tugas akan muncul di sini jika Admin sudah membuat &amp; mempublikasikannya.</p>
</div>
@else
<div class="tugas-list">
    @foreach($tugasList as $tugas)
    @php
        $dlLewat   = $tugas->deadline && now()->gt($tugas->deadline);
        $cardClass = match($tugas->statusPetugas) {
            'sudah'     => 'card-sudah',
            'terlambat' => 'card-terlambat',
            default     => 'card-belum',
        };
        $pillClass = match($tugas->statusPetugas) {
            'sudah'     => 'pill-sudah',
            'terlambat' => 'pill-terlambat',
            default     => 'pill-belum',
        };
        $pillIcons = [
            'sudah'     => '<svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>',
            'terlambat' => '<svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>',
            'belum'     => '<svg width="9" height="9" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="15" y1="9" x2="9" y2="15"/><line x1="9" y1="9" x2="15" y2="15"/></svg>',
        ];
        $dlTimestamp = $tugas->deadline ? $tugas->deadline->timestamp : 9999999999;
    @endphp

    <div class="tugas-card {{ $cardClass }}"
         data-judul="{{ strtolower($tugas->judul) }}"
         data-status="{{ $tugas->statusPetugas }}"
         data-deadline="{{ $dlTimestamp }}">
        <div class="card-main">

            {{-- Date block deadline --}}
            <div class="card-date-block">
                @if($tugas->deadline)
                    <div class="cdb-day" style="{{ $dlLewat ? 'color:var(--red)' : '' }}">{{ $tugas->deadline->format('d') }}</div>
                    <div class="cdb-mon">{{ $tugas->deadline->translatedFormat('M') }}<br>
                        <span style="font-size:9px;letter-spacing:.3px;color:var(--ink3)">{{ $tugas->deadline->format('Y') }}</span>
                    </div>
                @else
                    <div class="cdb-no-dl">—<br><span style="font-size:9px">no dl</span></div>
                @endif
            </div>

            {{-- Info --}}
            <div class="card-info">
                <div class="card-info-row1">
                    <span class="card-title">{{ $tugas->judul }}</span>
                    <span class="status-pill {{ $pillClass }}">
                        {!! $pillIcons[$tugas->statusPetugas] ?? $pillIcons['belum'] !!}
                        {{ $tugas->statusLabel }}
                    </span>
                </div>

                <div class="card-meta">
                    @if($tugas->deadline)
                    <span class="{{ $dlLewat ? 'meta-tag dl-lewat' : '' }}" style="{{ !$dlLewat ? 'color:var(--ink3)' : '' }}">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:2px">
                            <rect x="3" y="4" width="18" height="18" rx="2"/>
                            <line x1="16" y1="2" x2="16" y2="6"/>
                            <line x1="8" y1="2" x2="8" y2="6"/>
                            <line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        {{ $tugas->deadline->format('d M Y') }}{{ $dlLewat ? ' · Lewat' : '' }}
                    </span>
                    @endif

                    @if($tugas->quiz->count())
                    <span>
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:2px">
                            <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
                        </svg>
                        {{ $tugas->quiz->count() }} soal quiz
                    </span>
                    @endif

                    @if($tugas->file || $tugas->files->isNotEmpty())
                    <span>
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:2px">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        Materi
                    </span>
                    @endif

                    @if($tugas->link)
                    <span>
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:2px">
                            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
                        </svg>
                        Link
                    </span>
                    @endif

                    @if($tugas->statusPetugas !== 'belum' && $tugas->jawabanData && $tugas->jawabanData->skor !== null)
                    <span class="skor-badge">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                        Skor: <strong>{{ $tugas->jawabanData->skor }}</strong>/100
                    </span>
                    @endif
                </div>
            </div>

            {{-- Aksi --}}
            <div class="card-actions">
                <a href="{{ route('petugas.materi.show', $tugas->id) }}"
                   class="btn-sm-act {{ $tugas->statusPetugas === 'belum' ? 'btn-act-kerjakan' : 'btn-act-lihat' }}">
                    @if($tugas->statusPetugas === 'belum')
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="5 3 19 12 5 21 5 3"/></svg>
                    Kerjakan
                    @else
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                    Lihat Hasil
                    @endif
                </a>
            </div>
        </div>
    </div>
    @endforeach
</div>
@endif

</div>{{-- /panel-reguler --}}

<script>
const BULAN_ID    = ['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
const BULAN_SHORT = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

/* ── Kalender ── */
function getDeadlineDates() {
    const dates = new Set();
    document.querySelectorAll('.tugas-card[data-deadline]').forEach(c => {
        const ts = Number(c.dataset.deadline);
        if (ts && ts < 9999999999) {
            dates.add(new Date(ts * 1000).toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' }));
        }
    });
    return dates;
}

let calCur = (function() {
    const s = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
    const [y, m] = s.split('-').map(Number);
    return { y, m: m - 1 };
})();
const calToday = new Date().toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
let calSelected = null;
let activeStatus = 'semua';

function renderCal() {
    const deadlines = getDeadlineDates();
    document.getElementById('mc-month-label').textContent = BULAN_ID[calCur.m] + ' ' + calCur.y;
    const grid = document.getElementById('mc-grid');
    grid.innerHTML = '';
    const firstDay = new Date(calCur.y, calCur.m, 1).getDay();
    const daysInMonth = new Date(calCur.y, calCur.m + 1, 0).getDate();
    const daysInPrev  = new Date(calCur.y, calCur.m, 0).getDate();
    for (let i = firstDay - 1; i >= 0; i--) {
        const el = document.createElement('div');
        el.className = 'mc-day other-month'; el.textContent = daysInPrev - i; grid.appendChild(el);
    }
    for (let d = 1; d <= daysInMonth; d++) {
        const key = calCur.y + '-' + String(calCur.m + 1).padStart(2,'0') + '-' + String(d).padStart(2,'0');
        const el = document.createElement('div');
        el.className = 'mc-day'; el.textContent = d;
        if (key === calToday)    el.classList.add('today');
        if (deadlines.has(key)) el.classList.add('has-deadline');
        if (key === calSelected) el.classList.add('selected');
        el.addEventListener('click', () => selectDate(key, d));
        grid.appendChild(el);
    }
    const rem = (firstDay + daysInMonth) % 7 === 0 ? 0 : 7 - ((firstDay + daysInMonth) % 7);
    for (let d = 1; d <= rem; d++) {
        const el = document.createElement('div');
        el.className = 'mc-day other-month'; el.textContent = d; grid.appendChild(el);
    }
}

function selectDate(key, d) {
    calSelected = key; renderCal(); closeCal();
    const [y, m] = key.split('-').map(Number);
    const label = d + ' ' + BULAN_SHORT[m - 1] + ' ' + y;
    document.getElementById('mt-date-chip-label').textContent = label;
    document.getElementById('mt-date-chip').classList.add('visible');
    document.getElementById('mt-cal-btn').classList.add('has-date');
    document.getElementById('mt-cal-label').textContent = label;
    applyFilter();
}
function clearDateFilter() {
    calSelected = null; renderCal();
    document.getElementById('mt-date-chip').classList.remove('visible');
    document.getElementById('mt-cal-btn').classList.remove('has-date');
    document.getElementById('mt-cal-label').textContent = 'Filter Deadline';
    applyFilter();
}
function toggleCal(e) {
    e.stopPropagation();
    const popup = document.getElementById('mt-cal-popup');
    popup.classList.toggle('open');
    if (popup.classList.contains('open')) renderCal();
}
function closeCal() { document.getElementById('mt-cal-popup').classList.remove('open'); }

document.getElementById('mc-prev')?.addEventListener('click', () => {
    calCur.m--; if (calCur.m < 0) { calCur.m = 11; calCur.y--; } renderCal();
});
document.getElementById('mc-next')?.addEventListener('click', () => {
    calCur.m++; if (calCur.m > 11) { calCur.m = 0; calCur.y++; } renderCal();
});
document.addEventListener('click', e => {
    if (!e.target.closest('.mt-cal-popup') && !e.target.closest('#mt-cal-btn')) closeCal();
});

/* ── Filter status ── */
function setStatus(val, btn) {
    activeStatus = val;
    document.querySelectorAll('.mt-status-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    applyFilter();
}

/* ── Apply semua filter ── */
function applyFilter() {
    const q = (document.getElementById('mt-search')?.value || '').toLowerCase().trim();
    const cards = Array.from(document.querySelectorAll('.tugas-card'));
    let visible = 0;
    cards.forEach(c => {
        const judul  = c.dataset.judul || '';
        const status = c.dataset.status || '';
        const ts     = Number(c.dataset.deadline);
        const matchQ = !q || judul.includes(q);
        const matchS = activeStatus === 'semua' || status === activeStatus;
        let matchD   = true;
        if (calSelected) {
            if (!ts || ts >= 9999999999) { matchD = false; }
            else {
                const key = new Date(ts * 1000).toLocaleDateString('en-CA', { timeZone: 'Asia/Jakarta' });
                matchD = key === calSelected;
            }
        }
        const show = matchQ && matchS && matchD;
        c.style.display = show ? '' : 'none';
        if (show) visible++;
    });
    const countEl = document.getElementById('mt-count');
    if (countEl) countEl.textContent = visible + ' tugas';
    const noRes = document.getElementById('mt-no-result');
    if (noRes) noRes.style.display = visible === 0 ? 'block' : 'none';

    // Highlight judul
    document.querySelectorAll('.tugas-card .card-title').forEach(el => {
        const orig = el.dataset.orig || el.textContent.trim();
        el.dataset.orig = orig;
        if (!q) { el.innerHTML = orig; return; }
        const re = new RegExp('(' + q.replace(/[\-\[\]{}()*+?.,\\^$|#\s]/g,'\\$&') + ')', 'gi');
        el.innerHTML = orig.replace(re, '<mark style="background:#fef08a;color:#713f12;border-radius:2px;padding:0 1px">$1</mark>');
    });
}

document.addEventListener('DOMContentLoaded', function() {
    const inp = document.getElementById('mt-search');
    const clr = document.getElementById('mt-search-clear');
    if (!inp) return;
    const total = document.querySelectorAll('.tugas-card').length;
    const countEl = document.getElementById('mt-count');
    if (countEl) countEl.textContent = total + ' tugas';
    inp.addEventListener('input', function() {
        clr.style.display = this.value ? 'block' : 'none';
        applyFilter();
    });
    inp.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') { this.value = ''; clr.style.display = 'none'; applyFilter(); }
    });
    clr.addEventListener('click', function() {
        inp.value = ''; this.style.display = 'none'; applyFilter(); inp.focus();
    });
});

// ── Tab switching Reguler / Triwulan ──────────────────
function switchMainTab(tab, btn) {
    document.querySelectorAll('.main-tw-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    if (tab === 'triwulan') {
        document.getElementById('panel-triwulan').style.display = '';
        document.getElementById('panel-reguler').style.display  = 'none';
    } else {
        document.getElementById('panel-triwulan').style.display = 'none';
        document.getElementById('panel-reguler').style.display  = '';
    }
    // Simpan preferensi di sessionStorage
    sessionStorage.setItem('materi_tab', tab);
}

// Restore tab dari session
document.addEventListener('DOMContentLoaded', function() {
    const saved = sessionStorage.getItem('materi_tab');
    if (saved === 'triwulan') {
        const btn = document.getElementById('tab-btn-triwulan');
        if (btn) switchMainTab('triwulan', btn);
    }
});
</script>

@endsection