@extends('layouts.admin')

@section('title', 'Monitoring — ' . $wilayah->nama)

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <a href="{{ route('admin.materi') }}">Materi & Pembelajaran</a>
    <span>›</span>
    <strong>Monitoring {{ $wilayah->nama }}</strong>
@endsection

@push('styles')
<style>
/* ── Back link ── */
.back-link {
    display: inline-flex; align-items: center; gap: 6px;
    color: var(--ink3); text-decoration: none; font-size: 12px;
    margin-bottom: 20px; transition: color .2s;
}
.back-link:hover { color: var(--blue); }

/* ── Page head ── */
.page-head { display: flex; align-items: flex-start; justify-content: space-between; margin-bottom: 24px; flex-wrap: wrap; gap: 12px; }
.page-head h1 { font-size: 18px; font-weight: 700; color: var(--ink); }
.page-head p  { font-size: 12.5px; color: var(--ink3); margin-top: 3px; }

/* ── Stats strip ── */
.stat-strip { display: flex; gap: 12px; margin-bottom: 24px; flex-wrap: wrap; }
.stat-box {
    flex: 1; min-width: 120px;
    background: var(--surface); border: 1px solid var(--rule); border-radius: 10px;
    padding: 14px 18px; display: flex; flex-direction: column; gap: 4px;
}
.stat-num  { font-size: 24px; font-weight: 700; font-family: 'IBM Plex Mono', monospace; }
.stat-lbl  { font-size: 11px; color: var(--ink3); }
.stat-num.green { color: var(--green); }
.stat-num.red   { color: var(--red); }
.stat-num.blue  { color: var(--blue); }

/* ── Tugas accordion ── */
.tugas-block {
    background: var(--surface); border: 1px solid var(--rule);
    border-radius: 10px; margin-bottom: 14px; overflow: hidden;
}
.tugas-block-head {
    display: flex; align-items: center; justify-content: space-between;
    padding: 14px 18px; cursor: pointer; user-select: none;
    transition: background .15s; gap: 12px;
}
.tugas-block-head:hover { background: var(--wash); }

.tbh-left { display: flex; align-items: center; gap: 12px; flex: 1; min-width: 0; }
.tbh-judul { font-size: 13.5px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.tbh-meta  { font-size: 11px; color: var(--ink3); white-space: nowrap; }

.tbh-right { display: flex; align-items: center; gap: 10px; flex-shrink: 0; }

/* Progress mini */
.prog-wrap { display: flex; align-items: center; gap: 8px; }
.prog-bar  { width: 100px; height: 5px; background: var(--rule); border-radius: 3px; overflow: hidden; }
.prog-fill { height: 100%; background: var(--blue); border-radius: 3px; transition: width .4s; }
.prog-pct  { font-size: 11px; font-weight: 600; font-family: 'IBM Plex Mono', monospace; color: var(--ink2); min-width: 32px; text-align: right; }

/* Chevron */
.chevron { transition: transform .2s; color: var(--ink3); flex-shrink: 0; }
.tugas-block.open .chevron { transform: rotate(180deg); }

/* ── Petugas table inside ── */
.tugas-block-body { display: none; border-top: 1px solid var(--rule); }
.tugas-block.open .tugas-block-body { display: block; }

.petugas-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
.petugas-table th {
    padding: 9px 18px; text-align: left; font-size: 11px; font-weight: 600;
    color: var(--ink3); text-transform: uppercase; letter-spacing: .4px;
    background: var(--wash); border-bottom: 1px solid var(--rule);
}
.petugas-table td {
    padding: 11px 18px; border-bottom: 1px solid var(--rule); vertical-align: middle;
}
.petugas-table tr:last-child td { border-bottom: none; }
.petugas-table tr:hover td { background: var(--wash); }

/* Status pill */
.pill {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 10.5px; font-weight: 600; padding: 3px 9px; border-radius: 20px; white-space: nowrap;
}
.pill-sudah    { background: var(--green-lt); color: var(--green); }
.pill-belum    { background: var(--red-lt);   color: var(--red); }
.pill-terlambat{ background: var(--amber-lt); color: var(--amber); }

/* Skor badge */
.skor-badge {
    display: inline-flex; align-items: center; justify-content: center;
    font-size: 11px; font-weight: 700; font-family: 'IBM Plex Mono', monospace;
    min-width: 38px; padding: 2px 7px; border-radius: 4px;
    background: var(--wash2); color: var(--ink2);
}
.skor-badge.high { background: var(--green-lt); color: var(--green); }
.skor-badge.mid  { background: var(--amber-lt); color: var(--amber); }
.skor-badge.low  { background: var(--red-lt);   color: var(--red); }

/* File/link cell */
.file-link {
    display: inline-flex; align-items: center; gap: 4px;
    font-size: 11.5px; color: var(--blue); text-decoration: none; font-weight: 500;
}
.file-link:hover { text-decoration: underline; }
.no-data { color: var(--ink3); font-size: 11.5px; }

/* Empty state */
.empty-body { padding: 32px; text-align: center; color: var(--ink3); font-size: 12.5px; }
</style>
@endpush

@section('content')

<a href="{{ route('admin.materi') }}" class="back-link">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="19" y1="12" x2="5" y2="12"/><polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke Materi & Pembelajaran
</a>

<div class="page-head">
    <div>
        <h1>Monitoring — {{ $wilayah->nama }}</h1>
        <p>Progress pengerjaan tugas oleh petugas di wilayah ini</p>
    </div>
</div>

{{-- ── Stats strip ── --}}
@php
    $totalPetugas = $wilayah->petugas->count();
    $totalTugas   = $tugasList->count();
    $totalSudah   = $tugasList->sum('sudah');
    $totalBelum   = $tugasList->sum('belum');
@endphp

<div class="stat-strip">
    <div class="stat-box">
        <div class="stat-num blue">{{ $totalPetugas }}</div>
        <div class="stat-lbl">Total Petugas</div>
    </div>
    <div class="stat-box">
        <div class="stat-num">{{ $totalTugas }}</div>
        <div class="stat-lbl">Total Tugas</div>
    </div>
    <div class="stat-box">
        <div class="stat-num green">{{ $totalSudah }}</div>
        <div class="stat-lbl">Sudah Dikerjakan</div>
    </div>
    <div class="stat-box">
        <div class="stat-num red">{{ $totalBelum }}</div>
        <div class="stat-lbl">Belum Dikerjakan</div>
    </div>
</div>

{{-- ── List tugas ── --}}
@forelse($tugasList as $i => $tugas)
<div class="tugas-block" id="block-{{ $tugas->id }}">

    {{-- Head (klik untuk buka/tutup) --}}
    <div class="tugas-block-head" onclick="toggleBlock({{ $tugas->id }})">
        <div class="tbh-left">
            <div>
                <div class="tbh-judul">{{ $tugas->judul }}</div>
                <div class="tbh-meta">
                    Deadline: {{ $tugas->deadline ? $tugas->deadline->format('d M Y') : '—' }}
                    &nbsp;·&nbsp; {{ $tugas->quiz->count() }} soal quiz
                </div>
            </div>
        </div>
        <div class="tbh-right">
            <span style="font-size:11.5px; color:var(--ink3)">
                <span style="color:var(--green); font-weight:600">{{ $tugas->sudah }}</span> sudah &nbsp;
                <span style="color:var(--red); font-weight:600">{{ $tugas->belum }}</span> belum
            </span>
            <div class="prog-wrap">
                <div class="prog-bar">
                    <div class="prog-fill" style="width:{{ $tugas->progress }}%"></div>
                </div>
                <span class="prog-pct">{{ $tugas->progress }}%</span>
            </div>
            <svg class="chevron" width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="6 9 12 15 18 9"/>
            </svg>
        </div>
    </div>

    {{-- Body: tabel petugas ── --}}
    <div class="tugas-block-body">
        @if($wilayah->petugas->count() > 0)
        <table class="petugas-table">
            <thead>
                <tr>
                    <th>#</th>
                    <th>Nama Petugas</th>
                    <th>Status</th>
                    <th>Skor Quiz</th>
                    <th>File Dikumpulkan</th>
                    <th>Link</th>
                    <th>Waktu Submit</th>
                </tr>
            </thead>
            <tbody>
                @foreach($wilayah->petugas as $idx => $p)
                @php
                    $jaw = $tugas->jawabanList->get($p->id);
                    $statusVal = $jaw ? $jaw->status : 'belum';

                    // Cek terlambat
                    if ($jaw && $jaw->status === 'sudah' && $tugas->deadline) {
                        $submitDate = \Carbon\Carbon::parse($jaw->updated_at)->startOfDay();
                        if ($submitDate->gt($tugas->deadline)) {
                            $statusVal = 'terlambat';
                        }
                    }

                    $skor = $jaw ? $jaw->skor : null;
                    $skorClass = '';
                    if ($skor !== null) {
                        $skorClass = $skor >= 80 ? 'high' : ($skor >= 50 ? 'mid' : 'low');
                    }
                @endphp
                <tr>
                    <td style="color:var(--ink3); font-family:'IBM Plex Mono',monospace; font-size:11px">{{ $idx + 1 }}</td>
                    <td>
                        <div style="font-weight:500; color:var(--ink)">{{ $p->user->name ?? '—' }}</div>
                        <div style="font-size:11px; color:var(--ink3)">Shift {{ $p->shift ?? '—' }}</div>
                    </td>
                    <td>
                        @if($statusVal === 'sudah')
                            <span class="pill pill-sudah">✔ Selesai</span>
                        @elseif($statusVal === 'terlambat')
                            <span class="pill pill-terlambat">⚠ Terlambat</span>
                        @else
                            <span class="pill pill-belum">✖ Belum</span>
                        @endif
                    </td>
                    <td>
                        @if($skor !== null)
                            <span class="skor-badge {{ $skorClass }}">{{ $skor }}/100</span>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>
                    <td>
                        @if($jaw && $jaw->file)
                            <a href="{{ asset('storage/' . $jaw->file) }}" target="_blank" class="file-link">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                                    <polyline points="7 10 12 15 17 10"/>
                                    <line x1="12" y1="15" x2="12" y2="3"/>
                                </svg>
                                Download
                            </a>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>
                    <td>
                        @if($jaw && $jaw->link)
                            <a href="{{ $jaw->link }}" target="_blank" rel="noopener" class="file-link">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                    <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                                    <polyline points="15 3 21 3 21 9"/>
                                    <line x1="10" y1="14" x2="21" y2="3"/>
                                </svg>
                                Buka Link
                            </a>
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>
                    <td style="color:var(--ink3); font-size:11.5px">
                        @if($jaw && $jaw->updated_at && $jaw->status === 'sudah')
                            {{ $jaw->updated_at->format('d M Y, H:i') }}
                        @else
                            <span class="no-data">—</span>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
        @else
            <div class="empty-body">Belum ada petugas terdaftar di wilayah ini.</div>
        @endif
    </div>

</div>
@empty
<div style="text-align:center; padding:60px; color:var(--ink3); font-size:13px;">
    Belum ada tugas yang dibuat.
</div>
@endforelse

@endsection

@push('scripts')
<script>
function toggleBlock(id) {
    const block = document.getElementById('block-' + id);
    block.classList.toggle('open');
}
// Buka blok pertama secara default
document.addEventListener('DOMContentLoaded', function () {
    const first = document.querySelector('.tugas-block');
    if (first) first.classList.add('open');
});
</script>
@endpush