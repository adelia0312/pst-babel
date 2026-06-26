@extends('layouts.petugas')
@section('title', 'Detail Laporan Harian')

@push('styles')
<style>
.page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; gap:12px; flex-wrap:wrap; }
.page-head h1 { font-size:18px; font-weight:600; color:var(--ink); }
.page-head p  { font-size:12px; color:var(--ink3); margin-top:3px; }
.btn-back { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--ink3); text-decoration:none; padding:6px 12px; border:1px solid var(--rule); border-radius:6px; background:var(--surface); }
.btn-back:hover { background:var(--wash); color:var(--ink); }

/* Status Banner */
.status-banner { display:flex; align-items:center; gap:12px; padding:16px 20px; border-radius:10px; margin-bottom:20px; }
.status-banner.submitted { background:#fff8e6; border:1.5px solid #fde68a; color:#92400e; }
.status-banner.approved  { background:var(--green-lt); border:1.5px solid #86efac; color:#166534; }
.status-banner.rejected  { background:var(--red-lt); border:1.5px solid #fca5a5; color:#991b1b; }
.status-banner.draft     { background:var(--wash); border:1.5px solid var(--rule); color:var(--ink3); }
.status-icon { width:40px; height:40px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; }
.submitted .status-icon { background:#fef3c7; }
.approved  .status-icon { background:#dcfce7; }
.rejected  .status-icon { background:#fee2e2; }
.draft     .status-icon { background:var(--wash2); }
.status-text-main { font-size:14px; font-weight:700; }
.status-text-sub  { font-size:12px; margin-top:2px; opacity:.8; }

/* Info Grid */
.info-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:var(--rule); border:1px solid var(--rule); border-radius:10px; overflow:hidden; margin-bottom:20px; }
.info-cell { background:var(--surface); padding:14px 16px; }
.info-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--ink3); margin-bottom:5px; }
.info-value { font-size:13.5px; font-weight:600; color:var(--ink); }

/* Jawaban */
.jawaban-card { background:var(--surface); border:1px solid var(--rule); border-radius:10px; overflow:hidden; margin-bottom:20px; }
.jawaban-header { padding:14px 20px; border-bottom:1px solid var(--rule); background:var(--wash); }
.jawaban-header h2 { font-size:13px; font-weight:600; color:var(--ink); }
.jawaban-item { padding:16px 20px; border-bottom:1px solid var(--rule); display:flex; gap:16px; align-items:flex-start; }
.jawaban-item:last-child { border-bottom:none; }
.q-num { width:24px; height:24px; background:var(--blue); color:#fff; border-radius:50%; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:1px; }
.q-body { flex:1; }
.q-label { font-size:12px; font-weight:600; color:var(--ink2); margin-bottom:6px; display:flex; align-items:center; gap:6px; }
.q-wajib { font-size:9px; padding:1px 6px; border-radius:2px; background:var(--red-lt); color:var(--red); font-weight:600; }
.q-answer { font-size:13px; color:var(--ink); background:var(--wash); border:1px solid var(--rule); border-radius:6px; padding:10px 14px; line-height:1.5; }
.q-answer.empty { color:var(--ink3); font-style:italic; }

/* Catatan koordinator */
.catatan-box { border-radius:10px; padding:16px 20px; margin-bottom:20px; }
.catatan-box.rejected { background:var(--red-lt); border:1.5px solid #fca5a5; }
.catatan-box.approved { background:var(--green-lt); border:1.5px solid #86efac; }
.catatan-title { font-size:11.5px; font-weight:700; text-transform:uppercase; letter-spacing:.5px; margin-bottom:8px; }
.catatan-text { font-size:13px; line-height:1.6; }
</style>
@endpush

@section('content')
<div class="page-head">
    <div>
        <h1>Detail Laporan Harian</h1>
        <p>{{ $laporan->tanggal->translatedFormat('l, d F Y') }} · Sesi {{ $laporan->sesi }}</p>
    </div>
    <a href="{{ route('petugas.laporan.harian.index') }}" class="btn-back">← Kembali</a>
</div>

{{-- Status Banner --}}
@php
$statusConf = [
    'draft'     => ['class'=>'draft',     'icon'=>'📝', 'main'=>'Draft',               'sub'=>'Laporan belum dikirim ke koordinator.'],
    'submitted' => ['class'=>'submitted', 'icon'=>'⏳', 'main'=>'Menunggu Konfirmasi', 'sub'=>'Laporan sudah dikirim dan sedang direview koordinator. Data tidak dapat diubah.'],
    'approved'  => ['class'=>'approved',  'icon'=>'✅', 'main'=>'Disetujui',            'sub'=>'Laporan telah diverifikasi dan disetujui oleh koordinator.'],
    'rejected'  => ['class'=>'rejected',  'icon'=>'❌', 'main'=>'Dikembalikan',         'sub'=>'Laporan dikembalikan. Silakan perbaiki dan kirim ulang.'],
];
$sc = $statusConf[$laporan->status] ?? $statusConf['draft'];
@endphp

<div class="status-banner {{ $sc['class'] }}">
    <div class="status-icon">
        <span style="font-size:20px">{{ $sc['icon'] }}</span>
    </div>
    <div>
        <div class="status-text-main">{{ $sc['main'] }}</div>
        <div class="status-text-sub">{{ $sc['sub'] }}</div>
    </div>
    @if($laporan->reviewed_at)
    <div style="margin-left:auto;text-align:right;font-size:11px;opacity:.7">
        Direview<br>{{ $laporan->reviewed_at->format('d M Y, H:i') }} WIB
    </div>
    @endif
</div>

{{-- Catatan koordinator jika ada --}}
@if($laporan->catatan_koordinator)
<div class="catatan-box {{ $laporan->status }}">
    <div class="catatan-title" style="color:{{ $laporan->status === 'rejected' ? 'var(--red)' : 'var(--green)' }}">
        💬 Catatan Koordinator
    </div>
    <div class="catatan-text">{{ $laporan->catatan_koordinator }}</div>
</div>
@endif

{{-- Info Grid --}}
<div class="info-grid">
    <div class="info-cell">
        <div class="info-label">Tanggal</div>
        <div class="info-value">{{ $laporan->tanggal->format('d/m/Y') }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Hari</div>
        <div class="info-value">{{ $laporan->hari }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Sesi</div>
        <div class="info-value">{{ $laporan->sesi }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Dikirim</div>
        <div class="info-value" style="font-size:12px">{{ $laporan->created_at->format('d M, H:i') }}</div>
    </div>
</div>

{{-- Jawaban --}}
<div class="jawaban-card">
    <div class="jawaban-header">
        <h2>Isi Laporan</h2>
    </div>
    @forelse($templates as $i => $tpl)
    <div class="jawaban-item">
        <div class="q-num">{{ $i + 1 }}</div>
        <div class="q-body">
            <div class="q-label">
                {{ $tpl->judul }}
                @if($tpl->wajib)<span class="q-wajib">WAJIB</span>@endif
            </div>
            @php $jawaban = $laporan->jawaban[$tpl->id] ?? ''; @endphp
            <div class="q-answer {{ empty($jawaban) ? 'empty' : '' }}">
                {{ empty($jawaban) ? '— Tidak diisi —' : $jawaban }}
            </div>
        </div>
    </div>
    @empty
    <div style="padding:24px;text-align:center;color:var(--ink3);font-size:12.5px">Tidak ada pertanyaan.</div>
    @endforelse
</div>

@if($laporan->status === 'draft' || $laporan->status === 'rejected')
<div style="display:flex;gap:10px">
    <a href="{{ route('petugas.laporan.harian.edit', $laporan->id) }}"
       style="display:inline-flex;align-items:center;gap:6px;font-size:12.5px;font-weight:500;padding:9px 20px;border-radius:6px;background:var(--blue);color:#fff;text-decoration:none">
        ✏️ Edit & Kirim Laporan
    </a>
</div>
@endif

@endsection