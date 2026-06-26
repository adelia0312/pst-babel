@extends('layouts.petugas')
@section('title', 'Survey Internal')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Survey Internal</strong>
@endsection

@push('styles')
<style>
    .sv-page-title { font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 4px; }
    .sv-page-sub   { font-size: 12.5px; color: var(--ink3); margin: 0 0 20px; }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-sm { height: 26px; padding: 0 10px; font-size: 11px; }

    /* ── Info banner ── */
    .sv-info {
        display: flex; gap: 10px; align-items: flex-start;
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 7px;
        padding: 12px 14px; font-size: 12px; color: #1e40af; line-height: 1.7;
        margin-bottom: 20px;
    }
    .sv-info svg { flex-shrink: 0; margin-top: 2px; }
    .sv-warn {
        display: flex; gap: 10px; align-items: flex-start;
        background: #fffbeb; border: 1px solid #fde68a; border-radius: 7px;
        padding: 12px 14px; font-size: 12px; color: #92400e; line-height: 1.7;
        margin-bottom: 20px;
    }

    /* ── Status panel ── */
    .sv-status-panel {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 16px 18px; margin-bottom: 20px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px;
        flex-wrap: wrap;
    }
    .sv-status-panel.terbuka  { border-left: 3px solid var(--green); }
    .sv-status-panel.tertutup { border-left: 3px solid var(--rule); }
    .sv-status-label { font-size: 13px; font-weight: 600; color: var(--ink); margin-bottom: 2px; }
    .sv-status-sub   { font-size: 12px; color: var(--ink2); line-height: 1.6; }
    .sv-pill-status-open   { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:#dcfce7;color:#15803d; }
    .sv-pill-status-closed { display:inline-flex;align-items:center;gap:5px;padding:3px 10px;border-radius:99px;font-size:11px;font-weight:600;background:var(--wash2);color:var(--ink3); }
    .sv-dot { width:6px;height:6px;border-radius:50%;background:currentColor; }

    /* ── Tabel rekan — full width ── */
    .sv-panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; width: 100%; }
    .sv-ph {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; border-bottom: 1px solid var(--rule);
    }
    .sv-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
    .sv-ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }

    .sv-table { width: 100%; border-collapse: collapse; }
    .sv-table thead th {
        text-align: left; padding: 9px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: .9px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    .sv-table tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    .sv-table tbody tr:last-child { border-bottom: none; }
    .sv-table tbody tr:hover:not(.sv-row-done) { background: var(--wash); }
    .sv-table tbody td { padding: 11px 16px; vertical-align: middle; font-size: 12.5px; }

    .sv-ava {
        width: 32px; height: 32px; border-radius: 50%;
        display: inline-flex; align-items: center; justify-content: center;
        font-size: 12px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .sv-row-done td { color: var(--ink3); }
    .sv-row-done .sv-ava { opacity: .5; }

    .sv-pill { display:inline-block;font-size:10px;font-weight:500;padding:2px 8px;border-radius:3px; }
    .sv-pill-green { background:var(--green-lt);color:var(--green); }
    .sv-pill-gray  { background:var(--wash2);color:var(--ink3); }

    /* ── Progress bar ── */
    .sv-progress-row { display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid var(--rule);background:var(--wash); }
    .sv-progress-track { flex:1;height:4px;background:var(--rule);border-radius:99px;overflow:hidden; }
    .sv-progress-fill  { height:100%;background:var(--green);border-radius:99px;transition:width .4s; }
    .sv-progress-label { font-size:11px;color:var(--ink3);white-space:nowrap; }

    .sv-empty { padding:40px 20px;text-align:center;color:var(--ink3);font-size:12.5px;line-height:1.8; }

    .sv-alert { display:flex;align-items:center;gap:8px;padding:10px 14px;border-radius:6px;margin-bottom:16px;font-size:12.5px;font-weight:500; }
    .sv-alert-success { background:var(--green-lt);color:var(--green);border:1px solid #b7e4ce; }
    .sv-alert-info    { background:#eff6ff;color:#1e40af;border:1px solid #bfdbfe; }
    .sv-alert-error   { background:#fef2f2;color:#dc2626;border:1px solid #fecaca; }

    .sv-semua-selesai {
        padding:32px 20px;text-align:center;
        background:var(--green-lt);border-top:1px solid var(--green-bd);
    }
</style>
@endpush

@section('content')

<div class="sv-page-title">Survey Internal</div>
<div class="sv-page-sub">Penilaian kinerja antar rekan pegawai satu wilayah.</div>

@if(session('success'))
    <div class="sv-alert sv-alert-success">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 6L9 17l-5-5"/></svg>
        {{ session('success') }}
    </div>
@endif
@if(session('info'))
    <div class="sv-alert sv-alert-info">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('info') }}
    </div>
@endif
@if(session('error'))
    <div class="sv-alert sv-alert-error">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
        {{ session('error') }}
    </div>
@endif

{{-- Status panel --}}
@if($bisaDiakses)
<div class="sv-status-panel terbuka">
    <div>
        <div class="sv-status-label">
            <span class="sv-pill-status-open"><span class="sv-dot"></span>Survey Internal Terbuka</span>
            @if($overrideAktif)
                <span style="font-size:10.5px;color:#92400e;background:#fef3c7;border:1px solid #fde68a;border-radius:99px;padding:1px 8px;font-weight:600;margin-left:6px">⚡ Override{{ $overrideLabel?': '.$overrideLabel:'' }}</span>
            @endif
        </div>
        <div class="sv-status-sub" style="margin-top:4px">
            Periode: <strong>{{ $labelPeriode }}</strong> &nbsp;—&nbsp;
            Nilai rekan Anda menggunakan tombol <strong>Nilai</strong> di bawah.
            Setiap rekan hanya bisa dinilai satu kali per periode.
        </div>
    </div>
</div>
@else
<div class="sv-status-panel tertutup">
    <div>
        <div class="sv-status-label">
            <span class="sv-pill-status-closed"><span class="sv-dot"></span>Survey Internal Belum Dibuka</span>
        </div>
        <div class="sv-status-sub" style="margin-top:4px">
            Dibuka otomatis setiap awal triwulan (minggu 1–2 bulan Januari, April, Juli, Oktober).<br>
            Pembukaan berikutnya: <strong>{{ $infoBerikutnya }}</strong>.
        </div>
    </div>
</div>
@endif

{{-- Tabel rekan --}}
@if($bisaDiakses)
@if($rekanList->isEmpty())
<div class="sv-panel">
    <div class="sv-empty">Tidak ada rekan pegawai lain di wilayah Anda saat ini.</div>
</div>
@else
@php
    $total   = $rekanList->count();
    $selesai = count($sudahDinilai);
    $sisa    = $total - $selesai;
    $persen  = $total > 0 ? round(($selesai / $total) * 100) : 0;
@endphp

<div class="sv-panel">
    <div class="sv-ph">
        <div>
            <div class="sv-ph-title">Rekan Satu Wilayah</div>
            <div class="sv-ph-sub">{{ $total }} pegawai — {{ $sisa > 0 ? $sisa.' belum dinilai' : 'semua sudah dinilai' }}</div>
        </div>
    </div>

    {{-- Progress bar --}}
    <div class="sv-progress-row">
        <span class="sv-progress-label">Progress: {{ $selesai }}/{{ $total }}</span>
        <div class="sv-progress-track"><div class="sv-progress-fill" style="width:{{ $persen }}%"></div></div>
        <span class="sv-progress-label" style="color:{{ $selesai===$total?'var(--green)':'var(--ink3)' }}">{{ $persen }}%</span>
    </div>

    @if($selesai === $total && $total > 0)
    <div class="sv-semua-selesai">
        <div style="font-size:13px;font-weight:600;color:var(--green);margin-bottom:4px">Semua rekan sudah dinilai</div>
        <div style="font-size:12px;color:#166534">Terima kasih. Hasil akan direkap oleh admin pada akhir periode.</div>
    </div>
    @else
    <table class="sv-table">
        <thead>
            <tr>
                <th style="width:52px"></th>
                <th>Nama Pegawai</th>
                <th style="width:130px">Status</th>
                <th style="width:110px">Aksi</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rekanList as $rekan)
            @php
                $petugasId = $rekan->petugas?->id;
                $sdh       = $petugasId && in_array($petugasId, $sudahDinilai);
                $inisial   = strtoupper(substr($rekan->name, 0, 1));
                $warna     = ['#2563eb','#7c3aed','#0891b2','#d97706','#16a34a','#dc2626'];
                $warnaAva  = $warna[abs(crc32($rekan->name)) % count($warna)];
            @endphp
            <tr class="{{ $sdh ? 'sv-row-done' : '' }}">
                <td>
                    <div class="sv-ava" style="background:{{ $warnaAva }}">{{ $inisial }}</div>
                </td>
                <td>
                    <div style="font-weight:500;color:{{ $sdh?'var(--ink3)':'var(--ink)' }}">{{ $rekan->name }}</div>
                    <div style="font-size:11px;color:var(--ink3)">{{ $rekan->email }}</div>
                </td>
                <td>
                    @if($sdh)
                        <span class="sv-pill sv-pill-green">✓ Sudah dinilai</span>
                    @else
                        <span class="sv-pill sv-pill-gray">Belum dinilai</span>
                    @endif
                </td>
                <td>
                    @if($sdh)
                        <span style="font-size:11.5px;color:var(--ink3)">—</span>
                    @elseif($petugasId)
                        <a href="{{ route('petugas.survey-internal.form', $petugasId) }}" class="sv-btn sv-btn-primary sv-btn-sm">
                            Nilai
                        </a>
                    @endif
                </td>
            </tr>
            @endforeach
        </tbody>
    </table>
    @endif
</div>
@endif
@endif

@endsection