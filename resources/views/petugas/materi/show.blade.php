@extends('layouts.petugas')

@section('title', $tugas->judul)

@section('breadcrumb')
    <span>PST</span>
    <span>›</span>
    <a href="{{ route('petugas.materi') }}" style="color:var(--blue);text-decoration:none">Materi &amp; Tugas</a>
    <span>›</span>
    <strong>{{ Str::limit($tugas->judul, 32) }}</strong>
@endsection

@section('content')

<style>
    /* ── Back ── */
    .back-link {
        display:inline-flex; align-items:center; gap:6px; margin-bottom:16px;
        font-size:12px; color:var(--ink3); text-decoration:none; transition:color .12s;
    }
    .back-link:hover { color:var(--blue); }

    /* ── Alert ── */
    .alert-success {
        display:flex; gap:10px; align-items:flex-start; padding:12px 16px;
        background:var(--green-lt); border:1px solid rgba(10,124,78,.2); border-radius:7px;
        font-size:12.5px; color:var(--green); margin-bottom:16px;
    }
    .alert-error {
        display:flex; gap:10px; align-items:flex-start; padding:12px 16px;
        background:var(--red-lt); border:1px solid rgba(192,57,43,.2); border-radius:7px;
        font-size:12.5px; color:var(--red); margin-bottom:16px;
    }

    /* ── Submitted banner ── */
    .submitted-banner {
        display:flex; align-items:flex-start; gap:12px; padding:12px 16px;
        background:var(--green-lt); border:1px solid rgba(10,124,78,.2); border-radius:8px;
        margin-bottom:16px;
    }
    .submitted-banner svg { flex-shrink:0; color:var(--green); margin-top:1px; }
    .submitted-banner h4  { font-size:13px; font-weight:600; color:var(--green); margin-bottom:2px; }
    .submitted-banner p   { font-size:12px; color:var(--green); opacity:.8; }

    /* ── Hasil bar (compact, di atas) ── */
    .hasil-bar {
        background:var(--surface);
        border:1px solid var(--rule);
        border-radius:10px;
        margin-bottom:16px;
        overflow:hidden;
    }
    .hasil-bar-head {
        display:flex; align-items:center; gap:8px;
        padding:9px 16px;
        background:var(--wash);
        border-bottom:1px solid var(--rule);
    }
    .hasil-bar-head-dot { width:7px; height:7px; border-radius:50%; background:var(--green); flex-shrink:0; }
    .hasil-bar-head-title { font-size:11.5px; font-weight:600; color:var(--ink2); text-transform:uppercase; letter-spacing:.5px; }

    .hasil-bar-body {
        display:flex; align-items:center; gap:0; flex-wrap:wrap;
    }
    .hasil-cell {
        display:flex; flex-direction:column; gap:2px;
        padding:12px 20px;
        border-right:1px solid var(--rule);
        min-width:120px;
    }
    .hasil-cell:last-child { border-right:none; }
    .hasil-cell-label { font-size:10.5px; color:var(--ink3); font-weight:500; }
    .hasil-cell-value { font-size:13px; font-weight:600; color:var(--ink); }

    /* Skor pill */
    .skor-pill {
        display:inline-flex; align-items:baseline; gap:4px;
        font-size:22px; font-weight:800; font-family:'IBM Plex Mono',monospace;
        color:var(--green);
    }
    .skor-pill-sub { font-size:11px; font-weight:500; color:var(--ink3); }

    /* ── Card base ── */
    .card {
        background:var(--surface); border:1px solid var(--rule); border-radius:8px; overflow:hidden;
        margin-bottom:14px;
    }
    .card:last-child { margin-bottom:0; }
    .card-head {
        padding:12px 18px; border-bottom:1px solid var(--rule);
        display:flex; align-items:center; gap:8px;
        font-size:11.5px; font-weight:600; color:var(--ink2); text-transform:uppercase; letter-spacing:.6px;
    }
    .card-head .dot { width:7px; height:7px; border-radius:50%; flex-shrink:0; }
    .card-body { padding:18px; }

    /* ── Tugas info ── */
    .tugas-title { font-size:17px; font-weight:700; color:var(--ink); line-height:1.35; margin-bottom:10px; }
    .meta-row { display:flex; flex-wrap:wrap; gap:8px; margin-bottom:14px; }
    .badge {
        display:inline-flex; align-items:center; gap:5px; font-size:11px;
        font-weight:500; padding:3px 10px; border-radius:20px;
    }
    .badge-blue   { background:var(--blue-lt); color:var(--blue); }
    .badge-green  { background:var(--green-lt); color:var(--green); }
    .badge-amber  { background:var(--amber-lt); color:var(--amber); }
    .badge-red    { background:var(--red-lt);   color:var(--red);   }
    .tugas-desc { font-size:13px; color:var(--ink2); line-height:1.7; }

    /* ── Resource block ── */
    .resource-block {
        display:flex; align-items:center; gap:10px; padding:12px 14px;
        background:var(--wash); border:1px solid var(--rule); border-radius:6px; margin-top:12px;
    }
    .resource-icon {
        width:34px; height:34px; border-radius:5px; flex-shrink:0;
        display:flex; align-items:center; justify-content:center;
        background:var(--blue-lt); color:var(--blue);
    }
    .resource-name  { font-size:12.5px; font-weight:500; color:var(--ink); }
    .resource-sub   { font-size:11px; color:var(--ink3); }
    .btn-download {
        margin-left:auto; display:inline-flex; align-items:center; gap:5px;
        font-size:11.5px; font-weight:500; padding:6px 12px; border-radius:5px;
        background:var(--blue); color:#fff; text-decoration:none; white-space:nowrap;
        transition:opacity .15s;
    }
    .btn-download:hover { opacity:.85; }

    /* ── Quiz ── */
    .quiz-list { display:flex; flex-direction:column; gap:20px; }
    .soal-item { display:flex; flex-direction:column; gap:10px; }
    .soal-num  { font-size:10px; font-weight:600; text-transform:uppercase; letter-spacing:1px; color:var(--ink3); margin-bottom:2px; }
    .soal-teks { font-size:13px; font-weight:500; color:var(--ink); line-height:1.5; }
    .opsi-list { display:flex; flex-direction:column; gap:6px; }
    .opsi-label {
        display:flex; align-items:center; gap:10px; padding:9px 12px;
        border:1px solid var(--rule); border-radius:6px; cursor:pointer;
        font-size:12.5px; color:var(--ink2); transition:all .12s; user-select:none;
    }
    .opsi-label:has(input:checked)        { border-color:var(--blue); background:var(--blue-lt); color:var(--blue); }
    .opsi-label:has(input:checked).benar  { border-color:var(--green); background:var(--green-lt); color:var(--green); }
    .opsi-label:has(input:checked).salah  { border-color:var(--red); background:var(--red-lt); color:var(--red); }
    .opsi-label.benar-kunci               { border-color:var(--green); background:var(--green-lt); color:var(--green); }
    .opsi-label input[type="radio"] { accent-color:var(--blue); flex-shrink:0; }
    .opsi-key {
        width:20px; height:20px; border-radius:4px; flex-shrink:0; display:flex;
        align-items:center; justify-content:center; font-size:10px; font-weight:700;
        background:var(--wash2); color:var(--ink3);
    }

    /* ── Form submit ── */
    .form-section { display:flex; flex-direction:column; gap:10px; }
    .form-label { font-size:11.5px; font-weight:600; color:var(--ink2); margin-bottom:3px; display:block; }
    .form-hint  { font-size:11px; color:var(--ink3); margin-top:2px; }
    .file-drop {
        border:2px dashed var(--rule); border-radius:6px; padding:20px;
        text-align:center; cursor:pointer; transition:border-color .15s, background .15s;
    }
    .file-drop:hover { border-color:var(--blue); background:var(--blue-lt); }
    .file-drop input[type="file"] { display:none; }
    .file-drop-label { font-size:12px; color:var(--ink3); pointer-events:none; }
    .file-selected { font-size:12px; color:var(--green); font-weight:500; }
    .form-input {
        width:100%; padding:9px 12px; border:1px solid var(--rule); border-radius:6px;
        font-size:12.5px; font-family:inherit; color:var(--ink); background:var(--surface);
        outline:none; transition:border-color .15s; box-sizing:border-box;
    }
    .form-input:focus { border-color:var(--blue); }
    .btn-submit {
        width:100%; padding:10px; border-radius:6px; border:none; cursor:pointer;
        font-size:13px; font-weight:600; font-family:inherit;
        background:var(--blue); color:#fff; transition:opacity .15s;
        display:flex; align-items:center; justify-content:center; gap:7px;
    }
    .btn-submit:hover { opacity:.88; }
    .btn-submit:disabled { opacity:.5; cursor:not-allowed; }

    /* File yang sudah dikumpulkan */
    .file-info-block {
        padding:10px 12px; background:var(--wash); border:1px solid var(--rule); border-radius:6px;
    }
    .file-info-label { font-size:11px; color:var(--ink3); margin-bottom:4px; }
</style>

{{-- Back --}}
<a href="{{ route('petugas.materi') }}" class="back-link">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="15 18 9 12 15 6"/></svg>
    Kembali ke Daftar Tugas
</a>

{{-- Alert --}}
@if(session('success'))
<div class="alert-success">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if($errors->any())
<div class="alert-error">
    <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>{{ $errors->first() }}</div>
</div>
@endif

{{-- Submitted banner --}}
@if($sudahSubmit)
<div class="submitted-banner">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    <div>
        <h4>Tugas sudah dikumpulkan</h4>
        <p>
            Dikumpulkan pada {{ $jawaban->updated_at ? $jawaban->updated_at->format('d M Y, H:i') : '—' }}
            @if($tugas->deadline)
                —
                @if($jawaban->updated_at && $jawaban->updated_at->startOfDay()->gt($tugas->deadline))
                    <strong>Terlambat</strong> dari deadline {{ $tugas->deadline->format('d M Y') }}
                @else
                    <strong>Tepat waktu</strong>
                @endif
            @endif
        </p>
    </div>
</div>

{{-- ── HASIL BAR compact di atas ── --}}
<div class="hasil-bar">
    <div class="hasil-bar-head">
        <span class="hasil-bar-head-dot"></span>
        <span class="hasil-bar-head-title">Hasil Pengumpulan</span>
    </div>
    <div class="hasil-bar-body">
        @if($jawaban->skor !== null)
        <div class="hasil-cell">
            <span class="hasil-cell-label">Skor Quiz</span>
            <div class="skor-pill">
                {{ $jawaban->skor }}
                <span class="skor-pill-sub">/ 100</span>
            </div>
        </div>
        @endif
        <div class="hasil-cell">
            <span class="hasil-cell-label">Dikumpulkan</span>
            <span class="hasil-cell-value">{{ $jawaban->updated_at ? $jawaban->updated_at->format('d M Y') : '—' }}</span>
        </div>
        <div class="hasil-cell">
            <span class="hasil-cell-label">Pukul</span>
            <span class="hasil-cell-value">{{ $jawaban->updated_at ? $jawaban->updated_at->format('H:i') : '—' }}</span>
        </div>
        <div class="hasil-cell">
            @php $terlambat = $tugas->deadline && $jawaban->updated_at && $jawaban->updated_at->startOfDay()->gt($tugas->deadline); @endphp
            <span class="hasil-cell-label">Status</span>
            <span class="hasil-cell-value" style="color:{{ $terlambat ? 'var(--amber)' : 'var(--green)' }}">
                {{ $terlambat ? '⚠ Terlambat' : '✔ Tepat Waktu' }}
            </span>
        </div>
        @if($jawaban->file)
        <div class="hasil-cell" style="flex:1">
            <span class="hasil-cell-label">File Kamu</span>
            <a href="{{ asset('storage/' . $jawaban->file) }}" target="_blank"
               style="font-size:12.5px;color:var(--blue);text-decoration:none;display:inline-flex;align-items:center;gap:4px;font-weight:500">
                <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                Download
            </a>
        </div>
        @endif
        @if($jawaban->link)
        <div class="hasil-cell" style="flex:1">
            <span class="hasil-cell-label">Link Kamu</span>
            <a href="{{ $jawaban->link }}" target="_blank" rel="noopener"
               style="font-size:12px;color:var(--blue);text-decoration:none;font-weight:500;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;max-width:180px;display:block">
                {{ $jawaban->link }}
            </a>
        </div>
        @endif
        {{-- Tombol perbarui --}}
        @if(! ($tugas->deadline && now()->gt($tugas->deadline->addDay())))
        <div class="hasil-cell" style="margin-left:auto;border-right:none;justify-content:center">
            <button onclick="document.getElementById('resubmit-section').style.display='block';this.closest('.hasil-bar').style.display='none'"
                style="padding:6px 14px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);
                       font-size:12px;font-weight:500;color:var(--ink2);cursor:pointer;font-family:inherit;white-space:nowrap">
                ✏ Perbarui
            </button>
        </div>
        @endif
    </div>
</div>
@endif

{{-- ── KONTEN UTAMA (full width) ── --}}
<div style="display:flex;flex-direction:column;gap:14px;">

    {{-- Info tugas --}}
    <div class="card">
        <div class="card-head">
            <span class="dot" style="background:var(--blue)"></span>
            Informasi Tugas
        </div>
        <div class="card-body">
            <div class="tugas-title">{{ $tugas->judul }}</div>
            <div class="meta-row">
                @if($tugas->deadline)
                    @php $dlLewat = now()->gt($tugas->deadline); @endphp
                    <span class="badge {{ $dlLewat ? 'badge-red' : 'badge-amber' }}">
                        📅 Deadline {{ $tugas->deadline->format('d M Y') }}{{ $dlLewat ? ' (Lewat)' : '' }}
                    </span>
                @endif
                @if($tugas->quiz->count())
                    <span class="badge badge-blue">🧠 {{ $tugas->quiz->count() }} soal quiz</span>
                @endif
            </div>
            @if($tugas->deskripsi)
                <div class="tugas-desc">{{ $tugas->deskripsi }}</div>
            @endif

            @foreach($tugas->semuaFile() as $f)
                <div class="resource-block" style="margin-top:8px">
                    <div class="resource-icon">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/><polyline points="14 2 14 8 20 8"/></svg>
                    </div>
                    <div>
                        <div class="resource-name">{{ $f->nama_asli }}</div>
                        <div class="resource-sub">File disiapkan oleh Admin</div>
                    </div>
                    <a href="{{ asset('storage/' . $f->file) }}" target="_blank" class="btn-download">
                        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Download
                    </a>
                </div>
            @endforeach

            @if($tugas->link)
                <div class="resource-block" style="margin-top:8px">
                    <div class="resource-icon" style="background:var(--amber-lt);color:var(--amber)">
                        <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/><path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/></svg>
                    </div>
                    <div style="flex:1;overflow:hidden">
                        <div class="resource-name">Referensi Link</div>
                        <div class="resource-sub" style="overflow:hidden;text-overflow:ellipsis;white-space:nowrap">{{ $tugas->link }}</div>
                    </div>
                    <a href="{{ $tugas->link }}" target="_blank" rel="noopener" class="btn-download" style="background:var(--amber)">Buka →</a>
                </div>
            @endif
        </div>
    </div>

    {{-- Quiz --}}
    @if($tugas->quiz->count())
    <div class="card">
        <div class="card-head">
            <span class="dot" style="background:var(--amber)"></span>
            Quiz — {{ $tugas->quiz->count() }} Soal
            @if($sudahSubmit && $jawaban->skor !== null)
                <span style="margin-left:auto;font-size:11px;background:var(--green-lt);color:var(--green);padding:2px 8px;border-radius:4px;font-weight:700">
                    Skor: {{ $jawaban->skor }}/100
                </span>
            @endif
        </div>
        <div class="card-body">
            <div class="quiz-list">
                @foreach($tugas->quiz as $i => $soal)
                    @php
                        $pilihanPetugas = $jawabanQuizMap->get($soal->id)?->jawaban ?? null;
                        $kunciJawaban   = strtolower($soal->jawaban ?? '');
                    @endphp
                    <div class="soal-item">
                        <div class="soal-num">Soal {{ $i + 1 }}</div>
                        <div class="soal-teks">{{ $soal->pertanyaan }}</div>
                        <div class="opsi-list">
                            @foreach(['a' => $soal->opsi_a, 'b' => $soal->opsi_b, 'c' => $soal->opsi_c, 'd' => $soal->opsi_d] as $key => $opsi)
                                @if($opsi)
                                    @php
                                        $isBenar    = $sudahSubmit && $kunciJawaban === $key;
                                        $isSalah    = $sudahSubmit && $pilihanPetugas === $key && $kunciJawaban !== $key;
                                        $extraClass = '';
                                        if ($isBenar && $pilihanPetugas === $key) $extraClass = 'benar';
                                        elseif ($isSalah) $extraClass = 'salah';
                                        elseif ($sudahSubmit && $isBenar) $extraClass = 'benar-kunci';
                                    @endphp
                                    <label class="opsi-label {{ $extraClass }}">
                                        <input type="radio" name="quiz_jawaban[{{ $soal->id }}]" value="{{ $key }}"
                                            form="form-submit"
                                            {{ $pilihanPetugas === $key ? 'checked' : '' }}
                                            {{ $sudahSubmit ? 'disabled' : '' }}>
                                        <span class="opsi-key">{{ strtoupper($key) }}</span>
                                        {{ $opsi }}
                                        @if($sudahSubmit && $isBenar && $pilihanPetugas === $key)
                                            <svg style="margin-left:auto;flex-shrink:0" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                                        @elseif($sudahSubmit && $isSalah)
                                            <svg style="margin-left:auto;flex-shrink:0" width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><line x1="18" y1="6" x2="6" y2="18"/><line x1="6" y1="6" x2="18" y2="18"/></svg>
                                        @endif
                                    </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    @if(! $loop->last)
                        <hr style="border:none;border-top:1px solid var(--rule)">
                    @endif
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Form submit / Perbarui --}}
    @if(!$sudahSubmit)
    <div class="card">
        <div class="card-head">
            <span class="dot" style="background:var(--blue)"></span>
            Kumpulkan Tugas
        </div>
        <div class="card-body">
            @if($adaJadwalHariIni)
                @include('petugas.materi._form_submit', ['buttonLabel' => 'Kumpulkan Tugas'])
            @else
                <div style="display:flex;align-items:flex-start;gap:12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                    <svg width="18" height="18" fill="none" stroke="#b45309" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
                        <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                    </svg>
                    <div>
                        <div style="font-size:13px;font-weight:600;color:#92400e;">Bukan Jadwal Anda</div>
                        <div style="font-size:12px;color:#b45309;margin-top:3px;line-height:1.6;">
                            Tugas hanya dapat dikerjakan pada hari Anda terjadwal bertugas.
                            Silakan kerjakan saat jadwal Anda aktif.
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>
    @else
    <div id="resubmit-section" style="display:none">
        <div class="card">
            <div class="card-head">
                <span class="dot" style="background:var(--blue)"></span>
                Perbarui Jawaban
            </div>
            <div class="card-body">
                @if($adaJadwalHariIni)
                    @include('petugas.materi._form_submit', ['buttonLabel' => 'Perbarui Jawaban'])
                @else
                    <div style="display:flex;align-items:flex-start;gap:12px;background:#fffbeb;border:1px solid #fde68a;border-radius:8px;padding:14px 16px;">
                        <svg width="18" height="18" fill="none" stroke="#b45309" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px">
                            <circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <div>
                            <div style="font-size:13px;font-weight:600;color:#92400e;">Bukan Hari Jadwal Anda</div>
                            <div style="font-size:12px;color:#b45309;margin-top:3px;line-height:1.6;">
                                Perbaruan jawaban hanya dapat dilakukan pada hari Anda terjadwal bertugas.
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>
    @endif

</div>

@endsection