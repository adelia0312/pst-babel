@extends('layouts.admin')
@section('title', 'Dashboard Admin')
@section('breadcrumb')
    <span>PST</span><span>›</span><strong>Dashboard Admin</strong>
@endsection

@push('styles')
<style>
/* ══════════════════════════════════════
   DASHBOARD GRID SYSTEM
   Semua ukuran seragam, tidak ada yang "mengambang"
   ══════════════════════════════════════ */

.db { display:flex; flex-direction:column; gap:20px; }

/* ── Heading ── */
.db-head {
    display:flex; align-items:center; justify-content:space-between;
    padding-bottom:16px; border-bottom:1px solid var(--rule);
}
.db-head h1 { font-size:16px; font-weight:600; color:var(--ink); letter-spacing:-.2px; }
.db-head p  { font-size:11.5px; color:var(--ink3); margin-top:3px; }
.db-badge {
    font-size:11.5px; font-weight:500; color:var(--blue);
    background:var(--blue-lt); border:1px solid rgba(26,86,219,.15);
    padding:5px 12px; border-radius:4px; white-space:nowrap;
}

/* ── Section title ── */
.sec {
    font-size:9.5px; font-weight:600; letter-spacing:1.5px;
    text-transform:uppercase; color:var(--ink3);
    display:flex; align-items:center; gap:8px; margin-bottom:10px;
}
.sec::after { content:''; flex:1; height:1px; background:var(--rule); }

/* ════════════════════════════════
   KPI CARDS — warna solid, bold
   ════════════════════════════════ */
.kpi-row {
    display:grid;
    grid-template-columns:repeat(4,1fr);
    gap:12px;
}
.kpi {
    border-radius:8px;
    padding:18px 20px 16px;
    display:block; text-decoration:none;
    position:relative; overflow:hidden;
    transition:transform .12s, box-shadow .15s;
}
.kpi:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(0,0,0,.13); }

/* warna tiap card */
.kpi-blue   { background:#1a56db; color:#fff; }
.kpi-green  { background:#0a7c4e; color:#fff; }
.kpi-amber  { background:#b45309; color:#fff; }
.kpi-purple { background:#5b21b6; color:#fff; }

.kpi-label {
    font-size:10.5px; font-weight:600; letter-spacing:.5px;
    text-transform:uppercase; opacity:.75; margin-bottom:10px;
}
.kpi-val {
    font-size:38px; font-weight:300; letter-spacing:-2px;
    font-family:'IBM Plex Mono',monospace; line-height:1;
    margin-bottom:10px;
}
.kpi-foot {
    display:flex; align-items:center; gap:6px;
    font-size:11px; opacity:.72;
}
.kpi-chip {
    font-size:9.5px; font-weight:700;
    background:rgba(255,255,255,.2);
    padding:2px 6px; border-radius:3px;
    font-family:'IBM Plex Mono',monospace;
}
/* icon dekoratif kanan atas */
.kpi-deco {
    position:absolute; right:16px; top:14px;
    opacity:.18;
}

/* ════════════════════════════════
   CHART PANEL
   ════════════════════════════════ */
.chart-tabs { display:flex; gap:2px; background:var(--wash); border-radius:5px; padding:2px; }
.ctab {
    font-size:11px; font-weight:500; padding:4px 12px; border-radius:4px;
    border:none; background:transparent; color:var(--ink3); cursor:pointer;
    font-family:'IBM Plex Sans',sans-serif; transition:all .12s;
}
.ctab.active { background:var(--surface); color:var(--ink); box-shadow:0 1px 3px rgba(0,0,0,.08); }
.chart-legend { display:flex; gap:14px; padding:10px 18px; border-bottom:1px solid var(--rule); flex-wrap:wrap; }
.leg { display:flex; align-items:center; gap:6px; font-size:11px; color:var(--ink3); }
.leg-dot { width:9px; height:9px; border-radius:2px; flex-shrink:0; }
.chart-area { height:220px; padding:14px 16px 10px; position:relative; }

/* ════════════════════════════════
   2-COL + 1-COL GRID (tengah)
   ════════════════════════════════ */
.row-2-1 {
    display:grid;
    grid-template-columns:1fr 340px;
    gap:12px; align-items:start;
}
.row-3 {
    display:grid;
    grid-template-columns:repeat(3,1fr);
    gap:12px; align-items:stretch;
}
.row-3 > .panel {
    display:flex; flex-direction:column;
}
.row-3 > .panel .jdw-list,
.row-3 > .panel .act-list {
    flex:1;
}
/* empty state rata tengah di sisa ruang */
.row-3 > .panel .db-empty {
    flex:1; display:flex; flex-direction:column;
    align-items:center; justify-content:center;
    padding:32px 16px;
}

/* ════════════════════════════════
   DONUT ABSENSI
   ════════════════════════════════ */
.abs-wrap  { padding:16px 18px; display:flex; align-items:center; gap:16px; }
.abs-donut { position:relative; width:84px; height:84px; flex-shrink:0; }
.abs-donut canvas { display:block; }
.abs-center {
    position:absolute; inset:0;
    display:flex; flex-direction:column; align-items:center; justify-content:center;
    pointer-events:none;
}
.abs-pct   { font-size:19px; font-weight:300; font-family:'IBM Plex Mono',monospace; color:var(--ink); line-height:1; }
.abs-unit  { font-size:9.5px; color:var(--ink3); }
.abs-legs  { flex:1; display:flex; flex-direction:column; gap:7px; }
.abs-leg   { display:flex; align-items:center; gap:8px; }
.abs-ldot  { width:8px; height:8px; border-radius:2px; flex-shrink:0; }
.abs-lname { font-size:11px; color:var(--ink2); flex:1; }
.abs-lval  { font-size:11px; font-family:'IBM Plex Mono',monospace; color:var(--ink); font-weight:500; }

.abs-grid  {
    display:grid; grid-template-columns:repeat(4,1fr);
    gap:1px; background:var(--rule); border-top:1px solid var(--rule);
}
.abs-cell  { background:var(--surface); padding:11px 12px; text-align:center; }
.abs-cnum  { font-size:20px; font-weight:300; font-family:'IBM Plex Mono',monospace; line-height:1; }
.abs-clab  { font-size:9.5px; color:var(--ink3); font-weight:500; margin-top:3px; }

/* ════════════════════════════════
   JADWAL LIST
   ════════════════════════════════ */
.jdw-row {
    display:flex; align-items:center; gap:10px;
    padding:9px 16px; border-bottom:1px solid var(--rule);
}
.jdw-row:last-child { border-bottom:none; }
.jdw-shift {
    width:28px; height:28px; border-radius:5px;
    display:flex; align-items:center; justify-content:center;
    font-size:9px; font-weight:700; letter-spacing:.3px;
    text-transform:uppercase; flex-shrink:0;
}
.sh-p { background:var(--amber-lt); color:var(--amber); }
.sh-s { background:var(--blue-lt); color:var(--blue); }
.sh-m { background:#ede9fe; color:#6d28d9; }
.jdw-name { font-size:12px; font-weight:500; color:var(--ink); }
.jdw-wil  { font-size:10.5px; color:var(--ink3); }

/* ════════════════════════════════
   AKTIVITAS FEED
   ════════════════════════════════ */
.act-item { display:flex; gap:10px; padding:9px 16px; border-bottom:1px solid var(--rule); }
.act-item:last-child { border-bottom:none; }
.act-dot { width:6px; height:6px; border-radius:50%; flex-shrink:0; margin-top:5px; }
.act-body { flex:1; min-width:0; }
.act-text { font-size:12px; color:var(--ink2); line-height:1.5; }
.act-text strong { font-weight:600; color:var(--ink); }
.act-time { font-size:10px; color:var(--ink3); margin-top:1px; font-family:'IBM Plex Mono',monospace; }

/* ════════════════════════════════
   LAPORAN PENDING TABLE
   ════════════════════════════════ */
/* gunakan class table bawaan admin.css */

/* ════════════════════════════════
   PROGRESS BARS (checklist, kinerja)
   ════════════════════════════════ */
.prog-row  { display:flex; align-items:center; gap:10px; padding:8px 16px; border-bottom:1px solid var(--rule); }
.prog-row:last-child { border-bottom:none; }
.prog-label { font-size:11px; color:var(--ink2); width:90px; flex-shrink:0; overflow:hidden; text-overflow:ellipsis; white-space:nowrap; }
.prog-track { flex:1; height:5px; background:var(--wash2); border-radius:3px; overflow:hidden; }
.prog-fill  { height:100%; border-radius:3px; transition:width .7s ease; }
.prog-val   { font-size:10.5px; font-family:'IBM Plex Mono',monospace; color:var(--ink3); width:28px; text-align:right; flex-shrink:0; }

.stat-row {
    display:grid; gap:1px; background:var(--rule); border-top:1px solid var(--rule);
}
.stat-row.cols-3 { grid-template-columns:repeat(3,1fr); }
.stat-cell  { background:var(--surface); padding:11px 12px; text-align:center; }
.stat-num   { font-size:21px; font-weight:300; font-family:'IBM Plex Mono',monospace; line-height:1; }
.stat-lab   { font-size:9.5px; color:var(--ink3); font-weight:500; margin-top:2px; }

/* ════════════════════════════════
   SURVEI BINTANG
   ════════════════════════════════ */
.sv-score-wrap { display:flex; align-items:center; gap:14px; padding:14px 16px; border-bottom:1px solid var(--rule); }
.sv-num  { font-size:40px; font-weight:300; font-family:'IBM Plex Mono',monospace; color:var(--ink); letter-spacing:-2px; line-height:1; }
.sv-stars { display:flex; gap:2px; margin-bottom:4px; }
.sv-meta  { font-size:11px; color:var(--ink3); }
.star-row { display:flex; align-items:center; gap:8px; padding:7px 14px; border-bottom:1px solid var(--rule); }
.star-row:last-child { border-bottom:none; }
.star-n   { font-size:10.5px; color:var(--ink3); width:24px; text-align:right; font-family:'IBM Plex Mono',monospace; }
.star-t   { flex:1; height:5px; background:var(--wash2); border-radius:3px; overflow:hidden; }
.star-f   { height:100%; background:var(--amber); border-radius:3px; transition:width .7s ease; }
.star-c   { font-size:10px; color:var(--ink3); width:22px; font-family:'IBM Plex Mono',monospace; }

/* ════════════════════════════════
   AKSI CEPAT
   ════════════════════════════════ */
.qa-grid { display:grid; grid-template-columns:1fr 1fr; gap:6px; padding:10px; }
.qa {
    display:flex; align-items:center; gap:8px;
    padding:9px 10px; border:1px solid var(--rule); border-radius:6px;
    background:var(--wash); text-decoration:none;
    transition:border-color .12s, background .12s;
}
.qa:hover { border-color:var(--blue); background:var(--blue-lt); }
.qa:hover .qa-lab { color:var(--blue); }
.qa-lab { font-size:11.5px; font-weight:500; color:var(--ink2); }

/* ════════════════════════════════
   EMPTY STATES
   ════════════════════════════════ */
.db-empty {
    padding:28px 16px; text-align:center;
    color:var(--ink3); font-size:12px;
}
.db-empty svg { margin:0 auto 8px; display:block; opacity:.3; }

/* ════════════════════════════════
   ANIMASI MASUK
   ════════════════════════════════ */
@keyframes dbFadeUp { from{opacity:0;transform:translateY(8px)} to{opacity:1;transform:none} }
.db > * { animation:dbFadeUp .3s ease both; }
.db > *:nth-child(1){animation-delay:.03s}
.db > *:nth-child(2){animation-delay:.07s}
.db > *:nth-child(3){animation-delay:.11s}
.db > *:nth-child(4){animation-delay:.15s}
.db > *:nth-child(5){animation-delay:.19s}
.db > *:nth-child(6){animation-delay:.23s}

/* ════════════════════════════════
   RESPONSIVE
   ════════════════════════════════ */
@media(max-width:1100px){
    .kpi-row { grid-template-columns:repeat(2,1fr); }
    .row-2-1 { grid-template-columns:1fr; }
    .row-3   { grid-template-columns:1fr 1fr; }
}
@media(max-width:700px){
    .kpi-row { grid-template-columns:1fr; }
    .row-3   { grid-template-columns:1fr; }
    .abs-grid{ grid-template-columns:repeat(2,1fr); }
}
</style>
@endpush

@section('content')
<div class="db">

{{-- ══════════════════════════════════════
     HEADER
══════════════════════════════════════ --}}
<div class="db-head">
    <div>
        <h1>Monitoring Operasional PST</h1>
        <p>{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
    </div>
    <span class="db-badge">{{ Auth::user()->name }}</span>
</div>

{{-- ══════════════════════════════════════
     KPI UTAMA
══════════════════════════════════════ --}}
<div>
    <div class="sec">Indikator Utama</div>
    <div class="kpi-row">

        {{-- Total Petugas --}}
        <a href="{{ route('admin.tim-petugas') }}" class="kpi kpi-blue">
            <div class="kpi-deco">
                <svg width="44" height="44" fill="none" stroke="white" stroke-width="1.2" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/></svg>
            </div>
            <div class="kpi-label">Total Petugas</div>
            <div class="kpi-val">{{ $totalPetugas ?? 0 }}</div>
            <div class="kpi-foot">
                @if(($tambahPetugas ?? 0) > 0)
                    <span class="kpi-chip">+{{ $tambahPetugas }}</span> baru bulan ini
                @else
                    <span class="kpi-chip">Aktif</span> terdaftar
                @endif
            </div>
        </a>

        {{-- Hadir Hari Ini --}}
        <a href="{{ route('admin.absensi.index') }}" class="kpi kpi-green">
            <div class="kpi-deco">
                <svg width="44" height="44" fill="none" stroke="white" stroke-width="1.2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
            </div>
            <div class="kpi-label">Hadir Hari Ini</div>
            <div class="kpi-val">{{ $hadirHariIni ?? 0 }}</div>
            <div class="kpi-foot">
                <span class="kpi-chip">{{ $pctHadir ?? 0 }}%</span>
                dari {{ $totalTerjadwalHariIni ?? 0 }} terjadwal
            </div>
        </a>

        {{-- Laporan Pending --}}
        <a href="{{ route('admin.laporanharian.index') }}" class="kpi kpi-amber">
            <div class="kpi-deco">
                <svg width="44" height="44" fill="none" stroke="white" stroke-width="1.2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="9" y1="13" x2="15" y2="13"/><line x1="9" y1="17" x2="12" y2="17"/></svg>
            </div>
            <div class="kpi-label">Laporan Pending</div>
            <div class="kpi-val">{{ $laporanPending ?? 0 }}</div>
            <div class="kpi-foot">
                <span class="kpi-chip">Perlu Review</span>
                {{ $laporanApproved ?? 0 }} disetujui
            </div>
        </a>

        {{-- Rata-rata Kinerja --}}
        <a href="{{ route('admin.penilaian.index') }}" class="kpi kpi-purple">
            <div class="kpi-deco">
                <svg width="44" height="44" fill="none" stroke="white" stroke-width="1.2" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            </div>
            <div class="kpi-label">Rata-rata Kinerja</div>
            <div class="kpi-val">{{ $avgKinerja ?? 0 }}</div>
            <div class="kpi-foot">
                <span class="kpi-chip">{{ ($avgKinerja ?? 0) >= 80 ? 'Baik' : 'Perlu Peningkatan' }}</span>
                dari 100
            </div>
        </a>

    </div>
</div>

{{-- ══════════════════════════════════════
     GRAFIK AKTIVITAS OPERASIONAL
══════════════════════════════════════ --}}
<div>
    <div class="sec">Tren Aktivitas Operasional</div>
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Grafik Aktivitas Operasional</div>
                <div class="ph-sub" id="chart-sub-desc">30 hari terakhir — setiap titik = 1 hari</div>
            </div>
            <div class="chart-tabs">
                <button class="ctab active" onclick="switchChart('perWilayah',this)">Per Wilayah</button>
                <button class="ctab" onclick="switchChart('mingguan',this)">12 Minggu</button>
                <button class="ctab" onclick="switchChart('bulanan',this)">12 Bulan</button>
                <button class="ctab" onclick="switchChart('tahunan',this)">5 Tahun</button>
            </div>
        </div>

        {{-- Ringkasan angka di atas grafik - 1 baris compact --}}
        <div style="display:flex;gap:1px;background:var(--rule);border-bottom:1px solid var(--rule);overflow:hidden">
            <div style="background:var(--surface);padding:8px 12px;display:flex;align-items:center;gap:7px;flex:1;min-width:0">
                <div style="width:7px;height:7px;border-radius:2px;background:#1a56db;flex-shrink:0"></div>
                <span style="font-size:9.5px;color:var(--ink3);white-space:nowrap">Absensi</span>
                <span style="font-size:14px;font-weight:500;font-family:'IBM Plex Mono',monospace;color:var(--ink);margin-left:auto" id="sum-hadir">—</span>
            </div>
            <div style="background:var(--surface);padding:8px 12px;display:flex;align-items:center;gap:7px;flex:1;min-width:0">
                <div style="width:7px;height:7px;border-radius:2px;background:#0a7c4e;flex-shrink:0"></div>
                <span style="font-size:9.5px;color:var(--ink3);white-space:nowrap">Laporan</span>
                <span style="font-size:14px;font-weight:500;font-family:'IBM Plex Mono',monospace;color:var(--ink);margin-left:auto" id="sum-laporan">—</span>
            </div>
            <div style="background:var(--surface);padding:8px 12px;display:flex;align-items:center;gap:7px;flex:1;min-width:0">
                <div style="width:7px;height:7px;border-radius:2px;background:#b45309;flex-shrink:0"></div>
                <span style="font-size:9.5px;color:var(--ink3);white-space:nowrap">Checklist</span>
                <span style="font-size:14px;font-weight:500;font-family:'IBM Plex Mono',monospace;color:var(--ink);margin-left:auto" id="sum-checklist">—</span>
            </div>
            <div style="background:var(--surface);padding:8px 12px;display:flex;align-items:center;gap:7px;flex:1;min-width:0" id="sum-survey-wrap">
                <div style="width:7px;height:7px;border-radius:2px;background:#5b21b6;flex-shrink:0"></div>
                <span style="font-size:9.5px;color:var(--ink3);white-space:nowrap">Survei</span>
                <span style="font-size:14px;font-weight:500;font-family:'IBM Plex Mono',monospace;color:var(--ink);margin-left:auto" id="sum-survey">—</span>
            </div>
            <div style="background:var(--surface);padding:8px 12px;display:flex;align-items:center;gap:7px;flex:1;min-width:0" id="sum-kinerja-wrap">
                <div style="width:7px;height:7px;border-radius:2px;background:#dc2626;flex-shrink:0"></div>
                <span style="font-size:9.5px;color:var(--ink3);white-space:nowrap">Rata Kinerja</span>
                <span style="font-size:14px;font-weight:500;font-family:'IBM Plex Mono',monospace;color:var(--ink);margin-left:auto" id="sum-kinerja">—</span>
            </div>
        </div>

        {{-- Keterangan cara baca --}}
        <div style="background:#f8faff;border-bottom:1px solid var(--rule);padding:8px 16px;display:flex;align-items:center;gap:8px;flex-wrap:wrap">
            <svg width="12" height="12" fill="none" stroke="#1a56db" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            <span style="font-size:10.5px;color:#374151"><strong>Cara baca:</strong> Garis <span style="color:#1a56db;font-weight:600">biru</span> = jumlah absensi masuk, <span style="color:#0a7c4e;font-weight:600">hijau</span> = laporan shift dikirim, <span style="color:#b45309;font-weight:600">oranye</span> = checklist selesai. Hover titik untuk detail. Jika grafik kosong di kiri, artinya sistem belum lama berjalan.</span>
        </div>

        <div class="chart-legend">
            <span class="leg"><span class="leg-dot" style="background:#1a56db"></span>Kehadiran</span>
            <span class="leg"><span class="leg-dot" style="background:#0a7c4e"></span>Laporan Masuk</span>
            <span class="leg"><span class="leg-dot" style="background:#b45309"></span>Checklist Selesai</span>
            <span class="leg" id="leg-survey" style="display:none"><span class="leg-dot" style="background:#5b21b6"></span>Survei Kepuasan</span>
            <span class="leg"><span class="leg-dot" style="background:#dc2626;border-radius:0"></span>Rata Kinerja (kanan)</span>
        </div>
        <div class="chart-area" style="height:260px"><canvas id="mainChart"></canvas></div>
    </div>
</div>

{{-- ══════════════════════════════════════
     ABSENSI · JADWAL · AKTIVITAS
══════════════════════════════════════ --}}
<div>
    <div class="sec">Kehadiran & Jadwal Hari Ini</div>
    <div class="row-3">

        {{-- Absensi --}}
        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Absensi Hari Ini</div>
                    <div class="ph-sub">Status kehadiran seluruh petugas</div>
                </div>
                <a href="{{ route('admin.absensi.index') }}" class="ph-link">Detail →</a>
            </div>
            <div class="abs-wrap">
                <div class="abs-donut">
                    <canvas id="donutChart" width="84" height="84"></canvas>
                    <div class="abs-center">
                        <span class="abs-pct">{{ $pctHadir ?? 0 }}</span>
                        <span class="abs-unit">%</span>
                    </div>
                </div>
                <div class="abs-legs">
                    <div class="abs-leg">
                        <div class="abs-ldot" style="background:#0a7c4e"></div>
                        <div class="abs-lname">Hadir</div>
                        <div class="abs-lval">{{ $hadirHariIni ?? 0 }}</div>
                    </div>
                    <div class="abs-leg">
                        <div class="abs-ldot" style="background:#c0392b"></div>
                        <div class="abs-lname">Alpha</div>
                        <div class="abs-lval">{{ $absensiStatus['alpha'] ?? 0 }}</div>
                    </div>
                    <div class="abs-leg">
                        <div class="abs-ldot" style="background:var(--wash2)"></div>
                        <div class="abs-lname">Blm Absen (terjadwal)</div>
                        <div class="abs-lval">{{ $belumAbsenHariIni ?? 0 }}</div>
                    </div>
                </div>
            </div>
            <div class="abs-grid">
                <div class="abs-cell">
                    <div class="abs-cnum" style="color:#0a7c4e">{{ ($absensiStatus['tepat_waktu'] ?? 0) + ($absensiStatus['toleransi'] ?? 0) }}</div>
                    <div class="abs-clab">Tepat Waktu</div>
                </div>
                <div class="abs-cell">
                    <div class="abs-cnum" style="color:#b45309">{{ $absensiStatus['terlambat'] ?? 0 }}</div>
                    <div class="abs-clab">Terlambat</div>
                </div>
                <div class="abs-cell">
                    <div class="abs-cnum" style="color:#c0392b">{{ $absensiStatus['alpha'] ?? 0 }}</div>
                    <div class="abs-clab">Alpha</div>
                </div>
                <div class="abs-cell">
                    <div class="abs-cnum" style="color:var(--ink3)">{{ $absensiStatus['tidak_scan_keluar'] ?? 0 }}</div>
                    <div class="abs-clab">Blm Keluar</div>
                </div>
            </div>
        </div>

        {{-- Jadwal --}}
        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Jadwal Petugas Hari Ini</div>
                    <div class="ph-sub">{{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('d F Y') }}</div>
                </div>
                <a href="{{ route('admin.jadwal.index') }}" class="ph-link">Kelola →</a>
            </div>
            <div class="jdw-list" style="max-height:240px;overflow-y:auto;">
            @forelse($jadwalHariIni ?? [] as $j)
            <div class="jdw-row">
                <div class="jdw-shift {{ Str::contains(strtolower($j->shift ?? ''), 'pagi') ? 'sh-p' : (Str::contains(strtolower($j->shift ?? ''), 'siang') ? 'sh-s' : 'sh-m') }}">
                    {{ strtoupper(substr($j->shift ?? 'P', 0, 1)) }}
                </div>
                <div style="flex:1;min-width:0">
                    <div class="jdw-name">{{ $j->user->name ?? 'Petugas' }}</div>
                    <div class="jdw-wil">{{ $j->wilayah->nama ?? '-' }} · {{ ucfirst($j->shift ?? '-') }}</div>
                </div>
            </div>
            @empty
            <div class="db-empty">
                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                Belum ada jadwal hari ini
            </div>
            @endforelse
            </div>
        </div>

        {{-- Aktivitas Terbaru --}}
        <div class="panel">
            <div class="ph">
                <div class="ph-title">Aktivitas Terbaru</div>
            </div>
            <div class="act-list" style="flex:1;display:flex;flex-direction:column;justify-content:center;">
            @forelse($aktivitasTerbaru ?? [] as $ak)
            <div class="act-item">
                <div class="act-dot" style="background:{{ $ak->type === 'grn' ? '#0a7c4e' : ($ak->type === 'amb' ? '#b45309' : '#1a56db') }}"></div>
                <div class="act-body">
                    <div class="act-text">{!! $ak->text !!}</div>
                    <div class="act-time">{{ $ak->time }}</div>
                </div>
            </div>
            @empty
            <div class="db-empty">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                Belum ada aktivitas hari ini
            </div>
            @endforelse
            </div>
        </div>

    </div>
</div>

{{-- ══════════════════════════════════════
     LAPORAN PENDING · CHECKLIST+KINERJA · SURVEI+AKSI
══════════════════════════════════════ --}}
<div>
    <div class="sec">Laporan, Checklist & Kepuasan</div>
    <div class="row-2-1">

        {{-- Kiri: Laporan Pending + Checklist + Kinerja --}}
        <div style="display:flex;flex-direction:column;gap:12px">

            {{-- Laporan Pending --}}
            <div class="panel">
                <div class="ph">
                    <div>
                        <div class="ph-title">Laporan Harian Pending</div>
                        <div class="ph-sub">{{ $laporanPending ?? 0 }} laporan menunggu verifikasi</div>
                    </div>
                    <a href="{{ route('admin.laporanharian.index') }}" class="ph-link">Lihat semua →</a>
                </div>
                <table>
                    <thead>
                        <tr>
                            <th>Laporan</th>
                            <th>Petugas</th>
                            <th>Wilayah</th>
                            <th style="text-align:right">Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($laporanPendingList ?? [] as $l)
                        <tr onclick="window.location='{{ route('admin.laporanharian.index') }}'" style="cursor:pointer">
                            <td>
                                <div class="td-main">{{ $l->judul ?? 'Laporan Harian' }}</div>
                                <div class="td-id">{{ $l->tanggal ? \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') : '-' }}</div>
                            </td>
                            <td>
                                <span class="mava">{{ strtoupper(substr($l->user->name ?? 'XX', 0, 2)) }}</span>
                                {{ Str::limit($l->user->name ?? '-', 16) }}
                            </td>
                            <td class="mono">{{ $l->wilayah->nama ?? '-' }}</td>
                            <td style="text-align:right">
                                @if(($l->status ?? '') === 'terlambat')
                                    <span class="pill p-red">Terlambat</span>
                                @elseif(($l->status ?? '') === 'review')
                                    <span class="pill p-blue">Review</span>
                                @else
                                    <span class="pill p-amber">Pending</span>
                                @endif
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="4">
                                <div class="db-empty">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="#0a7c4e" stroke-width="1.5"><path d="M9 11l3 3L22 4"/></svg>
                                    Tidak ada laporan pending
                                </div>
                            </td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Checklist Harian + Kinerja per Wilayah --}}
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px">

                <div class="panel">
                    <div class="ph">
                        <div>
                            <div class="ph-title">Checklist Harian</div>
                            <div class="ph-sub">Progres hari ini</div>
                        </div>
                        <a href="{{ route('admin.checklist.index') }}" class="ph-link">Detail →</a>
                    </div>
                    <div style="padding:4px 0">
                        <div class="prog-row">
                            <div class="prog-label">Item Selesai</div>
                            <div class="prog-track"><div class="prog-fill" style="width:{{ $pctChecklistItem ?? 0 }}%;background:#0a7c4e"></div></div>
                            <div class="prog-val">{{ $pctChecklistItem ?? 0 }}%</div>
                        </div>
                        <div class="prog-row">
                            <div class="prog-label">Formulir</div>
                            <div class="prog-track"><div class="prog-fill" style="width:{{ $checklistTotal > 0 ? round($checklistSelesai/$checklistTotal*100) : 0 }}%;background:#1a56db"></div></div>
                            <div class="prog-val">{{ $checklistTotal > 0 ? round($checklistSelesai/$checklistTotal*100) : 0 }}%</div>
                        </div>
                    </div>
                    <div class="stat-row cols-3">
                        <div class="stat-cell">
                            <div class="stat-num" style="color:#0a7c4e">{{ $checklistSelesai ?? 0 }}</div>
                            <div class="stat-lab">Selesai</div>
                        </div>
                        <div class="stat-cell">
                            <div class="stat-num" style="color:#b45309">{{ $checklistBelum ?? 0 }}</div>
                            <div class="stat-lab">Pending</div>
                        </div>
                        <div class="stat-cell">
                            <div class="stat-num">{{ $checklistTotal ?? 0 }}</div>
                            <div class="stat-lab">Total</div>
                        </div>
                    </div>
                </div>

                <div class="panel">
                    <div class="ph">
                        <div>
                            <div class="ph-title">Kinerja per Wilayah</div>
                            <div class="ph-sub">Rata-rata nilai evaluasi</div>
                        </div>
                        <a href="{{ route('admin.penilaian.index') }}" class="ph-link">Detail →</a>
                    </div>
                    @forelse($kinerjaWilayah ?? [] as $kw)
                    <div class="prog-row">
                        <div class="prog-label" title="{{ $kw->nama }}">{{ $kw->nama }}</div>
                        <div class="prog-track">
                            <div class="prog-fill" style="width:{{ $kw->avg_kinerja }}%;background:{{ $kw->avg_kinerja >= 85 ? '#0a7c4e' : ($kw->avg_kinerja >= 70 ? '#1a56db' : '#b45309') }}"></div>
                        </div>
                        <div class="prog-val">{{ round($kw->avg_kinerja) }}</div>
                    </div>
                    @empty
                    <div class="db-empty" style="padding:20px 14px">Belum ada data penilaian</div>
                    @endforelse
                </div>

            </div>
        </div>

        {{-- Kanan: Survei + Aksi Cepat --}}
        <div style="display:flex;flex-direction:column;gap:12px">

            <div class="panel">
                <div class="ph">
                    <div>
                        <div class="ph-title">Survei Kepuasan</div>
                        <div class="ph-sub">{{ $surveyTotal ?? 0 }} responden</div>
                    </div>
                    <a href="{{ route('admin.survey.hasil') }}" class="ph-link">Detail →</a>
                </div>
                <div class="sv-score-wrap">
                    <div class="sv-num">{{ $avgRating ?? 0 }}</div>
                    <div>
                        <div class="sv-stars">
                            @for($i=1;$i<=5;$i++)
                            <svg width="12" height="12" viewBox="0 0 24 24" fill="{{ $i <= round($avgRating ?? 0) ? '#b45309' : 'none' }}" stroke="#b45309" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                            @endfor
                        </div>
                        <div class="sv-meta">dari 5 · {{ $surveyBulanIni ?? 0 }} bulan ini</div>
                    </div>
                </div>
                @php $maxR = max(1, max(array_values($ratingDistribusi ?? [1]))); @endphp
                @foreach(array_reverse(range(1,5)) as $star)
                <div class="star-row">
                    <div class="star-n">{{ $star }}</div>
                    <svg width="10" height="10" viewBox="0 0 24 24" fill="#b45309" stroke="none"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
                    <div class="star-t"><div class="star-f" style="width:{{ $maxR > 0 ? round(($ratingDistribusi[$star] ?? 0)/$maxR*100) : 0 }}%"></div></div>
                    <div class="star-c">{{ $ratingDistribusi[$star] ?? 0 }}</div>
                </div>
                @endforeach
            </div>

            <div class="panel">
                <div class="ph"><div class="ph-title">Aksi Cepat</div></div>
                <div class="qa-grid">
                    <a href="{{ route('admin.jadwal.index') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                        <span class="qa-lab">Jadwal</span>
                    </a>
                    <a href="{{ route('admin.tim-petugas') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/></svg>
                        <span class="qa-lab">Tim Petugas</span>
                    </a>
                    <a href="{{ route('admin.absensi.index') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
                        <span class="qa-lab">Absensi</span>
                    </a>
                    <a href="{{ route('admin.checklist.index') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg>
                        <span class="qa-lab">Checklist</span>
                    </a>
                    <a href="{{ route('admin.laporanharian.index') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                        <span class="qa-lab">Laporan</span>
                    </a>
                    <a href="{{ route('admin.penilaian.index') }}" class="qa">
                        <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                        <span class="qa-lab">Penilaian</span>
                    </a>
                </div>
            </div>

        </div>
    </div>
</div>

</div>{{-- /db --}}
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
/* ── DONUT ── */
const hadir  = {{ $hadirHariIni ?? 0 }};
const alpha  = {{ $absensiStatus['alpha'] ?? 0 }};
const total  = {{ $totalTerjadwalHariIni ?? 0 }};
const blm    = {{ $belumAbsenHariIni ?? 0 }};

new Chart(document.getElementById('donutChart').getContext('2d'), {
    type: 'doughnut',
    data: {
        datasets: [{
            data: [hadir, alpha, blm],
            backgroundColor: ['#0a7c4e','#c0392b','#eef0f3'],
            borderWidth: 0,
            hoverOffset: 3
        }]
    },
    options: {
        cutout: '74%',
        plugins: { legend: { display: false }, tooltip: { enabled: false } },
        animation: { duration: 800, easing: 'easeInOutQuart' }
    }
});

/* ── MAIN CHART ── */
const CHART_DATA = {
    perWilayah: @json($perWilayah ?? []),
    mingguan: @json($mingguan ?? []),
    harian:   @json($harian   ?? []),
    bulanan:  @json($bulanan  ?? []),
    tahunan:  @json($tahunan  ?? []),
};

const CHART_DESC = {
    perWilayah: 'Perbandingan per wilayah — bulan berjalan',
    mingguan:   '12 minggu terakhir — setiap batang = 1 minggu',
    harian:     '30 hari terakhir — setiap batang = 1 hari',
    bulanan:    '12 bulan terakhir — setiap batang = 1 bulan',
    tahunan:    '5 tahun terakhir — setiap batang = 1 tahun',
};

let mainChart = null;
const mainCtx = document.getElementById('mainChart').getContext('2d');

function updateSummary(rows, showSurvey) {
    const sum = (key) => rows.reduce((a, r) => a + (r[key] || 0), 0);
    const avg = (key) => {
        const vals = rows.map(r => r[key] || 0).filter(v => v > 0);
        return vals.length ? (vals.reduce((a,b) => a+b, 0) / vals.length).toFixed(1) : '—';
    };
    document.getElementById('sum-hadir').textContent     = sum('hadir');
    document.getElementById('sum-laporan').textContent   = sum('laporan');
    document.getElementById('sum-checklist').textContent = sum('checklist');
    document.getElementById('sum-survey').textContent    = sum('survey');
    document.getElementById('sum-kinerja').textContent   = avg('kinerja');
    document.getElementById('sum-survey-wrap').style.opacity = showSurvey ? '1' : '0.35';
}

function buildChart(mode) {
    const rows       = CHART_DATA[mode] || [];
    const showSurvey = true;
    const isWilayah  = mode === 'perWilayah';

    const labels    = rows.map(r => r.label);
    const hadir     = rows.map(r => r.hadir     || 0);
    const laporan   = rows.map(r => r.laporan   || 0);
    const checklist = rows.map(r => r.checklist || 0);
    const survey    = rows.map(r => r.survey    || 0);
    const kinerja   = rows.map(r => r.kinerja > 0 ? r.kinerja : null);

    document.getElementById('chart-sub-desc').textContent = CHART_DESC[mode];
    document.getElementById('leg-survey').style.display  = '';
    updateSummary(rows, showSurvey);

    const ds = [
        { label:'Kehadiran',         data:hadir,     type:'bar',  yAxisID:'y',  backgroundColor:'rgba(26,86,219,.8)',  borderColor:'#1a56db', borderWidth:1, borderRadius:4 },
        { label:'Laporan Masuk',     data:laporan,   type:'bar',  yAxisID:'y',  backgroundColor:'rgba(10,124,78,.8)',  borderColor:'#0a7c4e', borderWidth:1, borderRadius:4 },
        { label:'Checklist Selesai', data:checklist, type:'bar',  yAxisID:'y',  backgroundColor:'rgba(180,83,9,.8)',   borderColor:'#b45309', borderWidth:1, borderRadius:4 },
        { label:'Survei Kepuasan',   data:survey,    type:'bar',  yAxisID:'y',  backgroundColor:'rgba(91,33,182,.75)', borderColor:'#5b21b6', borderWidth:1, borderRadius:4 },
        { label:'Rata Kinerja',      data:kinerja,   type:'line', yAxisID:'y2', borderColor:'#dc2626', backgroundColor:'transparent',
          borderWidth:2, borderDash:[5,3], tension:.4, spanGaps:true,
          pointRadius:isWilayah?5:3, pointHoverRadius:7, pointBackgroundColor:'#fff', pointBorderColor:'#dc2626', pointBorderWidth:2 },
    ];

    if (mainChart) mainChart.destroy();
    mainChart = new Chart(mainCtx, {
        type: 'bar',
        data: { labels, datasets: ds },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            interaction: { mode:'index', intersect:false },
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor:'#fff', borderColor:'#e5e7eb', borderWidth:1,
                    titleColor:'#111827', bodyColor:'#6b7280', padding:10,
                    titleFont:{ family:'IBM Plex Sans', size:12, weight:'600' },
                    bodyFont:{ family:'IBM Plex Sans', size:11 },
                    callbacks: {
                        label: item => {
                            if (item.raw === null || item.raw === 0) return null;
                            const icons = { 'Kehadiran':'👥', 'Laporan Masuk':'📄', 'Checklist Selesai':'✅', 'Survei Kepuasan':'⭐', 'Rata Kinerja':'📊' };
                            const suffix = item.dataset.label === 'Rata Kinerja' ? '/100' : '';
                            return `  ${icons[item.dataset.label]||''} ${item.dataset.label}: ${item.formattedValue}${suffix}`;
                        },
                        afterBody: items => {
                            const total = items.filter(i => i.dataset.label !== 'Rata Kinerja').reduce((s,i) => s + (i.raw||0), 0);
                            return total > 0 ? ['', `  Total aktivitas: ${total}`] : [];
                        }
                    }
                }
            },
            scales: {
                x: {
                    grid: { display:false },
                    ticks: {
                        font:{ size: isWilayah ? 10 : 10, family:'IBM Plex Mono' },
                        color:'#6b7280',
                        autoSkip: isWilayah ? false : true,
                        minRotation: isWilayah ? 12 : 0,
                        maxRotation: isWilayah ? 12 : 0,
                        maxTicksLimit: isWilayah ? 20 : 12,
                    }
                },
                y: {
                    position:'left',
                    grid: { color:'rgba(0,0,0,.04)' },
                    ticks: { font:{ size:10, family:'IBM Plex Mono' }, color:'#9ca3af', maxTicksLimit:5, precision:0 },
                    beginAtZero:true,
                    title: { display:true, text:'Jumlah', font:{size:9}, color:'#9ca3af' }
                },
                y2: {
                    position:'right',
                    grid: { drawOnChartArea:false },
                    ticks: { font:{ size:10, family:'IBM Plex Mono' }, color:'#dc2626', maxTicksLimit:5 },
                    min:0, max:100,
                    title: { display:true, text:'Kinerja (0-100)', font:{size:9}, color:'#dc2626' }
                }
            },
            animation: { duration:400, easing:'easeInOutQuart' }
        }
    });
}

function switchChart(mode, btn) {
    document.querySelectorAll('.ctab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    buildChart(mode);
}

buildChart('perWilayah');
</script>
@endpush