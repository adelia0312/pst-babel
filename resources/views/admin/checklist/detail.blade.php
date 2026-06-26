@extends('layouts.admin')

@section('title', 'Detail Checklist')

@section('breadcrumb')
    <a href="{{ route('admin.checklist.index') }}" style="color:var(--ink3);text-decoration:none">Checklist Harian</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Detail</strong>
@endsection

@push('styles')
<style>
.detail-grid { display:grid; grid-template-columns:1fr 280px; gap:18px; align-items:start; }
.panel { background:var(--surface); border:1px solid var(--rule); border-radius:8px; overflow:hidden; }
.ph { display:flex; align-items:center; justify-content:space-between; padding:13px 18px; border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px; font-weight:600; }
.ph-sub   { font-size:11px; color:var(--ink3); margin-top:1px; }

/* Checklist items */
.cl-items { padding:8px 0; }
.cl-item {
    display:flex; align-items:flex-start; gap:12px;
    padding:10px 18px; border-bottom:1px solid var(--rule);
    transition: background .1s;
}
.cl-item:last-child { border-bottom:none; }
.cl-item:hover { background:var(--wash); }
.cl-num {
    font-size:10px; font-family:'IBM Plex Mono',monospace;
    color:var(--ink3); width:18px; flex-shrink:0; padding-top:2px;
}
.cl-check {
    width:16px; height:16px; border-radius:3px; flex-shrink:0; margin-top:1px;
    display:flex; align-items:center; justify-content:center;
}
.cl-check.done { background:var(--green); }
.cl-check.skip { background:var(--wash2); border:1.5px solid var(--rule); }
.cl-check.done svg { color:#fff; }
.cl-label { font-size:12.5px; line-height:1.5; }
.cl-label.skip-txt { color:var(--ink3); text-decoration:line-through; }
.cl-item.orphan { opacity:.65; }
.orphan-badge { font-size:9px; font-weight:600; padding:1px 5px; border-radius:3px; background:#fef3c7; color:#92400e; margin-left:6px; vertical-align:middle; }

/* Sidebar info */
.info-row { padding:12px 18px; border-bottom:1px solid var(--rule); display:flex; justify-content:space-between; align-items:center; }
.info-row:last-child { border-bottom:none; }
.info-key { font-size:11px; color:var(--ink3); }
.info-val { font-size:12.5px; font-weight:500; color:var(--ink); }

.pill { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
.p-draft    { background:var(--wash2); color:var(--ink3); }
.p-submit   { background:var(--amber-lt); color:var(--amber); }
.p-verified { background:var(--green-lt); color:var(--green); }

/* Progress ring area */
.score-box { padding:20px 18px; text-align:center; }
.score-num { font-size:40px; font-weight:300; font-family:'IBM Plex Mono',monospace; letter-spacing:-2px; color:var(--ink); line-height:1; }
.score-den { font-size:14px; color:var(--ink3); }
.score-lbl { font-size:11px; color:var(--ink3); margin-top:6px; }
.score-bar { height:6px; background:var(--wash2); border-radius:3px; margin:12px 0 0; }
.score-fill { height:100%; border-radius:3px; transition:width .6s; }

/* Back btn */
.back-btn {
    display:inline-flex; align-items:center; gap:6px;
    font-size:12px; color:var(--ink3); text-decoration:none;
    padding:5px 0; margin-bottom:16px; transition:color .12s;
}
.back-btn:hover { color:var(--ink); }

/* Catatan box */
.note-box {
    background:var(--wash); border-radius:6px; padding:12px 14px;
    font-size:12.5px; color:var(--ink2); line-height:1.6;
    margin:12px 18px;
}
.note-empty { color:var(--ink3); font-style:italic; }

/* Action bar */
.action-bar {
    display:flex; gap:8px; padding:14px 18px;
    border-top:1px solid var(--rule); background:var(--wash);
}
.btn-verify {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 16px; background:var(--green); color:#fff;
    border:none; border-radius:5px; font-size:12.5px; font-weight:500;
    cursor:pointer; transition:opacity .15s; font-family:'IBM Plex Sans',sans-serif;
}
.btn-verify:hover { opacity:.88; }
.btn-back {
    display:inline-flex; align-items:center; gap:6px;
    padding:7px 14px; background:var(--surface); color:var(--ink2);
    border:1px solid var(--rule); border-radius:5px; font-size:12.5px;
    font-weight:500; text-decoration:none; transition:border-color .12s;
}
.btn-back:hover { border-color:var(--ink2); color:var(--ink); }
</style>
@endpush

@section('content')

<a href="{{ route('admin.checklist.index') }}" class="back-btn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke daftar checklist
</a>

@if(session('success'))
<div style="display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:7px;margin-bottom:16px;font-size:12.5px;font-weight:500;background:var(--green-lt);color:var(--green);border:1px solid #0a7c4e22;">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg>
    {{ session('success') }}
</div>
@endif

@php $pct = $checklist->pctChecked(); @endphp

<div class="detail-grid">

    {{-- Left: checklist items --}}
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Item Checklist SOP</div>
                <div class="ph-sub">{{ $checklist->totalChecked() }} dari {{ count($itemsJson) }} item terpenuhi</div>
            </div>
            <span class="pill {{ $checklist->status === 'verified' ? 'p-verified' : ($checklist->status === 'submit' ? 'p-submit' : 'p-draft') }}">
                {{ $checklist->status === 'verified' ? '✓ Verified' : ($checklist->status === 'submit' ? 'Menunggu Verifikasi' : 'Draft') }}
            </span>
        </div>

        <div class="cl-items">
            @php $num = 1; @endphp
            @foreach($itemsJson as $tplId => $checked)
            @php
                $label    = $labelsMap[$tplId] ?? null;
                $isOrphan = $label === null;
                $labelTxt = $label ?? '[item dihapus]';
            @endphp
            <div class="cl-item{{ $isOrphan ? ' orphan' : '' }}">
                <span class="cl-num">{{ sprintf('%02d', $num++) }}</span>
                <span class="cl-check {{ $checked ? 'done' : 'skip' }}">
                    @if($checked)
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </span>
                <span class="cl-label {{ !$checked ? 'skip-txt' : '' }}">
                    {{ $labelTxt }}
                    @if($isOrphan)<span class="orphan-badge">dihapus</span>@endif
                </span>
            </div>
            @endforeach
        </div>

        {{-- Catatan --}}
        <div style="padding:12px 18px 4px;font-size:10.5px;font-weight:600;letter-spacing:.8px;text-transform:uppercase;color:var(--ink3);">Catatan Petugas</div>
        <div class="note-box">
            @if($checklist->catatan)
                {{ $checklist->catatan }}
            @else
                <span class="note-empty">Tidak ada catatan.</span>
            @endif
        </div>

        @if($checklist->status === 'submit')
        <div class="action-bar">
            <form method="POST" action="{{ route('admin.checklist.verify', $checklist->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-verify" onclick="return confirm('Verifikasi checklist ini?')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Verifikasi Checklist
                </button>
            </form>
            <a href="{{ route('admin.checklist.index') }}" class="btn-back">Batal</a>
        </div>
        @endif
    </div>

    {{-- Right: info sidebar --}}
    <div style="display:flex;flex-direction:column;gap:14px;">

        {{-- Score card --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Skor Kelengkapan</div></div>
            <div class="score-box">
                <div class="score-num">{{ $checklist->totalChecked() }}<span class="score-den">/{{ count($itemsJson) }}</span></div>
                <div class="score-lbl">item diselesaikan</div>
                <div class="score-bar">
                    <div class="score-fill" style="width:{{ $pct }}%;background:{{ $pct >= 80 ? 'var(--green)' : ($pct >= 50 ? 'var(--amber)' : 'var(--red)') }}"></div>
                </div>
                <div style="margin-top:8px;font-size:20px;font-weight:300;font-family:'IBM Plex Mono',monospace;color:{{ $pct >= 80 ? 'var(--green)' : ($pct >= 50 ? 'var(--amber)' : 'var(--red)') }}">{{ $pct }}%</div>
            </div>
        </div>

        {{-- Info rows --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Informasi</div></div>
            <div class="info-row">
                <span class="info-key">Petugas</span>
                <span class="info-val">{{ $checklist->user->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Tanggal</span>
                <span class="info-val" style="font-family:'IBM Plex Mono',monospace;font-size:12px">
                    {{ $checklist->tanggal->format('d/m/Y') }}
                </span>
            </div>
            <div class="info-row">
                <span class="info-key">Sesi</span>
                <span class="info-val">{{ ucfirst($checklist->sesi) }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Status</span>
                <span class="info-val">
                    <span class="pill {{ $checklist->status === 'verified' ? 'p-verified' : ($checklist->status === 'submit' ? 'p-submit' : 'p-draft') }}">
                        {{ $checklist->status }}
                    </span>
                </span>
            </div>
            <div class="info-row">
                <span class="info-key">Diperbarui</span>
                <span class="info-val" style="font-size:11.5px">{{ $checklist->updated_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($checklist->status === 'verified')
            <div class="info-row">
                <span class="info-key">Diverifikasi oleh</span>
                <span class="info-val">{{ $checklist->verifier->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-key">Waktu verifikasi</span>
                <span class="info-val" style="font-size:11.5px">{{ $checklist->verified_at?->format('d/m/Y H:i') }}</span>
            </div>
            @endif
        </div>

    </div>
</div>

@endsection