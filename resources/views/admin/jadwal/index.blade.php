@extends('layouts.admin')

@section('title', 'Jadwal Petugas')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <strong>Jadwal Petugas</strong>
@endsection

@section('content')

@php
    $namaBulan = ['','Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'];
    $hariNama  = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];
    $hariPanjang = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
    $totalWilayah = $semuaWilayah->count();
    $totalJadwal  = collect($jadwalPerWilayah)->sum(fn($j) => count($j));
@endphp

{{-- PAGE HEAD --}}
<div class="aj-page-head">
    <div>
        <h1 class="aj-title">Jadwal Petugas</h1>
        <p class="aj-sub">Kelola & rekap jadwal shift pagi &amp; siang seluruh wilayah</p>
    </div>
    <div class="aj-meta-chips">
        <span class="aj-chip aj-chip-blue">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
            {{ $totalWilayah }} Wilayah
        </span>
        <span class="aj-chip aj-chip-green">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="4" width="18" height="18" rx="2"/>
                <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                <line x1="3" y1="10" x2="21" y2="10"/>
            </svg>
            {{ $totalJadwal }} Hari Terjadwal
        </span>
        <button class="aj-btn-kelola" id="btn-toggle-kelola" onclick="toggleKelola()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            <span id="btn-kelola-label">Kelola Jadwal</span>
        </button>
    </div>
</div>

{{-- FLASH --}}
@if(session('success'))
<div class="aj-alert aj-alert-success" id="flash-msg">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="aj-alert aj-alert-error" id="flash-msg">
    {{ session('error') }}
</div>
@endif

{{-- FILTER --}}
<form method="GET" action="{{ route('admin.jadwal.index') }}" id="filterForm">
    <div class="aj-filter-bar">
        <div class="aj-filter-group">
            <label class="aj-filter-label">Bulan</label>
            <select name="bulan" class="aj-filter-select" onchange="document.getElementById('filterForm').submit()">
                @foreach(['Januari','Februari','Maret','April','Mei','Juni','Juli','Agustus','September','Oktober','November','Desember'] as $i => $nm)
                <option value="{{ $i+1 }}" {{ ($bulan == $i+1) ? 'selected' : '' }}>{{ $nm }}</option>
                @endforeach
            </select>
        </div>
        <div class="aj-filter-group">
            <label class="aj-filter-label">Tahun</label>
            <select name="tahun" class="aj-filter-select" onchange="document.getElementById('filterForm').submit()">
                @for($y = date('Y')-2; $y <= date('Y')+2; $y++)
                <option value="{{ $y }}" {{ ($tahun == $y) ? 'selected' : '' }}>{{ $y }}</option>
                @endfor
            </select>
        </div>
        <div class="aj-filter-group">
            <label class="aj-filter-label">Wilayah</label>
            <select name="wilayah" class="aj-filter-select" onchange="document.getElementById('filterForm').submit()">
                <option value="">Semua Wilayah</option>
                @foreach($semuaWilayah as $w)
                <option value="{{ $w->id }}" {{ request('wilayah') == $w->id ? 'selected' : '' }}>{{ $w->nama }}</option>
                @endforeach
            </select>
        </div>
        <span class="aj-filter-divider"></span>
        <span class="aj-filter-info">Data: <strong>{{ $namaBulan[(int)$bulan] }} {{ $tahun }}</strong></span>
    </div>
</form>

{{-- ============================================================== --}}
{{-- MODE VIEW: Rekap per wilayah (read)                           --}}
{{-- ============================================================== --}}
<div id="mode-view">
@if($semuaWilayah->isEmpty())
<div class="aj-empty-global">
    <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    <p>Belum ada data wilayah.</p>
</div>
@else
<div class="aj-wilayah-grid">
    @foreach($semuaWilayah as $wilayah)
    @php
        $jadwalWilayah = $jadwalPerWilayah[$wilayah->id] ?? [];
        $jumlahJadwal  = count($jadwalWilayah);
    @endphp
    <div class="aj-card">
        <div class="aj-card-header">
            <div class="aj-card-header-left">
                <div class="aj-wilayah-icon">{{ strtoupper(substr($wilayah->nama,0,2)) }}</div>
                <div>
                    <div class="aj-wilayah-name">{{ $wilayah->nama }}</div>
                    @if($wilayah->lokasi)
                    <div class="aj-wilayah-lokasi">
                        <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/>
                        </svg>
                        {{ $wilayah->lokasi }}
                    </div>
                    @endif
                </div>
            </div>
            <div>
                @if($jumlahJadwal > 0)
                    <span class="aj-badge aj-badge-blue">{{ $jumlahJadwal }} hari</span>
                @else
                    <span class="aj-badge aj-badge-gray">Belum ada jadwal</span>
                @endif
            </div>
        </div>
        <div class="aj-card-body">
            @if($jumlahJadwal === 0)
            <div class="aj-empty-wilayah">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.4" viewBox="0 0 24 24">
                    <rect x="3" y="4" width="18" height="18" rx="2"/>
                    <line x1="16" y1="2" x2="16" y2="6"/><line x1="8" y1="2" x2="8" y2="6"/>
                    <line x1="3" y1="10" x2="21" y2="10"/>
                </svg>
                <span>Belum ada jadwal. Klik <strong>Kelola Jadwal</strong> untuk mengisi.</span>
            </div>
            @else
            <div class="aj-table-wrap">
                <table class="aj-table">
                    <thead>
                        <tr>
                            <th class="col-tgl">Tanggal</th>
                            <th class="col-hari">Hari</th>
                            <th class="col-shift">Shift Pagi</th>
                            <th class="col-shift">Shift Siang</th>
                            <th class="col-ket">Keterangan</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php ksort($jadwalWilayah); @endphp
                        @foreach($jadwalWilayah as $tglStr => $row)
                        @php
                            $tgl      = \Carbon\Carbon::parse($tglStr);
                            $dow      = $tgl->dayOfWeek;
                            $isWknd   = ($dow == 0 || $dow == 6);
                            $namaPagi  = $row->shift_pagi_id  ? ($petugasMap[$row->shift_pagi_id]->name  ?? '—') : '—';
                            $namaSiang = $row->shift_siang_id ? ($petugasMap[$row->shift_siang_id]->name ?? '—') : '—';
                            $ketPagi   = $row->ket_pagi  ?? 'normal';
                            $ketSiang  = $row->ket_siang ?? 'normal';
                        @endphp
                        <tr class="{{ $isWknd ? 'tr-weekend' : '' }}">
                            <td><span class="td-tgl">{{ $tgl->translatedFormat('d M') }}</span></td>
                            <td><span class="td-hari {{ $isWknd ? 'hari-wknd' : '' }}">{{ $hariPanjang[$dow] }}</span></td>
                            <td>
                                @if($row->shift_pagi_id)
                                <div class="aj-petugas-cell">
                                    <span class="aj-ava aj-ava-pagi">{{ strtoupper(substr($namaPagi,0,2)) }}</span>
                                    <span class="aj-petugas-name">{{ $namaPagi }}</span>
                                </div>
                                @else <span class="aj-kosong">—</span> @endif
                            </td>
                            <td>
                                @if($row->shift_siang_id)
                                <div class="aj-petugas-cell">
                                    <span class="aj-ava aj-ava-siang">{{ strtoupper(substr($namaSiang,0,2)) }}</span>
                                    <span class="aj-petugas-name">{{ $namaSiang }}</span>
                                </div>
                                @else <span class="aj-kosong">—</span> @endif
                            </td>
                            <td class="td-ket">
                                <div class="aj-ket-wrap">
                                    @if($ketPagi=='diganti') <span class="aj-badge aj-badge-amber">Pagi Diganti</span>
                                    @elseif($ketPagi=='libur') <span class="aj-badge aj-badge-gray">Pagi Libur</span>
                                    @else <span class="aj-badge aj-badge-green">Pagi Normal</span>
                                    @endif
                                    @if($ketSiang=='diganti') <span class="aj-badge aj-badge-amber">Siang Diganti</span>
                                    @elseif($ketSiang=='libur') <span class="aj-badge aj-badge-gray">Siang Libur</span>
                                    @else <span class="aj-badge aj-badge-green">Siang Normal</span>
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
    @endforeach
</div>
@endif
</div>{{-- end mode-view --}}

{{-- ============================================================== --}}
{{-- MODE KELOLA: Pilih wilayah → form edit jadwal                 --}}
{{-- ============================================================== --}}
<div id="mode-kelola" style="display:none">

    {{-- Pilih wilayah yang akan dikelola --}}
    <div class="kelola-banner">
        <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
        </svg>
        <span>Mode kelola aktif. Pilih wilayah yang ingin diatur jadwalnya, lalu isi shift dan simpan.</span>
    </div>

    <div class="kelola-wilayah-picker">
        <label class="aj-filter-label">Pilih Wilayah:</label>
        <div class="wilayah-btn-group" id="wilayah-btn-group">
            @foreach($semuaWilayah as $w)
            <button type="button"
                    class="wilayah-btn {{ ($wilayahId && $wilayahId == $w->id) ? 'active' : '' }}"
                    onclick="selectWilayah({{ $w->id }}, '{{ addslashes($w->nama) }}')">
                {{ $w->nama }}
            </button>
            @endforeach
        </div>
    </div>

    {{-- Form jadwal per wilayah --}}
    @foreach($semuaWilayah as $wilayah)
    @php
        $jadwalWilayah2 = $jadwalPerWilayah[$wilayah->id] ?? [];
        $petugasWilayah = $petugasPerWilayah[$wilayah->id] ?? collect();
        $jumlahHari     = \Carbon\Carbon::createFromDate($tahun, $bulan, 1)->daysInMonth;
    @endphp
    <div class="kelola-form-panel" id="form-wilayah-{{ $wilayah->id }}" style="display:none">
        <div class="form-banner-wil">
            <div class="aj-wilayah-icon" style="width:30px;height:30px;font-size:11px">{{ strtoupper(substr($wilayah->nama,0,2)) }}</div>
            <div>
                <strong style="font-size:13px">{{ $wilayah->nama }}</strong>
                <span style="color:var(--ink3,#7a8394);font-size:11px;margin-left:8px">{{ $namaBulan[(int)$bulan] }} {{ $tahun }}</span>
            </div>
            <span class="aj-badge aj-badge-amber" id="change-badge-{{ $wilayah->id }}" style="display:none">
                <span id="change-count-{{ $wilayah->id }}">0</span> perubahan belum disimpan
            </span>
        </div>

        @if($petugasWilayah->isEmpty())
        <div class="aj-empty-wilayah" style="padding:20px">
            <svg width="20" height="20" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
            <span>Belum ada petugas terdaftar di wilayah ini.</span>
        </div>
        @else
        <form method="POST" action="{{ route('admin.jadwal.store') }}" id="form-jadwal-{{ $wilayah->id }}">
            @csrf
            <input type="hidden" name="bulan" value="{{ $bulan }}">
            <input type="hidden" name="tahun" value="{{ $tahun }}">
            <input type="hidden" name="wilayah_id" value="{{ $wilayah->id }}">

            <div class="aj-table-wrap">
                <table class="aj-table form-table">
                    <thead>
                        <tr>
                            <th style="width:55px">Tgl</th>
                            <th style="width:60px">Hari</th>
                            <th>
                                <span style="color:#1a56db">☀ Shift Pagi</span>
                            </th>
                            <th style="width:100px">Ket. Pagi</th>
                            <th>
                                <span style="color:#0a7c4e">🌙 Shift Siang</span>
                            </th>
                            <th style="width:100px">Ket. Siang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($hari = 1; $hari <= $jumlahHari; $hari++)
                        @php
                            $tgl2    = \Carbon\Carbon::createFromDate($tahun, $bulan, $hari);
                            $tglStr2 = $tgl2->toDateString();
                            $dow2    = $tgl2->dayOfWeek;
                            $isW2    = ($dow2 == 0 || $dow2 == 6);
                            $ex      = $jadwalWilayah2[$tglStr2] ?? null;
                        @endphp
                        <tr class="{{ $isW2 ? 'tr-weekend' : '' }}">
                            <td><span class="td-tgl">{{ str_pad($hari,2,'0',STR_PAD_LEFT) }}</span></td>
                            <td><span class="td-hari {{ $isW2 ? 'hari-wknd' : '' }}">{{ $hariNama[$dow2] }}</span></td>
                            <td>
                                <select name="jadwal[{{ $hari }}][shift_pagi]"
                                        class="form-sel shift-sel"
                                        data-original="{{ $ex->shift_pagi_id ?? '' }}"
                                        data-wid="{{ $wilayah->id }}"
                                        onchange="onSelChange(this)">
                                    <option value="">— Belum —</option>
                                    @foreach($petugasWilayah as $p)
                                    <option value="{{ $p->id }}" {{ (isset($ex) && $ex->shift_pagi_id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="jadwal[{{ $hari }}][ket_pagi]"
                                        class="form-sel ket-sel"
                                        data-original="{{ $ex->ket_pagi ?? 'normal' }}"
                                        data-wid="{{ $wilayah->id }}"
                                        onchange="onSelChange(this)">
                                    <option value="normal"  {{ (!$ex || $ex->ket_pagi=='normal')  ? 'selected' : '' }}>Normal</option>
                                    <option value="diganti" {{ ($ex && $ex->ket_pagi=='diganti') ? 'selected' : '' }}>Diganti</option>
                                    <option value="libur"   {{ ($ex && $ex->ket_pagi=='libur')   ? 'selected' : '' }}>Libur</option>
                                </select>
                            </td>
                            <td>
                                <select name="jadwal[{{ $hari }}][shift_siang]"
                                        class="form-sel shift-sel"
                                        data-original="{{ $ex->shift_siang_id ?? '' }}"
                                        data-wid="{{ $wilayah->id }}"
                                        onchange="onSelChange(this)">
                                    <option value="">— Belum —</option>
                                    @foreach($petugasWilayah as $p)
                                    <option value="{{ $p->id }}" {{ (isset($ex) && $ex->shift_siang_id == $p->id) ? 'selected' : '' }}>
                                        {{ $p->name }}
                                    </option>
                                    @endforeach
                                </select>
                            </td>
                            <td>
                                <select name="jadwal[{{ $hari }}][ket_siang]"
                                        class="form-sel ket-sel"
                                        data-original="{{ $ex->ket_siang ?? 'normal' }}"
                                        data-wid="{{ $wilayah->id }}"
                                        onchange="onSelChange(this)">
                                    <option value="normal"  {{ (!$ex || $ex->ket_siang=='normal')  ? 'selected' : '' }}>Normal</option>
                                    <option value="diganti" {{ ($ex && $ex->ket_siang=='diganti') ? 'selected' : '' }}>Diganti</option>
                                    <option value="libur"   {{ ($ex && $ex->ket_siang=='libur')   ? 'selected' : '' }}>Libur</option>
                                </select>
                            </td>
                        </tr>
                        @endfor
                    </tbody>
                </table>
            </div>

            <div class="form-actions">
                <button type="submit" class="aj-btn-kelola">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Jadwal
                </button>
                <button type="button" class="aj-btn-reset" onclick="resetFormWilayah({{ $wilayah->id }})">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <polyline points="1 4 1 10 7 10"/><path d="M3.51 15a9 9 0 102.13-9.36L1 10"/>
                    </svg>
                    Batalkan
                </button>
                <span class="save-hint" id="save-hint-{{ $wilayah->id }}">Belum ada perubahan</span>
            </div>
        </form>
        @endif
    </div>
    @endforeach
</div>{{-- end mode-kelola --}}

@endsection

@push('styles')
<style>
.aj-page-head { display:flex;align-items:flex-start;justify-content:space-between;flex-wrap:wrap;gap:12px;margin-bottom:20px;padding-bottom:18px;border-bottom:1px solid #e2e5ea; }
.aj-title { font-size:19px;font-weight:600;letter-spacing:-.3px;color:#0d1117;margin:0; }
.aj-sub { font-size:12px;color:#7a8394;margin:3px 0 0; }
.aj-meta-chips { display:flex;gap:8px;flex-wrap:wrap;align-items:center; }
.aj-chip { display:inline-flex;align-items:center;gap:5px;padding:4px 10px;border-radius:20px;font-size:11px;font-weight:500; }
.aj-chip-blue  { background:#e8eefb;color:#1a56db; }
.aj-chip-green { background:#e6f5ee;color:#0a7c4e; }
.aj-btn-kelola { display:inline-flex;align-items:center;gap:7px;padding:7px 14px;background:#1a56db;color:#fff;border:none;border-radius:6px;font-size:12.5px;font-weight:500;cursor:pointer;font-family:inherit;transition:background .15s; }
.aj-btn-kelola:hover { background:#1648c0; }
.aj-btn-reset { display:inline-flex;align-items:center;gap:7px;height:34px;padding:0 14px;background:#fff;color:#3d4450;border:1px solid #e2e5ea;border-radius:6px;font-size:12px;font-weight:500;cursor:pointer;font-family:inherit;transition:background .12s; }
.aj-btn-reset:hover { background:#f3f4f6; }
.aj-alert { display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:7px;font-size:12.5px;margin-bottom:16px; }
.aj-alert-success { background:#e6f5ee;color:#0a7c4e;border:1px solid rgba(10,124,78,.2); }
.aj-alert-error { background:#fee2e2;color:#991b1b;border:1px solid rgba(153,27,27,.2); }
.aj-filter-bar { display:flex;align-items:center;gap:12px;margin-bottom:22px;flex-wrap:wrap; }
.aj-filter-group { display:flex;align-items:center;gap:7px; }
.aj-filter-label { font-size:12px;font-weight:500;color:#3d4450;white-space:nowrap; }
.aj-filter-select { height:32px;padding:0 10px;border:1px solid #e2e5ea;border-radius:5px;background:#fff;color:#0d1117;font-size:12.5px;font-family:inherit;cursor:pointer;transition:border-color .12s; }
.aj-filter-select:focus { outline:none;border-color:#1a56db; }
.aj-filter-divider { width:1px;height:20px;background:#e2e5ea;margin:0 4px; }
.aj-filter-info { font-size:12px;color:#7a8394; }
.aj-filter-info strong { color:#3d4450; }
.aj-wilayah-grid { display:flex;flex-direction:column;gap:20px; }
.aj-card { background:#fff;border:1px solid #e2e5ea;border-radius:10px;overflow:hidden;box-shadow:0 1px 3px rgba(0,0,0,.04);transition:box-shadow .15s; }
.aj-card:hover { box-shadow:0 4px 16px rgba(0,0,0,.07); }
.aj-card-header { display:flex;align-items:center;justify-content:space-between;padding:14px 18px;border-bottom:1px solid #e2e5ea;background:#fafbfc; }
.aj-card-header-left { display:flex;align-items:center;gap:12px; }
.aj-wilayah-icon { width:36px;height:36px;border-radius:8px;background:linear-gradient(135deg,#1a56db,#3b82f6);color:#fff;font-size:12px;font-weight:700;display:flex;align-items:center;justify-content:center;flex-shrink:0;letter-spacing:.5px; }
.aj-wilayah-name { font-size:13.5px;font-weight:600;color:#0d1117; }
.aj-wilayah-lokasi { display:flex;align-items:center;gap:4px;font-size:11px;color:#7a8394;margin-top:2px; }
.aj-card-body { padding:0; }
.aj-table-wrap { overflow-x:auto; }
.aj-table { width:100%;border-collapse:collapse; }
.aj-table thead th { text-align:left;padding:8px 16px;font-size:9.5px;font-weight:600;letter-spacing:1px;text-transform:uppercase;color:#7a8394;background:#f5f6f8;border-bottom:1px solid #e2e5ea;white-space:nowrap; }
.aj-table tbody tr { border-bottom:1px solid #f0f1f3;transition:background .1s; }
.aj-table tbody tr:last-child { border-bottom:none; }
.aj-table tbody tr:hover { background:#f7f8fa; }
.aj-table tbody td { padding:9px 16px;vertical-align:middle;font-size:12.5px;color:#1a1f2e; }
.col-tgl{width:90px}.col-hari{width:90px}.col-shift{width:200px}.col-ket{width:180px}
.tr-weekend td { background:rgba(180,83,9,.025); }
.tr-weekend:hover td { background:#fef3e2 !important; }
.td-tgl { font-family:'IBM Plex Mono',monospace;font-size:12px;font-weight:500;color:#0d1117; }
.td-hari { font-size:12px;color:#3d4450; }
.hari-wknd { color:#b45309;font-weight:600; }
.aj-petugas-cell { display:inline-flex;align-items:center;gap:7px; }
.aj-ava { display:inline-flex;align-items:center;justify-content:center;width:24px;height:24px;border-radius:4px;font-size:9px;font-weight:700;text-transform:uppercase;flex-shrink:0; }
.aj-ava-pagi  { background:#eef0f3;color:#3d4450; }
.aj-ava-siang { background:#e6f5ee;color:#0a7c4e; }
.aj-petugas-name { font-size:12.5px;color:#1a1f2e; }
.aj-kosong { color:#c0c5ce;font-size:13px; }
.td-ket { vertical-align:middle; }
.aj-ket-wrap { display:flex;flex-wrap:wrap;gap:4px; }
.aj-badge { display:inline-block;font-size:10px;font-weight:500;padding:2px 8px;border-radius:3px;white-space:nowrap; }
.aj-badge-green  { background:#e6f5ee;color:#0a7c4e; }
.aj-badge-amber  { background:#fef3e2;color:#b45309; }
.aj-badge-gray   { background:#eef0f3;color:#7a8394; }
.aj-badge-blue   { background:#e8eefb;color:#1a56db; }
.aj-empty-global { text-align:center;padding:60px 20px;color:#7a8394;font-size:13px; }
.aj-empty-global svg { display:block;margin:0 auto 12px;color:#d0d4da; }
.aj-empty-wilayah { display:flex;align-items:center;justify-content:center;gap:10px;padding:24px 20px;color:#a0a8b4;font-size:12.5px;background:#fafbfc; }
/* Kelola mode */
.kelola-banner { display:flex;align-items:center;gap:9px;padding:10px 16px;margin-bottom:16px;background:#eff6ff;border:1px solid rgba(26,86,219,.2);border-radius:7px;font-size:12px;color:#1e40af; }
.kelola-wilayah-picker { display:flex;align-items:center;gap:12px;margin-bottom:20px;flex-wrap:wrap; }
.wilayah-btn-group { display:flex;gap:8px;flex-wrap:wrap; }
.wilayah-btn { padding:6px 14px;border:1px solid #e2e5ea;border-radius:6px;background:#fff;color:#3d4450;font-size:12px;font-family:inherit;cursor:pointer;transition:all .12s; }
.wilayah-btn:hover { border-color:#1a56db;color:#1a56db;background:#eff6ff; }
.wilayah-btn.active { border-color:#1a56db;background:#1a56db;color:#fff; }
.kelola-form-panel { background:#fff;border:1px solid #e2e5ea;border-radius:10px;overflow:hidden;margin-bottom:16px; }
.form-banner-wil { display:flex;align-items:center;gap:12px;padding:12px 18px;background:#fafbfc;border-bottom:1px solid #e2e5ea;flex-wrap:wrap; }
.form-sel { width:100%;max-width:160px;height:30px;padding:0 8px;border:1px solid #e2e5ea;border-radius:5px;background:#fff;color:#0d1117;font-size:12px;font-family:inherit;cursor:pointer;transition:border-color .12s; }
.form-sel:focus { outline:none;border-color:#1a56db; }
.form-sel.changed { border-color:#f59e0b;background:#fffbeb; }
.ket-sel { max-width:90px; }
.form-actions { display:flex;align-items:center;gap:10px;padding:12px 18px;border-top:1px solid #e2e5ea;background:#f9fafb;flex-wrap:wrap; }
.save-hint { font-size:11.5px;color:#9ca3af;margin-left:auto;font-style:italic; }
</style>
@endpush

@push('scripts')
<script>
var isKelola = false;
var changeCounts = {};

function toggleKelola() {
    isKelola = !isKelola;
    document.getElementById('mode-view').style.display = isKelola ? 'none' : 'block';
    document.getElementById('mode-kelola').style.display = isKelola ? 'block' : 'none';
    document.getElementById('btn-kelola-label').textContent = isKelola ? '← Kembali ke Rekap' : 'Kelola Jadwal';
    if (isKelola) window.scrollTo({ top: 0, behavior: 'smooth' });
}

function selectWilayah(id, nama) {
    document.querySelectorAll('.wilayah-btn').forEach(function(b){ b.classList.remove('active'); });
    document.querySelectorAll('.kelola-form-panel').forEach(function(p){ p.style.display = 'none'; });
    var btn = document.querySelector('.wilayah-btn[onclick*="selectWilayah(' + id + ',"]');
    if (btn) btn.classList.add('active');
    var panel = document.getElementById('form-wilayah-' + id);
    if (panel) { panel.style.display = 'block'; panel.scrollIntoView({ behavior: 'smooth', block: 'start' }); }
}

function onSelChange(sel) {
    var wid = sel.dataset.wid;
    var was = sel.classList.contains('changed');
    var now = (sel.value !== sel.dataset.original);
    if (now && !was)  { sel.classList.add('changed');    changeCounts[wid] = (changeCounts[wid] || 0) + 1; }
    if (!now && was)  { sel.classList.remove('changed'); changeCounts[wid] = Math.max(0, (changeCounts[wid] || 1) - 1); }
    var badge = document.getElementById('change-badge-' + wid);
    var hint  = document.getElementById('save-hint-' + wid);
    var cc    = document.getElementById('change-count-' + wid);
    if ((changeCounts[wid] || 0) > 0) {
        badge.style.display = 'inline-block';
        cc.textContent = changeCounts[wid];
        hint.textContent = changeCounts[wid] + ' baris diubah';
        hint.style.color = '#b45309';
    } else {
        badge.style.display = 'none';
        hint.textContent = 'Belum ada perubahan';
        hint.style.color = '#9ca3af';
    }
}

function resetFormWilayah(wid) {
    if (!confirm('Batalkan semua perubahan yang belum disimpan?')) return;
    var form = document.getElementById('form-jadwal-' + wid);
    if (!form) return;
    form.querySelectorAll('select').forEach(function(sel){
        sel.value = sel.dataset.original || '';
        sel.classList.remove('changed');
    });
    changeCounts[wid] = 0;
    document.getElementById('change-badge-' + wid).style.display = 'none';
    var hint = document.getElementById('save-hint-' + wid);
    hint.textContent = 'Belum ada perubahan';
    hint.style.color = '#9ca3af';
}

// Auto-dismiss flash
setTimeout(function() {
    var el = document.getElementById('flash-msg');
    if (el) { el.style.transition = 'opacity .5s'; el.style.opacity = '0'; setTimeout(function(){ el.remove(); }, 500); }
}, 4000);

// Jika ada wilayah terpilih di URL, otomatis buka mode kelola
@if($wilayahId)
document.addEventListener('DOMContentLoaded', function() {
    // buka mode kelola dan langsung pilih wilayah
    isKelola = true;
    document.getElementById('mode-view').style.display = 'none';
    document.getElementById('mode-kelola').style.display = 'block';
    document.getElementById('btn-kelola-label').textContent = '← Kembali ke Rekap';
    selectWilayah({{ $wilayahId }}, '');
});
@endif
</script>
@endpush