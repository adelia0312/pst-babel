@extends('layouts.admin')

@section('title', 'Penilaian — ' . $wilayah->nama)

@push('styles')
<style>
.grade-sb { background: #dcfce7; color: #166534; }
.grade-b  { background: #dbeafe; color: #1e40af; }
.grade-c  { background: #fef3c7; color: #92400e; }
.grade-k  { background: #ffedd5; color: #9a3412; }
.grade-sk { background: #fee2e2; color: #991b1b; }
.status-selesai { background: var(--green-lt); color: var(--green); }
.status-draft   { background: var(--amber-lt); color: var(--amber); }
.status-belum   { background: var(--wash2); color: var(--ink3); }
.inp { height: 32px; border: 1px solid var(--rule); border-radius: 5px; padding: 0 10px; font-size: 12px; font-family: inherit; color: var(--ink); background: var(--surface); }
.inp:focus { outline: none; border-color: var(--blue); }
.btn-back { height: 28px; padding: 0 12px; border-radius: 5px; border: 1px solid var(--rule); background: var(--surface); color: var(--ink2); font-size: 11.5px; font-family: inherit; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 6px; }
.btn-pdf  { height: 26px; padding: 0 10px; border-radius: 4px; border: 1px solid #e2bfff; background: #f5eeff; color: #6d28d9; font-size: 11px; font-family: inherit; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.btn-pdf:hover { background: #ede9fe; border-color: #c4b5fd; }
.btn-xls  { height: 26px; padding: 0 10px; border-radius: 4px; border: 1px solid var(--rule); background: var(--surface); color: var(--ink2); font-size: 11px; font-family: inherit; cursor: pointer; text-decoration: none; display: inline-flex; align-items: center; gap: 4px; white-space: nowrap; }
.btn-xls:hover { border-color: var(--ink3); }
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('admin.penilaian.index') }}">Rekap Penilaian</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>{{ $wilayah->nama }}</strong>
@endsection

@section('content')

<div style="display:flex;align-items:center;gap:10px;margin-bottom:20px;flex-wrap:wrap">
    <a href="{{ route('admin.penilaian.index', ['periode'=>$periode]) }}" class="btn-back">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
    <form method="GET" style="display:flex;align-items:center;gap:8px">
        <label style="font-size:12px;color:var(--ink3);font-weight:500">Periode:</label>
        <select name="periode" class="inp" onchange="this.form.submit()">
            @foreach($periodeOptions as $key => $label)
            <option value="{{ $key }}" {{ $key === $periode ? 'selected' : '' }}>{{ $label }}</option>
            @endforeach
        </select>
    </form>

    {{-- ── TOMBOL EXPORT EXCEL PER WILAYAH ─────────────────────── --}}
    @if($evaluasiList->isNotEmpty())
    <a href="{{ route('admin.penilaian.export.wilayah', [$wilayah->id, 'periode' => $periode]) }}"
       class="btn-xls" title="Download Excel rekap wilayah ini">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
        Export Excel
    </a>
    @endif
</div>

<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">{{ $wilayah->nama }} — {{ $periodeOptions[$periode] ?? $periode }}</div>
            <div class="ph-sub">{{ $evaluasiList->count() }} evaluasi, diurutkan nilai tertinggi</div>
        </div>
        @if($evaluasiList->isNotEmpty())
        <div style="font-size:11.5px;color:var(--ink3)">
            Rata: <strong class="mono" style="color:var(--ink)">{{ number_format($evaluasiList->whereNotNull('jumlah_nilai')->avg('jumlah_nilai'), 2) }}</strong>
        </div>
        @endif
    </div>

    @if($evaluasiList->isNotEmpty())
    <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Petugas</th>
                    <th style="text-align:center">Sikap</th>
                    <th style="text-align:center">Hasil</th>
                    <th style="text-align:center">Proses</th>
                    <th style="text-align:center">Mutu</th>
                    <th style="text-align:center">Nilai</th>
                    <th style="text-align:center">Grade</th>
                    <th style="text-align:center">Status</th>
                    <th style="text-align:right">Dinilai</th>
                    {{-- ── KOLOM AKSI PDF ── --}}
                    <th style="text-align:center">Cetak</th>
                </tr>
            </thead>
            <tbody>
                @foreach($evaluasiList as $i => $e)
                @php
                    $grCls = match($e->grade ?? '') {
                        'SB'=>'grade-sb','B'=>'grade-b','C'=>'grade-c',
                        'K'=>'grade-k','SK'=>'grade-sk',default=>'p-gray'
                    };
                    $stCls = match($e->status) {
                        'selesai'=>'status-selesai','draft'=>'status-draft',default=>'status-belum'
                    };
                @endphp
                <tr>
                    <td>
                        @if($i === 0)
                        <span style="color:var(--amber);font-size:14px">★</span>
                        @else
                        <span class="mono" style="color:var(--ink3)">{{ $i+1 }}</span>
                        @endif
                    </td>
                    <td>
                        <span class="td-main">{{ $e->petugas?->user?->name ?? '—' }}</span>
                        <div class="td-id">{{ $e->petugas?->user?->email }}</div>
                    </td>
                    <td style="text-align:center" class="mono">{{ $e->rata_sikap_kerja ? number_format($e->rata_sikap_kerja,2) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $e->rata_indikator_hasil ? number_format($e->rata_indikator_hasil,2) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $e->rata_indikator_proses ? number_format($e->rata_indikator_proses,2) : '—' }}</td>
                    <td style="text-align:center" class="mono">{{ $e->rata_mutu_pelayanan ? number_format($e->rata_mutu_pelayanan,2) : '—' }}</td>
                    <td style="text-align:center">
                        <strong class="mono" style="font-size:13px">{{ $e->jumlah_nilai ? number_format($e->jumlah_nilai,2) : '—' }}</strong>
                    </td>
                    <td style="text-align:center">
                        @if($e->grade)
                        <span class="pill {{ $grCls }}">{{ $e->grade }}</span>
                        @else
                        <span style="color:var(--ink3)">—</span>
                        @endif
                    </td>
                    <td style="text-align:center">
                        <span class="pill {{ $stCls }}">{{ ucfirst($e->status) }}</span>
                    </td>
                    <td style="text-align:right" class="mono" style="color:var(--ink3)">
                        {{ $e->tanggal_evaluasi?->isoFormat('D MMM YY') ?? '—' }}
                    </td>
                    {{-- ── TOMBOL CETAK PDF PER PETUGAS ── --}}
                    <td style="text-align:center">
                        @if($e->petugas_id && $e->jumlah_nilai !== null)
                        <a href="{{ route('admin.penilaian.pdf', [$e->petugas_id, 'periode' => $periode]) }}"
                           target="_blank"
                           class="btn-pdf"
                           title="Cetak PDF evaluasi {{ $e->petugas?->user?->name }}">
                            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
                            PDF
                        </a>
                        @else
                        <span style="color:var(--ink3);font-size:11px">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @else
    <div class="empty">
        <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
        Belum ada evaluasi selesai untuk wilayah ini pada periode yang dipilih.
    </div>
    @endif
</div>

<div style="margin-top:12px;display:flex;gap:8px;flex-wrap:wrap;align-items:center">
    <span style="font-size:10.5px;color:var(--ink3);font-weight:500">Grade:</span>
    <span class="pill grade-sb" style="font-size:10px">SB &gt;95</span>
    <span class="pill grade-b"  style="font-size:10px">B 86–95</span>
    <span class="pill grade-c"  style="font-size:10px">C 66–85</span>
    <span class="pill grade-k"  style="font-size:10px">K 51–65</span>
    <span class="pill grade-sk" style="font-size:10px">SK &lt;50</span>
</div>

@endsection