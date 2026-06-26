@extends('layouts.admin')
@section('title', 'Survey Internal — Rekap Hasil')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">Dashboard</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('admin.survey.pertanyaan') }}">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Hasil Survey Internal</strong>
@endsection

@push('styles')
<style>
    .si-topbar { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule);flex-wrap:wrap;gap:12px; }
    .si-topbar h1 { font-size:19px;font-weight:600;letter-spacing:-.3px;margin:0;color:var(--ink); }
    .si-topbar p  { font-size:12px;color:var(--ink3);margin-top:3px; }

    .si-btn { display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 14px;border-radius:5px;font-size:12px;font-weight:500;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;text-decoration:none;border:none;transition:opacity .15s; }
    .si-btn-primary  { background:var(--blue);color:#fff; }
    .si-btn-primary:hover { opacity:.88; }
    .si-btn-secondary { background:var(--surface);color:var(--ink2);border:1px solid var(--rule); }
    .si-btn-secondary:hover { border-color:var(--ink3);color:var(--ink); }

    .si-stats { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px; }
    .si-stat { background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:14px 18px;flex:1;min-width:130px; }
    .si-stat-label { font-size:10.5px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--ink3);margin-bottom:4px; }
    .si-stat-val   { font-size:22px;font-weight:700;color:var(--ink);letter-spacing:-.5px; }

    .si-filter { display:flex;gap:10px;align-items:center;margin-bottom:18px;flex-wrap:wrap; }
    .si-filter select, .si-filter input { height:32px;padding:0 10px;border:1px solid var(--rule);border-radius:5px;font-size:12.5px;background:var(--surface);color:var(--ink);font-family:'IBM Plex Sans',sans-serif; }
    .si-filter select:focus, .si-filter input:focus { outline:none;border-color:var(--blue); }

    .si-badge-tw { display:inline-flex;align-items:center;gap:4px;padding:2px 9px;border-radius:99px;font-size:10.5px;font-weight:600;background:#eff6ff;color:#1d4ed8; }

    /* Table */
    .si-panel  { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
    .si-ph     { display:flex;align-items:center;justify-content:space-between;padding:12px 18px;border-bottom:1px solid var(--rule); }
    .si-ph-title { font-size:12.5px;font-weight:600;color:var(--ink); }
    .si-ph-sub   { font-size:11px;color:var(--ink3);margin-top:1px; }

    .si-table { width:100%;border-collapse:collapse; }
    .si-table thead th { text-align:left;padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.9px;text-transform:uppercase;color:var(--ink3);background:var(--wash);border-bottom:1px solid var(--rule); }
    .si-table tbody tr { border-bottom:1px solid var(--rule);transition:background .1s; }
    .si-table tbody tr:last-child { border-bottom:none; }
    .si-table tbody tr:hover { background:var(--wash); }
    .si-table tbody td { padding:10px 16px;vertical-align:middle;font-size:12.5px; }

    .si-pill { display:inline-flex;align-items:center;padding:2px 8px;border-radius:99px;font-size:10.5px;font-weight:600; }
    .si-pill-green { background:#dcfce7;color:#15803d; }
    .si-pill-gray  { background:#f1f5f9;color:#64748b; }
    .si-pill-blue  { background:#eff6ff;color:#1d4ed8; }

    .si-empty { text-align:center;padding:40px 16px;color:var(--ink3);font-size:13px; }
    .si-star  { color:#f59e0b;font-size:13px; }
</style>
@endpush

@section('content')

<div class="si-topbar">
    <div>
        <h1>Rekap Survey Internal</h1>
        <p>Penilaian antar pegawai — hasil dikelompokkan per wilayah.</p>
    </div>
    <a href="{{ route('admin.survey.pertanyaan') }}" class="si-btn si-btn-secondary">
        ← Kembali ke Pertanyaan
    </a>
</div>

{{-- Alert --}}
@if(session('success'))
    <div style="display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:12.5px;font-weight:500;background:var(--green-lt);color:var(--green);border:1px solid #b7e4ce;">
        ✓ {{ session('success') }}
    </div>
@endif

{{-- Stats --}}
<div class="si-stats">
    <div class="si-stat">
        <div class="si-stat-label">Total Penilaian</div>
        <div class="si-stat-val">{{ $totalSurvey }}</div>
    </div>
    <div class="si-stat">
        <div class="si-stat-label">Rata-rata Global</div>
        <div class="si-stat-val">{{ $rataGlobal ? number_format($rataGlobal, 2) : '—' }}</div>
    </div>
    <div class="si-stat">
        <div class="si-stat-label">Periode</div>
        <div class="si-stat-val" style="font-size:14px;padding-top:4px">
            <span class="si-badge-tw">{{ \App\Helpers\PeriodeHelper::label($periode) }}</span>
        </div>
    </div>
</div>

{{-- Filter --}}
<form method="GET" action="{{ route('admin.survey.internal.hasil') }}" class="si-filter">
    <select name="periode" onchange="this.form.submit()">
        {{-- Tampilkan triwulan berjalan jika tidak ada di DB --}}
        @php
            $periodeOptions = $periodeList->isNotEmpty() ? $periodeList : collect([\App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang()]);
        @endphp
        @foreach($periodeOptions as $p)
            <option value="{{ $p }}" {{ $p === $periode ? 'selected' : '' }}>
                {{ \App\Helpers\PeriodeHelper::label($p) }}
            </option>
        @endforeach
    </select>
    <input type="text" name="search" placeholder="Cari nama pegawai…" value="{{ $search }}">
    <button type="submit" class="si-btn si-btn-primary">Tampilkan</button>
    @if($search)
        <a href="{{ route('admin.survey.internal.hasil', ['periode' => $periode]) }}" class="si-btn si-btn-secondary">Reset</a>
    @endif
</form>

{{-- Tabel per wilayah --}}
@forelse($dataPerWilayah as $item)
    @if($item['petugas']->isEmpty()) @continue @endif

    <div class="si-panel" style="margin-bottom:20px">
        <div class="si-ph">
            <div>
                <div class="si-ph-title">{{ $item['wilayah']->nama }}</div>
                <div class="si-ph-sub">
                    {{ $item['total_penilai'] }} penilaian diterima
                    @if($item['rata_kepuasan'])
                        · Rata-rata: <strong>{{ number_format($item['rata_kepuasan'], 2) }}</strong>
                    @endif
                </div>
            </div>
        </div>

        <table class="si-table">
            <thead>
                <tr>
                    <th>Nama Pegawai</th>
                    <th>Jumlah Penilai</th>
                    <th>Rata-rata Rating</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($item['petugas'] as $p)
                    <tr>
                        <td>
                            <div style="font-weight:500;color:var(--ink)">{{ $p['user']->name }}</div>
                            <div style="font-size:11px;color:var(--ink3)">{{ $p['user']->email }}</div>
                        </td>
                        <td>
                            @if($p['jumlah_penilai'] > 0)
                                <span class="si-pill si-pill-blue">{{ $p['jumlah_penilai'] }} penilai</span>
                            @else
                                <span style="color:var(--ink3)">Belum ada</span>
                            @endif
                        </td>
                        <td>
                            @if($p['rata_kepuasan'])
                                <span class="si-star">★</span>
                                <strong style="color:var(--ink)">{{ number_format($p['rata_kepuasan'], 2) }}</strong>
                                <span style="font-size:11px;color:var(--ink3)">/ 5</span>
                            @else
                                <span style="color:var(--ink3)">—</span>
                            @endif
                        </td>
                        <td>
                            @if($p['jumlah_penilai'] > 0)
                                <a href="{{ route('admin.survey.internal.hasil.detail', ['petugasId' => $p['petugas']->id, 'periode' => $periode]) }}"
                                   class="si-btn si-btn-secondary" style="height:26px;padding:0 10px;font-size:11px">
                                    Detail →
                                </a>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@empty
    <div class="si-panel">
        <div class="si-empty">
            <div style="font-size:28px;margin-bottom:8px">📋</div>
            Belum ada data Survey Internal untuk periode ini.
        </div>
    </div>
@endforelse

@endsection