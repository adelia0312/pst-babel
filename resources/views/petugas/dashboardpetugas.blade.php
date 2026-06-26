{{-- ============================================================
     FILE   : resources/views/petugas/dashboardpetugas.blade.php
     STATUS : GANTI FILE LAMA (file ini sudah ada, timpa isinya)
     ============================================================ --}}
@extends('layouts.petugas')

@section('title', 'Dashboard Petugas')

@section('breadcrumb')
    <span>PST</span><span>›</span><strong>Dashboard Petugas</strong>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════
   DASHBOARD PETUGAS — seragam dengan admin & koordinator
   ══════════════════════════════════════ */

.db { display:flex; flex-direction:column; gap:20px; }

/* ── Heading ── */
.db-head {
    display:flex; align-items:flex-start; justify-content:space-between;
    flex-wrap:wrap; gap:12px;
    padding-bottom:16px; border-bottom:1px solid var(--rule);
}
.db-head h1 { font-size:16px; font-weight:600; color:var(--ink); letter-spacing:-.2px; }
.db-head p  { font-size:11.5px; color:var(--ink3); margin-top:3px; }

/* Pill status absensi */
.pill-status { display:inline-flex; align-items:center; gap:5px; font-size:11.5px; font-weight:500; padding:5px 13px; border-radius:20px; text-decoration:none; }
.pill-green  { background:var(--green-lt); color:var(--green); }
.pill-amber  { background:var(--amber-lt); color:var(--amber); }

/* ── Section title ── */
.sec {
    font-size:9.5px; font-weight:600; letter-spacing:1.5px;
    text-transform:uppercase; color:var(--ink3);
    display:flex; align-items:center; gap:8px; margin-bottom:10px;
}
.sec::after { content:''; flex:1; height:1px; background:var(--rule); }

/* ════════════ KPI CARDS ════════════ */
.kpi-row { display:grid; grid-template-columns:repeat(4,1fr); gap:12px; }
.kpi {
    border-radius:8px; padding:18px 20px 16px;
    display:block; text-decoration:none; position:relative; overflow:hidden;
    transition:transform .12s, box-shadow .15s;
}
.kpi:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.13); }
.kpi-blue   { background:#1a56db; color:#fff; }
.kpi-green  { background:#0a7c4e; color:#fff; }
.kpi-amber  { background:#b45309; color:#fff; }
.kpi-purple { background:#5b21b6; color:#fff; }
.kpi-label  { font-size:10.5px; font-weight:600; letter-spacing:.5px; text-transform:uppercase; opacity:.75; margin-bottom:10px; }
.kpi-val    { font-size:38px; font-weight:300; letter-spacing:-2px; font-family:'IBM Plex Mono',monospace; line-height:1; margin-bottom:10px; }
.kpi-foot   { display:flex; align-items:center; gap:6px; font-size:11px; opacity:.72; }
.kpi-chip   { font-size:9.5px; font-weight:700; background:rgba(255,255,255,.2); padding:2px 6px; border-radius:3px; font-family:'IBM Plex Mono',monospace; }
.kpi-deco   { position:absolute; right:16px; top:14px; opacity:.15; }

/* ════════════ LAYOUT UTAMA 2 KOLOM ════════════ */
.row-main { display:grid; grid-template-columns:1fr 280px; gap:14px; align-items:start; }
.row-3    { display:grid; grid-template-columns:repeat(3,1fr); gap:12px; }

/* ════════════ PANEL ════════════ */
.panel   { background:var(--surface); border:1px solid var(--rule); border-radius:8px; overflow:hidden; }
.ph      { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px; font-weight:600; color:var(--ink); }
.ph-sub   { font-size:11px; color:var(--ink3); margin-top:1px; }
.ph-link  { font-size:11.5px; color:var(--blue); text-decoration:none; font-weight:500; display:inline-flex; align-items:center; gap:4px; }
.ph-link:hover { text-decoration:underline; }

/* ════════════ DONUT ABSENSI ════════════ */
.abs-wrap  { padding:16px; display:flex; align-items:center; gap:14px; }
.abs-donut { position:relative; width:80px; height:80px; flex-shrink:0; }
.abs-donut canvas { display:block; }
.abs-center {
    position:absolute; inset:0;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    pointer-events:none;
}
.abs-pct  { font-size:18px; font-weight:300; font-family:'IBM Plex Mono',monospace; color:var(--ink); line-height:1; }
.abs-unit { font-size:9px; color:var(--ink3); }
.abs-legs { flex:1; display:flex; flex-direction:column; gap:7px; }
.abs-leg  { display:flex; align-items:center; gap:7px; }
.abs-ldot { width:8px; height:8px; border-radius:2px; flex-shrink:0; }
.abs-lname { font-size:11px; color:var(--ink2); flex:1; }
.abs-lval  { font-size:11px; font-family:'IBM Plex Mono',monospace; color:var(--ink); font-weight:500; }

.abs-grid  { display:grid; grid-template-columns:repeat(3,1fr); gap:1px; background:var(--rule); border-top:1px solid var(--rule); }
.abs-cell  { background:var(--surface); padding:10px 12px; text-align:center; }
.abs-cnum  { font-size:20px; font-weight:300; font-family:'IBM Plex Mono',monospace; line-height:1; }
.abs-clab  { font-size:9px; color:var(--ink3); font-weight:600; margin-top:3px; letter-spacing:.5px; text-transform:uppercase; }

/* ════════════ JADWAL ════════════ */
.shift-row { display:flex; align-items:center; justify-content:space-between; padding:12px 16px; border-bottom:1px solid var(--rule); }
.shift-row:last-child { border-bottom:none; }
.shift-badge { width:32px; height:32px; border-radius:6px; display:flex; align-items:center; justify-content:center; font-size:9.5px; font-weight:700; flex-shrink:0; }
.sh-p { background:var(--amber-lt); color:var(--amber); }
.sh-s { background:var(--blue-lt); color:var(--blue); }
.shift-label { font-size:13px; font-weight:600; color:var(--ink); }
.shift-sub   { font-size:11px; color:var(--ink3); margin-top:2px; }

/* ════════════ TABEL JADWAL ════════════ */
table { width:100%; border-collapse:collapse; }
thead th {
    text-align:left; padding:8px 16px;
    font-size:9.5px; font-weight:600; letter-spacing:.8px; text-transform:uppercase;
    color:var(--ink3); background:var(--wash); border-bottom:1px solid var(--rule);
}
tbody tr { border-bottom:1px solid var(--rule); transition:background .1s; }
tbody tr:last-child { border-bottom:none; }
tbody tr:hover { background:var(--wash); }
tbody td { padding:9px 16px; vertical-align:middle; font-size:12.5px; }
.tr-today td  { background:#eef4ff; }
.tr-today:hover td { background:#dceaff !important; }
.tr-weekend td { background:rgba(180,83,9,.02); }
.td-tgl { font-family:'IBM Plex Mono',monospace; font-size:12px; font-weight:500; }
.hari-wknd { color:var(--amber); }

/* Avatar shift */
.mava { display:inline-flex; align-items:center; justify-content:center; width:22px; height:22px; border-radius:3px; font-size:9px; font-weight:700; text-transform:uppercase; margin-right:5px; vertical-align:middle; }
.mv-p { background:var(--amber-lt); color:var(--amber); }
.mv-s { background:var(--blue-lt); color:var(--blue); }

/* ════════════ BADGE / PILL ════════════ */
.badge       { display:inline-block; font-size:10px; font-weight:500; padding:2px 8px; border-radius:3px; }
.badge-green { background:var(--green-lt); color:var(--green); }
.badge-amber { background:var(--amber-lt); color:var(--amber); }
.badge-gray  { background:var(--wash2); color:var(--ink3); }
.badge-blue  { background:var(--blue-lt); color:var(--blue); }
.badge-red   { background:var(--red-lt); color:var(--red); }

/* ════════════ PROGRESS BARS (Laporan) ════════════ */
.prog-row   { display:flex; align-items:center; gap:10px; padding:9px 16px; border-bottom:1px solid var(--rule); }
.prog-row:last-child { border-bottom:none; }
.prog-label { font-size:11px; color:var(--ink2); width:90px; flex-shrink:0; }
.prog-track { flex:1; height:5px; background:var(--wash2); border-radius:3px; overflow:hidden; }
.prog-fill  { height:100%; border-radius:3px; transition:width .7s ease; }
.prog-val   { font-size:11px; font-family:'IBM Plex Mono',monospace; color:var(--ink); font-weight:500; width:20px; text-align:right; flex-shrink:0; }

/* ════════════ NILAI KINERJA (ring) ════════════ */
.score-wrap  { padding:16px; display:flex; flex-direction:column; align-items:center; gap:8px; }
.score-ring  { position:relative; width:96px; height:96px; }
.score-center {
    position:absolute; inset:0;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    pointer-events:none;
}
.score-num   { font-size:24px; font-weight:300; font-family:'IBM Plex Mono',monospace; color:var(--ink); line-height:1; }
.score-sub   { font-size:9px; color:var(--ink3); }
.score-predikat { font-size:11px; font-weight:500; padding:3px 10px; border-radius:10px; }

/* ════════════ AKSI CEPAT ════════════ */
.qa-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; padding:10px; }
.qa {
    display:flex; align-items:center; gap:8px; padding:9px 10px;
    border:1px solid var(--rule); border-radius:6px; background:var(--wash);
    text-decoration:none; transition:border-color .12s, background .12s;
}
.qa:hover { border-color:var(--green); background:var(--green-lt); }
.qa:hover .qa-lab { color:var(--green); }
.qa-icon { width:28px; height:28px; border-radius:5px; background:var(--surface); border:1px solid var(--rule); display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.qa-icon svg { stroke:var(--ink2); }
.qa-lab { font-size:11.5px; font-weight:500; color:var(--ink2); }

/* ════════════ AKTIVITAS ════════════ */
.act-item { display:flex; gap:10px; padding:9px 16px; border-bottom:1px solid var(--rule); }
.act-item:last-child { border-bottom:none; }
.act-dot  { width:6px; height:6px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.act-body { flex:1; min-width:0; }
.act-text { font-size:12px; color:var(--ink2); line-height:1.5; }
.act-text strong { font-weight:600; color:var(--ink); }
.act-time { font-size:10px; color:var(--ink3); margin-top:1px; font-family:'IBM Plex Mono',monospace; }

/* ════════════ TUGAS ════════════ */
.tugas-row {
    display:flex; align-items:center; gap:10px;
    padding:10px 16px; border-bottom:1px solid var(--rule);
    text-decoration:none; transition:background .1s;
}
.tugas-row:last-child { border-bottom:none; }
.tugas-row:hover { background:var(--wash); }
.tugas-dot  { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
.tugas-info { flex:1; min-width:0; }
.tugas-judul { font-size:12px; font-weight:500; color:var(--ink); white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.tugas-dl    { font-size:10.5px; color:var(--ink3); margin-top:1px; }
.tugas-dl.lewat { color:var(--red); font-weight:500; }

/* ════════════ EMPTY ════════════ */
.db-empty { padding:28px 16px; text-align:center; color:var(--ink3); font-size:12.5px; }
.db-empty svg { display:block; margin:0 auto 8px; opacity:.25; }

/* ════════════ ANIMASI MASUK ════════════ */
@keyframes dbFadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.db > * { animation:dbFadeUp .3s ease both; }
.db > *:nth-child(1){animation-delay:.03s}
.db > *:nth-child(2){animation-delay:.07s}
.db > *:nth-child(3){animation-delay:.11s}
.db > *:nth-child(4){animation-delay:.15s}
.db > *:nth-child(5){animation-delay:.19s}
.db > *:nth-child(6){animation-delay:.23s}

/* ════════════ RESPONSIVE ════════════ */
@media(max-width:1100px){
    .kpi-row  { grid-template-columns:repeat(2,1fr); }
    .row-main { grid-template-columns:1fr; }
    .row-3    { grid-template-columns:1fr 1fr; }
}
@media(max-width:700px){
    .kpi-row  { grid-template-columns:1fr 1fr; }
    .row-3    { grid-template-columns:1fr; }
    .qa-grid  { grid-template-columns:1fr; }
}
</style>
@endpush

@section('content')
@php
    $hariNama  = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $now       = \Carbon\Carbon::now('Asia/Jakarta');
    $dow       = $now->dayOfWeek;
    $isWeekend = ($dow === 0 || $dow === 6);

    // Hitung total laporan per status untuk progress bar
    $totalLaporanBulanIni = ($laporanApproved ?? 0) + ($laporanPending ?? 0) + ($laporanDraft ?? 0) + ($laporanRejected ?? 0);
    $maxLaporan = max(1, $totalLaporanBulanIni);

    // Warna & label nilai kinerja
    $nilaiColor = '#888780'; $nilaiLabel = 'Belum dinilai'; $nilaiBg = 'var(--wash2)';
    if(isset($nilaiKinerja) && $nilaiKinerja !== null) {
        if($nilaiKinerja >= 85)      { $nilaiColor = '#0a7c4e'; $nilaiLabel = 'Sangat Baik'; $nilaiBg = 'var(--green-lt)'; }
        elseif($nilaiKinerja >= 70)  { $nilaiColor = '#0a7c4e'; $nilaiLabel = 'Baik';        $nilaiBg = 'var(--green-lt)'; }
        elseif($nilaiKinerja >= 55)  { $nilaiColor = '#b45309'; $nilaiLabel = 'Cukup';       $nilaiBg = 'var(--amber-lt)'; }
        else                         { $nilaiColor = '#c0392b'; $nilaiLabel = 'Kurang';      $nilaiBg = 'var(--red-lt)'; }
    }
@endphp

<div class="db">

{{-- ══════════════════════════════════════
     HEADER
══════════════════════════════════════ --}}
<div class="db-head">
    <div>
        <h1>Selamat datang, {{ explode(' ', Auth::user()->name)[0] }} 👋</h1>
        <p>{{ $hariNama[$dow] }}, {{ $now->translatedFormat('d F Y') }} &middot; Portal Petugas PST</p>
    </div>
    <div>
        @if(isset($sudahAbsen) && $sudahAbsen)
            <span class="pill-status pill-green">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Sudah Absen — {{ $jamMasuk ?? '' }}
            </span>
        @else
            <a href="{{ route('petugas.absensi.index') }}" class="pill-status pill-amber">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                Belum Absen — Klik untuk absen
            </a>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════
     KPI CARDS
══════════════════════════════════════ --}}
<div>
    <div class="sec">Ringkasan Bulan {{ $now->translatedFormat('F Y') }}</div>
    <div class="kpi-row">

        {{-- Jadwal --}}
        <a href="{{ route('petugas.jadwal') }}" class="kpi kpi-blue">
            <div class="kpi-label">Jadwal Shift</div>
            <div class="kpi-val">{{ $totalJadwal ?? 0 }}</div>
            <div class="kpi-foot">
                <span class="kpi-chip">{{ $now->translatedFormat('F') }}</span>
                <span>shift dijadwalkan</span>
            </div>
            <svg class="kpi-deco" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </a>

        {{-- Kehadiran --}}
        <a href="{{ route('petugas.absensi.index') }}" class="kpi kpi-green">
            <div class="kpi-label">Kehadiran</div>
            <div class="kpi-val">{{ $totalHadir ?? 0 }}</div>
            <div class="kpi-foot">
                <span class="kpi-chip">{{ $pctHadir ?? 0 }}%</span>
                <span>dari jadwal</span>
            </div>
            <svg class="kpi-deco" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
            </svg>
        </a>

        {{-- Laporan Harian --}}
        <a href="{{ route('petugas.laporan.harian.index') }}" class="kpi kpi-amber">
            <div class="kpi-label">Laporan Harian</div>
            <div class="kpi-val">{{ $totalLaporan ?? 0 }}</div>
            <div class="kpi-foot">
                @if(($laporanPending ?? 0) > 0)
                    <span class="kpi-chip">{{ $laporanPending }} pending</span>
                    <span>menunggu review</span>
                @else
                    <span class="kpi-chip">bulan ini</span>
                    <span>laporan dikirim</span>
                @endif
            </div>
            <svg class="kpi-deco" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="16" y1="13" x2="8" y2="13"/><line x1="16" y1="17" x2="8" y2="17"/>
            </svg>
        </a>

        {{-- Nilai Kinerja --}}
        <a href="{{ route('petugas.penilaian.index') }}" class="kpi kpi-purple">
            <div class="kpi-label">Nilai Kinerja</div>
            <div class="kpi-val">{{ isset($nilaiKinerja) && $nilaiKinerja !== null ? $nilaiKinerja : '—' }}</div>
            <div class="kpi-foot">
                @if(isset($nilaiKinerja) && $nilaiKinerja !== null)
                    <span class="kpi-chip">{{ $nilaiLabel }}</span>
                    <span>penilaian terakhir</span>
                @else
                    <span>Belum ada penilaian</span>
                @endif
            </div>
            <svg class="kpi-deco" width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/>
            </svg>
        </a>

    </div>
</div>

{{-- ══════════════════════════════════════
     GRID UTAMA 2 KOLOM
══════════════════════════════════════ --}}
<div class="row-main">

    {{-- ─────────── KOLOM KIRI ─────────── --}}
    <div style="display:flex;flex-direction:column;gap:14px">

        {{-- Rekap Absensi --}}
        <div>
            <div class="sec">Absensi & Kehadiran</div>
            <div class="panel">
                <div class="ph">
                    <div>
                        <div class="ph-title">Rekap Absensi Bulan Ini</div>
                        <div class="ph-sub">{{ $now->translatedFormat('F Y') }}</div>
                    </div>
                    <a href="{{ route('petugas.absensi.index') }}" class="ph-link">
                        Detail
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>
                <div class="abs-wrap">
                    <div class="abs-donut">
                        <canvas id="donutAbsensi" width="80" height="80"></canvas>
                        <div class="abs-center">
                            <span class="abs-pct">{{ $pctHadir ?? 0 }}%</span>
                            <span class="abs-unit">hadir</span>
                        </div>
                    </div>
                    <div class="abs-legs">
                        <div class="abs-leg">
                            <div class="abs-ldot" style="background:var(--green)"></div>
                            <span class="abs-lname">Hadir</span>
                            <span class="abs-lval">{{ $totalHadir ?? 0 }}</span>
                        </div>
                        <div class="abs-leg">
                            <div class="abs-ldot" style="background:var(--amber)"></div>
                            <span class="abs-lname">Izin / Sakit</span>
                            <span class="abs-lval">{{ $totalIzin ?? 0 }}</span>
                        </div>
                        <div class="abs-leg">
                            <div class="abs-ldot" style="background:var(--ink3)"></div>
                            <span class="abs-lname">Tidak Hadir</span>
                            <span class="abs-lval">{{ $totalAlpha ?? 0 }}</span>
                        </div>
                    </div>
                </div>
                <div class="abs-grid">
                    <div class="abs-cell">
                        <div class="abs-cnum" style="color:var(--green)">{{ $totalHadir ?? 0 }}</div>
                        <div class="abs-clab">Hadir</div>
                    </div>
                    <div class="abs-cell">
                        <div class="abs-cnum" style="color:var(--amber)">{{ $totalIzin ?? 0 }}</div>
                        <div class="abs-clab">Izin</div>
                    </div>
                    <div class="abs-cell">
                        <div class="abs-cnum" style="color:var(--ink3)">{{ $totalAlpha ?? 0 }}</div>
                        <div class="abs-clab">Alpha</div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Jadwal Shift --}}
        <div>
            <div class="sec">Jadwal Shift</div>

            {{-- Hari Ini --}}
            <div class="panel" style="margin-bottom:10px">
                <div class="ph">
                    <div>
                        <div class="ph-title">Jadwal Hari Ini</div>
                        <div class="ph-sub">{{ $hariNama[$dow] }}, {{ $now->translatedFormat('d F Y') }}</div>
                    </div>
                    <a href="{{ route('petugas.jadwal') }}" class="ph-link">
                        Jadwal bulan ini
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                @php
                    $jadwalHariIni = isset($jadwalPetugas)
                        ? $jadwalPetugas->filter(fn($j) => \Carbon\Carbon::parse($j->tanggal)->isToday())
                        : collect();
                @endphp

                @if($jadwalHariIni->isEmpty())
                    <div class="db-empty">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Tidak ada jadwal tugas hari ini.
                    </div>
                @else
                    @foreach($jadwalHariIni as $j)
                    @php $ket = $j->keterangan ?? 'normal'; @endphp
                    <div class="shift-row">
                        <div style="display:flex;align-items:center;gap:10px">
                            <div class="shift-badge {{ $j->shift === 'pagi' ? 'sh-p' : 'sh-s' }}">
                                {{ strtoupper(substr($j->shift ?? 'P', 0, 2)) }}
                            </div>
                            <div>
                                <div class="shift-label">Shift {{ ucfirst($j->shift ?? 'Pagi') }}</div>
                                <div class="shift-sub">{{ $j->shift === 'pagi' ? '07.00 – 12.00 WIB' : '12.00 – 17.00 WIB' }}</div>
                            </div>
                        </div>
                        @if($ket === 'diganti')
                            <span class="badge badge-amber">Diganti</span>
                        @elseif($ket === 'libur')
                            <span class="badge badge-gray">Libur</span>
                        @else
                            <span class="badge badge-green">Normal</span>
                        @endif
                    </div>
                    @endforeach
                @endif
            </div>

            {{-- Tabel Bulan Ini --}}
            <div class="panel">
                <div class="ph">
                    <div>
                        <div class="ph-title">Jadwal Bulan Ini</div>
                        <div class="ph-sub">{{ $now->translatedFormat('F Y') }} — {{ $totalJadwal ?? 0 }} shift dijadwalkan</div>
                    </div>
                    <a href="{{ route('petugas.jadwal') }}" class="ph-link">
                        Lihat semua
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                @php
                    $jadwalBulanIni = isset($jadwalPetugas)
                        ? $jadwalPetugas->filter(fn($j) => \Carbon\Carbon::parse($j->tanggal)->month === $now->month)->sortBy('tanggal')
                        : collect();
                @endphp

                @if($jadwalBulanIni->isEmpty())
                    <div class="db-empty">
                        <svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                            <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
                        </svg>
                        Belum ada jadwal bulan ini.
                    </div>
                @else
                    <table>
                        <thead>
                            <tr>
                                <th style="width:90px">Tanggal</th>
                                <th style="width:90px">Hari</th>
                                <th>Shift</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($jadwalBulanIni->take(10) as $j)
                            @php
                                $tgl    = \Carbon\Carbon::parse($j->tanggal);
                                $jDow   = $tgl->dayOfWeek;
                                $isWknd = ($jDow === 0 || $jDow === 6);
                                $isToday = $tgl->isToday();
                                $ket    = $j->keterangan ?? 'normal';
                            @endphp
                            <tr class="{{ $isToday ? 'tr-today' : ($isWknd ? 'tr-weekend' : '') }}">
                                <td class="td-tgl">
                                    {{ $tgl->translatedFormat('d M') }}
                                    @if($isToday)
                                        <span class="badge badge-blue" style="font-size:8.5px;vertical-align:middle;margin-left:3px">Hari ini</span>
                                    @endif
                                </td>
                                <td>
                                    <span class="{{ $isWknd ? 'hari-wknd' : '' }}" style="font-size:12px;color:var(--ink2)">
                                        {{ $hariNama[$jDow] }}
                                    </span>
                                </td>
                                <td>
                                    @if($j->shift === 'pagi')
                                        <span class="mava mv-p">PG</span>
                                        <span style="font-size:12px;color:var(--ink2)">Pagi</span>
                                    @else
                                        <span class="mava mv-s">SG</span>
                                        <span style="font-size:12px;color:var(--ink2)">Siang</span>
                                    @endif
                                </td>
                                <td>
                                    @if($ket === 'diganti')
                                        <span class="badge badge-amber">Diganti</span>
                                    @elseif($ket === 'libur')
                                        <span class="badge badge-gray">Libur</span>
                                    @else
                                        <span class="badge badge-green">Normal</span>
                                    @endif
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                    @if($jadwalBulanIni->count() > 10)
                    <div style="padding:10px 16px;border-top:1px solid var(--rule);text-align:center">
                        <a href="{{ route('petugas.jadwal') }}" class="ph-link" style="font-size:11.5px">
                            Lihat {{ $jadwalBulanIni->count() - 10 }} jadwal lainnya →
                        </a>
                    </div>
                    @endif
                @endif
            </div>
        </div>

        {{-- Laporan & Checklist & Nilai ── 3 kolom --}}
        <div>
            <div class="sec">Laporan, Checklist & Kinerja</div>
            <div class="row-3">

                {{-- Laporan Harian --}}
                <div class="panel" style="display:flex;flex-direction:column">
                    <div class="ph">
                        <div>
                            <div class="ph-title">Laporan Harian</div>
                            <div class="ph-sub">Status bulan ini</div>
                        </div>
                        <a href="{{ route('petugas.laporan.harian.index') }}" class="ph-link">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                    <div style="flex:1">
                        <div class="prog-row">
                            <div class="prog-label">Disetujui</div>
                            <div class="prog-track">
                                <div class="prog-fill" style="width:{{ $maxLaporan > 0 ? round(($laporanApproved ?? 0) / $maxLaporan * 100) : 0 }}%;background:var(--green)"></div>
                            </div>
                            <div class="prog-val" style="color:var(--green)">{{ $laporanApproved ?? 0 }}</div>
                        </div>
                        <div class="prog-row">
                            <div class="prog-label">Menunggu</div>
                            <div class="prog-track">
                                <div class="prog-fill" style="width:{{ $maxLaporan > 0 ? round(($laporanPending ?? 0) / $maxLaporan * 100) : 0 }}%;background:var(--amber)"></div>
                            </div>
                            <div class="prog-val" style="color:var(--amber)">{{ $laporanPending ?? 0 }}</div>
                        </div>
                        <div class="prog-row">
                            <div class="prog-label">Draft</div>
                            <div class="prog-track">
                                <div class="prog-fill" style="width:{{ $maxLaporan > 0 ? round(($laporanDraft ?? 0) / $maxLaporan * 100) : 0 }}%;background:var(--ink3)"></div>
                            </div>
                            <div class="prog-val">{{ $laporanDraft ?? 0 }}</div>
                        </div>
                        <div class="prog-row">
                            <div class="prog-label">Dikembalikan</div>
                            <div class="prog-track">
                                <div class="prog-fill" style="width:{{ $maxLaporan > 0 ? round(($laporanRejected ?? 0) / $maxLaporan * 100) : 0 }}%;background:var(--red)"></div>
                            </div>
                            <div class="prog-val" style="color:var(--red)">{{ $laporanRejected ?? 0 }}</div>
                        </div>
                    </div>
                </div>

                {{-- Checklist Harian --}}
                <div class="panel" style="display:flex;flex-direction:column">
                    <div class="ph">
                        <div>
                            <div class="ph-title">Checklist Harian</div>
                            <div class="ph-sub">Kelengkapan bulan ini</div>
                        </div>
                        <a href="{{ route('petugas.checklist') }}" class="ph-link">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                    <div style="flex:1;padding:14px 16px;display:flex;flex-direction:column;align-items:center;gap:8px">
                        <div style="position:relative;width:80px;height:80px">
                            <canvas id="donutChecklist" width="80" height="80"></canvas>
                            <div style="position:absolute;inset:0;display:flex;flex-direction:column;align-items:center;justify-content:center;pointer-events:none">
                                <span style="font-size:17px;font-weight:300;font-family:'IBM Plex Mono',monospace;color:var(--ink);line-height:1">{{ $pctChecklist ?? 0 }}%</span>
                                <span style="font-size:9px;color:var(--ink3)">terverif.</span>
                            </div>
                        </div>
                        <div style="font-size:10.5px;color:var(--ink3);text-align:center">checklist terverifikasi</div>
                    </div>
                    <div class="abs-grid" style="grid-template-columns:1fr 1fr">
                        <div class="abs-cell">
                            <div class="abs-cnum" style="color:var(--green)">{{ $totalChecklist ?? 0 }}</div>
                            <div class="abs-clab">Verified</div>
                        </div>
                        <div class="abs-cell">
                            <div class="abs-cnum" style="color:var(--ink3)">{{ ($totalJadwal ?? 0) - ($totalChecklist ?? 0) > 0 ? ($totalJadwal - $totalChecklist) : 0 }}</div>
                            <div class="abs-clab">Draft</div>
                        </div>
                    </div>
                </div>

                {{-- Nilai Kinerja --}}
                <div class="panel" style="display:flex;flex-direction:column">
                    <div class="ph">
                        <div>
                            <div class="ph-title">Nilai Kinerja</div>
                            <div class="ph-sub">Evaluasi terakhir</div>
                        </div>
                        <a href="{{ route('petugas.penilaian.index') }}" class="ph-link">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                        </a>
                    </div>
                    <div class="score-wrap" style="flex:1">
                        <div class="score-ring">
                            <canvas id="donutNilai" width="96" height="96"
                                data-nilai="{{ $nilaiKinerja ?? 0 }}"
                                data-color="{{ $nilaiColor }}">
                            </canvas>
                            <div class="score-center">
                                <span class="score-num">{{ isset($nilaiKinerja) && $nilaiKinerja !== null ? $nilaiKinerja : '—' }}</span>
                                @if(isset($nilaiKinerja) && $nilaiKinerja !== null)
                                <span class="score-sub">/ 100</span>
                                @endif
                            </div>
                        </div>
                        @if(isset($nilaiKinerja) && $nilaiKinerja !== null)
                        <span class="score-predikat" style="background:{{ $nilaiBg }};color:{{ $nilaiColor }}">{{ $nilaiLabel }}</span>
                        @else
                        <span style="font-size:11px;color:var(--ink3)">Belum ada penilaian</span>
                        @endif
                        @if(isset($evalTerakhir) && $evalTerakhir)
                        <div style="font-size:10px;color:var(--ink3);text-align:center;line-height:1.7">
                            Penilaian: {{ \Carbon\Carbon::parse($evalTerakhir->created_at)->translatedFormat('M Y') }}<br>
                            Oleh Koordinator Wilayah
                        </div>
                        @endif
                    </div>
                </div>

            </div>
        </div>

    </div>{{-- /kolom kiri --}}

    {{-- ─────────── KOLOM KANAN ─────────── --}}
    <div style="display:flex;flex-direction:column;gap:14px">

        {{-- Aksi Cepat --}}
        <div>
            <div class="sec">Aksi Cepat</div>
            <div class="panel">
                <div class="qa-grid">
                    <a href="{{ route('petugas.absensi.index') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        </div>
                        <span class="qa-lab">Absensi QR</span>
                    </a>
                    <a href="{{ route('petugas.checklist') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><line x1="9" y1="12" x2="15" y2="12"/><line x1="9" y1="16" x2="13" y2="16"/></svg>
                        </div>
                        <span class="qa-lab">Checklist</span>
                    </a>
                    <a href="{{ route('petugas.laporan.harian.create') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg>
                        </div>
                        <span class="qa-lab">Buat Laporan</span>
                    </a>
                    <a href="{{ route('petugas.materi') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg>
                        </div>
                        <span class="qa-lab">Materi & Tugas</span>
                    </a>
                    <a href="{{ route('petugas.penilaian.index') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        </div>
                        <span class="qa-lab">Nilai Saya</span>
                    </a>
                    <a href="{{ route('petugas.survey.index') }}" class="qa">
                        <div class="qa-icon">
                            <svg width="14" height="14" fill="none" stroke-width="1.5" viewBox="0 0 24 24"><path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/></svg>
                        </div>
                        <span class="qa-lab">Survey</span>
                    </a>
                </div>
            </div>
        </div>

        {{-- Aktivitas Terakhir --}}
        <div>
            <div class="sec">Aktivitas Terakhir</div>
            <div class="panel">
                @forelse(isset($aktivitas) ? $aktivitas : [] as $act)
                <div class="act-item">
                    <div class="act-dot" style="background:{{ $act->type === 'grn' ? 'var(--green)' : ($act->type === 'blu' ? 'var(--blue)' : ($act->type === 'amb' ? 'var(--amber)' : 'var(--ink3)')) }}"></div>
                    <div class="act-body">
                        <div class="act-text">{{ $act->keterangan }}</div>
                        <div class="act-time">{{ \Carbon\Carbon::parse($act->created_at)->diffForHumans() }}</div>
                    </div>
                </div>
                @empty
                <div class="db-empty">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                    Belum ada aktivitas tercatat.
                </div>
                @endforelse
            </div>
        </div>

        {{-- Tugas Aktif --}}
        <div>
            <div class="sec">Tugas Aktif</div>
            <div class="panel">
                <div class="ph">
                    <div class="ph-title">Dari Admin & Koordinator</div>
                    <a href="{{ route('petugas.materi') }}" class="ph-link">
                        Semua
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                    </a>
                </div>

                @php
                    $tugasAktif = isset($tugasAktif) ? $tugasAktif : collect();
                @endphp

                @forelse($tugasAktif as $t)
                @php
                    $daysLeft = \Carbon\Carbon::parse($t->deadline ?? $t->due_date ?? now())->diffInDays(now(), false);
                    $isLewat  = $daysLeft > 0;
                @endphp
                <a href="{{ route('petugas.materi') }}" class="tugas-row">
                    <div class="tugas-dot" style="background:{{ $isLewat ? 'var(--red)' : ($daysLeft > -3 ? 'var(--amber)' : 'var(--blue)') }}"></div>
                    <div class="tugas-info">
                        <div class="tugas-judul">{{ $t->judul ?? $t->title ?? 'Tugas' }}</div>
                        <div class="tugas-dl {{ $isLewat ? 'lewat' : '' }}">
                            Deadline: {{ \Carbon\Carbon::parse($t->deadline ?? $t->due_date ?? now())->translatedFormat('d M Y') }}
                            @if($isLewat) · Terlambat {{ abs($daysLeft) }} hari @endif
                        </div>
                    </div>
                    @if($isLewat)
                        <span class="badge badge-red" style="flex-shrink:0">Terlambat</span>
                    @elseif($daysLeft > -3)
                        <span class="badge badge-amber" style="flex-shrink:0">Segera</span>
                    @else
                        <span class="badge badge-blue" style="flex-shrink:0">Aktif</span>
                    @endif
                </a>
                @empty
                <div class="db-empty">
                    <svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/><polyline points="9 12 11 14 15 10"/></svg>
                    Tidak ada tugas aktif saat ini.
                </div>
                @endforelse
            </div>
        </div>

    </div>{{-- /kolom kanan --}}

</div>{{-- /row-main --}}

</div>{{-- /db --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    const absEl = document.getElementById('donutAbsensi');
    if (absEl) {
        new Chart(absEl, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [
                        {{ $totalHadir ?? 0 }},
                        {{ $totalIzin  ?? 0 }},
                        {{ $totalAlpha ?? 0 }}
                    ],
                    backgroundColor: ['#0a7c4e','#b45309','#7a8394'],
                    borderWidth: 0,
                    spacing: 2
                }]
            },
            options: {
                cutout: '68%',
                animation: { duration: 800 },
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }

    const clEl = document.getElementById('donutChecklist');
    if (clEl) {
        const pct = parseInt('{{ $pctChecklist ?? 0 }}') || 0;
        new Chart(clEl, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [pct, 100 - pct],
                    backgroundColor: ['#0a7c4e','#eef0f3'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '68%',
                animation: { duration: 800 },
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }

    const nkEl = document.getElementById('donutNilai');
    if (nkEl) {
        const nilai = parseFloat(nkEl.dataset.nilai) || 0;
        const warna = nkEl.dataset.color || '#888780';
        new Chart(nkEl, {
            type: 'doughnut',
            data: {
                datasets: [{
                    data: [nilai, 100 - nilai],
                    backgroundColor: [warna, '#eef0f3'],
                    borderWidth: 0
                }]
            },
            options: {
                cutout: '72%',
                animation: { duration: 800 },
                plugins: { legend: { display: false }, tooltip: { enabled: false } }
            }
        });
    }
});
</script>
@endpush