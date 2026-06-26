@extends('layouts.admin')

@section('title', 'Rekap Penilaian')

@push('styles')
<style>
.inp { height:32px;border:1px solid var(--rule);border-radius:5px;padding:0 10px;font-size:12px;font-family:inherit;color:var(--ink);background:var(--surface); }
.inp:focus { outline:none;border-color:var(--blue); }
.stat-grid { display:grid;grid-template-columns:repeat(auto-fit,minmax(160px,1fr));gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
.chart-card { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:16px; }
.chart-head { padding:12px 16px;border-bottom:1px solid var(--rule);display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:8px; }
.chart-title { font-size:12.5px;font-weight:600;color:var(--ink); }
.chart-sub { font-size:11px;color:var(--ink3); }
.chart-body { padding:16px; }
.wilayah-picker-bar { display:flex;align-items:center;gap:8px;padding:10px 16px;border-bottom:1px solid var(--rule);background:#fafbfc;flex-wrap:wrap; }
.legend-row { display:flex;gap:12px;flex-wrap:wrap;margin-top:2px; }
.legend-dot { display:inline-flex;align-items:center;gap:5px;font-size:10.5px;color:var(--ink3); }
.legend-dot span { width:10px;height:10px;border-radius:2px;flex-shrink:0; }
</style>
@endpush

@section('breadcrumb')
    <strong>Rekap Penilaian</strong>
@endsection

@section('content')

{{-- FILTER --}}
<div style="display:flex;gap:10px;align-items:center;margin-bottom:20px;flex-wrap:wrap">
    <form method="GET" style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
        <label style="font-size:12px;color:var(--ink3);font-weight:500">Periode:</label>
        <select name="periode" class="inp" onchange="this.form.submit()">
            @foreach($periodeOptions as $key => $label)
            <option value="{{ $key }}" {{ $key === $periode ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>
    <a href="{{ route('admin.penilaian.export', ['periode' => $periode]) }}"
       style="display:inline-flex;align-items:center;gap:5px;height:32px;padding:0 12px;background:var(--green-lt);color:var(--green);border:1px solid rgba(22,163,74,.2);border-radius:5px;font-size:11.5px;font-weight:500;text-decoration:none">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
            <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
        </svg>
        Export Semua
    </a>

    {{-- Tombol Selesaikan Semua Draft --}}
    @php $jumlahDraft = \App\Models\EvaluasiPetugas::where('periode',$periode)->where('status','draft')->whereNotNull('jumlah_nilai')->count(); @endphp
    @if($jumlahDraft > 0)
    <form method="POST" action="{{ route('admin.penilaian.selesaikan-semua') }}" style="display:inline"
          onsubmit="return confirm('Selesaikan {{ $jumlahDraft }} evaluasi draft menjadi selesai? Tindakan ini tidak bisa dibatalkan.')">
        @csrf
        <input type="hidden" name="periode" value="{{ $periode }}">
        <button type="submit"
            style="display:inline-flex;align-items:center;gap:5px;height:32px;padding:0 12px;background:#fff3cd;color:#856404;border:1px solid #ffc107;border-radius:5px;font-size:11.5px;font-weight:600;cursor:pointer">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Selesaikan {{ $jumlahDraft }} Evaluasi Draft
        </button>
    </form>
    @endif
</div>

{{-- Alert sukses / info --}}
@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;border-radius:6px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('info'))
<div style="background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;border-radius:6px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('info') }}
</div>
@endif

{{-- STAT GLOBAL --}}
<div class="stat-grid">
    <div class="stat">
        <div class="stat-label">Total Petugas</div>
        <div class="stat-num">{{ $totalPetugas }}</div>
        <div class="stat-meta">Seluruh wilayah aktif</div>
        <div class="stat-bar"><div class="stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label"> Evaluasi Selesai</div>
        <div class="stat-num" style="color:var(--green)">{{ $totalSelesai }}</div>
        <div class="stat-meta">{{ $totalPetugas > 0 ? round(($totalSelesai/$totalPetugas)*100) : 0 }}% selesai</div>
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $totalPetugas > 0 ? round(($totalSelesai/$totalPetugas)*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label">Rata-rata Nilai</div>
        <div class="stat-num" style="color:var(--blue)">{{ $globalRata ? number_format($globalRata,2) : '—' }}</div>
        <div class="stat-meta">Semua wilayah</div>
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $globalRata ?? 0 }}%;background:var(--blue)"></div></div>
    </div>
    <div class="stat">
        <div class="stat-label">Petugas Terbaik</div>
        @if($globalTerbaik)
        <div class="stat-num" style="font-size:16px;font-weight:500;letter-spacing:0;color:var(--ink)">
            {{ $globalTerbaik->petugas?->user?->name ?? '—' }}
        </div>
        <div class="stat-meta">{{ $globalTerbaik->wilayah?->nama }} &middot; {{ number_format($globalTerbaik->jumlah_nilai,2) }}</div>
        @else
        <div class="stat-num">—</div>
        <div class="stat-meta">Belum ada data</div>
        @endif
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $globalTerbaik?->jumlah_nilai ?? 0 }}%;background:var(--amber)"></div></div>
    </div>
</div>

{{-- ── GRAFIK 1: BATANG — Rata-rata Komponen Nilai per Wilayah ── --}}
@php
    $wilayahLabels = $rekapWilayah->pluck('wilayah.nama')->values()->toArray();
@endphp

<div class="chart-card">
    <div class="chart-head">
        <div>
            <div class="chart-title">Rata-rata Komponen Nilai per Wilayah</div>
            <div class="chart-sub">Sumber data: tabel evaluasi petugas (kolom rata_sikap_kerja, rata_indikator_hasil, rata_indikator_proses, rata_mutu_pelayanan) &mdash; {{ $periodeOptions[$periode] ?? $periode }}</div>
        </div>
        <div class="legend-row">
            <span class="legend-dot"><span style="background:#1d4ed8"></span>Sikap Kerja</span>
            <span class="legend-dot"><span style="background:#16a34a"></span>Indikator Hasil</span>
            <span class="legend-dot"><span style="background:#b45309"></span>Indikator Proses</span>
            <span class="legend-dot"><span style="background:#7c3aed"></span>Mutu Pelayanan</span>
        </div>
    </div>
    <div class="chart-body" style="position:relative;height:300px">
        <canvas id="chartKomponenGlobal"></canvas>
    </div>
</div>

<div style="display:grid; grid-template-columns: 1fr 1.4fr; gap:16px; margin-bottom:16px;" class="chart-grid-2col">

    {{-- ── GRAFIK 2: DONUT — Distribusi Grade ── --}}
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-head">
            <div>
                <div class="chart-title">Distribusi Grade Petugas</div>
                <div class="chart-sub">Sumber data: kolom grade di tabel evaluasi petugas, seluruh wilayah</div>
            </div>
        </div>
        <div class="chart-body" style="position:relative;height:280px">
            <canvas id="chartGradeDistribusi"></canvas>
        </div>
    </div>

    {{-- ── GRAFIK 3: BATANG — Ranking Nilai Komposit per Petugas ── --}}
    <div class="chart-card" style="margin-bottom:0;">
        <div class="chart-head">
            <div>
                <div class="chart-title">Ranking Nilai Petugas</div>
                <div class="chart-sub">Sumber data: kolom jumlah_nilai (rata-rata 4 komponen) per petugas, diurutkan tertinggi ke terendah</div>
            </div>
        </div>
        <div class="wilayah-picker-bar">
            <label style="font-size:12px;color:var(--ink3);font-weight:500">Wilayah:</label>
            <select class="inp" id="selectWilayahChart" onchange="renderPetugasChart(this.value)">
                @foreach($rekapWilayah as $r)
                <option value="{{ $r['wilayah']->id }}">{{ $r['wilayah']->nama }}</option>
                @endforeach
            </select>
        </div>
        <div class="chart-body" style="position:relative;height:280px">
            <canvas id="chartPetugasKomponen"></canvas>
        </div>
    </div>

</div>

<style>
@media (max-width: 800px) {
    .chart-grid-2col { grid-template-columns: 1fr !important; }
}
</style>

{{-- REKAP TABEL PER WILAYAH --}}
<div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px">
    <div style="font-size:12px;font-weight:600;color:var(--ink)">Rekap per Wilayah</div>
    <div style="font-size:11px;color:var(--ink3)">{{ $periodeOptions[$periode] ?? $periode }}</div>
</div>
<div style="font-size:11px;color:var(--ink3);margin-bottom:12px">
    Data diambil dari hasil evaluasi petugas (tabel <code>evaluasi_petugas</code>) yang sudah memiliki nilai pada periode ini, baik berstatus draft maupun selesai.
</div>

<div class="panel" style="margin-bottom:20px">
    <div style="overflow-x:auto">
        <table style="width:100%;border-collapse:collapse">
            <thead>
                <tr style="background:#f5f6f8">
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:left;border-bottom:1px solid var(--rule)">Wilayah</th>
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:center;border-bottom:1px solid var(--rule)">Total</th>
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:center;border-bottom:1px solid var(--rule)">Selesai</th>
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:center;border-bottom:1px solid var(--rule)">Belum</th>
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:center;border-bottom:1px solid var(--rule)">Rata Nilai</th>
                    <th style="padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);text-align:left;border-bottom:1px solid var(--rule)">Petugas Terbaik</th>
                    <th style="padding:8px 12px;border-bottom:1px solid var(--rule)"></th>
                </tr>
            </thead>
            <tbody>
                @foreach($rekapWilayah as $r)
                <tr style="border-bottom:1px solid var(--rule)">
                    <td style="padding:10px 16px">
                        <div style="font-size:12.5px;font-weight:600;color:var(--ink)">{{ $r['wilayah']->nama }}</div>
                        @if($r['wilayah']->lokasi)
                        <div style="font-size:10.5px;color:var(--ink3)">{{ $r['wilayah']->lokasi }}</div>
                        @endif
                    </td>
                    <td style="padding:10px 16px;text-align:center;font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:300;color:var(--ink)">{{ $r['total_petugas'] }}</td>
                    <td style="padding:10px 16px;text-align:center;font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:300;color:var(--green)">{{ $r['sudah'] }}</td>
                    <td style="padding:10px 16px;text-align:center;font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:300;color:var(--amber)">{{ $r['belum'] }}</td>
                    <td style="padding:10px 16px;text-align:center;font-family:'IBM Plex Mono',monospace;font-size:15px;font-weight:300;color:var(--blue)">{{ $r['rata_rata'] ?? '—' }}</td>
                    <td style="padding:10px 16px">
                        @if($r['terbaik'])
                        @php $nm = $r['terbaik']->petugas?->user?->name ?? '?'; @endphp
                        <div style="display:flex;align-items:center;gap:8px">
                            <div style="width:26px;height:26px;border-radius:5px;background:var(--blue-lt);color:var(--blue);font-size:10px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0">{{ strtoupper(substr($nm,0,2)) }}</div>
                            <div>
                                <div style="font-size:12px;font-weight:500;color:var(--ink)">{{ $nm }}</div>
                                <div style="font-size:10.5px;color:var(--ink3)">{{ number_format($r['terbaik']->jumlah_nilai,2) }}</div>
                            </div>
                        </div>
                        @else
                        <span style="font-size:11.5px;color:var(--ink3)">—</span>
                        @endif
                    </td>
                    <td style="padding:10px 12px;text-align:right">
                        <div style="display:flex;gap:6px;justify-content:flex-end">
                            <a href="{{ route('admin.penilaian.export.wilayah', [$r['wilayah']->id, 'periode'=>$periode]) }}"
                               style="display:inline-flex;align-items:center;gap:4px;height:26px;padding:0 10px;background:var(--green-lt);color:var(--green);border:1px solid rgba(22,163,74,.2);border-radius:4px;font-size:11px;text-decoration:none">
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                                XLS
                            </a>
                            <a href="{{ route('admin.penilaian.detail', [$r['wilayah']->id, 'periode'=>$periode]) }}"
                               style="display:inline-flex;align-items:center;gap:4px;height:26px;padding:0 10px;border:1px solid var(--rule);border-radius:4px;font-size:11px;color:var(--blue);text-decoration:none;background:var(--surface)">
                                Detail
                                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
                            </a>
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

{{-- Keterangan grade --}}
<div style="margin-top:4px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="font-size:10.5px;color:var(--ink3);font-weight:500">Grade:</span>
    <span class="pill grade-sb" style="font-size:10px">SB &gt;95</span>
    <span class="pill grade-b"  style="font-size:10px">B 86–95</span>
    <span class="pill grade-c"  style="font-size:10px">C 66–85</span>
    <span class="pill grade-k"  style="font-size:10px">K 51–65</span>
    <span class="pill grade-sk" style="font-size:10px">SK &lt;50</span>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/4.4.1/chart.umd.min.js"></script>
<script>
// ── Data dari controller ──────────────────────────────────────────
var wilayahLabels = @json($wilayahLabels);

var komponenGlobal = {
    sikapKerja     : @json($grafikKomponenGlobal['Sikap Kerja']),
    indikatorHasil : @json($grafikKomponenGlobal['Indikator Hasil']),
    indikatorProses: @json($grafikKomponenGlobal['Indikator Proses']),
    mutuPelayanan  : @json($grafikKomponenGlobal['Mutu Pelayanan']),
};

var gradeDistribusi = @json($gradeDistribusi);

// Data per wilayah untuk grafik ranking petugas
var petugasPerWilayah = {};
@foreach($rekapWilayah as $r)
petugasPerWilayah[{{ $r['wilayah']->id }}] = @json($r['petugas_grafik']);
@endforeach

// Warna berdasarkan grade (konsisten dengan pill grade di tabel)
var GRADE_COLOR = {
    'SB': '#16a34a',  // hijau
    'B' : '#1d4ed8',  // biru
    'C' : '#b45309',  // amber/coklat
    'K' : '#dc2626',  // merah
    'SK': '#7c2d12',  // merah tua
    '-' : '#9ca3af',  // abu
};

// ── Grafik 1: Batang — rata-rata komponen per wilayah ────────────
// Dipilih bar chart (bukan garis) karena wilayah adalah KATEGORI, bukan
// urutan waktu — garis menyiratkan tren yang sebenarnya tidak ada di sini.
var ctx1 = document.getElementById('chartKomponenGlobal').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: wilayahLabels,
        datasets: [
            { label: 'Sikap Kerja',      data: komponenGlobal.sikapKerja,      backgroundColor: '#1d4ed8', borderRadius: 4 },
            { label: 'Indikator Hasil',  data: komponenGlobal.indikatorHasil,  backgroundColor: '#16a34a', borderRadius: 4 },
            { label: 'Indikator Proses', data: komponenGlobal.indikatorProses, backgroundColor: '#b45309', borderRadius: 4 },
            { label: 'Mutu Pelayanan',   data: komponenGlobal.mutuPelayanan,   backgroundColor: '#7c3aed', borderRadius: 4 },
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: {
            legend: { display: false },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        return ' ' + ctx.dataset.label + ': ' + (ctx.raw || 0).toFixed(2);
                    }
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                min: 0,
                max: 100,
                ticks: { font: { size: 10 } },
                grid: { color: 'rgba(0,0,0,.05)' },
                title: { display: true, text: 'Nilai (0–100)', font: { size: 10 } }
            },
            x: {
                ticks: { font: { size: 10.5 } },
                grid: { display: false }
            }
        }
    }
});

// ── Grafik 2: Donut — distribusi grade ───────────────────────────
// Dipilih donut karena ini soal PROPORSI ("berapa banyak dari total"),
// jenis pertanyaan yang paling cepat dijawab lewat pie/donut.
var ctx2 = document.getElementById('chartGradeDistribusi').getContext('2d');
var gradeLabels = ['Sangat Baik (SB)', 'Baik (B)', 'Cukup (C)', 'Kurang (K)', 'Sangat Kurang (SK)'];
var gradeKeys   = ['SB', 'B', 'C', 'K', 'SK'];
var gradeValues = gradeKeys.map(function(k){ return gradeDistribusi[k] || 0; });
var gradeColors = gradeKeys.map(function(k){ return GRADE_COLOR[k]; });
var gradeTotal  = gradeValues.reduce(function(a,b){ return a+b; }, 0);

new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: gradeLabels,
        datasets: [{
            data: gradeValues,
            backgroundColor: gradeColors,
            borderWidth: 2,
            borderColor: '#fff',
        }]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        cutout: '62%',
        plugins: {
            legend: {
                position: 'bottom',
                labels: { font: { size: 10.5 }, boxWidth: 10, padding: 10 }
            },
            tooltip: {
                callbacks: {
                    label: function(ctx) {
                        var val = ctx.raw || 0;
                        var pct = gradeTotal > 0 ? Math.round(val / gradeTotal * 100) : 0;
                        return ' ' + ctx.label + ': ' + val + ' petugas (' + pct + '%)';
                    }
                }
            }
        }
    },
    plugins: [{
        id: 'centerText',
        afterDraw: function(chart) {
            var ctx = chart.ctx;
            var w = chart.width, h = chart.height;
            ctx.save();
            ctx.font = '600 20px IBM Plex Mono, monospace';
            ctx.fillStyle = '#1a1a1a';
            ctx.textAlign = 'center';
            ctx.textBaseline = 'middle';
            ctx.fillText(gradeTotal, w / 2, h / 2 - 8);
            ctx.font = '400 10px IBM Plex Sans, sans-serif';
            ctx.fillStyle = '#888';
            ctx.fillText('total petugas', w / 2, h / 2 + 12);
            ctx.restore();
        }
    }]
});

// ── Grafik 3: Batang horizontal — ranking nilai komposit petugas ──
// Dipilih 1 nilai per orang (bukan 4 warna grouped) supaya yang dibaca
// cuma "siapa di atas siapa", tanpa perlu menguraikan banyak warna sekaligus.
// Warna batang mengikuti grade petugas tersebut.
var petugasChart = null;

function renderPetugasChart(wilayahId) {
    var d = petugasPerWilayah[wilayahId];
    var ctx3 = document.getElementById('chartPetugasKomponen').getContext('2d');
    if (petugasChart) petugasChart.destroy();

    if (!d || d.length === 0) {
        petugasChart = new Chart(ctx3, {
            type: 'bar',
            data: { labels: ['Belum ada data evaluasi'], datasets: [{ data: [0] }] },
            options: { responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } }, indexAxis: 'y' }
        });
        return;
    }

    var names  = d.map(function(p){ return p.nama; });
    var nilai  = d.map(function(p){ return p.jumlah_nilai; });
    var colors = d.map(function(p){ return GRADE_COLOR[p.grade] || GRADE_COLOR['-']; });
    var grades = d.map(function(p){ return p.grade; });

    // Tinggi canvas menyesuaikan jumlah petugas agar label nama tidak berdempetan
    var canvasEl = document.getElementById('chartPetugasKomponen');
    canvasEl.parentElement.style.height = Math.max(280, names.length * 34) + 'px';

    petugasChart = new Chart(ctx3, {
        type: 'bar',
        data: {
            labels: names,
            datasets: [{
                label: 'Nilai',
                data: nilai,
                backgroundColor: colors,
                borderRadius: 4,
                barThickness: 18,
            }]
        },
        options: {
            indexAxis: 'y',
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    callbacks: {
                        label: function(ctx) {
                            var idx = ctx.dataIndex;
                            return ' Nilai: ' + (ctx.raw || 0).toFixed(2) + '  ·  Grade: ' + grades[idx];
                        }
                    }
                }
            },
            scales: {
                x: {
                    beginAtZero: true,
                    max: 100,
                    ticks: { font: { size: 10 } },
                    grid: { color: 'rgba(0,0,0,.05)' },
                    title: { display: true, text: 'Nilai (0–100)', font: { size: 10 } }
                },
                y: {
                    ticks: { font: { size: 11 } },
                    grid: { display: false }
                }
            }
        }
    });
}

document.addEventListener('DOMContentLoaded', function() {
    var sel = document.getElementById('selectWilayahChart');
    if (sel && sel.options.length > 0) renderPetugasChart(sel.value);
});
</script>
@endpush