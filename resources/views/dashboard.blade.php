{{-- ============================================================
     FILE   : resources/views/petugas/dashboardpetugas.blade.php
     LAYOUT : layouts.petugas
     ROUTE  : /petugas/dashboard  (name: petugas.dashboard)
     ============================================================ --}}
@extends('layouts.petugas')

@section('title', 'Dashboard Petugas')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <strong>Dashboard</strong>
@endsection

@push('styles')
<style>
.page-head{display:flex;align-items:flex-end;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule)}
.page-head h1{font-size:19px;font-weight:600;letter-spacing:-.3px;color:var(--ink)}
.page-head p{font-size:12px;color:var(--ink3);margin-top:3px}
.status-chip{display:inline-flex;align-items:center;gap:6px;font-size:11.5px;color:var(--green);background:var(--green-lt);border:1px solid rgba(10,124,78,.14);padding:5px 12px;border-radius:4px;font-weight:500}
.status-dot{width:7px;height:7px;border-radius:50%;background:var(--green);animation:pulse 2s infinite}
@keyframes pulse{0%,100%{opacity:1}50%{opacity:.4}}

.stats{display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:22px}
.stat{background:var(--surface);padding:18px 20px;position:relative;cursor:pointer;transition:background .12s;text-decoration:none;display:block}
.stat:hover{background:var(--wash)}
.stat-label{font-size:10px;font-weight:600;letter-spacing:.6px;text-transform:uppercase;color:var(--ink3);margin-bottom:8px}
.stat-num{font-size:28px;font-weight:300;letter-spacing:-1px;color:var(--ink);font-family:'IBM Plex Mono',monospace;line-height:1;margin-bottom:6px}
.stat-meta{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--ink3)}
.delta{font-size:10px;font-weight:600;padding:1px 5px;border-radius:3px;font-family:'IBM Plex Mono',monospace}
.d-up{background:var(--green-lt);color:var(--green)}
.d-warn{background:var(--amber-lt);color:var(--amber)}
.stat-bar{position:absolute;bottom:0;left:0;right:0;height:2px}
.stat-fill{height:100%}

.grid2{display:grid;grid-template-columns:1fr 296px;gap:18px;align-items:start}
.side{display:flex;flex-direction:column;gap:14px}
.panel{background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden}
.ph{display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--rule)}
.ph-title{font-size:12.5px;font-weight:600;color:var(--ink)}
.ph-sub{font-size:11px;color:var(--ink3);margin-top:1px}
.ph-link{font-size:11.5px;color:var(--blue);text-decoration:none;font-weight:500;padding:3px 8px;border-radius:4px}
.ph-link:hover{background:var(--blue-lt)}

.chart-wrap{position:relative;width:100%;height:200px;padding:14px 14px 6px}
.chart-legend{display:flex;gap:16px;padding:0 16px 10px}
.legend-item{display:flex;align-items:center;gap:5px;font-size:11px;color:var(--ink3)}
.legend-dot{width:10px;height:10px;border-radius:2px}

table{width:100%;border-collapse:collapse}
thead th{text-align:left;padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);background:var(--wash);border-bottom:1px solid var(--rule)}
tbody tr{border-bottom:1px solid var(--rule);transition:background .1s;cursor:pointer}
tbody tr:last-child{border-bottom:none}
tbody tr:hover{background:var(--wash)}
tbody td{padding:9px 16px;vertical-align:middle}
.td-main{font-weight:500;color:var(--ink);font-size:12.5px}
.mono{font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink2)}
.pill{display:inline-block;font-size:10px;font-weight:500;padding:2px 7px;border-radius:3px}
.p-green{background:var(--green-lt);color:var(--green)}
.p-amber{background:var(--amber-lt);color:var(--amber)}
.p-blue{background:var(--blue-lt);color:var(--blue)}
.p-gray{background:var(--wash2);color:var(--ink3)}

.qa-list{display:flex;flex-direction:column;gap:4px;padding:8px}
.qa{display:flex;align-items:center;gap:10px;padding:9px 11px;border:1px solid var(--rule);border-radius:6px;background:var(--wash);cursor:pointer;transition:all .12s;text-decoration:none}
.qa:hover{border-color:var(--amber);background:var(--amber-lt)}
.qa:hover .qa-label{color:var(--amber)}
.qa-icon{width:30px;height:30px;border-radius:5px;background:var(--surface);border:1px solid var(--rule);display:flex;align-items:center;justify-content:center;flex-shrink:0}
.qa-label{font-size:12px;font-weight:500;color:var(--ink)}
.qa-desc{font-size:10.5px;color:var(--ink3);margin-top:1px}
.qa-arr{margin-left:auto;color:var(--ink3);opacity:.5}

.perf-row{display:flex;align-items:center;padding:9px 14px;gap:10px;border-bottom:1px solid var(--rule)}
.perf-row:last-child{border-bottom:none}
.perf-name{font-size:11.5px;font-weight:500;color:var(--ink2);flex:1;overflow:hidden;text-overflow:ellipsis;white-space:nowrap}
.perf-track{width:80px;height:3px;background:var(--wash2);border-radius:2px;overflow:hidden;flex-shrink:0}
.perf-fill{height:100%;border-radius:2px}
.perf-val{font-family:'IBM Plex Mono',monospace;font-size:10.5px;color:var(--ink3);width:28px;text-align:right;flex-shrink:0}

.notif-list{display:flex;flex-direction:column}
.notif{display:flex;gap:10px;padding:10px 14px;border-bottom:1px solid var(--rule);cursor:pointer;transition:background .1s}
.notif:last-child{border-bottom:none}
.notif:hover{background:var(--wash)}
.notif-dot{width:7px;height:7px;border-radius:50%;border:1.5px solid;flex-shrink:0;margin-top:4px}
.d-grn{border-color:var(--green);background:var(--green-lt)}
.d-blu{border-color:var(--blue);background:var(--blue-lt)}
.d-amb{border-color:var(--amber);background:var(--amber-lt)}
.notif-text{font-size:12px;color:var(--ink2);line-height:1.45}
.notif-text strong{font-weight:600;color:var(--ink)}
.notif-time{font-size:10px;color:var(--ink3);margin-top:2px;font-family:'IBM Plex Mono',monospace}

/* Checklist progress bar panel */
.checklist-summary{display:flex;align-items:center;gap:12px;padding:12px 16px;border-bottom:1px solid var(--rule)}
.ck-total{font-size:11px;color:var(--ink3)}
.ck-total strong{font-weight:600;color:var(--ink)}
.ck-bar-wrap{flex:1;height:4px;background:var(--wash2);border-radius:2px;overflow:hidden}
.ck-bar{height:100%;background:var(--amber);border-radius:2px;transition:width .6s}

@keyframes fadeUp{from{opacity:0;transform:translateY(6px)}to{opacity:1;transform:none}}
.stats{animation:fadeUp .3s ease both}
.grid2{animation:fadeUp .3s .1s ease both}
@media(max-width:1100px){.stats{grid-template-columns:repeat(2,1fr)}.grid2{grid-template-columns:1fr}}
@media(max-width:720px){.stats{grid-template-columns:1fr 1fr}}
</style>
@endpush

@section('content')

{{-- ── PAGE HEAD ── --}}
<div class="page-head">
    <div>
        <h1>Selamat Pagi, {{ Auth::user()->name }}</h1>
        <p>{{ $wilayah->nama ?? 'Wilayah' }} &middot; Petugas PST BPS &middot; {{ \Carbon\Carbon::now('Asia/Jakarta')->translatedFormat('l, d F Y') }}</p>
    </div>
    @if($absensiHariIni ?? false)
        <span class="status-chip"><span class="status-dot"></span>Hadir &middot; {{ $jamMasuk ?? '07:55' }}</span>
    @else
        <a href="{{ route('petugas.absensi.scan') }}" class="status-chip" style="color:var(--amber);background:var(--amber-lt);border-color:rgba(180,83,9,.14)">
            Belum Absen — Scan Sekarang
        </a>
    @endif
</div>

{{-- ── STAT CARDS ── --}}
<div class="stats">
    <a class="stat" href="{{ route('petugas.jadwal.index') }}">
        <div class="stat-label">Jadwal Bulan Ini</div>
        <div class="stat-num">{{ $totalJadwal ?? 22 }}</div>
        <div class="stat-meta"><span class="delta d-up">hari kerja</span></div>
        <div class="stat-bar"><div class="stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </a>
    <a class="stat" href="{{ route('petugas.absensi.index') }}">
        <div class="stat-label">Total Hadir</div>
        <div class="stat-num">{{ $totalHadir ?? 19 }}</div>
        <div class="stat-meta">
            <span class="delta d-up">{{ $pctHadir ?? '86.4' }}%</span>
            <span>kehadiran</span>
        </div>
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $pctHadir ?? 86 }}%;background:var(--green)"></div></div>
    </a>
    <a class="stat" href="{{ route('petugas.checklist.index') }}">
        <div class="stat-label">Checklist Selesai</div>
        <div class="stat-num">{{ $checklistSelesai ?? 14 }}</div>
        <div class="stat-meta">
            <span class="delta d-up">{{ $pctChecklist ?? '73' }}%</span>
            <span>diselesaikan</span>
        </div>
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $pctChecklist ?? 73 }}%;background:var(--green)"></div></div>
    </a>
    <a class="stat" href="{{ route('petugas.penilaian.index') }}">
        <div class="stat-label">Nilai Kinerja</div>
        <div class="stat-num">{{ $nilaiKinerja ?? '88.5' }}</div>
        <div class="stat-meta">
            <span class="delta d-up">+{{ $deltaNilai ?? '2.3' }}</span>
            <span>poin</span>
        </div>
        <div class="stat-bar"><div class="stat-fill" style="width:{{ $nilaiKinerja ?? 89 }}%;background:var(--amber)"></div></div>
    </a>
</div>

{{-- ── GRID 2 KOLOM ── --}}
<div class="grid2">

    {{-- Kolom kiri --}}
    <div>
        {{-- Grafik Aktivitas --}}
        <div class="panel" style="margin-bottom:16px">
            <div class="ph">
                <div>
                    <div class="ph-title">Aktivitas Saya (14 Hari Terakhir)</div>
                    <div class="ph-sub">Kehadiran &amp; laporan harian</div>
                </div>
            </div>
            <div class="chart-legend">
                <span class="legend-item"><span class="legend-dot" style="background:#1a56db"></span>Hadir</span>
                <span class="legend-item"><span class="legend-dot" style="background:#e2e5ea"></span>Laporan Submit</span>
            </div>
            <div class="chart-wrap">
                <canvas id="aktivitasChart" role="img" aria-label="Grafik kehadiran dan laporan 14 hari terakhir."></canvas>
            </div>
        </div>

        {{-- Jadwal Shift Minggu Ini --}}
        <div class="panel">
            <div class="ph">
                <div><div class="ph-title">Jadwal Shift Minggu Ini</div></div>
                <a href="{{ route('petugas.jadwal.index') }}" class="ph-link">Lihat semua →</a>
            </div>
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Shift</th>
                        <th>Lokasi</th>
                        <th style="text-align:right">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($jadwalMingguIni ?? [] as $j)
                    <tr onclick="window.location='{{ route('petugas.jadwal.index') }}'">
                        <td class="mono">{{ \Carbon\Carbon::parse($j->tanggal)->translatedFormat('D, d M') }}</td>
                        <td><div class="td-main" style="font-size:12px">{{ $j->shift ?? '-' }}</div></td>
                        <td style="font-size:11.5px;color:var(--ink3)">{{ $j->lokasi ?? '-' }}</td>
                        <td style="text-align:right">
                            @if($j->status === 'hadir') <span class="pill p-green">Hadir</span>
                            @elseif($j->status === 'hari_ini') <span class="pill p-blue">Hari Ini</span>
                            @elseif($j->status === 'terjadwal') <span class="pill p-gray">Terjadwal</span>
                            @else <span class="pill p-gray">-</span>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr><td class="mono">Sen, 02 Jun</td><td><div class="td-main" style="font-size:12px">Pagi 07:00–15:00</div></td><td style="font-size:11.5px;color:var(--ink3)">Kantor PST</td><td style="text-align:right"><span class="pill p-green">Hadir</span></td></tr>
                    <tr><td class="mono">Sel, 03 Jun</td><td><div class="td-main" style="font-size:12px">Pagi 07:00–15:00</div></td><td style="font-size:11.5px;color:var(--ink3)">Lapangan Sektor A</td><td style="text-align:right"><span class="pill p-green">Hadir</span></td></tr>
                    <tr><td class="mono">Rab, 04 Jun</td><td><div class="td-main" style="font-size:12px">Siang 12:00–20:00</div></td><td style="font-size:11.5px;color:var(--ink3)">Kantor PST</td><td style="text-align:right"><span class="pill p-green">Hadir</span></td></tr>
                    <tr><td class="mono">Kam, 05 Jun</td><td><div class="td-main" style="font-size:12px">Pagi 07:00–15:00</div></td><td style="font-size:11.5px;color:var(--ink3)">Lapangan Sektor B</td><td style="text-align:right"><span class="pill p-blue">Hari Ini</span></td></tr>
                    <tr><td class="mono">Jum, 06 Jun</td><td><div class="td-main" style="font-size:12px">Pagi 07:00–15:00</div></td><td style="font-size:11.5px;color:var(--ink3)">Kantor PST</td><td style="text-align:right"><span class="pill p-gray">Terjadwal</span></td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Kolom kanan --}}
    <div class="side">

        {{-- Aksi Cepat --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Aksi Cepat</div></div>
            <div class="qa-list">
                <a href="{{ route('petugas.absensi.scan') }}" class="qa">
                    <div class="qa-icon"><svg width="14" height="14" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
                    <div><div class="qa-label">Scan Absensi QR</div><div class="qa-desc">Rekam kehadiran hari ini</div></div>
                    <svg class="qa-arr" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('petugas.checklist.index') }}" class="qa">
                    <div class="qa-icon"><svg width="14" height="14" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/></svg></div>
                    <div><div class="qa-label">Isi Checklist Harian</div><div class="qa-desc">{{ $checklistBelum ?? 15 }} item belum selesai</div></div>
                    <svg class="qa-arr" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('petugas.laporan.create') }}" class="qa">
                    <div class="qa-icon"><svg width="14" height="14" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/><line x1="12" y1="18" x2="12" y2="12"/><line x1="9" y1="15" x2="15" y2="15"/></svg></div>
                    <div><div class="qa-label">Buat Laporan Harian</div><div class="qa-desc">Submit laporan hari ini</div></div>
                    <svg class="qa-arr" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('petugas.materi.index') }}" class="qa">
                    <div class="qa-icon"><svg width="14" height="14" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M4 19.5A2.5 2.5 0 016.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 014 19.5v-15A2.5 2.5 0 016.5 2z"/></svg></div>
                    <div><div class="qa-label">Materi Pelatihan</div><div class="qa-desc">{{ $materiBaru ?? 2 }} materi baru tersedia</div></div>
                    <svg class="qa-arr" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
                <a href="{{ route('petugas.penilaian.index') }}" class="qa">
                    <div class="qa-icon"><svg width="14" height="14" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg></div>
                    <div><div class="qa-label">Lihat Penilaian Saya</div><div class="qa-desc">Evaluasi kinerja terbaru</div></div>
                    <svg class="qa-arr" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M5 12h14M12 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Progress Checklist Hari Ini --}}
        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Checklist Hari Ini</div>
                </div>
                <a href="{{ route('petugas.checklist.index') }}" class="ph-link">Isi →</a>
            </div>
            <div class="checklist-summary">
                <div class="ck-total">
                    <strong>{{ $ckSelesai ?? 3 }}</strong> / {{ $ckTotal ?? 4 }} kategori
                </div>
                <div class="ck-bar-wrap">
                    <div class="ck-bar" style="width:{{ $ckPct ?? 75 }}%"></div>
                </div>
                <span style="font-size:10.5px;font-family:'IBM Plex Mono',monospace;color:var(--ink3)">{{ $ckPct ?? 75 }}%</span>
            </div>
            @forelse($checklistKategori ?? [] as $ck)
            <div class="perf-row">
                <div class="perf-name">{{ $ck->nama }}</div>
                <div class="perf-track"><div class="perf-fill" style="width:{{ $ck->pct }}%;background:{{ $ck->pct >= 100 ? 'var(--green)' : ($ck->pct > 0 ? 'var(--amber)' : 'var(--rule)') }}"></div></div>
                <div class="perf-val">{{ $ck->pct }}%</div>
            </div>
            @empty
            <div class="perf-row"><div class="perf-name">Pembukaan PST</div><div class="perf-track"><div class="perf-fill" style="width:100%;background:var(--green)"></div></div><div class="perf-val">100%</div></div>
            <div class="perf-row"><div class="perf-name">Layanan Tamu</div><div class="perf-track"><div class="perf-fill" style="width:80%;background:var(--amber)"></div></div><div class="perf-val">80%</div></div>
            <div class="perf-row"><div class="perf-name">Input Data</div><div class="perf-track"><div class="perf-fill" style="width:60%;background:var(--amber)"></div></div><div class="perf-val">60%</div></div>
            <div class="perf-row"><div class="perf-name">Penutupan</div><div class="perf-track"><div class="perf-fill" style="width:0%;background:var(--rule)"></div></div><div class="perf-val">0%</div></div>
            @endforelse
        </div>

        {{-- Notifikasi --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Notifikasi</div></div>
            <div class="notif-list">
                @forelse($notifikasi ?? [] as $n)
                <div class="notif">
                    <div class="notif-dot d-{{ $n->type ?? 'blu' }}"></div>
                    <div>
                        <div class="notif-text">{!! $n->text !!}</div>
                        <div class="notif-time">{{ $n->waktu }}</div>
                    </div>
                </div>
                @empty
                <div class="notif"><div class="notif-dot d-blu"></div><div><div class="notif-text"><strong>Materi baru:</strong> Panduan Pelayanan PST v2</div><div class="notif-time">1 jam lalu</div></div></div>
                <div class="notif"><div class="notif-dot d-amb"></div><div><div class="notif-text"><strong>Laporan kemarin</strong> belum disubmit</div><div class="notif-time">2 jam lalu</div></div></div>
                <div class="notif"><div class="notif-dot d-grn"></div><div><div class="notif-text"><strong>Nilai evaluasi</strong> Mei sudah tersedia</div><div class="notif-time">Kemarin</div></div></div>
                <div class="notif"><div class="notif-dot d-grn"></div><div><div class="notif-text"><strong>Jadwal Juni</strong> sudah diterbitkan</div><div class="notif-time">2 hari lalu</div></div></div>
                @endforelse
            </div>
        </div>

    </div>{{-- /side --}}
</div>{{-- /grid2 --}}

@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>
<script>
// Hasilkan label 14 hari terakhir
function getLast14Days() {
    const labels = [];
    for (let i = 13; i >= 0; i--) {
        const d = new Date();
        d.setDate(d.getDate() - i);
        labels.push((d.getDate()) + '/' + (d.getMonth() + 1));
    }
    return labels;
}

@php
    $aktLabels  = $aktLabels  ?? null;
    $aktHadir   = $aktHadir   ?? [1,1,0,1,1,1,0,1,1,1,1,0,1,1];
    $aktLaporan = $aktLaporan ?? [1,0,0,1,1,1,0,1,1,0,1,0,1,1];
@endphp
const aktLabels = @json($aktLabels) ?? getLast14Days();
const aktHadir  = @json($aktHadir);
const aktLaporan= @json($aktLaporan);

const ctx = document.getElementById('aktivitasChart').getContext('2d');
new Chart(ctx, {
    type: 'bar',
    data: {
        labels: aktLabels,
        datasets: [
            {
                label: 'Hadir',
                data: aktHadir,
                backgroundColor: '#1a56db',
                borderRadius: 2,
                borderSkipped: false
            },
            {
                label: 'Laporan Submit',
                data: aktLaporan,
                backgroundColor: '#e2e5ea',
                borderRadius: 2,
                borderSkipped: false
            }
        ]
    },
    options: {
        responsive: true,
        maintainAspectRatio: false,
        plugins: { legend: { display: false } },
        scales: {
            x: {
                grid: { display: false },
                ticks: { font: { size: 10 }, autoSkip: false, maxRotation: 45 }
            },
            y: { display: false, beginAtZero: true, max: 1.5 }
        }
    }
});
</script>
@endpush