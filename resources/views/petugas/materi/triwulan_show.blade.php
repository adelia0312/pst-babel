@extends('layouts.petugas')

@section('title', 'Quiz Triwulan — ' . $materi->judul)

@section('breadcrumb')
    <span>PST</span><span>›</span>
    <a href="{{ route('petugas.materi') }}" style="color:var(--blue);text-decoration:none">Materi & Tugas</a>
    <span>›</span><strong>Quiz Triwulan</strong>
@endsection

@push('styles')
<style>
    .page-head { display:flex; justify-content:space-between; align-items:flex-start; margin-bottom:24px; gap:12px; flex-wrap:wrap; }
    .page-head h1 { font-size:19px; font-weight:600; color:var(--ink); }
    .page-head p  { font-size:12.5px; color:var(--ink3); margin-top:4px; }

    /* Status banner */
    .status-banner { display:flex; align-items:flex-start; gap:12px; padding:14px 16px; border-radius:8px; margin-bottom:20px; font-size:12.5px; line-height:1.7; }
    .banner-open   { background:#eff6ff; border:1px solid #bfdbfe; color:#1e40af; }
    .banner-closed { background:#fffbeb; border:1px solid #fde68a; color:#92400e; }
    .banner-done   { background:var(--green-lt); border:1px solid #bbf7d0; color:#166534; }

    /* Materi info */
    .materi-info { background:var(--surface); border:1px solid var(--rule); border-radius:10px; padding:18px 20px; margin-bottom:20px; }
    .materi-info-title { font-size:15px; font-weight:600; color:var(--ink); margin-bottom:6px; }
    .materi-info-desc  { font-size:12.5px; color:var(--ink2); line-height:1.7; margin-bottom:12px; }
    .materi-badges { display:flex; flex-wrap:wrap; gap:8px; }
    .mta-badge { display:inline-flex; align-items:center; gap:5px; padding:5px 11px; background:var(--wash); border:1px solid var(--rule); border-radius:5px; font-size:11.5px; color:var(--ink2); text-decoration:none; }
    .mta-badge:hover { border-color:var(--blue); color:var(--blue); }

    /* Quiz form */
    .quiz-form-panel { background:var(--surface); border:1px solid var(--rule); border-radius:10px; overflow:hidden; margin-bottom:20px; }
    .quiz-header { padding:14px 20px; border-bottom:1px solid var(--rule); background:var(--wash); display:flex; align-items:center; justify-content:space-between; }
    .quiz-header-title { font-size:13px; font-weight:600; color:var(--ink); display:flex; align-items:center; gap:8px; }

    .soal-block { padding:18px 20px; border-bottom:1px solid var(--rule); }
    .soal-block:last-child { border-bottom:none; }
    .soal-num { font-size:10.5px; font-weight:700; color:var(--blue); text-transform:uppercase; letter-spacing:.6px; margin-bottom:6px; }
    .soal-teks { font-size:13px; font-weight:500; color:var(--ink); line-height:1.55; margin-bottom:14px; }

    /* Opsi radio */
    .opsi-list { display:flex; flex-direction:column; gap:8px; }
    .opsi-item { display:flex; align-items:center; gap:10px; padding:9px 12px; border:1px solid var(--rule); border-radius:7px; cursor:pointer; transition:all .12s; }
    .opsi-item:hover { border-color:var(--blue); background:var(--blue-lt); }
    .opsi-item input[type="radio"] { display:none; }
    .opsi-item.selected { border-color:var(--blue); background:var(--blue-lt); }
    .opsi-circle { width:20px; height:20px; border-radius:50%; border:2px solid var(--rule); flex-shrink:0; display:flex; align-items:center; justify-content:center; transition:all .12s; }
    .opsi-item.selected .opsi-circle { border-color:var(--blue); background:var(--blue); }
    .opsi-item.selected .opsi-circle::after { content:''; width:6px; height:6px; background:#fff; border-radius:50%; }
    .opsi-key { font-size:11px; font-weight:700; color:var(--ink3); width:18px; flex-shrink:0; }
    .opsi-item.selected .opsi-key { color:var(--blue); }
    .opsi-text { font-size:12.5px; color:var(--ink); }

    /* Hasil mode (setelah submit) */
    .opsi-item.correct { border-color:var(--green); background:var(--green-lt); }
    .opsi-item.wrong   { border-color:var(--red);   background:var(--red-lt);   }
    .opsi-item.correct .opsi-key, .opsi-item.correct .opsi-text { color:var(--green); }
    .opsi-item.wrong   .opsi-key, .opsi-item.wrong   .opsi-text { color:var(--red); }

    /* Score display */
    .score-card {
        text-align:center; padding:28px; background:var(--green-lt);
        border-bottom:1px solid #bbf7d0;
    }
    .score-val { font-size:48px; font-weight:300; letter-spacing:-2px; font-family:'IBM Plex Mono',monospace; color:var(--green); line-height:1; }
    .score-lbl { font-size:12.5px; color:#166534; margin-top:6px; }
    .score-detail { font-size:11.5px; color:#166534; margin-top:3px; }

    /* Submit btn */
    .quiz-footer { padding:16px 20px; border-top:1px solid var(--rule); display:flex; gap:10px; align-items:center; }
    .btn-submit { padding:10px 22px; background:var(--blue); color:#fff; border:none; border-radius:7px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:opacity .2s; display:flex; align-items:center; gap:7px; }
    .btn-submit:hover { opacity:.88; }
    .btn-back { padding:10px 16px; background:var(--surface); color:var(--ink2); border:1px solid var(--rule); border-radius:7px; font-size:12.5px; cursor:pointer; font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; }
    .btn-back:hover { border-color:var(--ink3); color:var(--ink); }

    /* Alert */
    .alert { display:flex; align-items:center; gap:8px; padding:10px 14px; border-radius:6px; margin-bottom:16px; font-size:12.5px; font-weight:500; }
    .alert-success { background:var(--green-lt); color:var(--green); border:1px solid #bbf7d0; }
    .alert-warn    { background:#fffbeb; color:#92400e; border:1px solid #fde68a; }
</style>
@endpush

@section('content')

{{-- Flash alerts --}}
@if(session('success'))
<div class="alert alert-success">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="alert alert-warn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('error') }}
</div>
@endif
@if(session('info'))
<div class="alert alert-warn">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    {{ session('info') }}
</div>
@endif

{{-- Page head --}}
<div class="page-head">
    <div>
        <h1>Quiz Triwulan</h1>
        <p>
            @php
                preg_match('/^(\d{4})-TW(\d)$/', $periode, $m);
                $periodeLabel = isset($m[1]) ? "Triwulan {$m[2]} Tahun {$m[1]}" : $periode;
            @endphp
            {{ $periodeLabel }}
        </p>
    </div>
</div>

{{-- Status banner --}}
@if($jawabanSudah && $jawabanSudah->status === 'sudah')
<div class="status-banner banner-done">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><polyline points="20 6 9 17 4 12"/></svg>
    <div>
        <strong>Anda sudah mengerjakan quiz ini.</strong><br>
        Dikerjakan pada {{ $jawabanSudah->dikerjakan_at?->format('d M Y, H:i') }} —
        Skor: <strong>{{ $jawabanSudah->skor }}/100</strong>
    </div>
</div>
@elseif($bisaIsi)
<div class="status-banner banner-open">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
    <div>
        <strong>Quiz terbuka!</strong><br>
        Isi dan submit quiz di bawah ini. Setiap soal hanya bisa dijawab satu kali, pastikan jawaban sudah benar sebelum submit.
    </div>
</div>
@else
<div class="status-banner banner-closed">
    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <div>
        <strong>Quiz belum dibuka.</strong><br>
        Quiz triwulan bisa diisi setelah Admin mengaktifkan Survey Internal. Pantau informasi dari koordinator.
    </div>
</div>
@endif

{{-- Info Materi --}}
<div class="materi-info">
    <div class="materi-info-title">{{ $materi->judul }}</div>
    @if($materi->deskripsi)
    <div class="materi-info-desc">{{ $materi->deskripsi }}</div>
    @endif
    <div class="materi-badges">
        @foreach($materi->semuaFile() as $f)
        <a href="{{ asset('storage/'.$f->file) }}" target="_blank" class="mta-badge">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
            {{ $f->nama_asli }}
        </a>
        @endforeach
        @if($materi->link)
        <a href="{{ $materi->link }}" target="_blank" class="mta-badge">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/><polyline points="15 3 21 3 21 9"/><line x1="10" y1="14" x2="21" y2="3"/></svg>
            Buka Link Referensi
        </a>
        @endif
        <span class="mta-badge" style="cursor:default">
            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            {{ $materi->quiz->count() }} soal quiz
        </span>
    </div>
</div>

{{-- Quiz --}}
@if($materi->quiz->isNotEmpty())

@if($jawabanSudah && $jawabanSudah->status === 'sudah')
{{-- Mode: Lihat hasil --}}
<div class="quiz-form-panel">
    <div class="score-card">
        <div class="score-val">{{ $jawabanSudah->skor }}</div>
        <div class="score-lbl">dari 100 poin</div>
        @php
            $detail  = $jawabanSudah->jawaban_detail ?? [];
            $benar   = 0;
            foreach ($materi->quiz as $soal) {
                $j = $detail[$soal->id] ?? null;
                if ($j && strtolower($j) === strtolower($soal->jawaban)) $benar++;
            }
        @endphp
        <div class="score-detail">{{ $benar }} dari {{ $materi->quiz->count() }} soal benar</div>
    </div>
    <div class="quiz-header">
        <div class="quiz-header-title">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Hasil Quiz — Jawaban Anda
        </div>
    </div>
    @foreach($materi->quiz as $idx => $soal)
    @php
        $jawabanSaya = $detail[$soal->id] ?? null;
        $benarSoal = $jawabanSaya && strtolower($jawabanSaya) === strtolower($soal->jawaban);
    @endphp
    <div class="soal-block">
        <div class="soal-num">Soal {{ $idx + 1 }} — @if($benarSoal) <span style="color:var(--green)">✔ Benar</span> @else <span style="color:var(--red)">✖ Salah</span> @endif</div>
        <div class="soal-teks">{{ $soal->pertanyaan }}</div>
        <div class="opsi-list">
            @foreach(['a','b','c','d'] as $huruf)
            @php
                $teks     = $soal->{'opsi_'.$huruf};
                $kunci    = strtolower($soal->jawaban) === $huruf;
                $dipilih  = strtolower($jawabanSaya ?? '') === $huruf;
                $extraClass = '';
                if ($kunci) $extraClass = 'correct';
                elseif ($dipilih && !$kunci) $extraClass = 'wrong';
            @endphp
            @if($teks)
            <label class="opsi-item {{ $extraClass }}" style="cursor:default">
                <div class="opsi-circle" @if($kunci || $dipilih) style="border-color:{{ $kunci ? 'var(--green)' : 'var(--red)' }};background:{{ $kunci ? 'var(--green)' : 'var(--red)' }}" @endif>
                    @if($kunci || $dipilih) <div style="width:6px;height:6px;background:#fff;border-radius:50%"></div> @endif
                </div>
                <span class="opsi-key">{{ strtoupper($huruf) }}.</span>
                <span class="opsi-text">{{ $teks }}</span>
                @if($kunci) <span style="font-size:10px;color:var(--green);margin-left:auto;font-weight:600">✔ Kunci</span> @endif
            </label>
            @endif
            @endforeach
        </div>
    </div>
    @endforeach
    <div class="quiz-footer">
        <a href="{{ route('petugas.materi') }}" class="btn-back">← Kembali ke Materi</a>
    </div>
</div>

@elseif($bisaIsi)
{{-- Mode: Isi quiz --}}
<form method="POST" action="{{ route('petugas.materi.triwulan.submit', $materi->id) }}" id="quiz-form">
@csrf
<div class="quiz-form-panel">
    <div class="quiz-header">
        <div class="quiz-header-title">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg>
            Kerjakan Quiz — {{ $materi->quiz->count() }} Soal
        </div>
        <span id="progress-info" style="font-size:11px;color:var(--ink3);font-family:'IBM Plex Mono',monospace">0 / {{ $materi->quiz->count() }} dijawab</span>
    </div>

    @foreach($materi->quiz as $idx => $soal)
    <div class="soal-block">
        <div class="soal-num">Soal {{ $idx + 1 }}</div>
        <div class="soal-teks">{{ $soal->pertanyaan }}</div>
        <div class="opsi-list" data-soal="{{ $soal->id }}">
            @foreach(['a','b','c','d'] as $huruf)
            @if($soal->{'opsi_'.$huruf})
            <label class="opsi-item" onclick="pilihOpsi(this, {{ $soal->id }}, '{{ $huruf }}')">
                <input type="radio" name="jawaban[{{ $soal->id }}]" value="{{ $huruf }}">
                <div class="opsi-circle"></div>
                <span class="opsi-key">{{ strtoupper($huruf) }}.</span>
                <span class="opsi-text">{{ $soal->{'opsi_'.$huruf} }}</span>
            </label>
            @endif
            @endforeach
        </div>
    </div>
    @endforeach

    <div class="quiz-footer">
        <button type="button" class="btn-submit" onclick="submitQuiz()">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
            Submit Quiz
        </button>
        <a href="{{ route('petugas.materi') }}" class="btn-back">Kembali</a>
        <span id="submit-warn" style="font-size:11.5px;color:var(--red);display:none">Harap jawab semua soal terlebih dahulu.</span>
    </div>
</div>
</form>

@else
{{-- Quiz tertutup --}}
<div class="quiz-form-panel">
    <div class="quiz-header">
        <div class="quiz-header-title" style="color:var(--ink3)">Quiz belum tersedia</div>
    </div>
    <div style="padding:40px 20px;text-align:center;color:var(--ink3);font-size:12.5px">
        Tunggu hingga admin mengaktifkan Survey Internal untuk periode ini.
    </div>
    <div class="quiz-footer">
        <a href="{{ route('petugas.materi') }}" class="btn-back">Kembali ke Materi</a>
    </div>
</div>
@endif

@else
<div style="text-align:center;padding:40px;color:var(--ink3);background:var(--surface);border:1px solid var(--rule);border-radius:10px">
    <p style="font-size:13px">Materi ini belum memiliki soal quiz.</p>
    <a href="{{ route('petugas.materi') }}" class="btn-back" style="margin-top:12px;display:inline-flex">Kembali</a>
</div>
@endif

@endsection

@push('scripts')
<script>
const totalSoal = {{ $materi->quiz->count() }};
const answered  = new Set();

function pilihOpsi(label, soalId, huruf) {
    // Clear pilihan lain dalam soal yang sama
    const wrap = document.querySelector('[data-soal="' + soalId + '"]');
    wrap.querySelectorAll('.opsi-item').forEach(el => el.classList.remove('selected'));
    label.classList.add('selected');
    label.querySelector('input[type="radio"]').checked = true;
    answered.add(soalId);
    updateProgress();
}

function updateProgress() {
    const el = document.getElementById('progress-info');
    if (el) el.textContent = answered.size + ' / ' + totalSoal + ' dijawab';
}

function submitQuiz() {
    if (answered.size < totalSoal) {
        document.getElementById('submit-warn').style.display = '';
        const firstUnanswered = document.querySelector('.soal-block:not(:has(input[type="radio"]:checked))');
        if (firstUnanswered) firstUnanswered.scrollIntoView({ behavior: 'smooth', block: 'center' });
        return;
    }
    if (!confirm('Yakin ingin submit? Jawaban tidak bisa diubah setelah submit.')) return;
    document.getElementById('quiz-form').submit();
}
</script>
@endpush