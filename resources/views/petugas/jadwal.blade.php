@extends('layouts.petugas')

@section('title', 'Jadwal Saya')

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <a href="{{ route('petugas.dashboard') }}">Dashboard</a>
    <span>›</span>
    <strong>Jadwal Saya</strong>
@endsection

@push('styles')
<style>
.page-head {
    display: flex;
    justify-content: space-between;
    align-items: flex-end;
    margin-bottom: 24px;
    padding-bottom: 14px;
    border-bottom: 1px solid #dde3ea;
    flex-wrap: wrap;
    gap: 12px;
}
.page-head h1 {
    font-size: 15px;
    font-weight: 600;
    margin: 0 0 3px 0;
    color: #111827;
    letter-spacing: -0.2px;
}
.page-head p {
    font-size: 11.5px;
    color: #6b7280;
    margin: 0;
}
.page-meta {
    font-size: 11px;
    color: #9ca3af;
    font-family: 'IBM Plex Mono', monospace;
}

/* Month nav */
.month-nav {
    display: flex;
    align-items: center;
    gap: 0;
    margin-bottom: 20px;
    border: 1px solid #e5e7eb;
    border-radius: 5px;
    overflow: hidden;
    width: fit-content;
}
.month-nav-label {
    font-size: 12px;
    font-weight: 600;
    color: #374151;
    padding: 7px 18px;
    background: #f9fafb;
    border-right: 1px solid #e5e7eb;
    letter-spacing: 0.2px;
    min-width: 140px;
    text-align: center;
}
.month-nav a {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 32px;
    height: 32px;
    color: #6b7280;
    text-decoration: none;
    background: #fff;
    transition: background 0.15s, color 0.15s;
}
.month-nav a:first-child {
    border-right: 1px solid #e5e7eb;
}
.month-nav a:hover {
    background: #f3f4f6;
    color: #111827;
}
.month-nav a.disabled {
    pointer-events: none;
    opacity: 0.3;
}

/* Table */
.jadwal-table {
    width: 100%;
    border-collapse: separate;
    border-spacing: 0 2px;
}
.jadwal-table thead th {
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.7px;
    text-transform: uppercase;
    color: #9ca3af;
    padding: 0 12px 8px 12px;
    text-align: left;
    border-bottom: 1px solid #e5e7eb;
}
.jadwal-table thead th.th-status {
    text-align: right;
    width: 90px;
}

.jrow {
    background: #ffffff;
    border: 1px solid #e5e7eb;
    transition: border-color 0.12s, background 0.12s;
}
.jrow:hover {
    border-color: #cbd5e1;
    background: #fafbfc;
}
.jrow.today {
    background: #f8faff;
    border-color: #93c5fd;
}
.jrow.past {
    opacity: 0.55;
}
.jrow td {
    padding: 9px 12px;
    vertical-align: middle;
}
.jrow td:first-child {
    border-radius: 4px 0 0 4px;
    border-left: 2px solid transparent;
}
.jrow.today td:first-child {
    border-left-color: #2563eb;
}
.jrow td:last-child {
    border-radius: 0 4px 4px 0;
}

.date-num {
    font-size: 13px;
    font-weight: 600;
    font-family: 'IBM Plex Mono', monospace;
    color: #111827;
    line-height: 1;
}
.date-dow {
    font-size: 10px;
    color: #9ca3af;
    margin-top: 2px;
}
.jrow.today .date-num,
.jrow.today .date-dow { color: #1d4ed8; }

.shift-pill {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    font-size: 11.5px;
    font-weight: 500;
    color: #374151;
}
.shift-pill svg { flex-shrink: 0; }
.shift-pill.pagi svg  { color: #b45309; }
.shift-pill.siang svg { color: #1d4ed8; }

.shift-none {
    font-size: 11px;
    color: #d1d5db;
}

.today-pill {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 9.5px;
    font-weight: 600;
    letter-spacing: 0.4px;
    text-transform: uppercase;
    color: #1d4ed8;
    background: #eff6ff;
    border: 1px solid #bfdbfe;
    padding: 2px 8px;
    border-radius: 3px;
}

.empty-state {
    background: #f9fafb;
    border: 1px solid #e5e7eb;
    border-radius: 6px;
    padding: 56px 24px;
    text-align: center;
}
.empty-state svg { opacity: 0.3; margin-bottom: 14px; }
.empty-state h3 { font-size: 13px; font-weight: 500; color: #374151; margin-bottom: 6px; }
.empty-state p  { font-size: 12px; color: #9ca3af; max-width: 280px; margin: 0 auto; line-height: 1.6; }

@media (max-width: 600px) {
    .col-siang, .th-siang { display: none; }
    .th-status, .td-status { display: none; }
}
</style>
@endpush

@section('content')

@php
    use Carbon\Carbon;

    $hariNama = ['Min','Sen','Sel','Rab','Kam','Jum','Sab'];

    // Bulan aktif: dari query ?bulan=YYYY-MM, default bulan terbaru yang ada jadwal
    $qBulan = request('bulan');
    if ($qBulan && preg_match('/^\d{4}-\d{2}$/', $qBulan)) {
        $activeMont = Carbon::createFromFormat('Y-m', $qBulan)->startOfMonth();
    } else {
        // Cari bulan terbaru dari data
        $latestDate = $jadwalPetugas->max('tanggal');
        $activeMont = $latestDate
            ? Carbon::parse($latestDate)->startOfMonth()
            : Carbon::now('Asia/Jakarta')->startOfMonth();
    }

    // Filter hanya bulan aktif
    $filtered = $jadwalPetugas->filter(fn($j) =>
        Carbon::parse($j->tanggal)->format('Y-m') === $activeMont->format('Y-m')
    );

    // Navigasi bulan: cari bulan apa saja yang ada jadwal
    $availableMonths = $jadwalPetugas
        ->map(fn($j) => Carbon::parse($j->tanggal)->format('Y-m'))
        ->unique()
        ->sort()
        ->values();

    $currentIdx = $availableMonths->search($activeMont->format('Y-m'));
    $prevMonth  = $currentIdx > 0 ? $availableMonths[$currentIdx - 1] : null;
    $nextMonth  = ($currentIdx !== false && $currentIdx < $availableMonths->count() - 1)
                  ? $availableMonths[$currentIdx + 1] : null;
@endphp

<div class="page-head">
    <div>
        <h1>Jadwal Saya</h1>
        <p>Pelayanan Statistik Terpadu — BPS</p>
    </div>
    <div class="page-meta">{{ now('Asia/Jakarta')->translatedFormat('d M Y') }}</div>
</div>

{{-- Month navigator --}}
<div class="month-nav">
    @if($prevMonth)
        <a href="{{ request()->fullUrlWithQuery(['bulan' => $prevMonth]) }}" title="Bulan sebelumnya">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </a>
    @else
        <a class="disabled" href="#">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="15 18 9 12 15 6"/>
            </svg>
        </a>
    @endif

    <div class="month-nav-label">{{ $activeMont->translatedFormat('F Y') }}</div>

    @if($nextMonth)
        <a href="{{ request()->fullUrlWithQuery(['bulan' => $nextMonth]) }}" title="Bulan berikutnya">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
    @else
        <a class="disabled" href="#">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="9 18 15 12 9 6"/>
            </svg>
        </a>
    @endif
</div>

@if($filtered->isEmpty())
<div class="empty-state">
    <svg width="40" height="40" fill="none" stroke="#9ca3af" stroke-width="1.2" viewBox="0 0 24 24">
        <rect x="3" y="4" width="18" height="18" rx="2"/>
        <line x1="16" y1="2" x2="16" y2="6"/>
        <line x1="8" y1="2" x2="8" y2="6"/>
        <line x1="3" y1="10" x2="21" y2="10"/>
    </svg>
    <h3>Tidak ada jadwal bulan ini</h3>
    <p>Belum ada jadwal tugas untuk {{ $activeMont->translatedFormat('F Y') }}. Hubungi koordinator PST.</p>
</div>
@else

<table class="jadwal-table">
    <thead>
        <tr>
            <th style="width:72px">Tgl</th>
            <th>Shift Pagi</th>
            <th class="th-siang">Shift Siang</th>
            <th class="th-status" style="text-align:right">Ket.</th>
        </tr>
    </thead>
    <tbody>
    @foreach($filtered->groupBy('tanggal') as $tgl => $dayItems)
    @php
        $date    = Carbon::parse($tgl);
        $isToday = $date->isToday();
        $isPast  = $date->isPast() && !$isToday;
        $dow     = $date->dayOfWeek;
        $pagi    = $dayItems->where('shift','pagi')->first();
        $siang   = $dayItems->where('shift','siang')->first();
    @endphp
    <tr class="jrow {{ $isToday ? 'today' : ($isPast ? 'past' : '') }}">
        <td>
            <div class="date-num">{{ $date->format('d') }}</div>
            <div class="date-dow">{{ $hariNama[$dow] }}</div>
        </td>
        <td>
            @if($pagi)
            <div class="shift-pill pagi">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="4"/>
                    <line x1="12" y1="2" x2="12" y2="5"/>
                    <line x1="12" y1="19" x2="12" y2="22"/>
                    <line x1="2" y1="12" x2="5" y2="12"/>
                    <line x1="19" y1="12" x2="22" y2="12"/>
                </svg>
                {{ $pagi->user->name ?? Auth::user()->name }}
            </div>
            @else
            <span class="shift-none">—</span>
            @endif
        </td>
        <td class="col-siang">
            @if($siang)
            <div class="shift-pill siang">
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <path d="M17 18a5 5 0 00-10 0"/>
                    <line x1="12" y1="2" x2="12" y2="9"/>
                    <line x1="4.22" y1="10.22" x2="5.64" y2="11.64"/>
                    <line x1="1" y1="18" x2="3" y2="18"/>
                    <line x1="21" y1="18" x2="23" y2="18"/>
                    <line x1="18.36" y1="11.64" x2="19.78" y2="10.22"/>
                </svg>
                {{ $siang->user->name ?? Auth::user()->name }}
            </div>
            @else
            <span class="shift-none">—</span>
            @endif
        </td>
        <td class="td-status" style="text-align:right">
            @if($isToday)
            <span class="today-pill">
                <svg width="5" height="5" viewBox="0 0 8 8"><circle cx="4" cy="4" r="4" fill="currentColor"/></svg>
                Hari ini
            </span>
            @endif
        </td>
    </tr>
    @endforeach
    </tbody>
</table>

@endif

@endsection