@extends('layouts.koordinator')
@section('title', 'Review Laporan Harian')

@section('breadcrumb')
    <span>PST</span> <span>›</span>
    <a href="{{ route('koordinator.laporan.harian.index') }}" style="color:var(--blue);text-decoration:none">Laporan Harian</a>
    <span>›</span> <strong>Review</strong>
@endsection

@push('styles')
<style>
.page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:20px; gap:12px; flex-wrap:wrap; }
.page-head h1 { font-size:18px; font-weight:600; color:var(--ink); }
.page-head p  { font-size:12px; color:var(--ink3); margin-top:3px; }
.btn-back { display:inline-flex; align-items:center; gap:6px; font-size:12px; color:var(--ink3); text-decoration:none; padding:6px 12px; border:1px solid var(--rule); border-radius:6px; background:var(--surface); }
.btn-back:hover { background:var(--wash); color:var(--ink); }

/* Flash */
.flash { padding:10px 16px; border-radius:8px; margin-bottom:16px; font-size:12.5px; font-weight:500; display:flex; align-items:center; gap:10px; }
.flash-ok  { background:var(--green-lt); color:var(--green); border:1px solid #86efac44; }
.flash-err { background:var(--red-lt); color:var(--red); border:1px solid #fca5a544; }

/* Info Grid */
.info-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:1px; background:var(--rule); border:1px solid var(--rule); border-radius:10px; overflow:hidden; margin-bottom:16px; }
.info-cell { background:var(--surface); padding:12px 16px; }
.info-label { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:.5px; color:var(--ink3); margin-bottom:4px; }
.info-value { font-size:13px; font-weight:600; color:var(--ink); }

/* Status badge */
.status-badge { display:inline-flex; align-items:center; gap:5px; font-size:11px; font-weight:600; padding:4px 10px; border-radius:20px; }
.badge-submitted { background:#fff8e6; color:#b45309; border:1px solid #fde68a; }
.badge-approved  { background:var(--green-lt); color:#166534; border:1px solid #86efac; }
.badge-rejected  { background:var(--red-lt); color:#991b1b; border:1px solid #fca5a5; }
.badge-draft     { background:var(--wash2); color:var(--ink3); border:1px solid var(--rule); }

/* ── VALIDATION BAR ── */
.validation-bar {
    background:var(--surface);
    border:1px solid var(--rule);
    border-radius:10px;
    margin-bottom:16px;
    overflow:hidden;
}
.validation-bar-head {
    display:flex;
    align-items:center;
    padding:9px 18px;
    background:var(--wash);
    border-bottom:1px solid var(--rule);
    gap:8px;
}
.validation-bar-title { font-size:12px; font-weight:600; color:var(--ink); }
.validation-bar-sub   { font-size:11.5px; color:var(--ink3); }

/* Two-column action grid */
.vb-grid {
    display:grid;
    grid-template-columns:1fr 1px 1fr;
}
.vb-col {
    padding:14px 20px;
    display:flex;
    flex-direction:column;
    gap:9px;
}
.vb-divider-v { background:var(--rule); }
.vb-col-label {
    font-size:10.5px;
    font-weight:600;
    text-transform:uppercase;
    letter-spacing:.4px;
    color:var(--ink3);
}
.vb-textarea {
    width:100%;
    padding:8px 11px;
    font-size:12.5px;
    font-family:'IBM Plex Sans',sans-serif;
    border:1px solid var(--rule);
    border-radius:6px;
    background:var(--wash);
    color:var(--ink);
    resize:none;
    outline:none;
    height:54px;
    box-sizing:border-box;
    line-height:1.5;
    transition:border-color .15s;
}
.vb-textarea:focus { border-color:var(--blue); background:#fff; }
.vb-textarea.err   { border-color:var(--red); }

/* Buttons — small, elegant */
.btn-approve {
    align-self:flex-start;
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 13px;
    font-size:12px; font-weight:500;
    background:#f0fdf4; color:#166534;
    border:1px solid #bbf7d0;
    border-radius:5px;
    cursor:pointer;
    font-family:'IBM Plex Sans',sans-serif;
    transition:background .15s, border-color .15s;
    line-height:1.4;
}
.btn-approve:hover { background:#dcfce7; border-color:#86efac; }

.btn-reject {
    align-self:flex-start;
    display:inline-flex; align-items:center; gap:6px;
    padding:5px 13px;
    font-size:12px; font-weight:500;
    background:#fef2f2; color:#991b1b;
    border:1px solid #fecaca;
    border-radius:5px;
    cursor:pointer;
    font-family:'IBM Plex Sans',sans-serif;
    transition:background .15s, border-color .15s;
    line-height:1.4;
}
.btn-reject:hover { background:#fee2e2; border-color:#fca5a5; }

/* Already reviewed bar */
.reviewed-bar { display:flex; align-items:center; gap:14px; padding:13px 18px; flex-wrap:wrap; }
.reviewed-bar-status { font-size:12.5px; font-weight:600; display:flex; align-items:center; gap:7px; }
.reviewed-bar-meta   { font-size:11.5px; color:var(--ink3); }
.reviewed-bar-note   { margin-left:auto; font-size:12px; color:var(--ink2); background:var(--wash); border:1px solid var(--rule); border-radius:6px; padding:5px 12px; max-width:360px; line-height:1.5; }

/* Jawaban */
.jawaban-card { background:var(--surface); border:1px solid var(--rule); border-radius:10px; overflow:hidden; }
.jawaban-header { padding:12px 20px; border-bottom:1px solid var(--rule); background:var(--wash); display:flex; justify-content:space-between; align-items:center; }
.jawaban-header h2 { font-size:13px; font-weight:600; color:var(--ink); }
.jawaban-item { padding:15px 20px; border-bottom:1px solid var(--rule); display:flex; gap:14px; align-items:flex-start; }
.jawaban-item:last-child { border-bottom:none; }
.q-num { width:22px; height:22px; background:var(--blue); color:#fff; border-radius:50%; font-size:11px; font-weight:700; display:flex; align-items:center; justify-content:center; flex-shrink:0; margin-top:2px; }
.q-label { font-size:10.5px; font-weight:600; color:var(--ink3); margin-bottom:5px; display:flex; align-items:center; gap:6px; text-transform:uppercase; letter-spacing:.3px; }
.q-wajib { font-size:9px; padding:1px 5px; border-radius:2px; background:var(--red-lt); color:var(--red); font-weight:600; }
.q-answer { font-size:13px; color:var(--ink); line-height:1.6; }
.q-answer.empty { color:var(--ink3); font-style:italic; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flash flash-err">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

<div class="page-head">
    <div>
        <h1>Review Laporan Harian</h1>
        <p>{{ $laporan->nama_petugas }} · {{ $laporan->tanggal->translatedFormat('l, d F Y') }} · Sesi {{ $laporan->sesi }}</p>
    </div>
    <a href="{{ route('koordinator.laporan.harian.index') }}" class="btn-back">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali ke Daftar
    </a>
</div>

{{-- Info Grid --}}
<div class="info-grid">
    <div class="info-cell">
        <div class="info-label">Petugas</div>
        <div class="info-value" style="font-size:12.5px">{{ $laporan->nama_petugas }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Tanggal</div>
        <div class="info-value">{{ $laporan->tanggal->format('d/m/Y') }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Sesi</div>
        <div class="info-value">{{ $laporan->sesi }}</div>
    </div>
    <div class="info-cell">
        <div class="info-label">Status</div>
        <div style="margin-top:2px">
            @php $bc = ['submitted'=>'badge-submitted','approved'=>'badge-approved','rejected'=>'badge-rejected','draft'=>'badge-draft']; @endphp
            <span class="status-badge {{ $bc[$laporan->status] ?? 'badge-draft' }}">
                @if($laporan->status === 'submitted')     ⏳ Menunggu Review
                @elseif($laporan->status === 'approved')  ✓ Disetujui
                @elseif($laporan->status === 'rejected')  ✕ Dikembalikan
                @else 📝 Draft
                @endif
            </span>
        </div>
    </div>
</div>

{{-- ── VALIDATION BAR ── --}}
<div class="validation-bar">
    <div class="validation-bar-head">
        <span class="validation-bar-title">Panel Validasi</span>
        @if($laporan->status === 'submitted')
            <span class="validation-bar-sub">— Tinjau isi laporan lalu pilih aksi</span>
        @endif
    </div>

    @if($laporan->status === 'submitted')

    {{-- Hidden forms --}}
    <form id="form-approve" method="POST" action="{{ route('koordinator.laporan.harian.approve', $laporan->id) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="catatan_koordinator" id="val-catatan">
    </form>
    <form id="form-reject" method="POST" action="{{ route('koordinator.laporan.harian.reject', $laporan->id) }}">
        @csrf @method('PATCH')
        <input type="hidden" name="catatan_koordinator" id="val-alasan">
    </form>

    <div class="vb-grid">

        {{-- Kolom kiri: Setujui --}}
        <div class="vb-col">
            <div class="vb-col-label">
                Catatan
                <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink3)"> — opsional</span>
            </div>
            <textarea class="vb-textarea" id="catatan-input" placeholder="Tambahkan catatan untuk petugas jika perlu..."></textarea>
            <button type="button" class="btn-approve" onclick="doApprove()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Setujui Laporan
            </button>
        </div>

        <div class="vb-divider-v"></div>

        {{-- Kolom kanan: Kembalikan --}}
        <div class="vb-col">
            <div class="vb-col-label">
                Alasan Pengembalian
                <span style="color:var(--red)"> *</span>
                <span style="font-weight:400;text-transform:none;letter-spacing:0;color:var(--ink3)"> — wajib diisi</span>
            </div>
            <textarea class="vb-textarea" id="alasan-input" placeholder="Jelaskan apa yang perlu diperbaiki petugas..."></textarea>
            <button type="button" class="btn-reject" onclick="doReject()">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>
                Kembalikan ke Petugas
            </button>
        </div>

    </div>

    @else
    {{-- Sudah direview --}}
    <div class="reviewed-bar">
        @if($laporan->status === 'approved')
            <div class="reviewed-bar-status" style="color:#166534">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
                Laporan Disetujui
            </div>
        @elseif($laporan->status === 'rejected')
            <div class="reviewed-bar-status" style="color:#991b1b">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 14 4 9 9 4"/><path d="M20 20v-7a4 4 0 00-4-4H4"/></svg>
                Laporan Dikembalikan
            </div>
        @endif
        @if($laporan->reviewed_at)
        <div class="reviewed-bar-meta">
            oleh <strong>{{ $laporan->reviewer->name ?? '-' }}</strong>
            · {{ $laporan->reviewed_at->format('d M Y, H:i') }} WIB
        </div>
        @endif
        @if($laporan->catatan_koordinator)
        <div class="reviewed-bar-note">{{ $laporan->catatan_koordinator }}</div>
        @endif
    </div>
    @endif
</div>

{{-- ── ISI LAPORAN ── --}}
<div class="jawaban-card">
    <div class="jawaban-header">
        <h2>Isi Laporan Petugas</h2>
        <span style="font-size:11px;color:var(--ink3)">{{ $templates->count() }} pertanyaan</span>
    </div>
    @forelse($templates as $i => $tpl)
    <div class="jawaban-item">
        <div class="q-num">{{ $i + 1 }}</div>
        <div style="flex:1">
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
    <div style="padding:24px;text-align:center;color:var(--ink3);font-size:12.5px">
        Tidak ada template pertanyaan.
    </div>
    @endforelse
</div>

<script>
function doApprove() {
    document.getElementById('val-catatan').value = document.getElementById('catatan-input').value;
    document.getElementById('form-approve').submit();
}
function doReject() {
    var alasan = document.getElementById('alasan-input').value.trim();
    if (!alasan) {
        document.getElementById('alasan-input').focus();
        document.getElementById('alasan-input').classList.add('err');
        return;
    }
    document.getElementById('val-alasan').value = alasan;
    document.getElementById('form-reject').submit();
}
document.addEventListener('DOMContentLoaded', function () {
    var el = document.getElementById('alasan-input');
    if (el) el.addEventListener('input', function () { this.classList.remove('err'); });
});
</script>

@endsection