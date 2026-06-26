@extends('layouts.koordinator')

@section('title', 'Detail — ' . $petugas->user->name)

@push('styles')
<style>
.mono{font-family:'IBM Plex Mono',monospace;font-size:12px;}
.pill{display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:10.5px;font-weight:600;}
.g-sb{background:#dcfce7;color:#166534;} .g-b{background:#dbeafe;color:#1e40af;}
.g-c{background:#fef3c7;color:#92400e;} .g-k{background:#ffedd5;color:#9a3412;}
.g-sk{background:#fee2e2;color:#991b1b;}
.p-green{background:#dcfce7;color:#166534;} .p-blue{background:#dbeafe;color:#1e40af;}
.p-amber{background:#fef3c7;color:#92400e;} .p-red{background:#fee2e2;color:#991b1b;}
.p-gray{background:#f3f4f6;color:#6b7280;}

.tab-bar{display:flex;gap:2px;border-bottom:1px solid var(--rule);margin-bottom:0;padding:0 4px;background:var(--wash);}
.tab-btn{padding:9px 14px;border:none;background:none;font-size:11.5px;font-family:inherit;cursor:pointer;color:var(--ink3);border-bottom:2px solid transparent;margin-bottom:-1px;white-space:nowrap;}
.tab-btn.active{color:var(--blue);font-weight:600;border-bottom-color:var(--blue);}
.tab-pane{display:none;padding:0;}
.tab-pane.active{display:block;}

table{width:100%;border-collapse:collapse;font-size:12.5px;}
th{padding:9px 14px;font-size:10px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--ink3);text-align:left;border-bottom:1px solid var(--rule);background:var(--wash);}
td{padding:10px 14px;border-bottom:1px solid var(--rule);color:var(--ink);}
tr:last-child td{border-bottom:none;}
tr:hover td{background:var(--wash);}
.empty-msg{padding:32px;text-align:center;color:var(--ink3);font-size:12px;}

.kiri{display:flex;flex-direction:column;gap:12px;width:280px;flex-shrink:0;}
.kanan{flex:1;min-width:0;background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;}
.layout{display:flex;gap:16px;align-items:flex-start;}
@media(max-width:820px){.layout{flex-direction:column;}.kiri{width:100%;}}

.card{background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;}
.card-head{padding:10px 14px;background:var(--wash);border-bottom:1px solid var(--rule);font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--ink3);}
.card-body{padding:14px 16px;}
.info-row{display:flex;justify-content:space-between;padding:8px 0;border-bottom:1px solid var(--rule);font-size:12.5px;}
.info-row:last-child{border-bottom:none;}
.info-key{color:var(--ink3);}
.info-val{font-weight:500;color:var(--ink);font-family:'IBM Plex Mono',monospace;font-size:12px;}

.komponen-row{display:flex;align-items:center;gap:8px;margin-bottom:10px;}
.komponen-row:last-child{margin-bottom:0;}
.komponen-lbl{width:110px;flex-shrink:0;font-size:11.5px;color:var(--ink3);}
.komponen-bar{flex:1;height:6px;background:var(--wash2);border-radius:3px;overflow:hidden;}
.komponen-fill{height:100%;border-radius:3px;background:var(--blue);}
.komponen-val{min-width:36px;text-align:right;font-family:'IBM Plex Mono',monospace;font-size:11.5px;font-weight:500;color:var(--ink2);}

/* tombol PDF */
.btn-pdf{height:32px;padding:0 14px;border-radius:5px;border:1px solid #c4b5fd;background:#f5eeff;color:#6d28d9;font-size:12px;font-family:inherit;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none;}
.btn-pdf:hover{background:#ede9fe;border-color:#a78bfa;}
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('koordinator.nilai-evaluasi.index') }}">Nilai &amp; Evaluasi</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>{{ $petugas->user->name }}</strong>
@endsection

@section('content')

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:20px;flex-wrap:wrap;gap:10px">
    <div style="display:flex;align-items:center;gap:12px">
        <div style="width:44px;height:44px;border-radius:8px;background:var(--blue-lt);color:var(--blue);font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;font-family:'IBM Plex Mono',monospace;flex-shrink:0">
            {{ strtoupper(substr($petugas->user->name,0,2)) }}
        </div>
        <div>
            <h1 style="font-size:17px;font-weight:600;letter-spacing:-.3px">{{ $petugas->user->name }}</h1>
            <p style="font-size:11.5px;color:var(--ink3);margin-top:2px">{{ $petugas->user->username }} &middot; Halaman ini menampilkan rekap lengkap performa petugas</p>
        </div>
    </div>
    <div style="display:flex;gap:8px;flex-wrap:wrap">
        {{-- ── TOMBOL CETAK PDF ─────────────────────────────────── --}}
        @if($histori->isNotEmpty())
        @php $periodeAktif = request('periode', \App\Http\Controllers\NilaiEvaluasiController::periodeSekarang()); @endphp
        <a href="{{ route('koordinator.nilai-evaluasi.pdf', [$petugas->id, 'periode' => $periodeAktif]) }}"
           target="_blank"
           class="btn-pdf"
           title="Cetak PDF evaluasi periode ini">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Cetak PDF
        </a>
        @endif
        {{-- ── TOMBOL BERI NILAI ─────────────────────────────────── --}}
        <a href="{{ route('koordinator.nilai-evaluasi.form', $petugas->id) }}"
           style="height:32px;padding:0 14px;border-radius:5px;border:none;background:var(--blue);color:#fff;font-size:12px;font-family:inherit;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:6px;text-decoration:none">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            Beri Nilai
        </a>
        <a href="{{ route('koordinator.nilai-evaluasi.index') }}"
           style="height:32px;padding:0 12px;border-radius:5px;border:1px solid var(--rule);background:var(--surface);color:var(--ink2);font-size:12px;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
    </div>
</div>

<div class="layout">

    {{-- ── KOLOM KIRI ── --}}
    <div class="kiri">

        {{-- Nilai Terakhir --}}
        @if($histori->isNotEmpty())
        @php $last = $histori->first(); $gc = match($last->grade??'') {'SB'=>'g-sb','B'=>'g-b','C'=>'g-c','K'=>'g-k','SK'=>'g-sk',default=>'g-b'}; @endphp
        <div class="card">
            <div class="card-head">Nilai Terakhir — {{ \App\Helpers\PeriodeHelper::isoLabel($last->periode) }}</div>
            <div class="card-body" style="text-align:center;padding-top:20px;padding-bottom:12px">
                <span class="pill {{ $gc }}" style="font-size:34px;padding:10px 22px;border-radius:8px;font-family:'IBM Plex Mono',monospace;font-weight:700">{{ $last->grade }}</span>
                <div style="font-size:26px;font-weight:300;font-family:'IBM Plex Mono',monospace;letter-spacing:-1px;margin-top:10px;color:var(--ink)">
                    {{ number_format($last->jumlah_nilai,2) }}
                </div>
                <div style="font-size:11px;color:var(--ink3);margin-top:4px">Total Nilai</div>
            </div>
            <div style="padding:0 16px 16px">
                @foreach([
                    ['Sikap Kerja', $last->rata_sikap_kerja],
                    ['Ind. Hasil', $last->rata_indikator_hasil],
                    ['Ind. Proses', $last->rata_indikator_proses],
                    ['Mutu Pelayanan', $last->rata_mutu_pelayanan],
                ] as [$lbl,$val])
                <div class="komponen-row">
                    <div class="komponen-lbl">{{ $lbl }}</div>
                    <div class="komponen-bar"><div class="komponen-fill" style="width:{{ $val ?? 0 }}%"></div></div>
                    <div class="komponen-val">{{ $val ? number_format($val,1) : '—' }}</div>
                </div>
                @endforeach
            </div>
        </div>
        @else
        <div class="card">
            <div class="card-body" style="text-align:center;padding:24px;color:var(--ink3);font-size:12px">
                Belum ada evaluasi untuk petugas ini.
            </div>
        </div>
        @endif

        {{-- Statistik Absensi --}}
        <div class="card">
            <div class="card-head">Absensi Bulan Ini</div>
            <div class="card-body" style="padding:0">
                <div class="info-row" style="padding:10px 16px"><span class="info-key">Total Hadir</span><span class="info-val">{{ $totalAbsensi }}</span></div>
                <div class="info-row" style="padding:10px 16px"><span class="info-key">Tepat Waktu</span><span class="info-val" style="color:var(--green)">{{ $tepat }}</span></div>
                <div class="info-row" style="padding:10px 16px"><span class="info-key">Terlambat</span><span class="info-val" style="color:var(--amber)">{{ $terlambat }}</span></div>
                @if($totalAbsensi > 0)
                <div class="info-row" style="padding:10px 16px"><span class="info-key">Tingkat Kehadiran</span><span class="info-val">{{ round(($tepat/$totalAbsensi)*100,1) }}%</span></div>
                @endif
            </div>
        </div>
    </div>

    {{-- ── KOLOM KANAN ── --}}
    <div class="kanan">
        <div class="tab-bar">
            <button class="tab-btn active" onclick="switchTab(this,'t-grafik')">📊 Grafik Nilai</button>
            <button class="tab-btn" onclick="switchTab(this,'t-absensi')">🗓 Absensi</button>
            <button class="tab-btn" onclick="switchTab(this,'t-checklist')">✅ Checklist</button>
            <button class="tab-btn" onclick="switchTab(this,'t-laporan')">📋 Laporan Shift</button>
            <button class="tab-btn" onclick="switchTab(this,'t-quiz')">📝 Quiz</button>
            <button class="tab-btn" onclick="switchTab(this,'t-histori')">🕐 Histori Evaluasi</button>
        </div>

        {{-- Tab: Grafik Nilai --}}
        <div id="t-grafik" class="tab-pane active">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Perkembangan Nilai per Periode</div>
                <div style="font-size:11px;color:var(--ink3);margin-top:2px">Riwayat nilai dari setiap triwulan/bulan yang sudah dievaluasi</div>
            </div>
            @if($grafikData->isNotEmpty())
            @php $maxVal = max($grafikData->max('jumlah_nilai') ?: 100, 1); @endphp
            <div style="padding:20px 18px 0;overflow-x:auto">
                <svg width="100%" height="140" viewBox="0 0 {{ max($grafikData->count()*56,320) }} 140" preserveAspectRatio="none" xmlns="http://www.w3.org/2000/svg">
                    @foreach([0,25,50,75,100] as $yv)
                    @php $y = 10+(1-$yv/100)*110; @endphp
                    <line x1="0" y1="{{ $y }}" x2="100%" y2="{{ $y }}" stroke="#e5e7eb" stroke-width="1"/>
                    <text x="3" y="{{ $y-3 }}" font-size="8" fill="#9ca3af" font-family="IBM Plex Mono,monospace">{{ $yv }}</text>
                    @endforeach
                    @foreach($grafikData as $idx => $g)
                    @php
                        $bw=32; $total=max($grafikData->count()*56,320);
                        $sw=$total/$grafikData->count();
                        $bx=$idx*$sw+($sw-$bw)/2;
                        $bh=max(($g['jumlah_nilai']/$maxVal)*110,3);
                        $by=10+110-$bh;
                        $bc=match($g['grade']??''){'SB'=>'#16a34a','B'=>'#1a56db','C'=>'#d97706','K'=>'#ea580c','SK'=>'#dc2626',default=>'#1a56db'};
                    @endphp
                    <rect x="{{ $bx }}" y="{{ $by }}" width="{{ $bw }}" height="{{ $bh }}" rx="3" fill="{{ $bc }}" opacity="0.85">
                        <title>{{ $g['periode'] }}: {{ number_format($g['jumlah_nilai'],2) }} ({{ $g['grade'] }})</title>
                    </rect>
                    <text x="{{ $bx+$bw/2 }}" y="{{ $by-4 }}" text-anchor="middle" font-size="9" fill="#374151" font-family="IBM Plex Mono,monospace">{{ number_format($g['jumlah_nilai'],1) }}</text>
                    @endforeach
                </svg>
            </div>
            <div style="display:flex;padding:6px 18px 14px;gap:0">
                @foreach($grafikData as $g)
                <div style="flex:1;text-align:center;font-size:10px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">
                    {{ substr($g['periode'],5) }}/{{ substr($g['periode'],2,2) }}
                </div>
                @endforeach
            </div>
            <div style="overflow-x:auto;border-top:1px solid var(--rule)">
                <table>
                    <thead><tr>
                        <th>Periode</th>
                        <th style="text-align:center">Sikap</th>
                        <th style="text-align:center">Ind. Hasil</th>
                        <th style="text-align:center">Ind. Proses</th>
                        <th style="text-align:center">Mutu</th>
                        <th style="text-align:center">Total</th>
                        <th style="text-align:center">Grade</th>
                        {{-- ── KOLOM CETAK PDF PER PERIODE ── --}}
                        <th style="text-align:center">PDF</th>
                    </tr></thead>
                    <tbody>
                        @foreach($histori as $h)
                        @php $gc2=match($h->grade??''){'SB'=>'g-sb','B'=>'g-b','C'=>'g-c','K'=>'g-k','SK'=>'g-sk',default=>'p-gray'}; @endphp
                        <tr>
                            <td class="mono">{{ \App\Helpers\PeriodeHelper::isoLabel($h->periode, 'MMM YYYY') }}</td>
                            <td style="text-align:center" class="mono">{{ $h->rata_sikap_kerja ? number_format($h->rata_sikap_kerja,1):'-' }}</td>
                            <td style="text-align:center" class="mono">{{ $h->rata_indikator_hasil ? number_format($h->rata_indikator_hasil,1):'-' }}</td>
                            <td style="text-align:center" class="mono">{{ $h->rata_indikator_proses ? number_format($h->rata_indikator_proses,1):'-' }}</td>
                            <td style="text-align:center" class="mono">{{ $h->rata_mutu_pelayanan ? number_format($h->rata_mutu_pelayanan,1):'-' }}</td>
                            <td style="text-align:center"><strong class="mono">{{ $h->jumlah_nilai ? number_format($h->jumlah_nilai,2):'-' }}</strong></td>
                            <td style="text-align:center"><span class="pill {{ $gc2 }}">{{ $h->grade??'-' }}</span></td>
                            {{-- tombol PDF per periode di tabel histori --}}
                            <td style="text-align:center">
                                <a href="{{ route('koordinator.nilai-evaluasi.pdf', [$petugas->id, 'periode' => $h->periode]) }}"
                                   target="_blank"
                                   style="height:24px;padding:0 8px;border-radius:4px;border:1px solid #c4b5fd;background:#f5eeff;color:#6d28d9;font-size:10.5px;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:3px;text-decoration:none"
                                   title="Cetak PDF periode {{ $h->periode }}">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                                    PDF
                                </a>
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            @else
            <div class="empty-msg">Belum ada riwayat evaluasi untuk petugas ini.</div>
            @endif
        </div>

        {{-- Tab: Absensi --}}
        <div id="t-absensi" class="tab-pane">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Riwayat Absensi (30 Hari Terakhir)</div>
            </div>
            @if($absensi->isNotEmpty())
            <table>
                <thead><tr>
                    <th>Tanggal</th><th>Sesi</th><th>Jenis</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Keterlambatan</th>
                </tr></thead>
                <tbody>
                    @foreach($absensi as $a)
                    <tr>
                        <td class="mono">{{ \Carbon\Carbon::parse($a->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($a->sesi) }}</td>
                        <td>{{ $a->label_jenis_scan }}</td>
                        <td style="text-align:center">
                            @php $sc=match($a->status_kehadiran){'tepat_waktu'=>'p-green','toleransi'=>'p-blue','terlambat'=>'p-amber','alpha'=>'p-red',default=>'p-gray'}; @endphp
                            <span class="pill {{ $sc }}">{{ $a->label_status_kehadiran }}</span>
                        </td>
                        <td style="text-align:center" class="mono">{{ $a->keterlambatan_menit ? $a->keterlambatan_menit.' mnt':'—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-msg">Tidak ada data absensi.</div>
            @endif
        </div>

        {{-- Tab: Checklist --}}
        <div id="t-checklist" class="tab-pane">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Checklist Harian (30 Hari Terakhir)</div>
            </div>
            @if($checklist->isNotEmpty())
            <table>
                <thead><tr>
                    <th>Tanggal</th><th>Sesi</th>
                    <th style="text-align:center">Item Selesai</th>
                    <th style="text-align:center">Persentase</th>
                    <th style="text-align:center">Status</th>
                </tr></thead>
                <tbody>
                    @foreach($checklist as $c)
                    <tr>
                        <td class="mono">{{ \Carbon\Carbon::parse($c->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($c->sesi) }}</td>
                        <td style="text-align:center" class="mono">{{ $c->totalChecked() }}/{{ $c->totalItems() }}</td>
                        <td style="text-align:center">
                            <div style="display:flex;align-items:center;gap:6px;justify-content:center">
                                <div style="width:60px;height:5px;background:var(--wash2);border-radius:3px">
                                    <div style="width:{{ $c->pctChecked() }}%;height:100%;background:var(--blue);border-radius:3px"></div>
                                </div>
                                <span class="mono">{{ $c->pctChecked() }}%</span>
                            </div>
                        </td>
                        <td style="text-align:center">
                            <span class="pill {{ $c->status==='verified'?'p-green':'p-amber' }}">{{ ucfirst($c->status) }}</span>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-msg">Tidak ada data checklist.</div>
            @endif
        </div>

        {{-- Tab: Laporan Shift --}}
        <div id="t-laporan" class="tab-pane">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Laporan Shift Terbaru</div>
            </div>
            @if($laporan->isNotEmpty())
            <table>
                <thead><tr>
                    <th>Tanggal</th><th>Sesi</th>
                    <th style="text-align:center">Status</th><th>Catatan Koordinator</th>
                </tr></thead>
                <tbody>
                    @foreach($laporan as $l)
                    <tr>
                        <td class="mono">{{ \Carbon\Carbon::parse($l->tanggal)->format('d/m/Y') }}</td>
                        <td>{{ ucfirst($l->sesi) }}</td>
                        <td style="text-align:center">
                            @php $sc=match($l->status){'approved'=>'p-green','rejected'=>'p-red','submitted'=>'p-blue',default=>'p-gray'}; @endphp
                            <span class="pill {{ $sc }}">{{ ucfirst($l->status) }}</span>
                        </td>
                        <td style="font-size:11.5px;color:var(--ink3)">{{ $l->catatan_koordinator ?? '—' }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-msg">Tidak ada data laporan shift.</div>
            @endif
        </div>

        {{-- Tab: Quiz --}}
        <div id="t-quiz" class="tab-pane">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Hasil Quiz &amp; Tugas</div>
            </div>
            @if($nilaiQuiz->isNotEmpty())
            <table>
                <thead><tr>
                    <th>Judul Tugas/Quiz</th>
                    <th style="text-align:center">Skor</th>
                    <th style="text-align:center">Status</th>
                    <th>Tanggal</th>
                </tr></thead>
                <tbody>
                    @foreach($nilaiQuiz as $q)
                    <tr>
                        <td>{{ $q->tugas?->judul ?? 'Tugas #'.$q->tugas_id }}</td>
                        <td style="text-align:center"><strong class="mono">{{ $q->skor ?? '—' }}</strong></td>
                        <td style="text-align:center">
                            <span class="pill {{ $q->status==='sudah'?'p-green':'p-amber' }}">{{ ucfirst($q->status) }}</span>
                        </td>
                        <td class="mono" style="font-size:11px">{{ $q->created_at->format('d/m/Y') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
            @else
            <div class="empty-msg">Tidak ada data quiz.</div>
            @endif
        </div>

        {{-- Tab: Histori Evaluasi --}}
        <div id="t-histori" class="tab-pane">
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="font-size:12.5px;font-weight:600">Histori Evaluasi oleh Koordinator</div>
                <div style="font-size:11px;color:var(--ink3);margin-top:2px">Semua penilaian yang pernah diberikan koordinator kepada petugas ini</div>
            </div>
            @if($histori->isNotEmpty())
            @foreach($histori as $h)
            @php $gc3=match($h->grade??''){'SB'=>'g-sb','B'=>'g-b','C'=>'g-c','K'=>'g-k','SK'=>'g-sk',default=>'p-gray'}; @endphp
            <div style="padding:14px 18px;border-bottom:1px solid var(--rule)">
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:6px;flex-wrap:wrap;gap:8px">
                    <div style="display:flex;align-items:center;gap:8px">
                        <span style="font-size:12.5px;font-weight:600">{{ \App\Helpers\PeriodeHelper::isoLabel($h->periode) }}</span>
                        <span class="pill {{ $gc3 }}">{{ $h->grade ?? '—' }}</span>
                        <span class="pill {{ $h->status==='selesai'?'p-green':'p-amber' }}" style="font-size:10px">{{ ucfirst($h->status) }}</span>
                    </div>
                    <div style="display:flex;align-items:center;gap:10px">
                        <span class="mono" style="font-size:17px;font-weight:600;color:var(--ink)">{{ $h->jumlah_nilai ? number_format($h->jumlah_nilai,2) : '—' }}</span>
                        {{-- tombol PDF di tiap kartu histori --}}
                        <a href="{{ route('koordinator.nilai-evaluasi.pdf', [$petugas->id, 'periode' => $h->periode]) }}"
                           target="_blank"
                           style="height:26px;padding:0 10px;border-radius:4px;border:1px solid #c4b5fd;background:#f5eeff;color:#6d28d9;font-size:10.5px;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:4px;text-decoration:none"
                           title="Cetak PDF periode {{ $h->periode }}">
                            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            PDF
                        </a>
                    </div>
                </div>
                <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:6px;margin-bottom:8px">
                    @foreach([['Sikap',$h->rata_sikap_kerja],['Hasil',$h->rata_indikator_hasil],['Proses',$h->rata_indikator_proses],['Mutu',$h->rata_mutu_pelayanan]] as [$k,$v])
                    <div style="background:var(--wash);border-radius:5px;padding:6px 8px;text-align:center">
                        <div style="font-size:10px;color:var(--ink3)">{{ $k }}</div>
                        <div class="mono" style="font-size:13px;font-weight:500;margin-top:2px">{{ $v ? number_format($v,1):'—' }}</div>
                    </div>
                    @endforeach
                </div>
                @if($h->catatan)
                <div style="background:var(--wash);border-radius:5px;padding:8px 10px;font-size:12px;color:var(--ink2)">
                    💬 {{ $h->catatan }}
                </div>
                @endif
                <div style="font-size:10.5px;color:var(--ink3);margin-top:7px">
                    Dinilai oleh: <strong>{{ $h->koordinator?->name ?? '—' }}</strong>
                    &middot; {{ $h->tanggal_evaluasi?->format('d/m/Y') }}
                </div>
            </div>
            @endforeach
            @else
            <div class="empty-msg">Belum ada histori evaluasi.</div>
            @endif
        </div>

    </div>
</div>

@endsection

@push('scripts')
<script>
function switchTab(btn,id){
    document.querySelectorAll('.tab-btn').forEach(b=>b.classList.remove('active'));
    document.querySelectorAll('.tab-pane').forEach(p=>p.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}
</script>
@endpush