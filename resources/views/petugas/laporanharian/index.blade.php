@extends('layouts.petugas')
@section('title', 'Laporan Harian PST')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <a href="{{ route('petugas.dashboard') }}">Dashboard</a>
    <span>›</span>
    <strong>Laporan Harian</strong>
@endsection

@push('styles')
<style>
/* ── Page header ── */
.lh-header {
    display: flex; justify-content: space-between; align-items: flex-start;
    margin-bottom: 24px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
    gap: 12px; flex-wrap: wrap;
}
.lh-header h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; color: var(--ink); }
.lh-header p  { font-size: 12.5px; color: var(--ink3); margin-top: 4px; }

/* ── CTA Button ── */
.btn-new {
    display: inline-flex; align-items: center; gap: 6px;
    font-size: 12.5px; font-weight: 500; padding: 9px 18px;
    border-radius: 6px; background: var(--blue); color: #fff;
    text-decoration: none; border: none; cursor: pointer; transition: opacity .15s;
    white-space: nowrap; flex-shrink: 0;
}
.btn-new:hover { opacity: .88; }
.btn-new.disabled {
    background: var(--ink3); opacity: .5;
    cursor: not-allowed; pointer-events: none;
}

/* ── Flash messages ── */
.flash {
    padding: 12px 16px; border-radius: 8px; margin-bottom: 20px;
    font-size: 12.5px; font-weight: 500; display: flex; align-items: center; gap: 10px;
}
.flash-ok  { background: var(--green-lt); color: var(--green); border: 1px solid #0a7c4e22; }
.flash-err { background: var(--red-lt);   color: var(--red);   border: 1px solid #c0392b22; }

/* ── Stats cards ── */
.cl-stats {
    display: grid; grid-template-columns: repeat(4, 1fr);
    gap: 1px; background: var(--rule);
    border: 1px solid var(--rule); border-radius: 8px;
    overflow: hidden; margin-bottom: 20px;
}
.cl-stat { background: var(--surface); padding: 18px 20px; }
.cl-stat-label {
    font-size: 10px; font-weight: 600; letter-spacing: .8px;
    text-transform: uppercase; color: var(--ink3); margin-bottom: 8px;
}
.cl-stat-val {
    font-size: 28px; font-weight: 300; letter-spacing: -1px;
    font-family: 'IBM Plex Mono', monospace; color: var(--ink); line-height: 1;
    margin-bottom: 6px;
}
.cl-stat-sub { font-size: 11px; color: var(--ink3); }
.cl-stat-bar { height: 2px; background: var(--wash2); border-radius: 1px; margin-top: 10px; }
.cl-stat-fill { height: 100%; border-radius: 1px; transition: width .5s; }

/* ── Tab bar ── */
.tab-bar {
    display: flex; gap: 2px; margin-bottom: 16px;
    border-bottom: 1px solid var(--rule); padding-bottom: 0;
}
.tab-btn {
    font-size: 13px; font-weight: 500; padding: 9px 18px;
    border: none; background: none; cursor: pointer; color: var(--ink3);
    border-bottom: 2px solid transparent; margin-bottom: -1px;
    transition: color .15s, border-color .15s; border-radius: 4px 4px 0 0;
    font-family: 'IBM Plex Sans', sans-serif;
}
.tab-btn:hover { color: var(--ink); background: var(--wash); }
.tab-btn.active { color: var(--blue); border-bottom-color: var(--blue); font-weight: 600; }
.tab-content { display: none; }
.tab-content.active { display: block; }

/* ── Jam info bar ── */
.jam-info {
    display: flex; align-items: center; gap: 8px;
    padding: 9px 14px; background: var(--wash);
    border: 1px solid var(--rule); border-radius: 7px;
    margin-bottom: 16px; font-size: 12px; color: var(--ink3);
}

/* ── Panel / Table ── */
.panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
.ph    { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
.ph-title { font-size: 12.5px; font-weight: 600; }
.ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

.table-scroll-outer { overflow-x: auto; }
.table-scroll-outer::-webkit-scrollbar { height: 6px; }
.table-scroll-outer::-webkit-scrollbar-thumb { background: var(--rule); border-radius: 3px; }

table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
thead th {
    text-align: left; padding: 8px 14px;
    font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
    color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    white-space: nowrap;
}
tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
tbody tr:last-child { border-bottom: none; }
tbody tr:hover { background: var(--wash); }
tbody td { padding: 10px 14px; vertical-align: middle; color: var(--ink2); }

.mono { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; }

.jawaban-cell {
    max-width: 200px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap;
    font-size: 12px; color: var(--ink);
}

/* ── Status badge ── */
.pill { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
.pill-submitted { background: var(--amber-lt); color: var(--amber); }
.pill-approved  { background: var(--green-lt);  color: var(--green); }
.pill-rejected  { background: var(--red-lt);    color: var(--red); }
.pill-draft     { background: var(--wash2);     color: var(--ink3); }

.btn-sm-act {
    font-size: 11.5px; font-weight: 500; padding: 5px 11px;
    border-radius: 5px; text-decoration: none; border: 1px solid var(--rule);
    display: inline-flex; align-items: center; gap: 5px;
    transition: all .12s; cursor: pointer;
    background: var(--surface); color: var(--ink2);
    white-space: nowrap;
}
.btn-sm-act:hover { background: var(--wash); color: var(--ink); }
.btn-edit {
    background: var(--blue); color: #fff; border-color: var(--blue);
}
.btn-edit:hover { opacity: .88; color: #fff; }

/* Pulse dot */
.waiting-pulse {
    width: 6px; height: 6px; border-radius: 50%; background: currentColor;
    animation: pulse-dot 1.6s ease-in-out infinite; flex-shrink: 0; display: inline-block;
}
@keyframes pulse-dot {
    0%,100% { opacity: 1; transform: scale(1); }
    50%      { opacity: .35; transform: scale(1.45); }
}

/* ── Empty state ── */
.empty-state { padding: 48px 20px; text-align: center; color: var(--ink3); }
.empty-state svg { margin: 0 auto 12px; display: block; opacity: .3; }
.empty-state p { font-size: 13px; }

.pagination-wrap { padding: 12px 18px; border-top: 1px solid var(--rule); font-size: 12px; }

@media (max-width: 640px) {
    .cl-stats { grid-template-columns: repeat(2, 1fr); }
}
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flash flash-err">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

@php
    $jam = now('Asia/Jakarta')->hour;
    $bisaIsi = ($jam >= 7 && $jam < 17);
    $sudahSubmitHariIni = $laporanHariIni ?? false;
    $total     = $stats['total'];
    $submitted = $stats['submitted'];
    $approved  = $stats['approved'];
    $rejected  = $stats['rejected'];
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="lh-header">
    <div>
        <h1>Laporan Harian Saya</h1>
        <p>Riwayat laporan harian yang telah kamu kirimkan ke koordinator</p>
    </div>
    @if($bisaIsi && !$sudahSubmitHariIni)
        <a href="{{ route('petugas.laporan.harian.create') }}" class="btn-new">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Isi Laporan Baru
        </a>
    @elseif($sudahSubmitHariIni)
        <span class="btn-new disabled">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Laporan Sudah Terkirim
        </span>
    @else
        <div style="font-size:11.5px;color:var(--ink3);text-align:right;padding-top:4px">
            Input laporan tersedia<br>
            <strong style="font-family:'IBM Plex Mono',monospace">07.00 – 17.00 WIB</strong>
        </div>
    @endif
</div>

{{-- ── STATS CARDS ── --}}
<div class="cl-stats">
    <div class="cl-stat">
        <div class="cl-stat-label">Total Laporan</div>
        <div class="cl-stat-val">{{ $total }}</div>
        <div class="cl-stat-sub">seluruh laporan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:100%;background:var(--blue)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Disetujui</div>
        <div class="cl-stat-val" style="color:var(--green)">{{ $approved }}</div>
        <div class="cl-stat-sub">sudah diverifikasi</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $total > 0 ? round($approved/$total*100) : 0 }}%;background:var(--green)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Menunggu Review</div>
        <div class="cl-stat-val" style="color:var(--amber)">{{ $submitted }}</div>
        <div class="cl-stat-sub">perlu diverifikasi</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $total > 0 ? round($submitted/$total*100) : 0 }}%;background:var(--amber)"></div></div>
    </div>
    <div class="cl-stat">
        <div class="cl-stat-label">Dikembalikan</div>
        <div class="cl-stat-val" style="color:var(--red)">{{ $rejected }}</div>
        <div class="cl-stat-sub">perlu perbaikan</div>
        <div class="cl-stat-bar"><div class="cl-stat-fill" style="width:{{ $total > 0 ? round($rejected/$total*100) : 0 }}%;background:var(--red)"></div></div>
    </div>
</div>

{{-- ── JAM INFO ── --}}
@if(!$bisaIsi)
<div class="jam-info">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    Input laporan harian hanya tersedia antara jam <strong>07.00 – 17.00 WIB</strong>.
    Di luar jam tersebut form ditutup.
</div>
@endif

{{-- ── TAB BAR ── --}}
<div class="tab-bar">
    <button class="tab-btn active" onclick="switchTab(this,'tab-tabel')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px"><rect x="3" y="3" width="18" height="18" rx="2"/><line x1="3" y1="9" x2="21" y2="9"/><line x1="9" y1="21" x2="9" y2="9"/></svg>
        Tabel Laporan
        @if($total > 0)
            <span style="font-size:10px;margin-left:6px;background:var(--wash2);color:var(--ink3);padding:1px 6px;border-radius:3px">{{ $total }}</span>
        @endif
    </button>
    <button class="tab-btn" onclick="switchTab(this,'tab-riwayat')">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
        Riwayat Kartu
    </button>
</div>

{{-- ── TAB 1: TABEL LAPORAN (seperti admin) ── --}}
<div id="tab-tabel" class="tab-content active">
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Data Laporan Saya</div>
                <div class="ph-sub">{{ $laporan->total() }} laporan di wilayah saya</div>
            </div>
        </div>

        <div class="table-scroll-outer">
            <table>
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Hari</th>
                        <th>Sesi</th>
                        @foreach($templates as $tpl)
                            <th title="{{ $tpl->judul }}">{{ \Illuminate\Support\Str::limit($tpl->judul, 24) }}</th>
                        @endforeach
                        <th>Status</th>
                        <th></th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($laporan as $lap)
                    <tr>
                        <td class="mono">{{ $lap->tanggal->format('d/m/Y') }}</td>
                        <td>{{ $lap->hari }}</td>
                        <td>{{ ucfirst($lap->sesi) }}</td>
                        @foreach($templates as $tpl)
                            @php
                                $berlakuPadaTgl = is_null($tpl->berlaku_mulai) || $tpl->berlaku_mulai->lte($lap->tanggal);
                                $jawaban = $lap->jawabUntuk($tpl->id);
                            @endphp
                            @if($berlakuPadaTgl)
                                <td class="jawaban-cell" title="{{ $jawaban ?? '-' }}">{{ $jawaban ?? '-' }}</td>
                            @else
                                <td class="jawaban-cell" style="color:var(--ink3);font-style:italic;text-align:center" title="Pertanyaan belum ada saat laporan ini dibuat">-</td>
                            @endif
                        @endforeach
                        <td>
                            @php
                                $pillClass = match($lap->status) {
                                    'submitted' => 'pill-submitted',
                                    'approved'  => 'pill-approved',
                                    'rejected'  => 'pill-rejected',
                                    default     => 'pill-draft',
                                };
                                $pillLabel = match($lap->status) {
                                    'submitted' => 'Menunggu',
                                    'approved'  => 'Disetujui',
                                    'rejected'  => 'Dikembalikan',
                                    default     => 'Draft',
                                };
                            @endphp
                            <span class="pill {{ $pillClass }}">{{ $pillLabel }}</span>
                        </td>
                        <td>
                            <div style="display:flex;gap:6px;justify-content:flex-end">
                                <a href="{{ route('petugas.laporan.harian.show', $lap->id) }}" class="btn-sm-act">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                                    Lihat
                                </a>
                                @if($lap->status === 'draft' || $lap->status === 'rejected')
                                <a href="{{ route('petugas.laporan.harian.edit', $lap->id) }}" class="btn-sm-act btn-edit">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                                    Edit
                                </a>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="{{ 3 + $templates->count() + 2 }}">
                            <div class="empty-state">
                                <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                    <polyline points="14 2 14 8 20 8"/>
                                    <line x1="9" y1="13" x2="15" y2="13"/>
                                    <line x1="9" y1="17" x2="12" y2="17"/>
                                </svg>
                                <p>Belum ada laporan. Mulai isi laporan setelah jam 07.00 WIB.</p>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if($laporan->hasPages())
        <div class="pagination-wrap">{{ $laporan->links() }}</div>
        @endif
    </div>
</div>

{{-- ── TAB 2: RIWAYAT KARTU (tampilan lama) ── --}}
<div id="tab-riwayat" class="tab-content">
    @if($laporan->isEmpty())
    <div class="panel" style="padding:64px 20px;text-align:center">
        <svg width="48" height="48" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24" style="margin:0 auto 16px;display:block">
            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
            <polyline points="14 2 14 8 20 8"/>
            <line x1="9" y1="13" x2="15" y2="13"/>
            <line x1="9" y1="17" x2="12" y2="17"/>
        </svg>
        <h3 style="font-size:15px;font-weight:600;color:var(--ink);margin-bottom:6px">Belum ada laporan</h3>
        <p style="font-size:12.5px;color:var(--ink3)">Kamu belum pernah mengisi laporan harian.</p>
        @if($bisaIsi)
        <a href="{{ route('petugas.laporan.harian.create') }}" class="btn-new" style="display:inline-flex;margin-top:16px">Isi Laporan Sekarang</a>
        @endif
    </div>
    @else
    <div style="display:flex;flex-direction:column;gap:6px">
        @foreach($laporan as $item)
        @php $terkunci = in_array($item->status, ['submitted', 'approved']); @endphp
        <div style="background:var(--surface);border:1px solid var(--rule);border-radius:7px;overflow:hidden;border-left:3px solid {{ $item->status === 'approved' ? 'var(--green)' : ($item->status === 'rejected' ? 'var(--red)' : ($item->status === 'submitted' ? 'var(--amber)' : '#c8cdd6')) }}">
            <div style="display:flex;align-items:stretch">
                <div style="flex-shrink:0;width:62px;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:14px 6px;border-right:1px solid var(--rule)">
                    <div style="font-size:24px;font-weight:300;letter-spacing:-1.5px;font-family:'IBM Plex Mono',monospace;color:var(--ink)">{{ $item->tanggal->format('d') }}</div>
                    <div style="font-size:10px;font-weight:500;color:var(--ink3);margin-top:3px;text-align:center">{{ $item->tanggal->translatedFormat('M') }}<br><span style="font-size:9px">{{ $item->tanggal->format('Y') }}</span></div>
                </div>
                <div style="flex:1;min-width:0;padding:12px 16px;display:flex;flex-direction:column;justify-content:center;gap:6px">
                    <div style="display:flex;align-items:center;gap:8px;flex-wrap:wrap">
                        <span style="font-size:13px;font-weight:600;color:var(--ink)">{{ $item->tanggal->translatedFormat('l, d F Y') }}</span>
                        <span style="font-size:11px;padding:1px 7px;border-radius:3px;background:var(--wash2);color:var(--ink3);font-weight:500;border:1px solid var(--rule)">Sesi {{ $item->sesi }}</span>
                        <span class="pill pill-{{ $item->status }}">
                            @if($item->status === 'submitted')<span class="waiting-pulse"></span> @endif
                            {{ match($item->status) { 'draft' => 'Draft', 'submitted' => 'Menunggu Konfirmasi', 'approved' => 'Disetujui', 'rejected' => 'Dikembalikan', default => ucfirst($item->status) } }}
                        </span>
                    </div>
                    <div style="font-size:11.5px;color:var(--ink3);display:flex;align-items:center;gap:12px;flex-wrap:wrap">
                        <span>Dikirim {{ $item->created_at->diffForHumans() }}</span>
                        @if($item->reviewed_at)<span>· Direview {{ $item->reviewed_at->diffForHumans() }}</span>@endif
                    </div>
                </div>
                <div style="flex-shrink:0;display:flex;align-items:center;gap:6px;padding:0 14px;border-left:1px solid var(--rule)">
                    <a href="{{ route('petugas.laporan.harian.show', $item->id) }}" class="btn-sm-act">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
                        Lihat
                    </a>
                    @if($item->status === 'draft' || $item->status === 'rejected')
                    <a href="{{ route('petugas.laporan.harian.edit', $item->id) }}" class="btn-sm-act btn-edit">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
                        Edit
                    </a>
                    @endif
                </div>
            </div>
            @if($item->status === 'rejected' && $item->catatan_koordinator)
            <div style="padding:7px 16px 7px 80px;font-size:11.5px;display:flex;align-items:center;gap:8px;border-top:1px solid;background:var(--red-lt);border-color:#fca5a444;color:var(--red)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span><strong>Catatan Koordinator: </strong>{{ $item->catatan_koordinator }}</span>
            </div>
            @elseif($item->status === 'approved' && $item->catatan_koordinator)
            <div style="padding:7px 16px 7px 80px;font-size:11.5px;display:flex;align-items:center;gap:8px;border-top:1px solid;background:var(--green-lt);border-color:#86efac44;color:var(--green)">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><polyline points="20 6 9 17 4 12"/></svg>
                <span><strong>Catatan: </strong>{{ $item->catatan_koordinator }}</span>
            </div>
            @elseif($item->status === 'submitted')
            <div style="padding:7px 16px 7px 80px;font-size:11.5px;display:flex;align-items:center;gap:8px;border-top:1px solid;background:#fffbf0;border-color:#fde68a66;color:#92400e">
                <span class="waiting-pulse"></span>
                <span>Menunggu review koordinator. Data <strong>tidak dapat diubah</strong> sampai mendapat konfirmasi.</span>
            </div>
            @endif
        </div>
        @endforeach
    </div>
    @if($laporan->hasPages())
    <div style="margin-top:20px">{{ $laporan->links() }}</div>
    @endif
    @endif
</div>

@push('scripts')
<script>
function switchTab(btn, id) {
    document.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
    document.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));
    btn.classList.add('active');
    document.getElementById(id).classList.add('active');
}
</script>
@endpush

@endsection
