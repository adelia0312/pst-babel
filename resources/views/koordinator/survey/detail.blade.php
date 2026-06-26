@extends('layouts.koordinator')
@section('title', 'Detail Survey')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('koordinator.survey.index', ['periode' => $periode]) }}">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>{{ $petugas->user?->name }}</strong>
@endsection

@push('styles')
<style>
    .sv-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .sv-topbar h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; color: var(--ink); }
    .sv-topbar p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }
    .sv-back { font-size: 12px; color: var(--ink3); text-decoration: none; display:inline-flex; align-items:center; gap:4px; margin-bottom:6px; }
    .sv-back:hover { color: var(--ink); }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s;
    }
    .sv-btn-primary { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-sm { height: 26px; padding: 0 10px; font-size: 11px; }

    .sv-alert {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 6px; margin-bottom: 14px;
        font-size: 12.5px; font-weight: 500;
    }
    .sv-alert-success { background: var(--green-lt); color: var(--green); border: 1px solid #b7e4ce; }
    .sv-alert-error   { background: var(--red-lt);   color: var(--red);   border: 1px solid #fbd5d5; }

    .sv-filter { display: flex; gap: 8px; align-items: flex-end; }
    .sv-filter label { font-size: 11px; font-weight: 500; color: var(--ink3); display:block; margin-bottom:4px; }
    .sv-filter input[type=month] {
        height: 30px; padding: 0 10px; font-size: 12px;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif;
    }

    /* Stats grid */
    .sv-stats {
        display: grid; grid-template-columns: repeat(4,1fr);
        gap: 1px; background: var(--rule);
        border: 1px solid var(--rule); border-radius: 8px;
        overflow: hidden; margin-bottom: 20px;
    }
    .sv-stat { background: var(--surface); padding: 16px 18px; }
    .sv-stat-label { font-size: 10px; font-weight: 600; letter-spacing: .8px; text-transform: uppercase; color: var(--ink3); margin-bottom: 6px; }
    .sv-stat-val { font-size: 26px; font-weight: 300; letter-spacing: -1px; font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1; }
    .sv-stat-green .sv-stat-val { color: var(--green); font-size:18px; }

    /* Sync bar */
    .sv-sync-bar {
        background: var(--surface); border: 1px solid var(--rule); border-radius: 8px;
        padding: 12px 16px; margin-bottom: 16px;
        display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    }
    .sv-sync-info { font-size: 12px; color: var(--ink2); flex: 1; }
    .sv-sync-info strong { color: var(--ink); }

    /* Panel & table */
    .sv-panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; margin-bottom: 16px; }
    .sv-ph { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
    .sv-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
    .sv-ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    .sv-table { width: 100%; border-collapse: collapse; }
    .sv-table thead th {
        text-align: left; padding: 8px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: .9px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    .sv-table tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    .sv-table tbody tr:last-child { border-bottom: none; }
    .sv-table tbody tr:hover { background: var(--wash); }
    .sv-table tbody td { padding: 10px 16px; vertical-align: middle; font-size: 12.5px; }

    .sv-pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 3px; }
    .sv-pill-green  { background: var(--green-lt); color: var(--green); }
    .sv-pill-amber  { background: var(--amber-lt); color: var(--amber); }
    .sv-pill-red    { background: var(--red-lt);   color: var(--red); }

    .sv-bar-wrap { display:flex; align-items:center; gap:6px; }
    .sv-bar { flex:1; background:var(--wash2); border-radius:3px; height:5px; overflow:hidden; max-width:140px; }
    .sv-bar-fill { height:100%; border-radius:3px; background:var(--blue); }
    .sv-bar-val { font-size:12.5px; font-weight:600; font-family:'IBM Plex Mono',monospace; color:var(--ink); }

    .sv-expand { background:var(--wash); }
    .sv-expand td { padding:16px 18px !important; }
    .sv-jaw-item { margin-bottom:12px; padding-bottom:12px; border-bottom:1px solid var(--rule); }
    .sv-jaw-item:last-child { border-bottom:none; margin-bottom:0; padding-bottom:0; }
    .sv-jaw-label { font-size:11px; color:var(--ink3); margin-bottom:5px; }
    .sv-star { font-size:16px; }

    .sv-empty { padding:48px 20px; text-align:center; color:var(--ink3); font-size:13px; line-height:1.8; }

    @media(max-width:768px) { .sv-stats { grid-template-columns: repeat(2,1fr); } }
</style>
@endpush

@section('content')

<div class="sv-topbar">
    <div>
        <a href="{{ route('koordinator.survey.index', ['periode' => $periode]) }}" class="sv-back">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali ke Rekap
        </a>
        <h1>{{ $petugas->user?->name }}</h1>
        <p>Hasil survey kepuasan — periode {{ \App\Helpers\PeriodeHelper::label($periode) }}</p>
    </div>
    <form method="GET" class="sv-filter">
        <div>
            <label>Periode</label>
            <input type="month" name="periode" value="{{ $periode }}">
        </div>
        <button type="submit" class="sv-btn sv-btn-secondary">Ganti</button>
    </form>
</div>

@if(session('success'))
<div class="sv-alert sv-alert-success">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="sv-alert sv-alert-error">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

@if($surveys->isEmpty())
<div class="sv-panel">
    <div class="sv-empty">
        Belum ada survey selesai untuk periode ini.<br>
        <a href="{{ route('koordinator.survey.index', ['periode' => $periode]) }}" class="sv-btn sv-btn-primary" style="margin-top:12px">Kembali ke Rekap</a>
    </div>
</div>
@else

@php
$allRatings = $surveys->flatMap(fn($s) => $s->jawaban)
    ->filter(fn($j) => ($j->pertanyaan?->tipe??'')==='rating' && is_numeric($j->jawaban))
    ->map(fn($j) => (float)$j->jawaban);
$rataTotal     = $allRatings->count() ? round($allRatings->avg(), 2) : null;
$nilaiKonversi = $rataTotal ? round(($rataTotal / 5) * 100, 2) : null;
@endphp

{{-- Stat cards --}}
<div class="sv-stats">
    <div class="sv-stat">
        <div class="sv-stat-label">Jumlah Responden</div>
        <div class="sv-stat-val">{{ $surveys->count() }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Rata-rata (1–5)</div>
        <div class="sv-stat-val">{{ $rataTotal ? number_format($rataTotal,2) : '—' }}</div>
    </div>
    <div class="sv-stat">
        <div class="sv-stat-label">Nilai (0–100)</div>
        <div class="sv-stat-val">{{ $nilaiKonversi ? number_format($nilaiKonversi,1) : '—' }}</div>
    </div>
    @if($evaluasi)
    <div class="sv-stat sv-stat-green">
        <div class="sv-stat-label">Sudah di Evaluasi</div>
        <div class="sv-stat-val">✓ {{ number_format($evaluasi->nilai_kepuasan_pelanggan,1) }}</div>
    </div>
    @endif
</div>

{{-- Sinkron ke evaluasi --}}
@if($nilaiKonversi)
<div class="sv-sync-bar">
    <div class="sv-sync-info">
        Nilai konversi <strong>{{ $nilaiKonversi }}</strong> siap disinkronkan ke evaluasi petugas ini.
    </div>
    <form method="POST" action="{{ route('koordinator.survey.sinkron', $petugas->id) }}">
        @csrf
        <input type="hidden" name="periode" value="{{ $periode }}">
        <button type="submit" class="sv-btn sv-btn-primary"
            onclick="return confirm('Masukkan nilai kepuasan ({{ $nilaiKonversi }}) ke evaluasi petugas ini?')">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <polyline points="23 4 23 10 17 10"/><path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
            </svg>
            Sinkronkan ke Evaluasi
        </button>
    </form>
</div>
@endif

{{-- Ringkasan per pertanyaan --}}
<div class="sv-panel">
    <div class="sv-ph">
        <div>
            <div class="sv-ph-title">Ringkasan per Pertanyaan</div>
        </div>
    </div>
    <table class="sv-table">
        <thead>
            <tr>
                <th style="width:32px">#</th>
                <th>Pertanyaan</th>
                <th style="width:210px">Rata-rata</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rataPerPertanyaan as $i => $item)
            <tr>
                <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">{{ $i+1 }}</td>
                <td style="color:var(--ink)">{{ $item['pertanyaan']->pertanyaan }}</td>
                <td>
                    @if($item['pertanyaan']->tipe==='rating' && $item['rata'])
                    <div class="sv-bar-wrap">
                        <div style="display:flex;gap:2px;align-items:center">
                            @for($s=1;$s<=5;$s++)
                            <span class="sv-star" style="color:{{ $s<=round($item['rata'])?'#f59e0b':'var(--rule)' }}">★</span>
                            @endfor
                        </div>
                        <span class="sv-bar-val">{{ number_format($item['rata'],2) }}</span>
                    </div>
                    @elseif($item['pertanyaan']->tipe!=='rating')
                    <span style="color:var(--ink3);font-size:11px">Teks — lihat detail</span>
                    @else —
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>

{{-- Detail jawaban --}}
<div class="sv-panel">
    <div class="sv-ph">
        <div>
            <div class="sv-ph-title">Jawaban Responden</div>
            <div class="sv-ph-sub">Klik baris untuk melihat detail jawaban</div>
        </div>
    </div>
    <table class="sv-table">
        <thead>
            <tr>
                <th style="width:32px">#</th>
                <th>Responden</th>
                <th>Waktu Isi</th>
                <th style="width:120px">Rating</th>
                <th style="width:70px"></th>
            </tr>
        </thead>
        <tbody>
            @foreach($surveys as $i => $survey)
            @php
                $r = $survey->jawaban->filter(fn($j)=>($j->pertanyaan?->tipe??'')==='rating'&&is_numeric($j->jawaban))->map(fn($j)=>(float)$j->jawaban);
                $rata = $r->count() ? round($r->avg(),2) : null;
            @endphp
            <tr>
                <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">{{ $i+1 }}</td>
                <td>
                    @if($survey->nama_responden)
                        <span style="font-size:12.5px;color:var(--ink)">{{ $survey->nama_responden }}</span>
                    @else
                        <em style="color:var(--ink3)">Anonim</em>
                    @endif
                </td>
                <td style="font-family:'IBM Plex Mono',monospace;font-size:11px;color:var(--ink3)">
                    {{ $survey->diisi_pada?->format('d M Y H:i') ?? '—' }}
                </td>
                <td>
                    @if($rata)
                    <span class="sv-pill {{ $rata>=4?'sv-pill-green':($rata>=3?'sv-pill-amber':'sv-pill-red') }}">
                        ★ {{ number_format($rata,1) }} / 5
                    </span>
                    @else <span style="color:var(--ink3)">—</span>
                    @endif
                </td>
                <td>
                    <button class="sv-btn sv-btn-secondary sv-btn-sm" onclick="toggle('r{{ $survey->id }}',this)">Buka</button>
                </td>
            </tr>
            <tr id="r{{ $survey->id }}" class="sv-expand" style="display:none">
                <td colspan="5">
                    @foreach($survey->jawaban->sortBy('pertanyaan.urutan') as $j)
                    <div class="sv-jaw-item">
                        <div class="sv-jaw-label">{{ $j->pertanyaan?->pertanyaan }}</div>
                        @if($j->pertanyaan?->tipe==='rating')
                        <div style="display:flex;gap:3px;align-items:center">
                            @for($s=1;$s<=5;$s++)
                            <span class="sv-star" style="color:{{ $s<=(int)$j->jawaban?'#f59e0b':'var(--rule)' }}">★</span>
                            @endfor
                            <span style="font-size:12px;color:var(--ink3);margin-left:6px">{{ $j->jawaban }} / 5</span>
                        </div>
                        @else
                        <div style="font-size:12.5px;color:var(--ink)">{{ $j->jawaban ?: '—' }}</div>
                        @endif
                    </div>
                    @endforeach
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
</div>
@endif

@endsection

@push('scripts')
<script>
function toggle(id, btn) {
    const el = document.getElementById(id);
    const open = el.style.display !== 'none' && el.style.display !== '';
    el.style.display = open ? 'none' : 'table-row';
    btn.textContent = open ? 'Buka' : 'Tutup';
}
</script>
@endpush