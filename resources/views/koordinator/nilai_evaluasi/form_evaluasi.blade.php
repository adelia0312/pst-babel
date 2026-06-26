@extends('layouts.koordinator')
@section('title', 'Form Evaluasi — ' . $petugas->user->name)

@push('styles')
<style>
.sek{background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:14px;}
.sek-head{padding:10px 16px;border-bottom:1px solid var(--rule);background:var(--wash);display:flex;align-items:center;gap:8px;flex-wrap:wrap;}
.sek-title{font-size:12px;font-weight:600;color:var(--ink);}
.sek-sub{font-size:10.5px;color:var(--ink3);}
.sek-body{padding:16px 20px;}
.fgrid{display:grid;grid-template-columns:1fr 1fr;gap:14px 28px;}
@media(max-width:680px){.fgrid{grid-template-columns:1fr;}}

.fl{font-size:11.5px;font-weight:500;color:var(--ink2);margin-bottom:6px;display:flex;align-items:center;gap:6px;flex-wrap:wrap;}
.tag-auto  {font-size:9px;background:#dbeafe;color:#1e40af;padding:2px 6px;border-radius:3px;font-weight:700;}
.tag-survey{font-size:9px;background:#d1fae5;color:#065f46;padding:2px 6px;border-radius:3px;font-weight:700;}
.tag-warn  {font-size:9px;background:#fef3c7;color:#92400e;padding:2px 6px;border-radius:3px;font-weight:700;}

.vbox{min-height:42px;background:var(--wash);border:1px solid var(--rule);border-radius:5px;padding:8px 12px;display:flex;align-items:center;gap:10px;flex-wrap:wrap;}
.vbox.warn{border-color:#fca5a5;background:#fff5f5;}
.vbox.null-data{border-style:dashed;background:#fafafa;}
.vnum{font-family:'IBM Plex Mono',monospace;font-size:20px;font-weight:700;color:var(--ink);flex-shrink:0;line-height:1;}
.vnum.red{color:#dc2626;}
.vnum.gray{color:var(--ink3);font-weight:300;}
.vsrc{font-size:10.5px;color:var(--ink3);line-height:1.5;}
.vsrc b{color:var(--ink2);}

.info-box{background:#eff6ff;border:1px solid #bfdbfe;border-radius:6px;padding:10px 14px;font-size:11.5px;color:#1e40af;margin-bottom:16px;display:flex;align-items:flex-start;gap:8px;}
.warn-box{background:#fff5f5;border:1px solid #fca5a5;border-radius:6px;padding:10px 14px;font-size:11.5px;color:#991b1b;margin-bottom:14px;display:flex;align-items:flex-start;gap:8px;}
.btn-ok{height:36px;padding:0 20px;border-radius:5px;border:none;background:var(--blue);color:#fff;font-size:13px;font-family:inherit;font-weight:500;cursor:pointer;display:inline-flex;align-items:center;gap:7px;}
.btn-ok:hover{background:#1648b8;}
.btn-draft{height:36px;padding:0 16px;border-radius:5px;border:1px solid var(--rule);background:var(--surface);color:var(--ink2);font-size:13px;font-family:inherit;cursor:pointer;display:inline-flex;align-items:center;gap:5px;}
.btn-draft:hover{border-color:var(--ink2);color:var(--ink);}
.btn-back{height:34px;padding:0 14px;border-radius:5px;border:1px solid var(--rule);background:var(--surface);color:var(--ink2);font-size:12px;font-family:inherit;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:5px;}
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('koordinator.dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('koordinator.nilai-evaluasi.index') }}">Nilai &amp; Evaluasi</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Form Evaluasi</strong>
@endsection

@section('content')

{{-- Header petugas --}}
<div style="background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:14px 18px;display:flex;align-items:center;gap:14px;margin-bottom:16px;flex-wrap:wrap;">
    <div style="width:44px;height:44px;border-radius:7px;background:var(--blue-lt);color:var(--blue);font-size:15px;font-weight:700;display:flex;align-items:center;justify-content:center;font-family:'IBM Plex Mono',monospace;flex-shrink:0;">
        {{ strtoupper(substr($petugas->user->name,0,2)) }}
    </div>
    <div style="flex:1;min-width:0;">
        <div style="font-size:14px;font-weight:600;color:var(--ink);">{{ $petugas->user->name }}</div>
        <div style="font-size:11.5px;color:var(--ink3);margin-top:2px;">
            Periode: <strong>{{ \App\Helpers\PeriodeHelper::isoLabel($periode) }}</strong>
            @if($evaluasi) &middot; <span style="color:var(--amber);font-weight:500">✏ Edit</span>
            @else &middot; <span style="color:var(--blue);font-weight:500">+ Baru</span> @endif
        </div>
    </div>
    <a href="{{ route('koordinator.nilai-evaluasi.index', ['periode'=>$periode]) }}" class="btn-back">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
        Kembali
    </a>
</div>

@if($errors->any())
<div class="warn-box">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ $errors->first() }}
</div>
@endif

@if(($otomatis['kehadiran'] ?? null) === 0.0)
<div class="warn-box">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M10.29 3.86L1.82 18a2 2 0 001.71 3h16.94a2 2 0 001.71-3L13.71 3.86a2 2 0 00-3.42 0z"/><line x1="12" y1="9" x2="12" y2="13"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
    <span><strong>Nilai kehadiran 0</strong> — tidak ada jadwal atau absensi tercatat. Pastikan jadwal sudah diinput dan petugas sudah scan QR, lalu buka kembali form ini.</span>
</div>
@endif

<div class="info-box">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        Semua nilai dihitung <strong>otomatis dari data sistem</strong>.
        <span class="tag-auto">AUTO</span> = dari absensi/checklist/laporan/quiz.
        <span class="tag-survey">SURVEY</span> = dari penilaian antar petugas (survey internal, diisi petugas lain).
        Koordinator <strong>tidak perlu dan tidak bisa mengubah nilai</strong> — cukup tambahkan catatan lalu simpan.
        Khusus <strong>Kepuasan Pelanggan</strong>: jika belum ada SKM dari pengunjung, koordinator dapat mengisi nilai manual.
    </div>
</div>

<form method="POST" action="{{ route('koordinator.nilai-evaluasi.simpan', $petugas->id) }}">
    @csrf
    @method('PUT')
    <input type="hidden" name="periode" value="{{ $periode }}">

    {{-- ══ I. SIKAP KERJA ══ --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="13" height="13" fill="none" stroke="#1a56db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            <span class="sek-title">I. Sikap Kerja</span>
            <span class="sek-sub">— penilaian sikap &amp; perilaku kerja petugas selama triwulan</span>
        </div>
        <div class="sek-body">
            <div class="fgrid">

                {{-- Kehadiran --}}
                <div>
                    <div class="fl">Kehadiran <span class="tag-auto">AUTO — Jadwal + Absensi QR</span></div>
                    <div class="vbox {{ ($otomatis['kehadiran'] ?? null) === 0.0 ? 'warn' : (is_null($otomatis['kehadiran'] ?? null) ? 'null-data' : '') }}">
                        <span class="vnum {{ ($otomatis['kehadiran'] ?? null) === 0.0 ? 'red' : (is_null($otomatis['kehadiran'] ?? null) ? 'gray' : '') }}">
                            {{ $otomatis['kehadiran'] !== null ? number_format($otomatis['kehadiran'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_kehadiran'] }}</b><br>
                            Rumus: shift hadir ÷ shift dijadwalkan (exclude libur) × 100
                        </span>
                    </div>
                </div>

                {{-- Disiplin Waktu --}}
                <div>
                    <div class="fl">Disiplin Waktu <span class="tag-auto">AUTO — Absensi QR</span></div>
                    <div class="vbox {{ is_null($otomatis['disiplin'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['disiplin'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['disiplin'] !== null ? number_format($otomatis['disiplin'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_disiplin'] }}</b><br>
                            Skor: tepat=100, toleransi=88, terlambat ≤10 mnt=80, ≤30 mnt=70, &gt;30 mnt=65
                        </span>
                    </div>
                </div>

                {{-- Komunikasi --}}
                <div>
                    <div class="fl">Komunikasi <span class="tag-survey">SURVEY INTERNAL — dinilai rekan</span></div>
                    <div class="vbox {{ is_null($otomatis['komunikasi'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['komunikasi'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['komunikasi'] !== null ? number_format($otomatis['komunikasi'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_survey_int'] }}</b><br>
                            Rata-rata rating pertanyaan komunikasi ÷ 5 × 100
                        </span>
                    </div>
                </div>

                {{-- Kerjasama --}}
                <div>
                    <div class="fl">Kerjasama <span class="tag-survey">SURVEY INTERNAL — dinilai rekan</span></div>
                    <div class="vbox {{ is_null($otomatis['kerjasama'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kerjasama'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kerjasama'] !== null ? number_format($otomatis['kerjasama'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_survey_int'] }}</b><br>
                            Rata-rata rating pertanyaan kerjasama/tim ÷ 5 × 100
                        </span>
                    </div>
                </div>

                {{-- Inovatif --}}
                <div style="grid-column:1/-1">
                    <div class="fl">Inovatif <span class="tag-survey">SURVEY INTERNAL — dinilai rekan</span></div>
                    <div class="vbox {{ is_null($otomatis['inovatif'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['inovatif'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['inovatif'] !== null ? number_format($otomatis['inovatif'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_survey_int'] }}</b><br>
                            Rata-rata rating pertanyaan inovatif/ide ÷ 5 × 100
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ II.A INDIKATOR HASIL ══ --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="13" height="13" fill="none" stroke="#059669" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span class="sek-title">II.A Indikator Hasil</span>
            <span class="sek-sub">— hasil kerja yang dicapai petugas</span>
        </div>
        <div class="sek-body">
            <div class="fgrid">

                {{-- Kepastian Waktu --}}
                <div>
                    <div class="fl">Kepastian Waktu <span class="tag-auto">AUTO — Kehadiran Pembinaan</span></div>
                    <div class="vbox {{ is_null($otomatis['kepastian_waktu'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kepastian_waktu'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kepastian_waktu'] !== null ? number_format($otomatis['kepastian_waktu'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_kepastian'] }}</b><br>
                            Rumus: pembinaan dikumpulkan ÷ total pembinaan × 100
                        </span>
                    </div>
                </div>

                {{-- Akurasi Data --}}
                <div>
                    <div class="fl">
                        Akurasi Data
                        @if($otomatis['akurasi'] !== null)
                            <span class="tag-auto">AUTO — Quiz Materi</span>
                        @else
                            <span class="tag-warn">Belum ada quiz dikerjakan</span>
                        @endif
                    </div>
                    <div class="vbox {{ is_null($otomatis['akurasi'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['akurasi'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['akurasi'] !== null ? number_format($otomatis['akurasi'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_akurasi'] }}</b><br>
                            Diambil dari quiz materi admin + quiz materi triwulan koordinator yang sudah dikerjakan petugas
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ II.B INDIKATOR PROSES ══ --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="13" height="13" fill="none" stroke="#b45309" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span class="sek-title">II.B Indikator Proses</span>
            <span class="sek-sub">— cara &amp; proses pelayanan petugas</span>
        </div>
        <div class="sek-body">
            <div class="fgrid">

                {{-- Tanggungjawab --}}
                <div>
                    <div class="fl">Tanggungjawab Pelayanan <span class="tag-auto">AUTO — Laporan Harian</span></div>
                    <div class="vbox {{ is_null($otomatis['tanggungjawab'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['tanggungjawab'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['tanggungjawab'] !== null ? number_format($otomatis['tanggungjawab'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_laporan'] }}</b><br>
                            Rumus: laporan disubmit ÷ jumlah shift dijadwalkan × 100
                        </span>
                    </div>
                </div>

                {{-- Kesopanan & Keramahan --}}
                <div>
                    <div class="fl">Kesopanan &amp; Keramahan <span class="tag-survey">SURVEY INTERNAL — dinilai rekan</span></div>
                    <div class="vbox {{ is_null($otomatis['kesopanan_keramahan'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kesopanan_keramahan'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kesopanan_keramahan'] !== null ? number_format($otomatis['kesopanan_keramahan'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_survey_int'] }}</b><br>
                            Rata-rata rating pertanyaan kategori Kesopanan &amp; Keramahan ÷ 5 × 100
                        </span>
                    </div>
                </div>

                {{-- Penampilan Atribut --}}
                <div style="grid-column:1/-1">
                    <div class="fl">Penampilan &amp; Kesesuaian Atribut <span class="tag-auto">AUTO — Checklist Harian</span></div>
                    <div class="vbox {{ is_null($otomatis['kesesuaian_atribut'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kesesuaian_atribut'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kesesuaian_atribut'] !== null ? number_format($otomatis['kesesuaian_atribut'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_checklist'] }}</b><br>
                            Rata-rata % item checklist yang dicentang petugas per shift
                        </span>
                    </div>
                </div>

            </div>
        </div>
    </div>

    {{-- ══ III. MUTU PELAYANAN ══ --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="13" height="13" fill="none" stroke="#7c3aed" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span class="sek-title">III. Mutu Pelayanan</span>
            <span class="sek-sub">— kualitas layanan yang diterima pengguna</span>
        </div>
        <div class="sek-body">
            <div class="fgrid">

                {{-- Kepatuhan SOP --}}
                <div>
                    <div class="fl">Kepatuhan SOP <span class="tag-auto">AUTO — Laporan Disetujui</span></div>
                    <div class="vbox {{ is_null($otomatis['kepatuhan_sop'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kepatuhan_sop'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kepatuhan_sop'] !== null ? number_format($otomatis['kepatuhan_sop'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_sop'] }}</b><br>
                            Rumus: laporan approved ÷ laporan submitted × 100
                        </span>
                    </div>
                </div>

                {{-- Kepuasan Pelanggan --}}
                <div>
                    <div class="fl">
                        Kepuasan Pelanggan
                        @if(($otomatis['sumber_kepuasan'] ?? 'kosong') === 'skm_eksternal')
                            <span class="tag-auto">AUTO — SKM Eksternal</span>
                        @elseif(($otomatis['sumber_kepuasan'] ?? 'kosong') === 'manual')
                            <span class="tag-warn">MANUAL — Input Koordinator</span>
                        @else
                            <span class="tag-warn">Belum ada SKM masuk</span>
                        @endif
                    </div>
                    <div class="vbox {{ is_null($otomatis['kepuasan_pelanggan'] ?? null) ? 'null-data' : '' }}">
                        <span class="vnum {{ is_null($otomatis['kepuasan_pelanggan'] ?? null) ? 'gray' : '' }}">
                            {{ $otomatis['kepuasan_pelanggan'] !== null ? number_format($otomatis['kepuasan_pelanggan'],2) : '—' }}
                        </span>
                        <span class="vsrc">
                            <b>{{ $otomatis['info_survey_ext'] }}</b><br>
                            @if(($otomatis['sumber_kepuasan'] ?? 'kosong') === 'skm_eksternal')
                                Rumus: rata-rata rating pengunjung ÷ 5 × 100
                            @elseif(($otomatis['sumber_kepuasan'] ?? 'kosong') === 'manual')
                                Nilai diisi manual oleh koordinator
                            @else
                                Isi nilai manual di bawah jika ada data SKM dari luar sistem
                            @endif
                        </span>
                    </div>

                    {{-- Input manual: hanya muncul jika belum ada SKM dari pengunjung --}}
                    @if(($otomatis['sumber_kepuasan'] ?? 'kosong') !== 'skm_eksternal')
                    <div style="margin-top:8px;padding:10px 12px;background:#fffbeb;border:1px dashed #f59e0b;border-radius:5px;">
                        <label style="font-size:11px;font-weight:600;color:#92400e;display:block;margin-bottom:5px;">
                            ✏ Input Manual Kepuasan Pelanggan
                        </label>
                        <div style="display:flex;align-items:center;gap:8px;">
                            <input type="number" name="kepuasan_manual"
                                min="0" max="100" step="0.01"
                                value="{{ old('kepuasan_manual', $evaluasi?->nilai_kepuasan_manual) }}"
                                placeholder="0 – 100"
                                style="width:110px;border:1px solid #f59e0b;border-radius:4px;padding:5px 8px;font-size:13px;font-family:'IBM Plex Mono',monospace;color:var(--ink);">
                            <span style="font-size:11px;color:#92400e;">Kosongkan jika tidak ada data → nilai tetap —</span>
                        </div>
                    </div>
                    @else
                    <input type="hidden" name="kepuasan_manual" value="">
                    @endif
                </div>

            </div>
        </div>
    </div>

    {{-- ══ CATATAN & SIMPAN ══ --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="13" height="13" fill="none" stroke="var(--ink3)" stroke-width="1.5" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/></svg>
            <span class="sek-title">Catatan &amp; Simpan</span>
        </div>
        <div class="sek-body" style="display:grid;grid-template-columns:1fr 200px;gap:16px;align-items:start;">
            <div>
                <label style="font-size:11.5px;font-weight:500;color:var(--ink2);display:block;margin-bottom:6px;">
                    Catatan untuk Petugas <span style="font-size:10px;color:var(--ink3)">(opsional)</span>
                </label>
                <textarea name="catatan" rows="3"
                    placeholder="Tuliskan catatan, saran, atau apresiasi untuk petugas ini…"
                    style="width:100%;border:1px solid var(--rule);border-radius:5px;padding:8px 10px;font-size:12px;font-family:inherit;resize:vertical;color:var(--ink);">{{ old('catatan', $evaluasi?->catatan) }}</textarea>
            </div>
            <div style="display:flex;flex-direction:column;gap:8px;padding-top:20px;">
                <button type="submit" name="status" value="selesai" class="btn-ok">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    Finalisasi
                </button>
                <button type="submit" name="status" value="draft" class="btn-draft">
                    Simpan Draft
                </button>
                <div style="font-size:10px;color:var(--ink3);text-align:center;">Draft tidak masuk grafik rekap</div>
            </div>
        </div>
    </div>

</form>
@endsection