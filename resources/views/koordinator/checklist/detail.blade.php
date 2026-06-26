@extends('layouts.koordinator')

@section('title', 'Detail Checklist')

@section('breadcrumb')
    <a href="{{ route('koordinator.checklist.index') }}" style="color:var(--ink3);text-decoration:none">Checklist Harian</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Detail — {{ $checklist->user->name ?? '—' }}</strong>
@endsection

@push('styles')
<style>
.detail-grid { display:grid; grid-template-columns:1fr 290px; gap:18px; align-items:start; }
@media(max-width:720px){ .detail-grid { grid-template-columns:1fr; } }

.panel { background:var(--surface); border:1px solid var(--rule); border-radius:8px; overflow:hidden; }
.ph { display:flex; align-items:center; justify-content:space-between; padding:13px 18px; border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px; font-weight:600; }
.ph-sub   { font-size:11px; color:var(--ink3); margin-top:2px; }

/* Item list */
.cl-items { padding:6px 0; }
.cl-item { display:flex; align-items:flex-start; gap:12px; padding:10px 18px; border-bottom:1px solid var(--rule); transition:background .1s; }
.cl-item:last-child { border-bottom:none; }
.cl-item:hover { background:var(--wash); }
.cl-item.orphan { opacity:.65; }
.cl-num { font-size:10px; font-family:'IBM Plex Mono',monospace; color:var(--ink3); width:22px; flex-shrink:0; padding-top:2px; text-align:right; }
.cl-check { width:17px; height:17px; border-radius:4px; flex-shrink:0; margin-top:1px; display:flex; align-items:center; justify-content:center; }
.cl-check.done { background:var(--green); }
.cl-check.skip { background:var(--wash2); border:1.5px solid var(--rule); }
.cl-label { font-size:12.5px; line-height:1.5; flex:1; }
.cl-label.skip-txt { color:var(--ink3); }
.cl-link { font-size:10.5px; color:var(--blue); display:block; margin-top:2px; word-break:break-all; }
.orphan-badge { font-size:9px; font-weight:600; padding:1px 5px; border-radius:3px; background:#fef3c7; color:#92400e; margin-left:6px; vertical-align:middle; }

/* Divider section */
.section-divider { display:flex; align-items:center; gap:10px; padding:8px 18px; background:var(--wash); border-top:1px solid var(--rule); border-bottom:1px solid var(--rule); }
.section-divider span { font-size:10.5px; font-weight:600; color:var(--ink3); letter-spacing:.4px; text-transform:uppercase; }

/* Sidebar panels */
.info-row { padding:10px 18px; border-bottom:1px solid var(--rule); display:flex; justify-content:space-between; align-items:center; font-size:12px; }
.info-row:last-child { border-bottom:none; }
.info-label { color:var(--ink3); }

.pill { display:inline-block; font-size:10px; font-weight:600; padding:2px 8px; border-radius:20px; }
.p-draft    { background:var(--wash2); color:var(--ink3); }
.p-submit   { background:var(--amber-lt); color:var(--amber); }
.p-verified { background:var(--green-lt); color:var(--green); }

.sesi-chip { font-size:10px; font-weight:600; padding:2px 7px; border-radius:3px; text-transform:uppercase; letter-spacing:.4px; }
.s-pagi  { background:#fff8e1; color:#b45309; }
.s-siang { background:#e8f4fd; color:#1a56db; }
.s-malam { background:#ede9fe; color:#7c3aed; }

/* Score box */
.score-box { padding:20px 18px; text-align:center; }
.score-num { font-size:40px; font-weight:300; font-family:'IBM Plex Mono',monospace; letter-spacing:-2px; line-height:1; }
.score-sub { font-size:11px; color:var(--ink3); margin-top:4px; }
.score-bar { height:6px; background:var(--wash2); border-radius:3px; margin:14px 0 8px; }
.score-fill { height:100%; border-radius:3px; transition:width .5s; }
.score-pct { font-size:20px; font-weight:300; font-family:'IBM Plex Mono',monospace; }

/* Note */
.note-box { background:var(--wash); border-radius:6px; padding:12px 14px; margin:10px 18px 14px; font-size:12.5px; line-height:1.6; color:var(--ink); }

/* Actions */
.act-footer { display:flex; gap:8px; padding:14px 18px; border-top:1px solid var(--rule); background:var(--wash); }
.btn-verify { height:34px; padding:0 16px; background:var(--green); color:#fff; border:none; border-radius:5px; cursor:pointer; display:inline-flex; align-items:center; gap:6px; font-size:12.5px; font-weight:600; font-family:'IBM Plex Sans',sans-serif; }
.btn-verify:hover { opacity:.88; }
.back-btn { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--ink3); text-decoration:none; margin-bottom:16px; transition:color .12s; }
.back-btn:hover { color:var(--ink); }

.flash { display:flex; align-items:center; gap:8px; padding:10px 16px; border-radius:7px; margin-bottom:16px; font-size:12.5px; font-weight:500; }
.flash-ok  { background:var(--green-lt); color:var(--green); border:1px solid #0a7c4e22; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif

<a href="{{ route('koordinator.checklist.index') }}" class="back-btn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke daftar checklist
</a>

@php
    $itemsJson    = $checklist->items ?? [];
    $totalActive  = $templates->count();
    $totalChecked = $checklist->totalChecked();

    // Hitung total semua item (aktif + orphan yang terceklis di data lama)
    $totalAll     = $totalActive + count($orphanIds);
    $pct          = $totalActive > 0 ? round(($totalChecked / $totalActive) * 100) : 0;
    $pctColor     = $pct >= 80 ? 'var(--green)' : ($pct >= 50 ? 'var(--amber)' : 'var(--red)');

    $statusLabel  = match($checklist->status) {
        'verified' => '✓ Verified',
        'submit'   => 'Menunggu Verifikasi',
        default    => 'Draft',
    };
    $statusClass  = match($checklist->status) {
        'verified' => 'p-verified',
        'submit'   => 'p-submit',
        default    => 'p-draft',
    };
@endphp

<div class="detail-grid">

    {{-- ── KIRI: Item Checklist ── --}}
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Item Checklist SOP</div>
                <div class="ph-sub">{{ $totalChecked }} dari {{ $totalActive }} item terpenuhi (template aktif)</div>
            </div>
            <span class="pill {{ $statusClass }}">{{ $statusLabel }}</span>
        </div>

        <div class="cl-items">
            {{-- Item dari template AKTIF saat ini --}}
            @forelse($templates as $idx => $tpl)
            @php
                $key       = (string) $tpl->id;
                $isChecked = isset($itemsJson[$key]) && $itemsJson[$key];
            @endphp
            <div class="cl-item">
                <span class="cl-num">{{ sprintf('%02d', $idx + 1) }}</span>
                <span class="cl-check {{ $isChecked ? 'done' : 'skip' }}">
                    @if($isChecked)
                        <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </span>
                <span class="cl-label {{ !$isChecked ? 'skip-txt' : '' }}">
                    {{ $tpl->label }}
                    @if($tpl->link)
                        <a class="cl-link" href="{{ $tpl->link }}" target="_blank">{{ $tpl->link }}</a>
                    @endif
                </span>
            </div>
            @empty
            <div style="padding:20px 18px;text-align:center;color:var(--ink3);font-size:12px">
                Belum ada template checklist aktif.
            </div>
            @endforelse

            {{-- Item HISTORIS: template sudah dihapus/dinonaktifkan tapi ada di data lama --}}
            @if(count($orphanIds) > 0)
            <div class="section-divider">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>Item dari template lama (sudah diperbarui koordinator)</span>
            </div>
            @foreach($orphanIds as $oIdx => $orphanId)
            @php
                $wasChecked  = isset($itemsJson[$orphanId]) && $itemsJson[$orphanId];
                $orphanLabel = $labelsMap[$orphanId] ?? null;
            @endphp
            <div class="cl-item orphan">
                <span class="cl-num">—</span>
                <span class="cl-check {{ $wasChecked ? 'done' : 'skip' }}">
                    @if($wasChecked)
                        <svg width="10" height="10" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    @endif
                </span>
                <span class="cl-label {{ !$wasChecked ? 'skip-txt' : '' }}">
                    @if($orphanLabel)
                        {{ $orphanLabel }}
                    @else
                        <em style="color:var(--ink3)">(item #{{ $orphanId }})</em>
                    @endif
                    <span class="orphan-badge">Dihapus</span>
                </span>
            </div>
            @endforeach
            @endif
        </div>

        {{-- Catatan Petugas --}}
        <div style="padding:10px 18px 2px;font-size:10.5px;font-weight:600;color:var(--ink3);border-top:1px solid var(--rule)">
            Catatan Petugas
        </div>
        <div class="note-box">
            @if($checklist->catatan)
                {{ $checklist->catatan }}
            @else
                <span style="color:var(--ink3);font-style:italic">Tidak ada catatan.</span>
            @endif
        </div>

        {{-- Tombol verifikasi --}}
        @if($checklist->status === 'submit')
        <div class="act-footer">
            <form method="POST" action="{{ route('koordinator.checklist.verify', $checklist->id) }}">
                @csrf @method('PATCH')
                <button type="submit" class="btn-verify" onclick="return confirm('Verifikasi checklist ini?')">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                    Verifikasi Checklist
                </button>
            </form>
        </div>
        @elseif($checklist->status === 'verified')
        <div style="padding:12px 18px;background:var(--green-lt);border-top:1px solid #0a7c4e22;display:flex;align-items:center;gap:8px;font-size:12px;color:var(--green);font-weight:500">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
            Sudah diverifikasi oleh {{ $checklist->verifier->name ?? '—' }}
            pada {{ $checklist->verified_at?->format('d/m/Y H:i') ?? '—' }}
        </div>
        @endif
    </div>

    {{-- ── KANAN: Sidebar info ── --}}
    <div style="display:flex;flex-direction:column;gap:14px">

        {{-- Skor --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Skor Kelengkapan</div></div>
            <div class="score-box">
                <div class="score-num">{{ $totalChecked }}<span style="font-size:14px;color:var(--ink3)">/{{ $totalActive }}</span></div>
                <div class="score-sub">item terpenuhi</div>
                <div class="score-bar">
                    <div class="score-fill" style="width:{{ $pct }}%;background:{{ $pctColor }}"></div>
                </div>
                <div class="score-pct" style="color:{{ $pctColor }}">{{ $pct }}%</div>
            </div>
        </div>

        {{-- Informasi --}}
        <div class="panel">
            <div class="ph"><div class="ph-title">Informasi</div></div>
            <div class="info-row">
                <span class="info-label">Petugas</span>
                <span style="font-weight:500">{{ $checklist->user->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Tanggal</span>
                <span>{{ $checklist->tanggal->translatedFormat('d M Y') }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Sesi</span>
                <span class="sesi-chip s-{{ $checklist->sesi }}">{{ ucfirst($checklist->sesi) }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Status</span>
                <span class="pill {{ $statusClass }}">{{ $statusLabel }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Diperbarui</span>
                <span style="font-size:11px;font-family:'IBM Plex Mono',monospace">{{ $checklist->updated_at->format('d/m/Y H:i') }}</span>
            </div>
            @if($checklist->status === 'verified')
            <div class="info-row">
                <span class="info-label">Diverifikasi oleh</span>
                <span style="font-weight:500">{{ $checklist->verifier->name ?? '—' }}</span>
            </div>
            <div class="info-row">
                <span class="info-label">Waktu verifikasi</span>
                <span style="font-size:11px;font-family:'IBM Plex Mono',monospace">{{ $checklist->verified_at?->format('d/m/Y H:i') ?? '—' }}</span>
            </div>
            @endif
        </div>

        {{-- Info template --}}
        @if(count($orphanIds) > 0)
        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Info Template</div>
                    <div class="ph-sub">Perubahan sejak checklist diisi</div>
                </div>
            </div>
            <div style="padding:12px 18px;font-size:12px;color:var(--ink3);line-height:1.6">
                Template checklist telah diperbarui oleh koordinator setelah petugas mengisi data ini.
                <strong style="color:var(--ink)">{{ count($orphanIds) }} item lama</strong> ditampilkan sebagai historis di bawah daftar item aktif.
            </div>
        </div>
        @endif

    </div>
</div>

@endsection