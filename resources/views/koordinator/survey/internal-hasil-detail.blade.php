@extends('layouts.koordinator')
@section('title', 'Survey Internal — Detail ' . $petugas->user->name)

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('koordinator.survey-internal.hasil', ['periode' => $periode]) }}">Survey Internal</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>{{ $petugas->user->name }}</strong>
@endsection

@push('styles')
<style>
    .sid-topbar { display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule);flex-wrap:wrap;gap:12px; }
    .sid-topbar h1 { font-size:18px;font-weight:600;margin:0;color:var(--ink); }
    .sid-topbar p  { font-size:12px;color:var(--ink3);margin-top:3px; }

    .sid-btn { display:inline-flex;align-items:center;gap:6px;height:32px;padding:0 14px;border-radius:5px;font-size:12px;font-weight:500;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;text-decoration:none;border:none;transition:opacity .15s; }
    .sid-btn-secondary { background:var(--surface);color:var(--ink2);border:1px solid var(--rule); }
    .sid-btn-secondary:hover { border-color:var(--ink3); }

    .sid-stats { display:flex;gap:12px;flex-wrap:wrap;margin-bottom:20px; }
    .sid-stat { background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:14px 18px;flex:1;min-width:120px; }
    .sid-stat-label { font-size:10.5px;font-weight:600;letter-spacing:.7px;text-transform:uppercase;color:var(--ink3);margin-bottom:4px; }
    .sid-stat-val   { font-size:22px;font-weight:700;color:var(--ink); }

    .sid-panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
    .sid-ph    { padding:12px 18px;border-bottom:1px solid var(--rule);background:var(--wash); }
    .sid-ph-title { font-size:12.5px;font-weight:600;color:var(--ink); }
    .sid-ph-sub   { font-size:11px;color:var(--ink3);margin-top:2px; }

    .sid-table { width:100%;border-collapse:collapse; }
    .sid-table thead th { text-align:left;padding:8px 16px;font-size:10px;font-weight:600;letter-spacing:.9px;text-transform:uppercase;color:var(--ink3);background:var(--wash);border-bottom:1px solid var(--rule); }
    .sid-table tbody tr { border-bottom:1px solid var(--rule); }
    .sid-table tbody tr:last-child { border-bottom:none; }
    .sid-table tbody td { padding:10px 16px;vertical-align:middle;font-size:12.5px; }

    .sid-star  { color:#f59e0b;font-size:13px; }
    .sid-badge { display:inline-flex;align-items:center;padding:2px 8px;border-radius:99px;font-size:10.5px;font-weight:600; }
    .sid-badge-blue { background:#eff6ff;color:#1d4ed8; }
</style>
@endpush

@section('content')

<div class="sid-topbar">
    <div>
        <h1>{{ $petugas->user->name }}</h1>
        <p>Detail Survey Internal · <span class="sid-badge sid-badge-blue">{{ \App\Helpers\PeriodeHelper::label($periode) }}</span></p>
    </div>
    <a href="{{ route('koordinator.survey-internal.hasil', ['periode' => $periode]) }}" class="sid-btn sid-btn-secondary">
        ← Kembali ke Rekap
    </a>
</div>

{{-- Statistik --}}
<div class="sid-stats">
    <div class="sid-stat">
        <div class="sid-stat-label">Jumlah Penilai</div>
        <div class="sid-stat-val">{{ $surveys->count() }}</div>
    </div>
    <div class="sid-stat">
        <div class="sid-stat-label">Rata-rata Keseluruhan</div>
        <div class="sid-stat-val">
            @php
                $rataAll = $rataPerPertanyaan->filter(fn($r) => $r['rata'] !== null)->avg('rata');
            @endphp
            {{ $rataAll ? number_format($rataAll, 2) : '—' }}
        </div>
    </div>
</div>

{{-- Rata-rata per pertanyaan --}}
<div class="sid-panel" style="margin-bottom:22px">
    <div class="sid-ph">
        <div class="sid-ph-title">Rata-rata per Pertanyaan</div>
        <div class="sid-ph-sub">Berdasarkan seluruh penilai periode ini</div>
    </div>
    <table class="sid-table">
        <thead>
            <tr>
                <th>#</th>
                <th>Pertanyaan</th>
                <th>Rata-rata</th>
                <th>Jumlah Jawaban</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rataPerPertanyaan as $i => $r)
                <tr>
                    <td style="color:var(--ink3)">{{ $i + 1 }}</td>
                    <td>{{ $r['pertanyaan']->pertanyaan }}</td>
                    <td>
                        @if($r['rata'] !== null)
                            <span class="sid-star">★</span>
                            <strong>{{ number_format($r['rata'], 2) }}</strong>
                            <span style="font-size:11px;color:var(--ink3)">/ 5</span>
                        @else
                            <span style="color:var(--ink3)">Bukan rating</span>
                        @endif
                    </td>
                    <td style="color:var(--ink2)">{{ $r['count'] ?: '—' }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Jawaban per penilai (anonim) --}}
@if($surveys->isNotEmpty())
<div class="sid-panel">
    <div class="sid-ph">
        <div class="sid-ph-title">Detail Jawaban Penilai</div>
        <div class="sid-ph-sub">Identitas penilai dirahasiakan — ditampilkan sebagai Penilai #N</div>
    </div>
    <table class="sid-table">
        <thead>
            <tr>
                <th>Penilai</th>
                @foreach($pertanyaan as $p)
                    <th style="max-width:180px;white-space:normal;line-height:1.4">{{ Str::limit($p->pertanyaan, 35) }}</th>
                @endforeach
                <th>Waktu</th>
            </tr>
        </thead>
        <tbody>
            @foreach($surveys as $idx => $survey)
                <tr>
                    <td style="color:var(--ink3);font-size:11.5px">Penilai #{{ $idx + 1 }}</td>
                    @foreach($pertanyaan as $p)
                        @php
                            $jwb = $survey->jawaban->firstWhere('pertanyaan_id', $p->id);
                        @endphp
                        <td>
                            @if($jwb)
                                @if($p->tipe === 'rating')
                                    <span class="sid-star">{{ str_repeat('★', (int)$jwb->jawaban) }}</span>
                                    <span style="font-size:11.5px;color:var(--ink2)">({{ $jwb->jawaban }})</span>
                                @else
                                    <span style="font-size:12px;color:var(--ink2)">{{ $jwb->jawaban }}</span>
                                @endif
                            @else
                                <span style="color:var(--ink3)">—</span>
                            @endif
                        </td>
                    @endforeach
                    <td style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">
                        {{ $survey->diisi_pada?->format('d/m/Y H:i') ?? '—' }}
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection