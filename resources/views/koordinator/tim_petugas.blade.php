{{-- resources/views/koordinator/tim_petugas.blade.php --}}
@extends('layouts.koordinator')

@section('title', 'Tim Petugas')

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">PST</a>
    <span>›</span>
    <strong>Tim Petugas</strong>
@endsection

@section('content')

@php
    $jumlah = isset($petugas) ? $petugas->count() : 0;
@endphp

{{-- ══════════════════════════════════
     PAGE HEAD
══════════════════════════════════ --}}
<div class="page-head">
    <div>
        <h1>Tim Petugas</h1>
        <p>Petugas dalam wilayah Anda</p>
    </div>

    {{-- Chip jumlah petugas --}}
    <div class="tp-chip">
        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
            <circle cx="9" cy="7" r="4"/>
            <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
        </svg>
        {{ $jumlah }} Petugas
    </div>
</div>

{{-- ══════════════════════════════════
     PANEL TABEL
══════════════════════════════════ --}}
<div class="panel" style="animation: up .28s ease both;">
    <div class="ph">
        <div>
            <div class="ph-title">Daftar Anggota Tim</div>
            <div class="ph-sub">
                @if($jumlah > 0)
                    {{ $jumlah }} petugas terdaftar di wilayah Anda
                @else
                    Belum ada petugas yang terdaftar
                @endif
            </div>
        </div>

        {{-- Wilayah label (jika ada variabel $wilayah) --}}
        @isset($wilayah)
        <span class="tp-wilayah-badge">
            <svg width="10" height="10" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M21 10c0 7-9 13-9 13S3 17 3 10a9 9 0 0118 0z"/>
                <circle cx="12" cy="10" r="3"/>
            </svg>
            {{ $wilayah->nama }}
        </span>
        @endisset
    </div>

    {{-- ── EMPTY STATE ── --}}
    @if($jumlah === 0)
        <div class="empty">
            <svg width="36" height="36" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                <circle cx="9" cy="7" r="4"/>
                <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
            </svg>
            Belum ada petugas di wilayah Anda.
        </div>

    {{-- ── TABEL ── --}}
    @else
        <table class="tp-table">
            <thead>
                <tr>
                    <th class="col-no">No</th>
                    <th class="col-nama">Nama Petugas</th>
                    <th class="col-hp">No HP</th>
                </tr>
            </thead>
            <tbody>
                @foreach($petugas as $p)
                @php
                    $isEven = $loop->even;
                    $initials = strtoupper(substr($p->user->name ?? '?', 0, 2));
                @endphp
                <tr class="{{ $isEven ? 'tr-zebra' : '' }}">

                    {{-- Nomor urut --}}
                    <td class="td-no">
                        {{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}
                    </td>

                    {{-- Nama + inisial avatar --}}
                    <td>
                        <div class="tp-name-cell">
                            <span class="tp-ava">{{ $initials }}</span>
                            <div>
                                <div class="td-main">{{ $p->user->name ?? '—' }}</div>
                            </div>
                        </div>
                    </td>

                    {{-- No HP --}}
                    <td>
                        @if(!empty($p->user->no_hp))
                            <div class="tp-hp">
                                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                                    <path d="M22 16.92v3a2 2 0 01-2.18 2 19.79 19.79 0 01-8.63-3.07A19.5 19.5 0 013.07 10.8a19.79 19.79 0 01-3.07-8.68A2 2 0 012 0h3a2 2 0 012 1.72c.127.96.361 1.903.7 2.81a2 2 0 01-.45 2.11L6.09 7.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.907.339 1.85.573 2.81.7A2 2 0 0122 14.92z"/>
                                </svg>
                                {{ $p->user->no_hp }}
                            </div>
                        @else
                            <span class="tp-empty-val">—</span>
                        @endif
                    </td>

                </tr>
                @endforeach
            </tbody>
        </table>

        {{-- Footer count --}}
        <div class="tp-footer">
            Menampilkan <strong>{{ $jumlah }}</strong> petugas
        </div>
    @endif

</div>{{-- end .panel --}}

@endsection


{{-- ══════════════════════════════════════════════════ STYLES ══ --}}
@push('styles')
<style>

/* ── Page Head ─────────────────────────────────────────────── */
.page-head {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 22px; padding-bottom: 20px;
    border-bottom: 1px solid var(--rule);
}
.page-head h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; }
.page-head p  { font-size: 12px; color: var(--ink3); margin-top: 3px; }

/* ── Chip jumlah (kanan page-head) ──────────────────────────── */
.tp-chip {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 5px 13px; border-radius: 20px;
    background: var(--blue-lt); color: var(--blue);
    font-size: 11.5px; font-weight: 500;
    border: 1px solid rgba(26,86,219,.14);
    flex-shrink: 0;
}

/* ── Badge wilayah (kanan panel header) ─────────────────────── */
.tp-wilayah-badge {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 4px 10px; border-radius: 4px;
    background: var(--wash); border: 1px solid var(--rule);
    font-size: 11px; color: var(--ink2); font-weight: 500;
    flex-shrink: 0;
}

/* ── Table ──────────────────────────────────────────────────── */
.tp-table { width: 100%; border-collapse: collapse; }

.tp-table thead th {
    text-align: left;
    padding: 9px 18px;
    font-size: 9.5px; font-weight: 600;
    letter-spacing: 1.1px; text-transform: uppercase;
    color: var(--ink3); background: var(--wash);
    border-bottom: 1px solid var(--rule);
    white-space: nowrap;
}

/* Column widths */
.col-no   { width: 60px; }
.col-nama { }
.col-hp   { width: 200px; }

.tp-table tbody tr {
    border-bottom: 1px solid var(--rule);
    transition: background .1s;
}
.tp-table tbody tr:last-child { border-bottom: none; }

/* Hover */
.tp-table tbody tr:hover { background: var(--blue-lt) !important; }
.tp-table tbody tr:hover .td-main { color: var(--blue); }

/* Zebra — baris genap sedikit lebih gelap */
.tr-zebra { background: #fafbfc; }

.tp-table tbody td {
    padding: 11px 18px;
    vertical-align: middle;
}

/* ── Kolom No ───────────────────────────────────────────────── */
.td-no {
    font-family: 'IBM Plex Mono', monospace;
    font-size: 11px; font-weight: 500;
    color: var(--ink3);
}

/* ── Nama Cell ──────────────────────────────────────────────── */
.tp-name-cell {
    display: flex; align-items: center; gap: 10px;
}
.tp-ava {
    width: 28px; height: 28px;
    border-radius: 5px;
    background: var(--wash2);
    color: var(--ink2);
    font-size: 10px; font-weight: 600;
    display: inline-flex; align-items: center; justify-content: center;
    flex-shrink: 0;
    text-transform: uppercase;
    letter-spacing: .3px;
    transition: background .1s, color .1s;
}
tr:hover .tp-ava {
    background: rgba(26,86,219,.13);
    color: var(--blue);
}

.td-main {
    font-size: 12.5px; font-weight: 500;
    color: var(--ink);
    transition: color .1s;
}

/* ── No HP ──────────────────────────────────────────────────── */
.tp-hp {
    display: inline-flex; align-items: center; gap: 6px;
    font-family: 'IBM Plex Mono', monospace;
    font-size: 12px; color: var(--ink2);
}
.tp-hp svg { color: var(--ink3); flex-shrink: 0; }
.tp-empty-val { color: var(--rule); font-size: 13px; }

/* ── Empty State ────────────────────────────────────────────── */
.empty {
    padding: 52px 20px; text-align: center;
    color: var(--ink3); font-size: 13px; line-height: 2;
}
.empty svg { display: block; margin: 0 auto 12px; color: var(--rule); }

/* ── Footer ─────────────────────────────────────────────────── */
.tp-footer {
    padding: 10px 18px;
    border-top: 1px solid var(--rule);
    background: var(--wash);
    font-size: 11px; color: var(--ink3);
    text-align: right;
}
.tp-footer strong { color: var(--ink2); }

</style>
@endpush