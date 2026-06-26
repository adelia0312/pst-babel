@extends('layouts.petugas')

@section('title', 'Rincian Nilai — ' . \App\Helpers\PeriodeHelper::isoLabel($evaluasi->periode))

@push('styles')
<style>
.grade-sb{background:#dcfce7;color:#166534;} .grade-b{background:#dbeafe;color:#1e40af;}
.grade-c{background:#fef3c7;color:#92400e;} .grade-k{background:#ffedd5;color:#9a3412;}
.grade-sk{background:#fee2e2;color:#991b1b;}
.grade-sb-bg{background:#dcfce7;color:#166534;} .grade-b-bg{background:#dbeafe;color:#1e40af;}
.grade-c-bg{background:#fef3c7;color:#92400e;} .grade-k-bg{background:#ffedd5;color:#9a3412;}
.grade-sk-bg{background:#fee2e2;color:#991b1b;} .grade-none-bg{background:var(--wash2);color:var(--ink3);}

.hero{background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:20px 22px;display:flex;align-items:center;gap:18px;margin-bottom:20px;flex-wrap:wrap;}
.hg{width:64px;height:64px;border-radius:10px;flex-shrink:0;display:flex;flex-direction:column;align-items:center;justify-content:center;font-family:'IBM Plex Mono',monospace;}
.hg-l{font-size:28px;font-weight:700;line-height:1;}
.hg-s{font-size:9px;font-weight:600;letter-spacing:.8px;opacity:.75;text-transform:uppercase;}
.hero-total{font-size:34px;font-weight:300;letter-spacing:-1px;font-family:'IBM Plex Mono',monospace;color:var(--ink);line-height:1;}
.hero-meta{font-size:11.5px;color:var(--ink3);margin-top:5px;}

/* Section card */
.sek{background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:14px;}
.sek-head{padding:10px 16px;border-bottom:1px solid var(--rule);background:var(--wash);display:flex;align-items:center;gap:8px;}
.sek-head-txt{font-size:10px;font-weight:700;text-transform:uppercase;letter-spacing:1px;color:var(--ink3);}
.sek-rata{padding:10px 16px 14px;border-bottom:1px solid var(--rule);display:flex;align-items:baseline;gap:8px;}
.sek-rata-val{font-size:26px;font-weight:300;font-family:'IBM Plex Mono',monospace;letter-spacing:-.5px;color:var(--ink);}
.sek-rata-lbl{font-size:11px;color:var(--ink3);}

/* Baris nilai */
.nr{display:flex;align-items:center;padding:10px 16px;border-bottom:1px solid var(--rule);gap:10px;}
.nr:last-child{border-bottom:none;}
.nr-key{flex:1;font-size:12px;color:var(--ink2);}
.nr-src{font-size:10px;color:var(--ink3);margin-top:2px;line-height:1.4;}
.nr-bar{width:70px;height:4px;background:var(--wash2);border-radius:2px;overflow:hidden;flex-shrink:0;}
.nr-fill{height:100%;border-radius:2px;}
.nr-num{font-family:'IBM Plex Mono',monospace;font-size:13px;font-weight:600;color:var(--ink);width:44px;text-align:right;flex-shrink:0;}
.nr-num.nil{color:var(--ink3);font-weight:300;}
.b-auto{font-size:9px;background:var(--blue-lt);color:var(--blue);padding:1px 5px;border-radius:2px;font-weight:700;}
.b-survey{font-size:9px;background:#d1fae5;color:#065f46;padding:1px 5px;border-radius:2px;font-weight:700;}

.grid2{display:grid;grid-template-columns:1fr 1fr;gap:14px;}
@media(max-width:660px){.grid2{grid-template-columns:1fr;}}

.btn-back{height:30px;padding:0 14px;border-radius:5px;border:1px solid var(--rule);background:var(--surface);color:var(--ink2);font-size:11.5px;font-family:inherit;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.btn-pdf{height:30px;padding:0 14px;border-radius:5px;border:1px solid #c4b5fd;background:#f5eeff;color:#6d28d9;font-size:11.5px;font-family:inherit;font-weight:500;cursor:pointer;text-decoration:none;display:inline-flex;align-items:center;gap:6px;}
.status-selesai{background:var(--green-lt);color:var(--green);}
.status-draft{background:var(--amber-lt);color:var(--amber);}
.pill{display:inline-flex;align-items:center;padding:2px 7px;border-radius:4px;font-size:10.5px;font-weight:600;}
</style>
@endpush

@section('breadcrumb')
    <a href="{{ url('/petugas/dashboard') }}">Dashboard</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('petugas.penilaian.index') }}">Nilai Saya</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>{{ \App\Helpers\PeriodeHelper::isoLabel($evaluasi->periode) }}</strong>
@endsection

@section('content')
@php
    $grBg = match($evaluasi->grade ?? '') {
        'SB'=>'grade-sb-bg','B'=>'grade-b-bg','C'=>'grade-c-bg',
        'K'=>'grade-k-bg','SK'=>'grade-sk-bg',default=>'grade-none-bg',
    };
    $gradeLbl = \App\Models\EvaluasiPetugas::labelGrade($evaluasi->grade ?? '-');
@endphp

{{-- Hero --}}
<div class="hero">
    <div class="hg {{ $grBg }}">
        <div class="hg-l">{{ $evaluasi->grade ?? '—' }}</div>
        <div class="hg-s">{{ $gradeLbl }}</div>
    </div>
    <div style="flex:1">
        <div class="hero-total">{{ $evaluasi->jumlah_nilai ? number_format($evaluasi->jumlah_nilai,2) : '—' }}</div>
        <div class="hero-meta">
            {{ \App\Helpers\PeriodeHelper::isoLabel($evaluasi->periode) }}
            &middot;
            <span class="pill {{ $evaluasi->status === 'selesai' ? 'status-selesai' : 'status-draft' }}" style="font-size:10px">
                {{ $evaluasi->status === 'selesai' ? 'Selesai' : 'Draft' }}
            </span>
            @if($evaluasi->tanggal_evaluasi)
            &middot; Dinilai {{ $evaluasi->tanggal_evaluasi->isoFormat('D MMMM YYYY') }}
            @endif
        </div>
    </div>
    <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
        @if($evaluasi->status === 'selesai' && $evaluasi->jumlah_nilai !== null)
        <a href="{{ route('petugas.penilaian.pdf', $evaluasi->periode) }}"
           target="_blank" class="btn-pdf" title="Unduh PDF">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/><rect x="6" y="14" width="12" height="8"/></svg>
            Unduh PDF
        </a>
        @endif
        <a href="{{ route('petugas.penilaian.index', ['periode'=>$evaluasi->periode]) }}" class="btn-back">
            <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
            Kembali
        </a>
    </div>
</div>

{{-- 4 Section Cards --}}
<div class="grid2">

    {{-- I. SIKAP KERJA --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="12" height="12" fill="none" stroke="#1a56db" stroke-width="1.5" viewBox="0 0 24 24"><path d="M20.84 4.61a5.5 5.5 0 00-7.78 0L12 5.67l-1.06-1.06a5.5 5.5 0 00-7.78 7.78l1.06 1.06L12 21.23l7.78-7.78 1.06-1.06a5.5 5.5 0 000-7.78z"/></svg>
            <span class="sek-head-txt">I. Sikap Kerja</span>
        </div>
        <div class="sek-rata">
            <span class="sek-rata-val" style="color:#1a56db">{{ $evaluasi->rata_sikap_kerja ? number_format($evaluasi->rata_sikap_kerja,2) : '—' }}</span>
            <span class="sek-rata-lbl">rata-rata komponen</span>
        </div>
        @php $rows = [
            ['Kehadiran',     $evaluasi->nilai_kehadiran,  'auto',   'Dari scan absensi QR tiap shift'],
            ['Disiplin Waktu',$evaluasi->nilai_disiplin,   'auto',   'Dari ketepatan waktu scan masuk'],
            ['Komunikasi',    $evaluasi->nilai_komunikasi, 'survey', 'Dari survey internal antar petugas'],
            ['Kerjasama',     $evaluasi->nilai_kerjasama,  'survey', 'Dari survey internal antar petugas'],
            ['Inovatif',      $evaluasi->nilai_inovatif,   'survey', 'Dari survey internal antar petugas'],
        ]; @endphp
        @foreach($rows as [$lbl,$val,$type,$src])
        <div class="nr">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ink2);">
                    {{ $lbl }}
                    @if($type==='auto') <span class="b-auto">AUTO</span>
                    @else <span class="b-survey">SURVEY</span> @endif
                </div>
                <div class="nr-src">{{ $src }}</div>
            </div>
            <div class="nr-bar"><div class="nr-fill" style="width:{{ $val ?? 0 }}%;background:#1a56db;opacity:.7"></div></div>
            <div class="nr-num {{ is_null($val) ? 'nil' : '' }}">{{ $val ? number_format($val,1) : '—' }}</div>
        </div>
        @endforeach
    </div>

    {{-- II.A INDIKATOR HASIL --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="12" height="12" fill="none" stroke="#0a7c4e" stroke-width="1.5" viewBox="0 0 24 24"><polyline points="22 12 18 12 15 21 9 3 6 12 2 12"/></svg>
            <span class="sek-head-txt">II.A Indikator Hasil</span>
        </div>
        <div class="sek-rata">
            <span class="sek-rata-val" style="color:#0a7c4e">{{ $evaluasi->rata_indikator_hasil ? number_format($evaluasi->rata_indikator_hasil,2) : '—' }}</span>
            <span class="sek-rata-lbl">rata-rata komponen</span>
        </div>
        @php $rows = [
            ['Kepastian Waktu','Dari kehadiran pembinaan triwulan',$evaluasi->nilai_kepastian_waktu,'auto'],
            ['Akurasi Data',   'Dari nilai post-test quiz pembinaan',$evaluasi->nilai_akurasi,'auto'],
        ]; @endphp
        @foreach($rows as [$lbl,$src,$val,$type])
        <div class="nr">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ink2);">
                    {{ $lbl }} <span class="b-auto">AUTO</span>
                </div>
                <div class="nr-src">{{ $src }}</div>
            </div>
            <div class="nr-bar"><div class="nr-fill" style="width:{{ $val ?? 0 }}%;background:#0a7c4e;opacity:.7"></div></div>
            <div class="nr-num {{ is_null($val) ? 'nil' : '' }}">{{ $val ? number_format($val,1) : '—' }}</div>
        </div>
        @endforeach
    </div>

    {{-- II.B INDIKATOR PROSES --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="12" height="12" fill="none" stroke="#b45309" stroke-width="1.5" viewBox="0 0 24 24"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
            <span class="sek-head-txt">II.B Indikator Proses</span>
        </div>
        <div class="sek-rata">
            <span class="sek-rata-val" style="color:#b45309">{{ $evaluasi->rata_indikator_proses ? number_format($evaluasi->rata_indikator_proses,2) : '—' }}</span>
            <span class="sek-rata-lbl">rata-rata komponen</span>
        </div>
        @php $rows = [
            ['Tanggungjawab Pelayanan', 'Dari laporan shift disubmit / target sesi',  $evaluasi->nilai_tanggungjawab,      'auto'],
            ['Kesopanan & Keramahan',   'Dari survey internal antar petugas',          $evaluasi->nilai_kesopanan_keramahan, 'survey'],
            ['Penampilan & Atribut',    'Dari checklist harian tiap shift',            $evaluasi->nilai_kesesuaian_atribut, 'auto'],
        ]; @endphp
        @foreach($rows as [$lbl,$src,$val,$type])
        <div class="nr">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ink2);">
                    {{ $lbl }}
                    @if($type==='auto') <span class="b-auto">AUTO</span>
                    @else <span class="b-survey">SURVEY</span> @endif
                </div>
                <div class="nr-src">{{ $src }}</div>
            </div>
            <div class="nr-bar"><div class="nr-fill" style="width:{{ $val ?? 0 }}%;background:#b45309;opacity:.7"></div></div>
            <div class="nr-num {{ is_null($val) ? 'nil' : '' }}">{{ $val ? number_format($val,1) : '—' }}</div>
        </div>
        @endforeach
    </div>

    {{-- III. MUTU PELAYANAN --}}
    <div class="sek">
        <div class="sek-head">
            <svg width="12" height="12" fill="none" stroke="#7c3aed" stroke-width="1.5" viewBox="0 0 24 24"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>
            <span class="sek-head-txt">III. Mutu Pelayanan</span>
        </div>
        <div class="sek-rata">
            <span class="sek-rata-val" style="color:#7c3aed">{{ $evaluasi->rata_mutu_pelayanan ? number_format($evaluasi->rata_mutu_pelayanan,2) : '—' }}</span>
            <span class="sek-rata-lbl">rata-rata komponen</span>
        </div>
        @php $rows = [
            ['Kepatuhan SOP',      'Dari checklist harian tiap shift',          $evaluasi->nilai_kepatuhan_sop,      'auto'],
            ['Kepuasan Pelanggan', 'Dari rating SKM pengunjung (rata/5 × 100)', $evaluasi->nilai_kepuasan_pelanggan, 'auto'],
        ]; @endphp
        @foreach($rows as [$lbl,$src,$val,$type])
        <div class="nr">
            <div style="flex:1">
                <div style="display:flex;align-items:center;gap:6px;font-size:12px;color:var(--ink2);">
                    {{ $lbl }} <span class="b-auto">AUTO</span>
                </div>
                <div class="nr-src">{{ $src }}</div>
            </div>
            <div class="nr-bar"><div class="nr-fill" style="width:{{ $val ?? 0 }}%;background:#7c3aed;opacity:.7"></div></div>
            <div class="nr-num {{ is_null($val) ? 'nil' : '' }}">{{ $val ? number_format($val,1) : '—' }}</div>
        </div>
        @endforeach
    </div>

</div>

{{-- Catatan Koordinator --}}
@if($evaluasi->catatan)
<div style="background:var(--surface);border:1px solid var(--rule);border-radius:8px;padding:16px 18px;margin-top:4px;">
    <div style="font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:1px;color:var(--ink3);margin-bottom:8px;">Catatan dari Koordinator</div>
    <p style="font-size:12.5px;color:var(--ink2);line-height:1.6;margin:0;">{{ $evaluasi->catatan }}</p>
</div>
@endif

{{-- Keterangan badge --}}
<div style="margin-top:14px;display:flex;gap:10px;flex-wrap:wrap;align-items:center;">
    <span style="font-size:10.5px;color:var(--ink3);font-weight:500;">Keterangan:</span>
    <span style="display:flex;align-items:center;gap:4px;font-size:10.5px;color:var(--ink3);"><span class="b-auto">AUTO</span> Dihitung otomatis oleh sistem</span>
    <span style="display:flex;align-items:center;gap:4px;font-size:10.5px;color:var(--ink3);"><span class="b-survey">SURVEY</span> Dari penilaian antar petugas</span>
</div>
@endsection