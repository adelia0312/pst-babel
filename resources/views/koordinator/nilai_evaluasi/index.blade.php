@extends('layouts.koordinator')

@section('title', 'Nilai & Evaluasi')

@push('styles')
<style>
.inp { height:32px;border:1px solid var(--rule);border-radius:5px;padding:0 10px;font-size:12px;font-family:inherit;color:var(--ink);background:var(--surface); }
.inp:focus { outline:none;border-color:var(--blue); }

/* Stats */
.stat-row { display:grid;grid-template-columns:repeat(auto-fit,minmax(150px,1fr));gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
.stat-box { background:var(--surface);padding:18px 20px;position:relative; }
.stat-lbl { font-size:10px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);margin-bottom:8px; }
.stat-num { font-size:28px;font-weight:300;font-family:'IBM Plex Mono',monospace;letter-spacing:-1px;line-height:1;margin-bottom:4px; }
.stat-hint { font-size:11px;color:var(--ink3); }
.stat-line { position:absolute;bottom:0;left:0;right:0;height:2px; }

/* Podium top 3 */
.podium { display:grid;grid-template-columns:repeat(3,1fr);gap:10px;margin-bottom:20px; }
@media(max-width:560px){.podium{grid-template-columns:1fr;}}
.pod { background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:14px 16px;position:relative;overflow:hidden; }
.pod::after { content:'';position:absolute;top:0;left:0;right:0;height:3px; }
.pod-1::after { background:linear-gradient(90deg,#f59e0b,#fbbf24); }
.pod-2::after { background:linear-gradient(90deg,#6b7280,#9ca3af); }
.pod-3::after { background:linear-gradient(90deg,#b45309,#d97706); }
.pod-rank { font-size:10px;font-weight:700;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);margin-bottom:6px; }
.pod-name { font-size:13px;font-weight:600;color:var(--ink);margin-bottom:8px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis; }
.pod-score { font-size:24px;font-weight:300;font-family:'IBM Plex Mono',monospace;letter-spacing:-1px;color:var(--ink); }

/* Grade badges */
.g-sb{background:#dcfce7;color:#166534;}
.g-b{background:#dbeafe;color:#1e40af;}
.g-c{background:#fef3c7;color:#92400e;}
.g-k{background:#ffedd5;color:#9a3412;}
.g-sk{background:#fee2e2;color:#991b1b;}

/* Table */
.tbl-wrap { overflow-x:auto; }
table { width:100%;border-collapse:collapse;font-size:12.5px; }
th { padding:9px 14px;font-size:10px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--ink3);text-align:left;border-bottom:1px solid var(--rule);background:var(--wash); }
td { padding:11px 14px;border-bottom:1px solid var(--rule);color:var(--ink); }
tr:last-child td { border-bottom:none; }
tr:hover td { background:var(--wash); }
.mono { font-family:'IBM Plex Mono',monospace;font-size:12px; }
.pill { display:inline-flex;align-items:center;padding:2px 8px;border-radius:4px;font-size:10.5px;font-weight:600; }
.s-selesai{background:#dcfce7;color:#166534;}
.s-draft{background:#fef3c7;color:#92400e;}
.s-belum{background:#f3f4f6;color:#6b7280;}
.ava { width:28px;height:28px;border-radius:5px;background:var(--blue-lt);color:var(--blue);font-size:10.5px;font-weight:700;display:inline-flex;align-items:center;justify-content:center;flex-shrink:0;vertical-align:middle;margin-right:8px;font-family:'IBM Plex Mono',monospace; }
.btn-sm { height:28px;padding:0 12px;border-radius:4px;font-size:11.5px;font-weight:500;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:5px;text-decoration:none;border:none; }
.btn-blue { background:var(--blue);color:#fff; }
.btn-blue:hover { background:#1648b8; }
.btn-out { background:var(--surface);color:var(--ink2);border:1px solid var(--rule)!important; }
.btn-out:hover { border-color:var(--ink2)!important;color:var(--ink); }
.panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
.ph { padding:12px 16px;border-bottom:1px solid var(--rule);display:flex;justify-content:space-between;align-items:center; }
.ph-title { font-size:12.5px;font-weight:600;color:var(--ink); }
.ph-sub { font-size:11px;color:var(--ink3); }

/* Dropdown export */
.dl-menu {
    display:none; position:absolute; top:calc(100% + 6px); right:0;
    min-width:270px; background:var(--surface); border:1px solid var(--rule);
    border-radius:8px; box-shadow:0 8px 24px rgba(0,0,0,.12); padding:6px; z-index:30;
}
.dl-menu.open { display:block; }
.dl-item {
    display:flex; align-items:center; gap:8px; padding:8px 10px;
    border-radius:6px; font-size:12px; color:var(--ink); text-decoration:none;
}
.dl-item:hover { background:var(--blue-lt); color:var(--blue); }

</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Nilai &amp; Evaluasi</strong>
@endsection

@section('content')

{{-- Alert --}}
@if(session('success'))
<div style="background:#d1fae5;border:1px solid #6ee7b7;color:#065f46;border-radius:6px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>{{ session('success') }}
</div>
@endif
@if(session('info'))
<div style="background:#dbeafe;border:1px solid #93c5fd;color:#1e40af;border-radius:6px;padding:10px 14px;font-size:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>{{ session('info') }}
</div>
@endif

{{-- Header --}}
<div style="display:flex;justify-content:space-between;align-items:flex-start;margin-bottom:20px;flex-wrap:wrap;gap:12px">
    <div>
        <h1 style="font-size:18px;font-weight:600;letter-spacing:-.3px;color:var(--ink)">Nilai &amp; Evaluasi</h1>
        <p style="font-size:12px;color:var(--ink3);margin-top:2px">Penilaian kinerja petugas wilayah Anda</p>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap">
        <form method="GET" style="display:flex;gap:6px;align-items:center">
            <select name="periode" class="inp" onchange="this.form.submit()">
                @foreach($periodeOptions as $key => $label)
                <option value="{{ $key }}" {{ $key === $periode ? 'selected' : '' }}>{{ $label }}</option>
                @endforeach
            </select>
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari petugas…" class="inp" style="width:150px">
            <button type="submit" class="btn-sm btn-blue">Cari</button>
        </form>
        @php $tahunAktif = substr($periode, 0, 4); @endphp
        <div class="dl-wrap" style="position:relative">
            <button type="button" class="btn-sm btn-out" id="dlToggle" onclick="toggleDl()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Export Excel
                <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg>
            </button>
            <div class="dl-menu" id="dlMenu">
                <a href="{{ route('koordinator.nilai-evaluasi.export', ['periode' => $periode]) }}" class="dl-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    <span>Triwulan ini — {{ $periodeOptions[$periode] ?? $periode }}</span>
                </a>
                <a href="{{ route('koordinator.nilai-evaluasi.export.tahunan', ['tahun' => $tahunAktif]) }}" class="dl-item">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
                    <span>1 Tahun — Tahun {{ $tahunAktif }} (semua triwulan)</span>
                </a>
            </div>
        </div>
        {{-- Tombol selesaikan draft --}}
        @php
            $jumlahDraftKoor = \App\Models\EvaluasiPetugas::where('wilayah_id', Auth::user()->wilayah_id)
                ->where('periode', $periode)->where('status','draft')->whereNotNull('jumlah_nilai')->count();
        @endphp
        @if($jumlahDraftKoor > 0)
        <form method="POST" action="{{ route('koordinator.nilai-evaluasi.selesaikan-semua') }}" style="display:inline"
              onsubmit="return confirm('Selesaikan {{ $jumlahDraftKoor }} evaluasi draft sekarang?')">
            @csrf
            <input type="hidden" name="periode" value="{{ $periode }}">
            <button type="submit" class="btn-sm" style="background:#fff3cd;color:#856404;border:1px solid #ffc107">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                Selesaikan {{ $jumlahDraftKoor }} Evaluasi Draft
            </button>
        </form>
        @endif
    </div>
</div>

{{-- Statistik --}}
<div class="stat-row">
    <div class="stat-box">
        <div class="stat-lbl">Total Petugas</div>
        <div class="stat-num">{{ $totalPetugas }}</div>
        <div class="stat-hint">Di wilayah Anda</div>
        <div class="stat-line" style="background:var(--blue);width:100%"></div>
    </div>
    <div class="stat-box">
        <div class="stat-lbl">Sudah Dievaluasi</div>
        <div class="stat-num" style="color:var(--green)">{{ $sudahEvaluasi }}</div>
        <div class="stat-hint">{{ $totalPetugas > 0 ? round(($sudahEvaluasi/$totalPetugas)*100) : 0 }}% dari total</div>
        <div class="stat-line" style="background:var(--green);width:{{ $totalPetugas > 0 ? round(($sudahEvaluasi/$totalPetugas)*100) : 0 }}%"></div>
    </div>
    <div class="stat-box">
        <div class="stat-lbl">Belum Dievaluasi</div>
        <div class="stat-num" style="color:var(--amber)">{{ $belumEvaluasi }}</div>
        <div class="stat-hint">Perlu ditindaklanjuti</div>
        <div class="stat-line" style="background:var(--amber);width:{{ $totalPetugas > 0 ? round(($belumEvaluasi/$totalPetugas)*100) : 0 }}%"></div>
    </div>
    <div class="stat-box">
        <div class="stat-lbl">Rata-rata Nilai</div>
        <div class="stat-num" style="color:#7c3aed">{{ $rataRata ? number_format($rataRata,2) : '—' }}</div>
        <div class="stat-hint">{{ $rataRata ? \App\Models\EvaluasiPetugas::labelGrade(\App\Models\EvaluasiPetugas::hitungGrade($rataRata)) : 'Belum ada data' }}</div>
        <div class="stat-line" style="background:#7c3aed;width:{{ $rataRata ?? 0 }}%"></div>
    </div>
    @if($petugasTerbaik)
    <div class="stat-box">
        <div class="stat-lbl">Terbaik Periode Ini</div>
        <div class="stat-num" style="font-size:15px;font-weight:600;font-family:inherit;letter-spacing:0;padding-top:4px">{{ $petugasTerbaik['user']->name }}</div>
        <div class="stat-hint">{{ number_format($petugasTerbaik['jumlah_nilai'],2) }} &middot; {{ \App\Models\EvaluasiPetugas::labelGrade($petugasTerbaik['grade']) }}</div>
        <div class="stat-line" style="background:#f59e0b;width:{{ $petugasTerbaik['jumlah_nilai'] }}%"></div>
    </div>
    @endif
</div>

{{-- Top 3 Ranking --}}
@if($ranking->count() >= 1)
<div class="podium">
    @foreach($ranking->take(3) as $i => $d)
    @php $gc = match($d['grade']) {'SB'=>'g-sb','B'=>'g-b','C'=>'g-c','K'=>'g-k','SK'=>'g-sk',default=>'g-b'}; @endphp
    <div class="pod pod-{{ $i+1 }}">
        <div class="pod-rank">Peringkat {{ $i+1 }}</div>
        <div class="pod-name">{{ $d['user']->name }}</div>
        <div style="display:flex;align-items:baseline;gap:8px">
            <span class="pod-score">{{ number_format($d['jumlah_nilai'],2) }}</span>
            <span class="pill {{ $gc }}" style="font-size:10px">{{ $d['grade'] }}</span>
        </div>
    </div>
    @endforeach
    @for($j = $ranking->take(3)->count(); $j < 3; $j++)
    <div class="pod" style="opacity:.3;border-style:dashed">
        <div class="pod-rank">Peringkat {{ $j+1 }}</div>
        <div style="font-size:12px;color:var(--ink3);margin-top:8px">Belum ada data</div>
    </div>
    @endfor
</div>
@endif

{{-- Tabel Petugas --}}
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Daftar Penilaian Petugas</div>
            <div class="ph-sub">Klik Proses Evaluasi untuk menilai, klik Detail untuk lihat riwayat lengkap per petugas</div>
        </div>
    </div>
    <div class="tbl-wrap">
        <table>
            <thead>
                <tr>
                    <th>Nama Petugas</th>
                    <th style="text-align:center">Sikap</th>
                    <th style="text-align:center">Ind. Hasil</th>
                    <th style="text-align:center">Ind. Proses</th>
                    <th style="text-align:center">Mutu</th>
                    <th style="text-align:center">Total Nilai</th>
                    <th style="text-align:center">Grade</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:center">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @forelse($dataPetugas as $d)
                @php
                    $gc = match($d['grade']) {'SB'=>'g-sb','B'=>'g-b','C'=>'g-c','K'=>'g-k','SK'=>'g-sk',default=>''};
                    $sc = match($d['status']) {'selesai'=>'s-selesai','draft'=>'s-draft',default=>'s-belum'};
                    $stLabel = match($d['status']) {'selesai'=>'Selesai','draft'=>'Draft',default=>'Belum'};
                @endphp
                <tr>
                    <td>
                        <span class="ava">{{ strtoupper(substr($d['user']->name,0,2)) }}</span>
                        <span style="font-weight:500">{{ $d['user']->name }}</span>
                        <div style="font-size:10.5px;color:var(--ink3);margin-top:2px;margin-left:36px">{{ $d['user']->username }}</div>
                    </td>
                    <td style="text-align:center" class="mono">{{ $d['rata_sikap'] ? number_format($d['rata_sikap'],1) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $d['rata_hasil'] ? number_format($d['rata_hasil'],1) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $d['rata_proses'] ? number_format($d['rata_proses'],1) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $d['rata_mutu'] ? number_format($d['rata_mutu'],1) : '—' }}</td>
                    <td style="text-align:center">
                        <strong class="mono" style="font-size:13.5px">{{ $d['jumlah_nilai'] ? number_format($d['jumlah_nilai'],2) : '—' }}</strong>
                    </td>
                    <td style="text-align:center">
                        @if($d['grade'] !== '-')
                        <span class="pill {{ $gc }}">{{ $d['grade'] }}</span>
                        @else <span style="color:var(--ink3)">—</span> @endif
                    </td>
                    <td style="text-align:center"><span class="pill {{ $sc }}">{{ $stLabel }}</span></td>
                    <td style="text-align:center">
                        <div style="display:flex;gap:6px;justify-content:center">
                            <a href="{{ route('koordinator.nilai-evaluasi.detail', $d['petugas']->id) }}"
                               class="btn-sm btn-out" style="border:1px solid var(--rule)" title="Lihat riwayat & data lengkap petugas">Detail</a>
                            <a href="{{ route('koordinator.nilai-evaluasi.form', [$d['petugas']->id, 'periode'=>$periode]) }}"
                               class="btn-sm btn-blue">
                                {{ $d['status'] === 'belum' ? '+ Evaluasi' : 'Edit Nilai' }}
                            </a>
                        </div>
                    </td>
                </tr>
                @empty
                <tr><td colspan="9" style="text-align:center;padding:32px;color:var(--ink3);font-size:12px">
                    Tidak ada petugas di wilayah Anda.
                </td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>

<div style="display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="font-size:10.5px;color:var(--ink3);font-weight:600">Grade:</span>
    <span class="pill g-sb" style="font-size:10px">SB &gt;95 (Sangat Baik)</span>
    <span class="pill g-b"  style="font-size:10px">B 86–95 (Baik)</span>
    <span class="pill g-c"  style="font-size:10px">C 66–85 (Cukup)</span>
    <span class="pill g-k"  style="font-size:10px">K 51–65 (Kurang)</span>
@endsection

@push('scripts')
<script>
function toggleDl() {
    document.getElementById('dlMenu')?.classList.toggle('open');
}
document.addEventListener('click', function (e) {
    const wrap = document.querySelector('.dl-wrap');
    if (wrap && !wrap.contains(e.target)) {
        document.getElementById('dlMenu')?.classList.remove('open');
    }
});
</script>
@endpush