{{-- resources/views/petugas/absensi/index.blade.php --}}
@extends('layouts.petugas')

@section('title', 'Absensi Saya')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <a href="{{ route('petugas.dashboard') }}">Dashboard</a>
    <span>›</span>
    <strong>Absensi Saya</strong>
@endsection

@push('styles')
<style>
    /* ── Filter bar ── */
    .filter-bar {
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 16px; flex-wrap: wrap;
    }
    .filter-bar label {
        font-size: 10.5px; font-weight: 500; letter-spacing: .5px;
        text-transform: uppercase; color: var(--ink3);
    }
    .filter-select {
        height: 30px; border: 1px solid var(--rule); border-radius: 5px;
        background: var(--surface); color: var(--ink);
        font-family: 'IBM Plex Sans', sans-serif; font-size: 12px;
        padding: 0 9px; outline: none; transition: border-color .12s;
    }
    .filter-select:focus { border-color: var(--blue); }
    .btn-filter {
        height: 30px; padding: 0 14px;
        background: var(--blue); color: #fff;
        border: none; border-radius: 5px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12px; font-weight: 500; cursor: pointer;
        transition: opacity .12s;
    }
    .btn-filter:hover { opacity: .88; }
    .btn-reset {
        height: 30px; padding: 0 12px;
        background: none; color: var(--ink3);
        border: 1px solid var(--rule); border-radius: 5px;
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 12px; cursor: pointer;
        text-decoration: none; display: inline-flex; align-items: center;
        transition: border-color .12s, color .12s;
    }
    .btn-reset:hover { border-color: var(--ink2); color: var(--ink); }

    /* ── Status hari ini ── */
    .absen-status-inline {
        display: flex; align-items: center; gap: 8px;
        padding: 7px 12px; border-radius: 5px;
        font-size: 12px; font-weight: 500; border: 1px solid;
    }
    .absen-status-inline.hadir  { background: var(--green-lt); color: var(--green); border-color: #86efac44; }
    .absen-status-inline.belum  { background: var(--amber-lt); color: var(--amber); border-color: #fde68a; }
    .absen-jam { font-family: 'IBM Plex Mono', monospace; font-weight: 600; }

    /* ── Info box ── */
    .info-box {
        background: var(--blue-lt); border: 1px solid rgba(26,86,219,.15);
        border-radius: 5px; padding: 10px 14px;
        font-size: 11.5px; color: var(--ink2); line-height: 1.6;
        margin-bottom: 20px; display: flex; gap: 9px; align-items: flex-start;
    }
    .info-box svg { flex-shrink: 0; margin-top: 1px; color: var(--blue); }

    /* ── Detail button ── */
    .btn-detail {
        display: inline-flex; align-items: center; gap: 5px;
        height: 26px; padding: 0 10px; border-radius: 4px;
        background: var(--blue-lt); color: var(--blue);
        border: 1px solid rgba(26,86,219,.2);
        font-family: 'IBM Plex Sans', sans-serif;
        font-size: 11.5px; font-weight: 500; cursor: pointer;
        transition: background .12s, border-color .12s;
        white-space: nowrap;
    }
    .btn-detail:hover { background: #d5e3f9; border-color: rgba(26,86,219,.4); }

    .sesi-pagi  { background: #fff8e1; color: #e65100; }
    .sesi-siang { background: var(--blue-lt); color: var(--blue); }

    /* ── Modal ── */
    .modal-overlay {
        display: none; position: fixed; inset: 0; z-index: 500;
        background: rgba(0,0,0,.3); align-items: center; justify-content: center;
    }
    .modal-overlay.open { display: flex; }
    .modal {
        background: var(--surface); border-radius: 7px;
        width: 420px; max-width: 95vw; overflow: hidden;
        box-shadow: 0 16px 48px rgba(0,0,0,.14);
        border: 1px solid var(--rule);
    }
    .modal-head {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; border-bottom: 1px solid var(--rule);
    }
    .modal-title { font-size: 13px; font-weight: 600; color: var(--ink); }
    .modal-close {
        width: 26px; height: 26px; border: 1px solid var(--rule); border-radius: 5px;
        background: none; cursor: pointer; display: flex; align-items: center;
        justify-content: center; color: var(--ink3); transition: border-color .12s, color .12s;
    }
    .modal-close:hover { border-color: var(--ink2); color: var(--ink); }
    .modal-body { padding: 18px; display: flex; flex-direction: column; gap: 12px; }
    .modal-foto { width: 100%; max-height: 170px; object-fit: cover; border-radius: 5px; border: 1px solid var(--rule); }
    .modal-row { display: flex; gap: 10px; }
    .modal-kv  { flex: 1; }
    .modal-k { font-size: 10px; font-weight: 600; text-transform: uppercase; letter-spacing: .7px; color: var(--ink3); margin-bottom: 3px; }
    .modal-v { font-size: 12.5px; color: var(--ink); font-weight: 500; }
    .modal-foot { display: flex; align-items: center; justify-content: flex-end; padding: 12px 18px; border-top: 1px solid var(--rule); }

    /* Panel table */
    .panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .ph    { display: flex; align-items: center; justify-content: space-between; padding: 12px 18px; border-bottom: 1px solid var(--rule); }
    .ph-title { font-size: 12.5px; font-weight: 600; }
    .ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }
    table { width: 100%; border-collapse: collapse; }
    thead th {
        text-align: left; padding: 8px 16px;
        font-size: 10px; font-weight: 600; letter-spacing: 1px; text-transform: uppercase;
        color: var(--ink3); background: var(--wash); border-bottom: 1px solid var(--rule);
    }
    tbody tr { border-bottom: 1px solid var(--rule); transition: background .1s; }
    tbody tr:last-child { border-bottom: none; }
    tbody tr:hover { background: var(--wash); }
    tbody td { padding: 10px 16px; vertical-align: middle; }
    .mono { font-family: 'IBM Plex Mono', monospace; font-size: 11.5px; }
    .pill { display: inline-block; font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 20px; letter-spacing: .3px; }
    .p-green { background: var(--green-lt); color: var(--green); }
    .p-amber { background: var(--amber-lt); color: var(--amber); }
    .p-red   { background: var(--red-lt); color: var(--red); }
    .empty { text-align: center; padding: 48px 20px; color: var(--ink3); }
    .empty svg { margin: 0 auto 12px; display: block; opacity: .3; }
</style>
@endpush

@section('content')

@php
    $absenHariIni = $absensiHariIni ?? null;
    $sudahAbsen   = $absenHariIni !== null;
@endphp

{{-- ── PAGE HEADER ── --}}
<div class="page-head" style="align-items:center; display: flex; justify-content: space-between; margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--rule); flex-wrap: wrap; gap: 12px;">
    <div>
        <h1 style="font-size: 18px; font-weight: 600; margin: 0;">Absensi Saya</h1>
        <p style="font-size: 12px; color: var(--ink3); margin: 2px 0 0;">Riwayat kehadiran — disinkronkan dari aplikasi Flutter</p>
    </div>
    @if($sudahAbsen)
    <div class="absen-status-inline hadir">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        <span>Sudah Absen Hari Ini</span>
        <span class="absen-jam">{{ $absenHariIni->jam_masuk ?? '—' }}</span>
        <span class="pill {{ $absenHariIni->verified_status === 'approved' ? 'p-green' : ($absenHariIni->verified_status === 'rejected' ? 'p-red' : 'p-amber') }}">
            {{ ucfirst($absenHariIni->verified_status ?? 'pending') }}
        </span>
    </div>
    @else
    <div class="absen-status-inline belum">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/>
        </svg>
        <span>Belum Absen Hari Ini</span>
        <span style="font-size:11px;opacity:.7">· via app Flutter</span>
    </div>
    @endif
</div>


{{-- ── FILTER ── --}}
<form method="GET" action="{{ route('petugas.absensi.index') }}" class="filter-bar">
    <label>Bulan</label>
    <select name="bulan" class="filter-select">
        @foreach(range(1,12) as $b)
        <option value="{{ $b }}" {{ ($filterBulan ?? now()->month) == $b ? 'selected' : '' }}>
            {{ \Carbon\Carbon::create()->month($b)->isoFormat('MMMM') }}
        </option>
        @endforeach
    </select>

    <label>Tahun</label>
    <select name="tahun" class="filter-select">
        @foreach(range(now()->year, now()->year - 2) as $y)
        <option value="{{ $y }}" {{ ($filterTahun ?? now()->year) == $y ? 'selected' : '' }}>{{ $y }}</option>
        @endforeach
    </select>

    <label>Sesi</label>
    <select name="sesi" class="filter-select">
        <option value="">Semua Sesi</option>
        <option value="pagi"  {{ ($filterSesi ?? '') === 'pagi'  ? 'selected' : '' }}>Pagi</option>
        <option value="siang" {{ ($filterSesi ?? '') === 'siang' ? 'selected' : '' }}>Siang</option>
    </select>

    <button type="submit" class="btn-filter">Terapkan</button>
    <a href="{{ route('petugas.absensi.index') }}" class="btn-reset">Reset</a>
</form>

{{-- ── TABEL RIWAYAT ── --}}
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Riwayat Absensi</div>
            <div class="ph-sub">
                {{ $absensi->total() }} record · {{ \Carbon\Carbon::create()->month($filterBulan ?? now()->month)->isoFormat('MMMM') }} {{ $filterTahun ?? now()->year }}
            </div>
        </div>
    </div>
    <table>
        <thead>
            <tr>
                <th>Tanggal</th>
                <th>Jenis Scan</th>
                <th>Jam</th>
                <th>Status Kehadiran</th>
                <th>Keterlambatan</th>
                <th></th>
            </tr>
        </thead>
        <tbody>
            @forelse($absensi as $a)
            <tr>
                <td class="mono">{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}</td>
                <td>
                    @php
                        $jCls = match($a->jenis_scan ?? '') {
                            'masuk_pagi','keluar_pagi'   => 'sesi-pagi',
                            'masuk_siang','keluar_siang' => 'sesi-siang',
                            default => '',
                        };
                    @endphp
                    <span class="pill {{ $jCls }}">{{ $a->label_jenis_scan }}</span>
                </td>
                <td class="mono">{{ $a->jam_masuk ?? $a->jam_keluar ?? '—' }}</td>
                <td>
                    @php
                        $skCls = match($a->status_kehadiran ?? '') {
                            'tepat_waktu' => 'p-green',
                            'toleransi'   => 'p-amber',
                            'terlambat','alpha' => 'p-red',
                            default => '',
                        };
                    @endphp
                    @if($a->status_kehadiran)
                        <span class="pill {{ $skCls }}">{{ $a->label_status_kehadiran }}</span>
                    @else
                        <span style="color:var(--ink3);font-size:11px">Keluar</span>
                    @endif
                </td>
                <td class="mono">
                    @if($a->keterlambatan_menit > 0)
                        <span style="color:var(--red)">+{{ $a->keterlambatan_menit }} mnt</span>
                    @else
                        —
                    @endif
                </td>
                <td>
                    <button class="btn-detail" onclick="openDetail(
                        '{{ \Carbon\Carbon::parse($a->tanggal)->format('d M Y') }}',
                        '{{ ucfirst($a->sesi) }}',
                        '{{ $a->jam_masuk ?? '-' }}',
                        '{{ $a->jam_keluar ?? '-' }}',
                        '{{ $a->status_kehadiran ?? 'keluar' }}',
                        '{{ addslashes($a->device_info ?? '') }}'
                    )">
                        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/>
                        </svg>
                        Detail
                    </button>
                </td>
            </tr>
            @empty
            <tr>
                <td colspan="6">
                    <div class="empty">
                        <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/>
                        </svg>
                        Belum ada data absensi bulan ini.<br>
                        <span style="font-size:11px">Absen via aplikasi Flutter.</span>
                    </div>
                </td>
            </tr>
            @endforelse
        </tbody>
    </table>

    @if($absensi->hasPages())
    <div style="padding:11px 18px;display:flex;align-items:center;justify-content:space-between;border-top:1px solid var(--rule)">
        <div style="font-size:11px;color:var(--ink3)">
            Menampilkan {{ $absensi->firstItem() }}–{{ $absensi->lastItem() }} dari {{ $absensi->total() }} data
        </div>
        {{ $absensi->appends(request()->query())->links('vendor.pagination.simple-default') }}
    </div>
    @endif
</div>

{{-- ── MODAL DETAIL ── --}}
<div class="modal-overlay" id="modalOverlay">
    <div class="modal">
        <div class="modal-head">
            <div class="modal-title">Detail Absensi</div>
            <button class="modal-close" onclick="closeDetail()">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/>
                </svg>
            </button>
        </div>
        <div class="modal-body">
            <img id="dFoto" class="modal-foto" src="" alt="Foto Selfie" style="display:none">
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Tanggal</div>
                    <div class="modal-v" id="dTanggal">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Sesi</div>
                    <div class="modal-v" id="dSesi">—</div>
                </div>
            </div>
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Jam Masuk</div>
                    <div class="modal-v mono" id="dMasuk">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Jam Keluar</div>
                    <div class="modal-v mono" id="dKeluar">—</div>
                </div>
            </div>
            <div class="modal-row">
                <div class="modal-kv">
                    <div class="modal-k">Status Hadir</div>
                    <div class="modal-v" id="dStatus">—</div>
                </div>
                <div class="modal-kv">
                    <div class="modal-k">Perangkat</div>
                    <div class="modal-v" id="dDevice" style="font-size:11px;color:var(--ink3);font-weight:400">—</div>
                </div>
            </div>
        </div>
        <div class="modal-foot">
            <button class="btn-reset" onclick="closeDetail()">Tutup</button>
        </div>
    </div>
</div>

@endsection

@push('scripts')
<script>
function openDetail(tanggal, sesi, masuk, keluar, status, device) {
    document.getElementById('dTanggal').textContent = tanggal;
    document.getElementById('dSesi').textContent    = sesi;
    document.getElementById('dMasuk').textContent   = masuk;
    document.getElementById('dKeluar').textContent  = keluar;

    const pillMap = { tepat_waktu:'p-green', toleransi:'p-amber', terlambat:'p-red', alpha:'p-red', keluar:'' };
    document.getElementById('dStatus').innerHTML = `<span class="pill ${pillMap[status]??''}">${status.charAt(0).toUpperCase()+status.slice(1)}</span>`;
    document.getElementById('dDevice').textContent = device || '—';

    document.getElementById('modalOverlay').classList.add('open');
}
function closeDetail() {
    document.getElementById('modalOverlay').classList.remove('open');
}
</script>
@endpush