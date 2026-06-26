@extends('layouts.koordinator')

@section('title', 'Jadwal Petugas')

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">PST</a>
    <span class="sep">›</span>
    <strong>Jadwal Petugas</strong>
@endsection

@section('content')

{{-- ===== PAGE HEAD ===== --}}
<div class="page-head">
    <div>
        <h1>Jadwal Petugas</h1>
        <p>Lihat dan atur jadwal shift pagi & siang per bulan</p>
    </div>
    <button class="btn-primary" id="btn-toggle-form" onclick="toggleMode()">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>
        <span id="btn-label">Kelola Jadwal</span>
    </button>
</div>

{{-- ===== FLASH MESSAGES ===== --}}
@if(session('success'))
<div class="alert alert-success" id="flash-msg">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-error" id="flash-msg">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- ===== FILTER BULAN / TAHUN ===== --}}
<form method="GET" action="{{ route('jadwal.index') }}" id="filterForm">
    <div class="filter-bar">
        <div class="filter-group">
            <label class="filter-label">Bulan</label>
            <select name="bulan" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                <option value="{{ $i+1 }}" {{ ($bulan == $i+1) ? 'selected' : '' }}>{{ $nm }}</option>
                @endforeach
            </select>
        </div>
        <div class="filter-group">
            <label class="filter-label">Tahun</label>
            <select name="tahun" class="filter-select" onchange="document.getElementById('filterForm').submit()">
                @for($y = date('Y')-1; $y <= date('Y')+2; $y++)
                <option value="{{ $y }}" {{ ($tahun == $y) ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <span class="filter-badge">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/></svg>
            {{ count($jadwalBulan) }} hari terjadwal
        </span>
    </div>
</form>

@php
    $jumlahHari = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
    $hariNama   = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    $hariPanjang = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $petugasMap = $petugas->keyBy('id');
    $namaBulan  = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
@endphp

{{-- ===================================================== --}}
{{-- MODE VIEW: Tabel ringkasan                            --}}
{{-- ===================================================== --}}
<div id="mode-view">
    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Jadwal {{ $namaBulan[(int)$bulan] }} {{ $tahun }}</div>
                <div class="ph-sub">Ringkasan shift pagi dan siang</div>
            </div>
        </div>

        @if(empty($jadwalBulan))
        <div class="empty">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/><line x1="16" y1="2" x2="16" y2="6"/>
                <line x1="8" y1="2" x2="8" y2="6"/><line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            Belum ada jadwal untuk bulan ini.<br>
            Klik <strong>Kelola Jadwal</strong> di atas untuk mulai membuat jadwal.
        </div>
        @else
        <div style="overflow-x:auto">
        <table>
            <thead>
                <tr>
                    <th style="width:100px">Tanggal</th>
                    <th style="width:80px">Hari</th>
                    <th>Shift Pagi</th>
                    <th>Shift Siang</th>
                    <th style="width:160px">Status</th>
                </tr>
            </thead>
            <tbody>
                @foreach($jadwalBulan as $tglStr => $row)
                @php
                    $tgl = \Carbon\Carbon::parse($tglStr);
                    $dow = $tgl->dayOfWeek;
                    $isWknd = ($dow == 0 || $dow == 6);
                    $namaPagi  = $row->shift_pagi_id  ? ($petugasMap[$row->shift_pagi_id]->name  ?? '—') : '—';
                    $namaSiang = $row->shift_siang_id ? ($petugasMap[$row->shift_siang_id]->name ?? '—') : '—';
                    $ketPagi   = $row->ket_pagi  ?? 'normal';
                    $ketSiang  = $row->ket_siang ?? 'normal';
                @endphp
                <tr class="{{ $isWknd ? 'tr-weekend' : '' }}">
                    <td>
                        <span class="td-date">{{ $tgl->translatedFormat('d M') }}</span>
                    </td>
                    <td>
                        <span class="td-hari {{ $isWknd ? 'hari-wknd' : '' }}">{{ $hariPanjang[$dow] }}</span>
                    </td>
                    <td>
                        @if($row->shift_pagi_id)
                            <span class="mava mava-pagi">{{ strtoupper(substr($namaPagi,0,2)) }}</span>{{ $namaPagi }}
                        @else
                            <span class="td-empty">—</span>
                        @endif
                    </td>
                    <td>
                        @if($row->shift_siang_id)
                            <span class="mava mava-siang">{{ strtoupper(substr($namaSiang,0,2)) }}</span>{{ $namaSiang }}
                        @else
                            <span class="td-empty">—</span>
                        @endif
                    </td>
                    <td>
                        <div style="display:flex;gap:4px;flex-wrap:wrap">
                            @if($ketPagi == 'diganti')
                                <span class="badge badge-amber">Pagi Diganti</span>
                            @elseif($ketPagi == 'libur')
                                <span class="badge badge-gray">Pagi Libur</span>
                            @else
                                <span class="badge badge-green">Pagi Normal</span>
                            @endif
                            @if($ketSiang == 'diganti')
                                <span class="badge badge-amber">Siang Diganti</span>
                            @elseif($ketSiang == 'libur')
                                <span class="badge badge-gray">Siang Libur</span>
                            @else
                                <span class="badge badge-green">Siang Normal</span>
                            @endif
                        </div>
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        </div>
        @endif
    </div>
</div>

{{-- ===================================================== --}}
{{-- MODE FORM: Input / edit jadwal bulanan                --}}
{{-- ===================================================== --}}
<div id="mode-form" style="display:none">

    {{-- Info banner --}}
    <div class="form-banner">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>Mode edit aktif. Pilih petugas untuk setiap shift, lalu klik <strong>Simpan Jadwal</strong> di bawah.</span>
    </div>

    <form method="POST" action="{{ route('jadwal.store') }}" id="jadwalForm">
        @csrf
        <input type="hidden" name="bulan" value="{{ $bulan }}">
        <input type="hidden" name="tahun" value="{{ $tahun }}">

        <div class="panel">
            <div class="ph">
                <div>
                    <div class="ph-title">Input Jadwal — {{ $namaBulan[(int)$bulan] }} {{ $tahun }}</div>
                    <div class="ph-sub">{{ $jumlahHari }} hari &middot; Isi shift pagi dan siang tiap hari</div>
                </div>
                <span class="badge badge-amber" id="perubahan-badge" style="display:none">
                    <span id="change-count">0</span> perubahan belum disimpan
                </span>
            </div>

            <div style="overflow-x:auto">
                <table class="form-table">
                    <thead>
                        <tr>
                            <th style="width:60px">Tgl</th>
                            <th style="width:70px">Hari</th>
                            <th>
                                <span class="th-shift th-pagi">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/><line x1="4.22" y1="19.78" x2="5.64" y2="18.36"/><line x1="18.36" y1="5.64" x2="19.78" y2="4.22"/></svg>
                                    Shift Pagi
                                </span>
                            </th>
                            <th>Ket. Pagi</th>
                            <th>
                                <span class="th-shift th-siang">
                                    <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M21 12.79A9 9 0 1111.21 3 7 7 0 0021 12.79z"/></svg>
                                    Shift Siang
                                </span>
                            </th>
                            <th>Ket. Siang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($hari = 1; $hari <= $jumlahHari; $hari++)
                        @php
                            $tgl2    = \Carbon\Carbon::createFromDate($tahun, $bulan, $hari);
                            $tglStr2 = $tgl2->toDateString();
                            $dow2    = $tgl2->dayOfWeek;
                            $isW2    = ($dow2 == 0 || $dow2 == 6);
                            $ex      = $jadwalBulan[$tglStr2] ?? null;
                        @endphp
                        <tr class="{{ $isW2 ? 'tr-weekend' : '' }}">
                            <td>
                                <span class="td-date">{{ str_pad($hari, 2, '0', STR_PAD_LEFT) }}</span>
                            </td>
                            <td>
                                <span class="td-hari {{ $isW2 ? 'hari-wknd' : '' }}">{{ $hariNama[$dow2] }}</span>
                            </td>

                            {{-- Shift Pagi --}}
                            <td>
                                <select name="jadwal[{{ $hari }}][shift_pagi]"
                                        class="form-select shift-pagi"
                                        data-original="{{ $ex->shift_pagi_id ?? '' }}"
                                        onchange="onSelectChange(this)">
                                    <option value="">— Belum diisi —</option>
                                    @foreach($petugas as $p)
                                    <option value="{{ $p->id }}" {{ (isset($ex) && $ex->shift_pagi_id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Ket Pagi --}}
                            <td>
                                <select name="jadwal[{{ $hari }}][ket_pagi]"
                                        class="form-select form-select-ket"
                                        data-original="{{ $ex->ket_pagi ?? 'normal' }}"
                                        onchange="onSelectChange(this)">
                                    <option value="normal"  {{ (!$ex || $ex->ket_pagi == 'normal') ? 'selected' : '' }}>Normal</option>
                                    <option value="diganti" {{ ($ex && $ex->ket_pagi == 'diganti') ? 'selected' : '' }}>Diganti</option>
                                    <option value="libur"   {{ ($ex && $ex->ket_pagi == 'libur') ? 'selected' : '' }}>Libur</option>
                                </select>
                            </td>

                            {{-- Shift Siang --}}
                            <td>
                                <select name="jadwal[{{ $hari }}][shift_siang]"
                                        class="form-select shift-siang"
                                        data-original="{{ $ex->shift_siang_id ?? '' }}"
                                        onchange="onSelectChange(this)">
                                    <option value="">— Belum diisi —</option>
                                    @foreach($petugas as $p)
                                    <option value="{{ $p->id }}" {{ (isset($ex) && $ex->shift_siang_id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>

                            {{-- Ket Siang --}}
                            <td>
                                <select name="jadwal[{{ $hari }}][ket_siang]"
                                        class="form-select form-select-ket"
                                        data-original="{{ $ex->ket_siang ?? 'normal' }}"
                                        onchange="onSelectChange(this)">
                                    <option value="normal"  {{ (!$ex || $ex->ket_siang == 'normal') ? 'selected' : '' }}>Normal</option>
                                    <option value="diganti" {{ ($ex && $ex->ket_siang == 'diganti') ? 'selected' : '' }}>Diganti</option>
                                    <option value="libur"   {{ ($ex && $ex->ket_siang == 'libur') ? 'selected' : '' }}>Libur</option>
                                </select>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            {{-- Rekap otomatis --}}
            <div class="rekap-wrap">
                <div class="rekap-header">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
                    Rekap Shift Bulan Ini
                </div>
                <div class="rekap-list" id="rekap-list">
                    @foreach($petugas as $p)
                    <div class="rekap-item">
                        <span class="mava mava-pagi">{{ strtoupper(substr($p->name,0,2)) }}</span>
                        <span class="rekap-name">{{ $p->name }}</span>
                        <span class="rekap-count" id="rekap-count-{{ $p->id }}">0</span>
                        <span class="rekap-unit">shift</span>
                    </div>
                    @endforeach
                </div>
            </div>

            {{-- Tombol aksi --}}
            <div class="form-actions">
                <button type="submit" class="btn-primary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/>
                        <polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Jadwal
                </button>
                <button type="button" class="btn-secondary" onclick="resetForm()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10"/>
                        <path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                    </svg>
                    Batalkan Perubahan
                </button>
                <span class="save-hint" id="save-info">Belum ada perubahan</span>
            </div>
        </div>
    </form>
</div>

@endsection

@push('styles')
<style>
/* ── Tombol utama ── */
.btn-primary {
    display: inline-flex; align-items: center; gap: 7px;
    padding: 8px 16px; background: #1a56db; color: #fff;
    border: none; border-radius: 6px; font-size: 12.5px; font-weight: 500;
    cursor: pointer; font-family: inherit; transition: background .15s;
}
.btn-primary:hover { background: #1648c0; }

/* ── Filter bar ── */
.filter-bar {
    display: flex; align-items: center; gap: 16px;
    margin-bottom: 18px; flex-wrap: wrap;
}
.filter-group { display: flex; align-items: center; gap: 8px; }
.filter-label { font-size: 12px; font-weight: 500; color: #3d4450; white-space: nowrap; }
.filter-select {
    height: 32px; padding: 0 10px;
    border: 1px solid #e2e5ea; border-radius: 5px;
    background: #fff; color: #0d1117;
    font-size: 12.5px; font-family: inherit; cursor: pointer;
    transition: border-color .12s;
}
.filter-select:focus { outline: none; border-color: #1a56db; }
.filter-badge {
    display: inline-flex; align-items: center; gap: 5px;
    font-size: 11px; font-weight: 500;
    padding: 4px 10px; border-radius: 5px;
    background: #e8eefb; color: #1a56db;
    border: 1px solid rgba(26,86,219,.15);
    margin-left: auto;
}

/* ── Table cells ── */
.td-date {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px; font-weight: 500; color: #0d1117;
}
.td-hari { font-size: 12px; color: #3d4450; }
.hari-wknd { color: #b45309; font-weight: 600; }
.td-empty { color: #c4c9d4; font-size: 12px; }

.tr-weekend td       { background: rgba(180,83,9,.025); }
.tr-weekend:hover td { background: #fef9f0 !important; }

/* ── Avatars ── */
.mava       { display: inline-flex; align-items: center; justify-content: center; width: 22px; height: 22px; border-radius: 4px; font-size: 9px; font-weight: 700; text-transform: uppercase; margin-right: 7px; vertical-align: middle; flex-shrink: 0; }
.mava-pagi  { background: #e8eefb; color: #1a56db; }
.mava-siang { background: #e6f5ee; color: #0a7c4e; }

/* ── Badges ── */
.badge        { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 7px; border-radius: 4px; }
.badge-green  { background: #e6f5ee; color: #0a7c4e; }
.badge-amber  { background: #fef3e2; color: #b45309; }
.badge-gray   { background: #eef0f3; color: #7a8394; }

/* ── Form banner ── */
.form-banner {
    display: flex; align-items: center; gap: 9px;
    padding: 10px 16px; margin-bottom: 14px;
    background: #eff6ff; border: 1px solid rgba(26,86,219,.2);
    border-radius: 7px; font-size: 12px; color: #1e40af;
}
.form-banner svg { flex-shrink: 0; }

/* ── Thead shifts ── */
.th-shift { display: inline-flex; align-items: center; gap: 5px; }
.th-pagi  { color: #1a56db; }
.th-siang { color: #0a7c4e; }

/* ── Form selects ── */
.form-table tbody td { padding: 6px 16px; }
.form-select {
    width: 100%; max-width: 170px;
    height: 30px; padding: 0 9px;
    border: 1px solid #e2e5ea; border-radius: 5px;
    background: #fff; color: #0d1117; font-size: 12px;
    font-family: inherit; cursor: pointer;
    transition: border-color .12s, background .12s;
}
.form-select-ket {
    max-width: 100px;
}
.form-select:focus { outline: none; border-color: #1a56db; box-shadow: 0 0 0 2px rgba(26,86,219,.1); }
.form-select.changed { border-color: #f59e0b; background: #fffbeb; }

/* ── Rekap ── */
.rekap-wrap {
    padding: 14px 18px;
    border-top: 1px solid #e2e5ea;
    background: #f9fafb;
}
.rekap-header {
    display: flex; align-items: center; gap: 6px;
    font-size: 10.5px; font-weight: 600;
    letter-spacing: .6px; text-transform: uppercase;
    color: #7a8394; margin-bottom: 12px;
}
.rekap-list { display: flex; flex-wrap: wrap; gap: 8px; }
.rekap-item {
    display: inline-flex; align-items: center; gap: 7px;
    background: #fff; border: 1px solid #e2e5ea;
    border-radius: 6px; padding: 6px 12px;
}
.rekap-name  { font-size: 12px; color: #3d4450; font-weight: 500; }
.rekap-count {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 14px; font-weight: 700; color: #1a56db;
    min-width: 16px; text-align: right;
}
.rekap-unit { font-size: 10px; color: #9ca3af; }

/* ── Form actions ── */
.form-actions {
    display: flex; align-items: center; gap: 10px;
    padding: 14px 18px; border-top: 1px solid #e2e5ea;
    background: #f9fafb; flex-wrap: wrap;
}
.btn-secondary {
    display: inline-flex; align-items: center; gap: 7px;
    height: 34px; padding: 0 14px;
    background: #fff; color: #3d4450;
    border: 1px solid #e2e5ea; border-radius: 6px;
    font-size: 12px; font-weight: 500;
    cursor: pointer; font-family: inherit; transition: background .12s, border-color .12s;
}
.btn-secondary:hover { background: #f3f4f6; border-color: #cbd5e1; }
.save-hint {
    font-size: 11.5px; color: #9ca3af;
    margin-left: auto;
    font-style: italic;
}
</style>
@endpush

@push('scripts')
<script>
var isFormMode = false;

function toggleMode() {
    isFormMode = !isFormMode;
    document.getElementById('mode-view').style.display = isFormMode ? 'none'  : 'block';
    document.getElementById('mode-form').style.display = isFormMode ? 'block' : 'none';
    var btn = document.getElementById('btn-toggle-form');
    document.getElementById('btn-label').textContent = isFormMode ? '← Kembali ke Ringkasan' : 'Kelola Jadwal';
    if (isFormMode) {
        updateRekap();
        window.scrollTo({ top: 0, behavior: 'smooth' });
    }
}

var changeCount = 0;

function onSelectChange(sel) {
    var was = sel.classList.contains('changed');
    var now = (sel.value !== sel.dataset.original);
    if (now && !was)  { sel.classList.add('changed');    changeCount++; }
    if (!now && was)  { sel.classList.remove('changed'); changeCount = Math.max(0, changeCount - 1); }

    var badge = document.getElementById('perubahan-badge');
    var hint  = document.getElementById('save-info');
    var cc    = document.getElementById('change-count');

    if (changeCount > 0) {
        badge.style.display = 'inline-block';
        cc.textContent = changeCount;
        hint.textContent = changeCount + ' baris diubah, belum disimpan';
        hint.style.color = '#b45309';
    } else {
        badge.style.display = 'none';
        hint.textContent = 'Belum ada perubahan';
        hint.style.color = '#9ca3af';
    }
    updateRekap();
}

function updateRekap() {
    var counts = {};
    document.querySelectorAll('select.shift-pagi, select.shift-siang').forEach(function(sel) {
        if (sel.value) counts[sel.value] = (counts[sel.value] || 0) + 1;
    });
    document.querySelectorAll('[id^="rekap-count-"]').forEach(function(el) {
        var id  = el.id.replace('rekap-count-', '');
        var val = counts[id] || 0;
        el.textContent = val;
        el.style.color = val > 0 ? '#1a56db' : '#c4c9d4';
    });
}

function resetForm() {
    if (!confirm('Batalkan semua perubahan yang belum disimpan?')) return;
    document.querySelectorAll('td select').forEach(function(sel) {
        sel.value = sel.dataset.original || '';
        sel.classList.remove('changed');
    });
    changeCount = 0;
    document.getElementById('save-info').textContent = 'Belum ada perubahan';
    document.getElementById('save-info').style.color = '#9ca3af';
    document.getElementById('perubahan-badge').style.display = 'none';
    updateRekap();
}

// Auto-dismiss flash
setTimeout(function() {
    var el = document.getElementById('flash-msg');
    if (el) {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(function(){ el.remove(); }, 500);
    }
}, 4000);
</script>
@endpush