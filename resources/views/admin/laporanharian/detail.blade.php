@extends('layouts.admin')
@section('title', 'Detail Laporan Harian')

@section('content')

@push('styles')
<style>
    .page-head { display:flex; align-items:flex-end; justify-content:space-between; margin-bottom:22px; padding-bottom:20px; border-bottom:1px solid var(--rule); }
    .page-head h1 { font-size:19px; font-weight:600; letter-spacing:-.3px; }
    .page-head p  { font-size:12px; color:var(--ink3); margin-top:3px; }

    .meta-bar { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:var(--rule); border:1px solid var(--rule); border-radius:8px; overflow:hidden; margin-bottom:20px; }
    .meta-cell { background:var(--surface); padding:14px 18px; }
    .meta-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--ink3); margin-bottom:4px; }
    .meta-val   { font-size:14px; font-weight:600; color:var(--ink); }

    .qa-card { background:var(--surface); border:1px solid var(--rule); border-radius:8px; overflow:hidden; margin-bottom:16px; }
    .qa-row { display:grid; grid-template-columns:280px 1fr; border-bottom:1px solid var(--rule); }
    .qa-row:last-child { border-bottom:none; }
    .qa-q { padding:13px 18px; font-size:12px; font-weight:600; color:var(--ink2); background:var(--wash); border-right:1px solid var(--rule); }
    .qa-a { padding:13px 18px; font-size:13px; color:var(--ink); white-space:pre-wrap; }
    .qa-empty { color:var(--ink3); font-style:italic; }

    .pill { display:inline-block; font-size:10px; font-weight:500; padding:2px 8px; border-radius:3px; }
    .pill-submitted { background:var(--amber-lt); color:var(--amber); }
    .pill-approved  { background:var(--green-lt);  color:var(--green); }
    .pill-rejected  { background:var(--red-lt);    color:var(--red); }
    .pill-draft     { background:var(--wash2);     color:var(--ink3); }

    .btn-back { font-size:12px; font-weight:500; color:var(--ink3); text-decoration:none; padding:7px 14px; border:1px solid var(--rule); border-radius:6px; background:var(--surface); }
    .btn-back:hover { color:var(--ink); border-color:var(--ink3); }

    .catatan-box { background:var(--surface); border:1px solid var(--rule); border-radius:8px; padding:20px 22px; margin-top:20px; }
    .catatan-label { font-size:11px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--ink3); margin-bottom:8px; }
</style>
@endpush

<div class="page-head">
    <div>
        <h1>Detail Laporan Harian</h1>
        <p>{{ $laporan->nama_petugas }} — {{ $laporan->tanggal->format('d/m/Y') }} Sesi {{ $laporan->sesi }}</p>
    </div>
    <a href="{{ route('admin.laporanharian.index') }}" class="btn-back">← Kembali</a>
</div>

{{-- META --}}
<div class="meta-bar">
    <div class="meta-cell">
        <div class="meta-label">Tanggal</div>
        <div class="meta-val">{{ $laporan->tanggal->format('d/m/Y') }}</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Hari</div>
        <div class="meta-val">{{ $laporan->hari }}</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Sesi</div>
        <div class="meta-val">{{ $laporan->sesi }}</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Status</div>
        <div class="meta-val">
            @php
                $pillClass = match($laporan->status) {
                    'submitted' => 'pill-submitted',
                    'approved'  => 'pill-approved',
                    'rejected'  => 'pill-rejected',
                    default     => 'pill-draft',
                };
                $pillLabel = match($laporan->status) {
                    'submitted' => 'Menunggu Review',
                    'approved'  => 'Disetujui',
                    'rejected'  => 'Dikembalikan',
                    default     => 'Draft',
                };
            @endphp
            <span class="pill {{ $pillClass }}">{{ $pillLabel }}</span>
        </div>
    </div>
</div>

<div class="meta-bar" style="grid-template-columns:repeat(2,1fr); margin-bottom:20px">
    <div class="meta-cell">
        <div class="meta-label">Petugas</div>
        <div class="meta-val">{{ $laporan->nama_petugas }}</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Wilayah</div>
        <div class="meta-val">{{ $laporan->wilayah?->nama ?? '-' }}</div>
    </div>
</div>

{{-- JAWABAN --}}
<div class="qa-card">
    @forelse($templates as $tpl)
        <div class="qa-row">
            <div class="qa-q">
                {{ $tpl->judul }}
                @if($tpl->wajib)<span style="color:var(--red);margin-left:4px">*</span>@endif
            </div>
            <div class="qa-a">
                @php $jawaban = $laporan->jawabUntuk($tpl->id); @endphp
                @if($jawaban)
                    {{ $jawaban }}
                @else
                    <span class="qa-empty">— tidak diisi —</span>
                @endif
            </div>
        </div>
    @empty
        <div style="padding:32px;text-align:center;color:var(--ink3);font-size:13px">
            Belum ada template pertanyaan.
        </div>
    @endforelse
</div>

@if($laporan->catatan_koordinator)
<div class="catatan-box">
    <div class="catatan-label">Catatan Koordinator</div>
    <p style="font-size:13px;color:var(--ink);margin:0">{{ $laporan->catatan_koordinator }}</p>
    @if($laporan->reviewed_at)
        <p style="font-size:11px;color:var(--ink3);margin-top:8px">
            Direview oleh {{ $laporan->reviewer?->name ?? '-' }} pada {{ $laporan->reviewed_at->format('d/m/Y H:i') }}
        </p>
    @endif
</div>
@endif

@endsection