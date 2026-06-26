@extends('layouts.koordinator')

@section('title', 'Materi & Quiz Triwulan')

@section('breadcrumb')
    <span>PST</span><span>›</span><span>Materi & Tugas</span><span>›</span><strong>Triwulan</strong>
@endsection

@push('styles')
<style>
    /* ── Page ── */
    .page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; flex-wrap:wrap; gap:12px; }
    .page-head h1 { font-size:19px; font-weight:600; color:var(--ink); letter-spacing:-.3px; }
    .page-head p  { font-size:12px; color:var(--ink3); margin-top:3px; }

    /* ── Tab bar antara Reguler & Triwulan ── */
    .main-tab-bar { display:flex; gap:0; border-bottom:2px solid var(--rule); margin-bottom:24px; }
    .main-tab-btn {
        padding:9px 20px; font-size:12.5px; font-weight:500; color:var(--ink3);
        background:none; border:none; border-bottom:2px solid transparent;
        margin-bottom:-2px; cursor:pointer; display:flex; align-items:center; gap:7px;
        transition:color .12s,border-color .12s; font-family:'IBM Plex Sans',sans-serif;
    }
    .main-tab-btn:hover { color:var(--ink); }
    .main-tab-btn.active { color:var(--blue); border-bottom-color:var(--blue); font-weight:600; }

    /* ── Periode selector ── */
    .periode-bar {
        display:flex; align-items:center; gap:10px;
        background:var(--surface); border:1px solid var(--rule);
        border-radius:8px; padding:10px 14px; margin-bottom:20px; flex-wrap:wrap;
    }
    .periode-label { font-size:12px; font-weight:600; color:var(--ink); }
    .periode-select {
        height:32px; padding:0 10px; border:1px solid var(--rule); border-radius:6px;
        font-size:12px; font-family:'IBM Plex Sans',sans-serif; color:var(--ink);
        background:var(--wash); cursor:pointer;
    }
    .periode-select:focus { outline:none; border-color:var(--blue); }

    /* ── Stats strip ── */
    .stat-strip { display:flex; gap:1px; margin-bottom:20px; background:var(--rule); border:1px solid var(--rule); border-radius:8px; overflow:hidden; }
    .stat-box { flex:1; background:var(--surface); padding:18px 20px; display:flex; flex-direction:column-reverse; }
    .stat-lbl { font-size:10.5px; font-weight:500; letter-spacing:.5px; text-transform:uppercase; color:var(--ink3); margin-bottom:10px; }
    .stat-num { font-size:28px; font-weight:300; letter-spacing:-1px; font-family:'IBM Plex Mono',monospace; color:var(--ink); line-height:1; margin-top:8px; }
    .num-blue { color:var(--blue); } .num-green { color:var(--green); } .num-red { color:var(--red); }

    /* ── Alert ── */
    .tw-alert { display:flex; align-items:flex-start; gap:10px; padding:12px 14px; border-radius:7px; font-size:12px; margin-bottom:20px; line-height:1.7; }
    .tw-alert-info  { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }
    .tw-alert-warn  { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
    .tw-alert-green { background:var(--green-lt); border:1px solid #bbf7d0; color:#166534; }

    /* ── Materi card ── */
    .tw-card {
        background:var(--surface); border:1px solid var(--rule);
        border-radius:10px; overflow:hidden; margin-bottom:14px;
        transition:box-shadow .15s;
    }
    .tw-card:hover { box-shadow:0 3px 14px rgba(0,0,0,.07); }
    .tw-card-head {
        display:flex; align-items:center; gap:14px; padding:14px 18px;
        cursor:pointer; user-select:none;
    }
    .tw-card-head:hover { background:var(--wash); }
    .tw-card-icon {
        width:36px; height:36px; border-radius:8px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:#f0fdf4; color:var(--green);
    }
    .tw-card-info { flex:1; min-width:0; }
    .tw-card-title { font-size:13.5px; font-weight:600; color:var(--ink); }
    .tw-card-sub   { font-size:11px; color:var(--ink3); margin-top:2px; }

    /* Progress inline */
    .tw-prog { display:flex; align-items:center; gap:10px; flex-shrink:0; }
    .prog-track { width:100px; height:6px; background:var(--wash2); border-radius:3px; overflow:hidden; }
    .prog-fill  { height:100%; border-radius:3px; transition:width .4s; }
    .prog-lbl   { font-size:11px; font-weight:700; font-family:'IBM Plex Mono',monospace; min-width:34px; text-align:right; }

    /* Chips */
    .chip { display:inline-flex; align-items:center; gap:4px; font-size:10.5px; font-weight:600; padding:3px 9px; border-radius:20px; white-space:nowrap; }
    .chip-green { background:var(--green-lt); color:var(--green); }
    .chip-red   { background:var(--red-lt);   color:var(--red); }

    /* Chevron */
    .chevron { color:var(--ink3); flex-shrink:0; transition:transform .2s; }
    .tw-card.open .chevron { transform:rotate(180deg); }

    /* Delete btn */
    .btn-del { background:none; border:1px solid var(--rule); border-radius:5px; padding:4px 9px; font-size:10.5px; color:var(--red); cursor:pointer; transition:all .12s; flex-shrink:0; }
    .btn-del:hover { background:var(--red-lt); border-color:var(--red); }

    /* Body */
    .tw-card-body { display:none; border-top:1px solid var(--rule); }
    .tw-card.open .tw-card-body { display:block; }

    /* Tab: semua/sudah/belum */
    .inner-tab-bar { display:flex; gap:0; border-bottom:1px solid var(--rule); padding:0 18px; background:var(--wash); }
    .inner-tab-btn {
        padding:9px 14px; font-size:12px; font-weight:500; cursor:pointer;
        border:none; background:none; color:var(--ink3); border-bottom:2px solid transparent;
        margin-bottom:-1px; transition:color .15s; font-family:inherit;
    }
    .inner-tab-btn.active { color:var(--blue); border-bottom-color:var(--blue); font-weight:600; }

    /* Petugas list */
    .p-section { padding:10px 18px 14px; }
    .p-item { display:flex; align-items:center; gap:12px; padding:9px 10px; border-radius:7px; transition:background .12s; }
    .p-item:hover { background:var(--wash); }
    .p-ava { width:30px; height:30px; border-radius:7px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:11px; font-weight:700; }
    .ava-g { background:var(--green-lt); color:var(--green); }
    .ava-r { background:var(--red-lt);   color:var(--red); }
    .p-name { font-size:12.5px; font-weight:500; color:var(--ink); }
    .p-meta { font-size:11px; color:var(--ink3); }
    .skor-badge { font-size:11px; font-weight:700; font-family:'IBM Plex Mono',monospace; padding:2px 8px; border-radius:4px; flex-shrink:0; }
    .skor-high { background:var(--green-lt); color:var(--green); }
    .skor-low  { background:var(--red-lt);   color:var(--red); }
    .skor-mid  { background:var(--amber-lt); color:var(--amber); }
    .skor-none { background:var(--wash2); color:var(--ink3); }
    .submit-time { font-size:10.5px; color:var(--ink3); flex-shrink:0; font-family:'IBM Plex Mono',monospace; }
    .empty-msg { text-align:center; padding:28px; color:var(--ink3); font-size:12.5px; }

    /* Empty state */
    .empty-state { text-align:center; padding:60px 20px; color:var(--ink3); background:var(--surface); border:1px solid var(--rule); border-radius:10px; }
    .empty-state svg { margin-bottom:12px; opacity:.3; }
    .empty-state p { font-size:13px; margin-bottom:16px; }

    /* Buttons */
    .btn-primary { display:inline-flex; align-items:center; gap:5px; padding:8px 14px; background:var(--blue); color:#fff; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer; text-decoration:none; transition:opacity .2s; }
    .btn-primary:hover { opacity:.88; color:#fff; }
    .btn-green { background:var(--green); }

    /* Session flash */
    .flash-alert { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:12.5px; font-weight:500; }
    .flash-success { background:var(--green-lt); color:var(--green); border:1px solid #bbf7d0; }
</style>
@endpush

@section('content')

{{-- Flash --}}
@if(session('success'))
<div class="flash-alert flash-success">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif

{{-- Page head --}}
<div class="page-head">
    <div>
        <h1>Materi & Tugas</h1>
        <p>Pantau tugas reguler & materi quiz triwulan wilayah <strong>{{ $wilayah->nama ?? '-' }}</strong></p>
    </div>
</div>

{{-- TAB UTAMA: Reguler vs Triwulan --}}
<div class="main-tab-bar">
    <a href="{{ route('koordinator.materi.index') }}" class="main-tab-btn">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/><rect x="9" y="3" width="6" height="4" rx="1"/>
        </svg>
        Materi & Tugas Reguler
    </a>
    <button class="main-tab-btn active" onclick="void(0)">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <rect x="3" y="4" width="18" height="18" rx="2"/>
            <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
            <line x1="3" y1="10" x2="21" y2="10"/>
        </svg>
        Quiz Triwulan
        <span style="font-size:10px;background:var(--blue-lt);color:var(--blue);padding:1px 6px;border-radius:3px">{{ count($materiList) }}</span>
    </button>
</div>

{{-- Periode selector --}}
<div class="periode-bar">
    <span class="periode-label">Periode:</span>
    <form method="GET" action="{{ route('koordinator.materi.triwulan') }}" id="periode-form">
        <select name="periode" class="periode-select" onchange="document.getElementById('periode-form').submit()">
            @foreach($periodeOptions as $val => $label)
                <option value="{{ $val }}" {{ $periodeAktif === $val ? 'selected' : '' }}>
                    {{ $label }}
                </option>
            @endforeach
        </select>
    </form>
    <span style="font-size:11.5px;color:var(--ink3);">— Pilih periode triwulan untuk melihat atau membuat materi</span>
    <div style="margin-left:auto">
        <a href="{{ route('koordinator.materi.triwulan.create') }}" class="btn-primary btn-green">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Buat Materi & Quiz
        </a>
    </div>
</div>

{{-- Info tentang periode aktif --}}
@php
    $periodeLabel = '';
    if (preg_match('/^(\d{4})-TW(\d)$/', $periodeAktif, $m)) {
        $periodeLabel = "Triwulan {$m[2]} Tahun {$m[1]}";
    }
    $totalMateri  = count($materiList);
    $totalPetugas = $petugasList->count();
@endphp

<div class="tw-alert tw-alert-info" style="margin-bottom:20px">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        Menampilkan <strong>{{ $periodeLabel }}</strong>. 
        Petugas dapat mengisi quiz pada saat <strong>toggle Survey Internal</strong> di Admin dihidupkan.
        Koordinator bisa membuat materi & quiz untuk periode ini kapan saja.
    </div>
</div>

{{-- Stats --}}
<div class="stat-strip">
    <div class="stat-box">
        <div class="stat-num num-blue">{{ $totalMateri }}</div>
        <div class="stat-lbl">Total Materi</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $totalPetugas }}</div>
        <div class="stat-lbl">Petugas Wilayah</div>
    </div>
    <div class="stat-box">
        @php $totalSudah = $materiList->sum('jmlSudah'); @endphp
        <div class="stat-num num-green">{{ $totalSudah }}</div>
        <div class="stat-lbl">Total Jawaban Masuk</div>
    </div>
    <div class="stat-box">
        @php $totalBelum = $materiList->sum('jmlBelum'); @endphp
        <div class="stat-num num-red">{{ $totalBelum }}</div>
        <div class="stat-lbl">Total Belum Mengisi</div>
    </div>
</div>

{{-- Daftar materi --}}
@if($materiList->isEmpty())
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    <p>Belum ada materi untuk <strong>{{ $periodeLabel }}</strong></p>
    <a href="{{ route('koordinator.materi.triwulan.create') }}" class="btn-primary btn-green">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Buat Materi & Quiz Sekarang
    </a>
</div>
@else

@foreach($materiList as $i => $materi)
@php
    $fillColor = $materi->progres >= 80 ? 'var(--green)' : ($materi->progres >= 40 ? 'var(--amber)' : 'var(--red)');
    $openClass = $i === 0 ? 'open' : '';
@endphp
<div class="tw-card {{ $openClass }}" id="twc-{{ $materi->id }}">
    {{-- Head --}}
    <div class="tw-card-head" onclick="toggleTwCard({{ $materi->id }})">
        <div class="tw-card-icon">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
        </div>
        <div class="tw-card-info">
            <div class="tw-card-title">{{ $materi->judul }}</div>
            <div class="tw-card-sub">
                {{ $materi->quiz->count() }} soal &nbsp;·&nbsp;
                {{ $periodeLabel }} &nbsp;·&nbsp;
                Dibuat {{ $materi->created_at->format('d M Y') }}
            </div>
        </div>

        <div class="chip chip-green">✔ {{ $materi->jmlSudah }} sudah</div>
        <div class="chip chip-red">✖ {{ $materi->jmlBelum }} belum</div>

        <div class="tw-prog">
            <div class="prog-track">
                <div class="prog-fill" style="width:{{ $materi->progres }}%; background:{{ $fillColor }}"></div>
            </div>
            <span class="prog-lbl" style="color:{{ $fillColor }}">{{ $materi->progres }}%</span>
        </div>

        {{-- Edit & Hapus --}}
        <a href="{{ route('koordinator.materi.triwulan.edit', $materi->id) }}" class="btn-del" style="color:var(--blue);text-decoration:none;" onclick="event.stopPropagation()">Edit</a>
        <form method="POST" action="{{ route('koordinator.materi.triwulan.destroy', $materi->id) }}"
              onsubmit="return confirm('Hapus materi ini beserta semua jawaban?')" style="flex-shrink:0" onclick="event.stopPropagation()">
            @csrf @method('DELETE')
            <button type="submit" class="btn-del">Hapus</button>
        </form>

        <svg class="chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <polyline points="6 9 12 15 18 9"/>
        </svg>
    </div>

    {{-- Body --}}
    <div class="tw-card-body">
        {{-- Inner tab --}}
        <div class="inner-tab-bar">
            <button class="inner-tab-btn active" onclick="switchInner({{ $materi->id }}, 'semua', this)">Semua ({{ $totalPetugas }})</button>
            <button class="inner-tab-btn" onclick="switchInner({{ $materi->id }}, 'sudah', this)">✔ Sudah ({{ $materi->jmlSudah }})</button>
            <button class="inner-tab-btn" onclick="switchInner({{ $materi->id }}, 'belum', this)">✖ Belum ({{ $materi->jmlBelum }})</button>
        </div>

        <div class="p-section">

            {{-- Tab: semua --}}
            <div data-inner="{{ $materi->id }}-semua">
                @forelse($materi->petugasList as $p)
                @php
                    $jaw   = $materi->jawabanMap->get($p->id);
                    $sudah = $jaw && $jaw->status === 'sudah';
                    $skor  = $jaw?->skor;
                    $skorClass = $skor === null ? 'skor-none' : ($skor >= 80 ? 'skor-high' : ($skor >= 50 ? 'skor-mid' : 'skor-low'));
                @endphp
                <div class="p-item">
                    <div class="p-ava {{ $sudah ? 'ava-g' : 'ava-r' }}">{{ strtoupper(substr($p->user->name ?? '?', 0, 2)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="p-name">{{ $p->user->name ?? '—' }}</div>
                        <div class="p-meta">Shift {{ $p->shift ?? '—' }}</div>
                    </div>
                    @if($sudah)
                        <span class="chip chip-green" style="font-size:10px">✔ Sudah</span>
                    @else
                        <span class="chip chip-red" style="font-size:10px">✖ Belum</span>
                    @endif
                    <span class="skor-badge {{ $skorClass }}">{{ $skor !== null ? $skor.'/100' : '—' }}</span>
                    @if($jaw?->dikerjakan_at)
                        <span class="submit-time">{{ $jaw->dikerjakan_at->format('d M, H:i') }}</span>
                    @endif
                </div>
                @empty
                <div class="empty-msg">Belum ada petugas di wilayah ini.</div>
                @endforelse
            </div>

            {{-- Tab: sudah --}}
            <div data-inner="{{ $materi->id }}-sudah" style="display:none">
                @php $sudahList = $materi->petugasList->filter(fn($p) => ($materi->jawabanMap->get($p->id)?->status ?? '') === 'sudah'); @endphp
                @forelse($sudahList as $p)
                @php
                    $jaw  = $materi->jawabanMap->get($p->id);
                    $skor = $jaw?->skor;
                    $skorClass = $skor === null ? 'skor-none' : ($skor >= 80 ? 'skor-high' : ($skor >= 50 ? 'skor-mid' : 'skor-low'));
                @endphp
                <div class="p-item">
                    <div class="p-ava ava-g">{{ strtoupper(substr($p->user->name ?? '?', 0, 2)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="p-name">{{ $p->user->name ?? '—' }}</div>
                        <div class="p-meta">Shift {{ $p->shift ?? '—' }}</div>
                    </div>
                    <span class="chip chip-green" style="font-size:10px">✔ Selesai</span>
                    <span class="skor-badge {{ $skorClass }}">{{ $skor !== null ? $skor.'/100' : '—' }}</span>
                    @if($jaw?->dikerjakan_at)
                        <span class="submit-time">{{ $jaw->dikerjakan_at->format('d M, H:i') }}</span>
                    @endif
                </div>
                @empty
                <div class="empty-msg">Belum ada yang mengerjakan.</div>
                @endforelse
            </div>

            {{-- Tab: belum --}}
            <div data-inner="{{ $materi->id }}-belum" style="display:none">
                @php $belumList = $materi->petugasList->filter(fn($p) => ($materi->jawabanMap->get($p->id)?->status ?? 'belum') !== 'sudah'); @endphp
                @forelse($belumList as $p)
                <div class="p-item">
                    <div class="p-ava ava-r">{{ strtoupper(substr($p->user->name ?? '?', 0, 2)) }}</div>
                    <div style="flex:1;min-width:0">
                        <div class="p-name">{{ $p->user->name ?? '—' }}</div>
                        <div class="p-meta">Shift {{ $p->shift ?? '—' }}</div>
                    </div>
                    <span class="chip chip-red" style="font-size:10px">✖ Belum Mengisi</span>
                    <span class="skor-badge skor-none">—</span>
                </div>
                @empty
                <div class="empty-msg">🎉 Semua petugas sudah mengerjakan!</div>
                @endforelse
            </div>

        </div>
    </div>
</div>
@endforeach
@endif

@endsection

@push('scripts')
<script>
function toggleTwCard(id) {
    document.getElementById('twc-' + id).classList.toggle('open');
}
function switchInner(matId, tab, btn) {
    document.querySelectorAll('[data-inner^="' + matId + '-"]').forEach(el => el.style.display = 'none');
    document.querySelector('[data-inner="' + matId + '-' + tab + '"]').style.display = 'block';
    btn.closest('.inner-tab-bar').querySelectorAll('.inner-tab-btn').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
}
</script>
@endpush