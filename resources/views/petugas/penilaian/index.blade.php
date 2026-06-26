@extends('layouts.petugas')

@section('title', 'Nilai Saya')

@push('styles')
<style>
/* ═══════════════════════════════════════════════════
   NILAI SAYA v3 — Compact & Clean
   ═══════════════════════════════════════════════════ */

/* ── Filter bar ──────────────────────────────────── */
.filter-bar {
    display: flex; align-items: center; gap: 10px;
    flex-wrap: wrap; margin-bottom: 16px;
}
.inp {
    height: 32px; border: 1px solid var(--rule); border-radius: 6px;
    padding: 0 10px; font-size: 12px; font-family: inherit;
    color: var(--ink); background: var(--surface);
}
.inp:focus { outline: none; border-color: var(--blue); }

.rank-chip {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11.5px; font-weight: 600; padding: 3px 10px;
    border-radius: 20px; background: var(--blue-lt); color: var(--blue);
}

/* ── Dropdown unduh PDF ──────────────────────────── */
.dl-wrap { position: relative; }
.btn-out {
    height: 32px; padding: 0 12px; border-radius: 6px;
    border: 1px solid var(--rule); background: var(--surface);
    color: var(--ink2); font-size: 11.5px; font-weight: 500; font-family: inherit;
    cursor: pointer; display: inline-flex; align-items: center; gap: 6px;
    white-space: nowrap; transition: all .15s;
}
.btn-out:hover { border-color: var(--blue); color: var(--blue); }
.dl-menu {
    display: none; position: absolute; top: calc(100% + 6px); left: 0;
    min-width: 250px; background: var(--surface); border: 1px solid var(--rule);
    border-radius: 8px; box-shadow: 0 8px 24px rgba(0,0,0,.12);
    padding: 8px; z-index: 30;
}
.dl-menu.open { display: block; }
.dl-menu-lbl {
    font-size: 9.5px; font-weight: 700; text-transform: uppercase; letter-spacing: .5px;
    color: var(--ink3); padding: 6px 8px 4px;
}
.dl-menu-divider { height: 1px; background: var(--rule); margin: 4px 0; }
.dl-item {
    display: flex; align-items: center; gap: 8px; padding: 7px 8px;
    border-radius: 6px; font-size: 12px; color: var(--ink); text-decoration: none;
    cursor: pointer;
}
.dl-item:hover { background: var(--blue-lt); color: var(--blue); }
.dl-item span:nth-child(2) { flex: 1; }
.dl-item-disabled { color: var(--ink3); cursor: not-allowed; }
.dl-item-disabled:hover { background: none; color: var(--ink3); }
.dl-item-tag {
    font-size: 9.5px; font-weight: 600; padding: 1px 6px; border-radius: 10px;
    background: var(--wash2); color: var(--ink3); flex-shrink: 0;
}
.dl-item-tag-ok { background: var(--green-lt); color: var(--green); }

/* ── Hero strip — 3 kolom compact ───────────────── */
.hero-strip {
    display: grid;
    grid-template-columns: auto 1px auto 1px 1fr;
    gap: 0;
    background: var(--surface);
    border: 1px solid var(--rule);
    border-radius: 10px;
    margin-bottom: 16px;
    overflow: hidden;
}
.hero-div { background: var(--rule); width: 1px; margin: 16px 0; }

.hero-cell {
    padding: 16px 22px;
    display: flex; flex-direction: column; justify-content: center;
}
.hc-label {
    font-size: 10px; font-weight: 600; text-transform: uppercase;
    letter-spacing: .6px; color: var(--ink3); margin-bottom: 6px;
    display: flex; align-items: center; gap: 5px;
}
.hc-value {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 32px; font-weight: 300; letter-spacing: -1px;
    color: var(--ink); line-height: 1;
}
.hc-sub { font-size: 11px; color: var(--ink3); margin-top: 5px; }

/* Grade badge kecil inline */
.grade-inline {
    display: inline-flex; align-items: center; gap: 8px; margin-top: 4px;
}
.grade-badge-sm {
    padding: 3px 10px; border-radius: 6px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 18px; font-weight: 700; line-height: 1;
    display: inline-flex; flex-direction: column; align-items: center;
}
.gb-lbl { font-size: 8px; font-weight: 500; opacity: .7; letter-spacing: .3px; }

.grade-sb-bg  { background: #dcfce7; color: #166534; }
.grade-b-bg   { background: #dbeafe; color: #1e40af; }
.grade-c-bg   { background: #fef3c7; color: #92400e; }
.grade-k-bg   { background: #ffedd5; color: #9a3412; }
.grade-sk-bg  { background: #fee2e2; color: #991b1b; }
.grade-none-bg{ background: var(--wash2); color: var(--ink3); }

/* Trend badge */
.trend-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 7px; border-radius: 20px;
    font-size: 10.5px; font-weight: 600;
}
.trend-up   { background: #dcfce7; color: #166534; }
.trend-down { background: #fee2e2; color: #991b1b; }
.trend-same { background: var(--wash2); color: var(--ink3); }

/* Tombol */
.btn-primary {
    height: 30px; padding: 0 14px; border-radius: 6px; border: none;
    background: var(--blue); color: #fff; font-size: 11.5px; font-family: inherit;
    font-weight: 500; cursor: pointer; text-decoration: none;
    display: inline-flex; align-items: center; gap: 5px; white-space: nowrap;
}
.btn-primary:hover { background: #1648b8; color: #fff; }

/* ── Chart card ──────────────────────────────────── */
.chart-card {
    background: var(--surface);
    border: 1px solid var(--rule);
    border-radius: 10px;
    overflow: hidden;
    margin-bottom: 16px;
}
.cc-head {
    padding: 12px 18px 10px;
    border-bottom: 1px solid var(--rule);
    display: flex; align-items: center; justify-content: space-between; gap: 10px;
    flex-wrap: wrap;
}
.cc-title {
    font-size: 12.5px; font-weight: 600; color: var(--ink);
    display: flex; align-items: center; gap: 7px;
}
.cc-sub { font-size: 11px; color: var(--ink3); margin-top: 2px; }
.cc-body { padding: 14px 18px; position: relative; height: 260px; }

/* Filter grafik pills */
.chart-filters {
    display: flex; gap: 4px; align-items: center;
}
.cf-btn {
    height: 26px; padding: 0 10px; border-radius: 5px; border: 1px solid var(--rule);
    font-size: 11px; font-family: inherit; font-weight: 500;
    color: var(--ink3); background: var(--surface); cursor: pointer;
    transition: all .15s;
}
.cf-btn:hover  { border-color: var(--blue); color: var(--blue); }
.cf-btn.active { background: var(--blue); color: #fff; border-color: var(--blue); }

/* No data overlay */
.chart-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    height: 100%; gap: 8px; color: var(--ink3); text-align: center;
}
.chart-empty svg { opacity: .25; }
.chart-empty p   { font-size: 11.5px; margin: 0; }

/* ── Komponen tabel ringkas di bawah grafik ──────── */
.komp-strip {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    border-top: 1px solid var(--rule);
}
@media(max-width:700px){ .komp-strip { grid-template-columns: 1fr 1fr; } }
@media(max-width:400px){ .komp-strip { grid-template-columns: 1fr; } }

.ks-cell {
    padding: 12px 16px;
    border-right: 1px solid var(--rule);
}
.ks-cell:last-child { border-right: none; }

.ks-dot {
    width: 8px; height: 8px; border-radius: 50%;
    display: inline-block; margin-right: 5px; vertical-align: middle; flex-shrink: 0;
}
.ks-lbl {
    font-size: 10px; font-weight: 600; color: var(--ink3);
    text-transform: uppercase; letter-spacing: .4px;
    display: flex; align-items: center; margin-bottom: 5px;
}
.ks-val {
    font-size: 20px; font-weight: 300; letter-spacing: -.5px;
    font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1;
}
.ks-bar { height: 3px; background: var(--wash2); border-radius: 2px; margin-top: 7px; overflow: hidden; }
.ks-fill{ height: 100%; border-radius: 2px; }


/* ── Grade & status pills ────────────────────────── */
.grade-sb { background: #dcfce7; color: #166534; }
.grade-b  { background: #dbeafe; color: #1e40af; }
.grade-c  { background: #fef3c7; color: #92400e; }
.grade-k  { background: #ffedd5; color: #9a3412; }
.grade-sk { background: #fee2e2; color: #991b1b; }
.status-selesai { background: var(--green-lt); color: var(--green); }
.status-draft   { background: var(--amber-lt); color: var(--amber); }

/* ── Ranking table ───────────────────────────────── */
.rank-me td { background: #eff6ff !important; }
.rank-me td:first-child { border-left: 3px solid var(--blue); padding-left: 13px; }
.rank-me .td-main { font-weight: 600; }
.rank-num {
    width: 26px; height: 26px; border-radius: 50%;
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; font-family: 'IBM Plex Mono', monospace;
}
.rn-1 { background: #fef3c7; color: #92400e; }
.rn-2 { background: #f1f5f9; color: #334155; }
.rn-3 { background: #fff7ed; color: #9a3412; }
.rn-n { background: var(--wash2); color: var(--ink3); }
.saya-tag {
    font-size: 9.5px; background: var(--blue-lt); color: var(--blue);
    padding: 1px 5px; border-radius: 3px; font-weight: 600; margin-left: 5px;
}

/* ── Tabel panjang: scroll internal + fade cue ───── */
.tbl-scroll-wrap { position: relative; }
.tbl-scroll {
    overflow-y: auto;
    overflow-x: auto;
    max-height: 318px; /* ≈ header + 6 baris @22px-ish, terlihat alami */
}
.tbl-scroll table thead th {
    position: sticky; top: 0; z-index: 2;
    background: var(--surface);
    box-shadow: 0 1px 0 var(--rule);
}
.tbl-scroll-fade {
    position: absolute; left: 0; right: 0; bottom: 0; height: 28px;
    background: linear-gradient(to top, var(--surface), rgba(255,255,255,0));
    pointer-events: none; border-radius: 0 0 8px 8px;
    opacity: 0; transition: opacity .15s;
}
.tbl-scroll-wrap.has-overflow .tbl-scroll-fade { opacity: 1; }
.tbl-count-badge {
    font-size: 10.5px; font-weight: 600; padding: 2px 9px; border-radius: 20px;
    background: var(--wash2); color: var(--ink3); white-space: nowrap;
}

@media(max-width:640px){
    .hero-strip {
        grid-template-columns: 1fr;
    }
    .hero-div { display: none; }
    .hero-cell { border-bottom: 1px solid var(--rule); padding: 14px 16px; }
    .hero-cell:last-child { border-bottom: none; }
}
</style>
@endpush

@section('breadcrumb')
    <a href="{{ url('/petugas/dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Nilai Saya</strong>
@endsection

@section('content')

{{-- FILTER PERIODE --}}
<div class="filter-bar">
    <form method="GET" style="display:flex;align-items:center;gap:8px">
        <label style="font-size:12px;color:var(--ink3);font-weight:500">Periode Aktif:</label>
        <select name="periode" class="inp" onchange="this.form.submit()">
            @foreach($periodeOptions as $key => $label)
                <option value="{{ $key }}" {{ $key === $periode ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    {{-- Dropdown unduh PDF & Excel --}}
    <div class="dl-wrap">
        <button type="button" class="btn-out" id="dlToggle" onclick="toggleDl()">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            Unduh
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="margin-left:1px"><polyline points="6 9 12 15 18 9"/></svg>
        </button>
        <div class="dl-menu" id="dlMenu">
            <div class="dl-menu-lbl">Transkrip Triwulan (PDF, Nilai Saya)</div>
            @if($evaluasi && $evaluasi->status === 'selesai')
            <a href="{{ route('petugas.penilaian.pdf', $periode) }}" class="dl-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>{{ $periodeOptions[$periode] ?? $periode }}</span>
            </a>
            @else
            <div class="dl-item dl-item-disabled" title="Evaluasi periode ini belum diselesaikan koordinator">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>{{ $periodeOptions[$periode] ?? $periode }}</span>
                <span class="dl-item-tag">Belum selesai</span>
            </div>
            @endif

            <div class="dl-menu-divider"></div>
            <div class="dl-menu-lbl">Transkrip 1 Tahun (PDF, Nilai Saya)</div>
            @if($jumlahSelesaiTahunIni > 0)
            <a href="{{ route('petugas.penilaian.pdf.tahunan', $tahunAktif) }}" class="dl-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Tahun {{ $tahunAktif }}</span>
                <span class="dl-item-tag dl-item-tag-ok">{{ $jumlahSelesaiTahunIni }}/4 TW</span>
            </a>
            @else
            <div class="dl-item dl-item-disabled" title="Belum ada evaluasi yang selesai pada tahun ini">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Tahun {{ $tahunAktif }}</span>
                <span class="dl-item-tag">Belum ada data</span>
            </div>
            @endif

            <div class="dl-menu-divider"></div>
            <div class="dl-menu-lbl">Ranking Wilayah (Excel, Semua Petugas)</div>
            @if($rankingWilayah->isNotEmpty())
            <a href="{{ route('petugas.penilaian.export', ['periode' => $periode]) }}" class="dl-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>{{ $periodeOptions[$periode] ?? $periode }}</span>
                <span class="dl-item-tag dl-item-tag-ok">{{ $rankingWilayah->count() }} petugas</span>
            </a>
            @else
            <div class="dl-item dl-item-disabled" title="Belum ada petugas yang dievaluasi koordinator pada periode ini">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                <span>{{ $periodeOptions[$periode] ?? $periode }}</span>
                <span class="dl-item-tag">Belum ada data</span>
            </div>
            @endif

            <a href="{{ route('petugas.penilaian.export.tahunan', ['tahun' => $tahunAktif]) }}" class="dl-item">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                <span>Rekap Tahun {{ $tahunAktif }}</span>
            </a>
        </div>
    </div>

    @if($myRank)
    <span class="rank-chip">
        <svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
        Ranking #{{ $myRank }} dari {{ $rankingWilayah->count() }} petugas di wilayah
    </span>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     HERO STRIP — compact 3 kolom
     ══════════════════════════════════════════════ --}}
@php
    $grBg = match($gradePreview ?? '') {
        'SB'=>'grade-sb-bg','B'=>'grade-b-bg','C'=>'grade-c-bg',
        'K'=>'grade-k-bg','SK'=>'grade-sk-bg',default=>'grade-none-bg'
    };
    $gradeLbl = \App\Models\EvaluasiPetugas::labelGrade($gradePreview ?? '-');
@endphp

<div class="hero-strip">

    {{-- Nilai Akhir --}}
    <div class="hero-cell">
        <div class="hc-label">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            Nilai Akhir
        </div>
        <div class="hc-value">{{ $nilaiPreview !== null ? number_format($nilaiPreview, 2) : '—' }}</div>
        <div class="hc-sub" style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
            <span>{{ \App\Helpers\PeriodeHelper::isoLabel($periode) }}</span>
            @if($trendChange)
            <span class="trend-badge trend-{{ $trendChange['direction'] }}">
                @if($trendChange['direction']==='up') ▲ @elseif($trendChange['direction']==='down') ▼ @else → @endif
                {{ $trendChange['label'] }}
            </span>
            @endif
            @if(!$evaluasi && $nilaiPreview !== null)
            <span style="color:var(--amber);font-size:10.5px">⏳ Estimasi</span>
            @elseif($evaluasi)
            <span class="pill status-selesai" style="font-size:10px">{{ $evaluasi->status === 'selesai' ? 'Selesai' : 'Draft' }}</span>
            @endif
        </div>
    </div>

    <div class="hero-div"></div>

    {{-- Grade --}}
    <div class="hero-cell">
        <div class="hc-label">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            Grade Kinerja
        </div>
        <div class="grade-inline">
            <div class="grade-badge-sm {{ $grBg }}">
                {{ $gradePreview ?? '—' }}
                <span class="gb-lbl">{{ $gradeLbl }}</span>
            </div>
            @if($evaluasi)
            <div style="font-size:10.5px;color:var(--ink3);line-height:1.6">
                Dinilai<br>{{ $evaluasi->tanggal_evaluasi?->isoFormat('D MMM YY') ?? '-' }}
            </div>
            @endif
        </div>
    </div>

    <div class="hero-div"></div>

    {{-- Ranking & action --}}
    <div class="hero-cell" style="flex-direction:row;align-items:center;justify-content:space-between;gap:16px">
        <div>
            <div class="hc-label">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 0 0-4-4H5a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 0 0-3-3.87"/><path d="M16 3.13a4 4 0 0 1 0 7.75"/></svg>
                Ranking Wilayah
            </div>
            @if($myRank)
                <div class="hc-value">#{{ $myRank }}</div>
                <div class="hc-sub">dari {{ $rankingWilayah->count() }} petugas terevaluasi</div>
            @else
                <div class="hc-value" style="font-size:22px;color:var(--ink3)">—</div>
                <div class="hc-sub">Belum masuk ranking periode ini</div>
            @endif
        </div>
        @if($evaluasi)
        <a href="{{ route('petugas.penilaian.show', $periode) }}" class="btn-primary">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
            Rincian
        </a>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════
     GRAFIK TREN — Line Chart + komponen strip
     Satu grafik, filter: Semua / Per Triwulan / Per Tahun
     ══════════════════════════════════════════════ --}}
<div class="chart-card">
    <div class="cc-head">
        <div>
            <div class="cc-title">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                Tren Perkembangan Nilai
            </div>
            <div class="cc-sub">Performa dari waktu ke waktu — semua evaluasi yang telah selesai</div>
        </div>
        {{-- Filter tampilan grafik --}}
        <div class="chart-filters">
            <button class="cf-btn active" id="btnSemua"     onclick="switchChart('semua')">Semua</button>
            <button class="cf-btn"        id="btnTriwulan"  onclick="switchChart('triwulan')">Triwulan</button>
            <button class="cf-btn"        id="btnTahunan"   onclick="switchChart('tahunan')">Tahunan</button>
        </div>
    </div>

    @php $hasTrend = count($trendRaw) >= 1; @endphp

    @if($hasTrend)
    <div class="cc-body">
        <canvas id="trendChart"></canvas>
    </div>
    @else
    <div class="cc-body">
        <div class="chart-empty">
            <svg width="44" height="44" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <p>Belum ada data tren</p>
            <p style="font-size:10.5px;opacity:.6">Grafik muncul setelah koordinator menyelesaikan minimal 1 evaluasi</p>
        </div>
    </div>
    @endif

    {{-- Strip komponen periode aktif (di bawah grafik) --}}
    @if($evaluasi || $nilaiPreview !== null)
    <div class="komp-strip">
        @php
            $kompData = [
                ['I — Sikap Kerja',      $kompSikap,  '#1a56db'],
                ['IIA — Ind. Hasil',     $kompHasil,  '#0a7c4e'],
                ['IIB — Ind. Proses',    $kompProses, '#b45309'],
                ['III — Mutu Pelayanan', $kompMutu,   '#7c3aed'],
            ];
        @endphp
        @foreach($kompData as [$lbl, $val, $clr])
        <div class="ks-cell">
            <div class="ks-lbl">
                <span class="ks-dot" style="background:{{ $clr }}"></span>{{ $lbl }}
            </div>
            <div class="ks-val">{{ $val !== null ? number_format($val, 2) : '—' }}</div>
            <div class="ks-bar"><div class="ks-fill" style="width:{{ $val ?? 0 }}%;background:{{ $clr }}"></div></div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- ══════════════════════════════════════════════
     RANKING SE-WILAYAH
     ══════════════════════════════════════════════ --}}
@if($rankingWilayah->isNotEmpty())
<div class="panel" style="margin-bottom:16px">
    <div class="ph">
        <div>
            <div class="ph-title">Ranking Petugas — Wilayah Ini</div>
            <div class="ph-sub">
                Nilai seluruh petugas periode {{ \App\Helpers\PeriodeHelper::isoLabel($periode) }}
            </div>
        </div>
        <span class="tbl-count-badge">{{ $rankingWilayah->count() }} petugas terevaluasi</span>
    </div>
    <div class="tbl-scroll-wrap" id="rankingScrollWrap">
        <div class="tbl-scroll" id="rankingScroll">
            <table>
                <thead>
                    <tr>
                        <th style="width:44px;text-align:center">#</th>
                        <th>Nama Petugas</th>
                        <th style="text-align:right">Sikap Kerja</th>
                        <th style="text-align:right">Ind. Hasil</th>
                        <th style="text-align:right">Ind. Proses</th>
                        <th style="text-align:right">Mutu Pelayanan</th>
                        <th style="text-align:right">Nilai</th>
                        <th style="text-align:center">Grade</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($rankingWilayah as $r)
                    @php
                        $gc = match($r->grade ?? '') { 'SB'=>'grade-sb','B'=>'grade-b','C'=>'grade-c','K'=>'grade-k','SK'=>'grade-sk',default=>'' };
                        $rc = match($r->rank) { 1=>'rn-1', 2=>'rn-2', 3=>'rn-3', default=>'rn-n' };
                    @endphp
                    <tr class="{{ $r->is_me ? 'rank-me' : '' }}">
                        <td style="text-align:center"><span class="rank-num {{ $rc }}">{{ $r->rank }}</span></td>
                        <td>
                            <span class="td-main">{{ $r->nama }}@if($r->is_me)<span class="saya-tag">Saya</span>@endif</span>
                        </td>
                        <td class="mono" style="text-align:right">{{ $r->rata_sikap_kerja !== null ? number_format($r->rata_sikap_kerja,1) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $r->rata_indikator_hasil !== null ? number_format($r->rata_indikator_hasil,1) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $r->rata_indikator_proses !== null ? number_format($r->rata_indikator_proses,1) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $r->rata_mutu_pelayanan !== null ? number_format($r->rata_mutu_pelayanan,1) : '-' }}</td>
                        <td style="text-align:right"><strong class="mono" style="font-size:13px">{{ $r->jumlah_nilai !== null ? number_format($r->jumlah_nilai,2) : '-' }}</strong></td>
                        <td style="text-align:center">
                            @if($r->grade)<span class="pill {{ $gc }}">{{ $r->grade }}</span>
                            @else<span style="color:var(--ink3)">-</span>@endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tbl-scroll-fade"></div>
    </div>
</div>
@endif

{{-- ══════════════════════════════════════════════
     RIWAYAT PENILAIAN
     ══════════════════════════════════════════════ --}}
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Riwayat Penilaian</div>
            <div class="ph-sub">Semua periode evaluasi</div>
        </div>
        @if($evaluasiList->isNotEmpty())
        <span class="tbl-count-badge">{{ $evaluasiList->count() }} periode</span>
        @endif
    </div>
    @if($evaluasiList->isNotEmpty())
    <div class="tbl-scroll-wrap" id="riwayatScrollWrap">
        <div class="tbl-scroll" id="riwayatScroll">
            <table>
                <thead>
                    <tr>
                        <th>Periode</th>
                        <th style="text-align:right">Sikap</th>
                        <th style="text-align:right">Hasil</th>
                        <th style="text-align:right">Proses</th>
                        <th style="text-align:right">Mutu</th>
                        <th style="text-align:right">Nilai</th>
                        <th style="text-align:center">Grade</th>
                        <th style="text-align:center">Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($evaluasiList->sortByDesc('periode') as $h)
                    @php
                        $gc = match($h->grade ?? '') { 'SB'=>'grade-sb','B'=>'grade-b','C'=>'grade-c','K'=>'grade-k','SK'=>'grade-sk',default=>'' };
                        $sc = $h->status === 'selesai' ? 'status-selesai' : 'status-draft';
                    @endphp
                    <tr>
                        <td>
                            <span class="td-main">{{ \App\Helpers\PeriodeHelper::isoLabel($h->periode) }}</span>
                            <div class="td-id">{{ $h->tanggal_evaluasi?->isoFormat('D MMM YYYY') }}</div>
                        </td>
                        <td class="mono" style="text-align:right">{{ $h->rata_sikap_kerja !== null ? number_format($h->rata_sikap_kerja,2) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $h->rata_indikator_hasil !== null ? number_format($h->rata_indikator_hasil,2) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $h->rata_indikator_proses !== null ? number_format($h->rata_indikator_proses,2) : '-' }}</td>
                        <td class="mono" style="text-align:right">{{ $h->rata_mutu_pelayanan !== null ? number_format($h->rata_mutu_pelayanan,2) : '-' }}</td>
                        <td style="text-align:right"><strong class="mono" style="font-size:13px">{{ $h->jumlah_nilai !== null ? number_format($h->jumlah_nilai,2) : '-' }}</strong></td>
                        <td style="text-align:center">
                            @if($h->grade)<span class="pill {{ $gc }}">{{ $h->grade }}</span>
                            @else<span style="color:var(--ink3)">-</span>@endif
                        </td>
                        <td style="text-align:center"><span class="pill {{ $sc }}">{{ $h->status === 'selesai' ? 'Selesai' : 'Draft' }}</span></td>
                        <td style="text-align:right"><a href="{{ route('petugas.penilaian.show', $h->periode) }}" class="btn-detail">Rincian</a></td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="tbl-scroll-fade"></div>
    </div>
    @else
    <div class="empty">
        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Belum ada evaluasi.<br>
        <span style="font-size:11px;margin-top:4px;display:block">Data muncul setelah koordinator menyelesaikan penilaian.</span>
    </div>
    @endif
</div>

<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="font-size:10.5px;color:var(--ink3);font-weight:500">Grade:</span>
    <span class="pill grade-sb" style="font-size:10px">SB &gt;95</span>
    <span class="pill grade-b"  style="font-size:10px">B 86–95</span>
    <span class="pill grade-c"  style="font-size:10px">C 66–85</span>
    <span class="pill grade-k"  style="font-size:10px">K 51–65</span>
    <span class="pill grade-sk" style="font-size:10px">SK &lt;50</span>
</div>

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ── Dropdown unduh PDF ──────────────────────────────────────
function toggleDl() {
    document.getElementById('dlMenu')?.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    const wrap = document.querySelector('.dl-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('dlMenu')?.classList.remove('open');
    }
});

// ── Tabel panjang: tampilkan fade cue hanya kalau memang overflow,
//    dan sembunyikan begitu user sudah scroll sampai dasar ──────
function initScrollFade(wrapId, scrollId) {
    const wrap = document.getElementById(wrapId);
    const box  = document.getElementById(scrollId);
    if (!wrap || !box) return;

    function update() {
        const overflowing = box.scrollHeight > box.clientHeight + 1;
        const atBottom     = box.scrollTop + box.clientHeight >= box.scrollHeight - 2;
        wrap.classList.toggle('has-overflow', overflowing && !atBottom);
    }
    update();
    box.addEventListener('scroll', update);
    window.addEventListener('resize', update);
}
document.addEventListener('DOMContentLoaded', function () {
    initScrollFade('rankingScrollWrap', 'rankingScroll');
    initScrollFade('riwayatScrollWrap', 'riwayatScroll');
});

document.addEventListener('DOMContentLoaded', function () {

    // ── Data dari server ──────────────────────────────────────
    // trendRaw: semua periode (bulanan & triwulan)
    const trendAll = @json($trendRaw);

    // trendPerTahun: agregasi rata-rata per tahun
    const trendYear = @json($trendPerTahun);

    // Filter: triwulan saja (tipe = 'triwulan')
    const trendTW = trendAll.filter(d => d.tipe === 'triwulan');

    // ── Warna dataset ─────────────────────────────────────────
    const clrNilai  = '#1a56db';
    const clrSikap  = '#1a56db';
    const clrHasil  = '#0a7c4e';
    const clrProses = '#b45309';
    const clrMutu   = '#7c3aed';

    const KOMPONEN_DEFS = [
        { key: 'nilai',  label: 'Nilai Akhir',     color: clrNilai  },
        { key: 'sikap',  label: 'Sikap Kerja',     color: clrSikap  },
        { key: 'hasil',  label: 'Ind. Hasil',      color: clrHasil  },
        { key: 'proses', label: 'Ind. Proses',     color: clrProses },
        { key: 'mutu',   label: 'Mutu Pelayanan',  color: clrMutu   },
    ];

    // ── Datasets utk LINE chart (≥2 periode → terlihat sbg tren) ──
    function buildLineDatasets(data) {
        const pointColors = data.map((d, i) => {
            if (i === 0) return clrNilai;
            return d.nilai >= data[i-1].nilai ? '#0a7c4e' : '#dc2626';
        });
        return [
            {
                label: 'Nilai Akhir',
                data: data.map(d => d.nilai),
                borderColor: clrNilai,
                backgroundColor: 'rgba(26,86,219,.07)',
                borderWidth: 2.5,
                pointBackgroundColor: pointColors,
                pointBorderColor: '#fff',
                pointBorderWidth: 2,
                pointRadius: 6,
                pointHoverRadius: 8,
                fill: true, tension: 0.35,
            },
            {
                label: 'Sikap Kerja',
                data: data.map(d => d.sikap),
                borderColor: clrSikap, borderWidth: 1.2, borderDash: [5,4],
                pointRadius: 3, pointBackgroundColor: clrSikap,
                fill: false, tension: 0.3,
            },
            {
                label: 'Ind. Hasil',
                data: data.map(d => d.hasil),
                borderColor: clrHasil, borderWidth: 1.2, borderDash: [5,4],
                pointRadius: 3, pointBackgroundColor: clrHasil,
                fill: false, tension: 0.3,
            },
            {
                label: 'Ind. Proses',
                data: data.map(d => d.proses),
                borderColor: clrProses, borderWidth: 1.2, borderDash: [5,4],
                pointRadius: 3, pointBackgroundColor: clrProses,
                fill: false, tension: 0.3,
            },
            {
                label: 'Mutu Pelayanan',
                data: data.map(d => d.mutu),
                borderColor: clrMutu, borderWidth: 1.2, borderDash: [5,4],
                pointRadius: 3, pointBackgroundColor: clrMutu,
                fill: false, tension: 0.3,
            },
        ];
    }

    // ── Datasets utk BAR chart (1 periode → bandingkan komponen) ──
    // Tiap komponen jadi 1 batang berdampingan, lebih mudah dibaca
    // daripada garis yang cuma punya 1 titik (terlihat kosong/aneh).
    function buildBarDatasets(data) {
        const d = data[0] || {};
        return [{
            label: 'Nilai',
            data: KOMPONEN_DEFS.map(k => d[k.key] ?? null),
            backgroundColor: KOMPONEN_DEFS.map(k => k.color),
            borderRadius: 6,
            borderSkipped: false,
            maxBarThickness: 56,
        }];
    }

    const baseScales = {
        x: {
            grid: { color: 'rgba(0,0,0,.04)', drawBorder: false },
            ticks: { font: { size: 10.5 }, color: '#6b7280', maxRotation: 25 }
        },
        y: {
            min: 0, max: 100,
            grid: { color: 'rgba(0,0,0,.05)', drawBorder: false },
            ticks: { font: { size: 10 }, color: '#9ca3af', stepSize: 20 }
        }
    };

    function gradeLabel(v) {
        if (v === null || v === undefined) return null;
        return v > 95 ? 'SB – Sangat Baik'
            : v >= 86 ? 'B – Baik'
            : v >= 66 ? 'C – Cukup'
            : v >= 51 ? 'K – Kurang' : 'SK – Sangat Kurang';
    }

    const lineOptions = {
        responsive: true,
        maintainAspectRatio: false,
        interaction: { mode: 'index', intersect: false },
        scales: baseScales,
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 10.5 }, boxWidth: 28, padding: 14, usePointStyle: true }
            },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,.92)',
                titleFont: { size: 11, weight: '600' },
                bodyFont: { size: 10.5 },
                padding: 12,
                callbacks: {
                    label: ctx => {
                        const v = ctx.parsed.y;
                        if (v === null || v === undefined) return null;
                        if (ctx.datasetIndex === 0) {
                            const arr = ctx.dataset.data;
                            const i   = ctx.dataIndex;
                            const prev = i > 0 ? arr[i-1] : null;
                            let diff = '';
                            if (prev !== null) {
                                const d = (v - prev).toFixed(2);
                                diff = d > 0 ? ` ▲ +${d}` : (d < 0 ? ` ▼ ${d}` : ' → sama');
                            }
                            return ` ${ctx.dataset.label}: ${v.toFixed(2)}${diff}`;
                        }
                        return ` ${ctx.dataset.label}: ${v.toFixed(2)}`;
                    },
                    afterBody: (items) => {
                        const g = gradeLabel(items[0]?.parsed?.y);
                        return g ? ['', ` Grade: ${g}`] : [];
                    }
                }
            }
        }
    };

    const barOptions = {
        responsive: true,
        maintainAspectRatio: false,
        scales: baseScales,
        plugins: {
            legend: { display: false },
            tooltip: {
                backgroundColor: 'rgba(15,23,42,.92)',
                titleFont: { size: 11, weight: '600' },
                bodyFont: { size: 10.5 },
                padding: 12,
                callbacks: {
                    label: ctx => {
                        const v = ctx.parsed.y;
                        if (v === null || v === undefined) return ' Belum ada data';
                        return ` ${KOMPONEN_DEFS[ctx.dataIndex].label}: ${v.toFixed(2)}`;
                    },
                    afterBody: (items) => {
                        if (items[0]?.dataIndex !== 0) return []; // grade cuma relevan utk Nilai Akhir
                        const g = gradeLabel(items[0]?.parsed?.y);
                        return g ? ['', ` Grade: ${g}`] : [];
                    }
                }
            }
        }
    };

    // ── Render: pilih bar (1 periode) atau line (≥2 periode) ──
    const canvas = document.getElementById('trendChart');
    if (!canvas) return;

    let myChart = null;

    function renderChart(data) {
        const isSingle = data.length <= 1;
        const type     = isSingle ? 'bar' : 'line';

        const config = isSingle
            ? {
                type: 'bar',
                data: {
                    labels: KOMPONEN_DEFS.map(k => k.label),
                    datasets: buildBarDatasets(data),
                },
                options: barOptions,
              }
            : {
                type: 'line',
                data: {
                    labels: data.map(d => d.label),
                    datasets: buildLineDatasets(data),
                },
                options: lineOptions,
              };

        if (myChart) {
            myChart.destroy();
        }
        myChart = new Chart(canvas, config);
    }

    renderChart(trendAll);

    // ── Switch filter ─────────────────────────────────────────
    window.switchChart = function(mode) {
        // Update tombol aktif
        ['btnSemua','btnTriwulan','btnTahunan'].forEach(id => {
            document.getElementById(id)?.classList.remove('active');
        });
        const btnMap = { semua:'btnSemua', triwulan:'btnTriwulan', tahunan:'btnTahunan' };
        document.getElementById(btnMap[mode])?.classList.add('active');

        // Pilih data
        let data;
        if (mode === 'triwulan') {
            data = trendTW.length ? trendTW : trendAll;
        } else if (mode === 'tahunan') {
            data = trendYear.length ? trendYear : trendAll;
        } else {
            data = trendAll;
        }

        renderChart(data);
    };
});
</script>
@endpush