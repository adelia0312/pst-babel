@extends('layouts.admin')

@section('title', 'Detail Tugas')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <a href="{{ route('admin.materi') }}">Materi & Pembelajaran</a>
    <span>›</span>
    <strong>{{ $tugas->judul }}</strong>
@endsection

@push('styles')
<style>
.back-link {
    display: inline-flex;
    align-items: center;
    gap: 6px;
    color: var(--ink3);
    text-decoration: none;
    font-size: 12px;
    margin-bottom: 20px;
    transition: color 0.2s;
}

.back-link:hover {
    color: var(--blue);
}

.detail-section {
    margin-bottom: 24px;
}

.section-title {
    font-size: 13px;
    font-weight: 600;
    color: var(--ink2);
    margin-bottom: 12px;
    text-transform: uppercase;
    letter-spacing: 0.5px;
}

.info-grid {
    display: grid;
    grid-template-columns: 150px 1fr;
    gap: 12px;
    font-size: 13px;
}

.info-label {
    color: var(--ink3);
    font-weight: 500;
}

.info-value {
    color: var(--ink);
}

.file-preview-box {
    border: 1px solid var(--rule);
    border-radius: 8px;
    padding: 10px;
    background: var(--wash);
}

.file-preview-box iframe {
    width: 100%;
    height: 340px;
    border: none;
    border-radius: 6px;
}

.link-box {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: var(--blue-lt);
    border: 1px solid rgba(26, 86, 219, 0.2);
    border-radius: 6px;
    font-size: 13px;
}

.link-box a {
    color: var(--blue);
    text-decoration: none;
    font-weight: 500;
}

.link-box a:hover {
    text-decoration: underline;
}

.quiz-list {
    display: flex;
    flex-direction: column;
    gap: 16px;
}

.quiz-item-detail {
    background: var(--wash);
    border: 1px solid var(--rule);
    border-radius: 8px;
    padding: 18px;
}

.quiz-number {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: var(--blue);
    color: white;
    border-radius: 50%;
    font-size: 12px;
    font-weight: 600;
    margin-bottom: 10px;
}

.quiz-question {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
    margin-bottom: 12px;
}

.quiz-options {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 10px;
}

.quiz-option {
    display: flex;
    align-items: center;
    gap: 8px;
    padding: 10px 12px;
    background: var(--surface);
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 12.5px;
}

.quiz-option.correct {
    border-color: var(--green);
    background: var(--green-lt);
}

.option-letter {
    width: 24px;
    height: 24px;
    background: var(--rule);
    color: var(--ink2);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 11px;
    flex-shrink: 0;
}

.quiz-option.correct .option-letter {
    background: var(--green);
    color: white;
}

.action-buttons {
    display: flex;
    gap: 10px;
}

.btn-action {
    padding: 10px 18px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    text-decoration: none;
}

.btn-edit {
    background: var(--blue);
    color: white;
    border: none;
}

.btn-edit:hover {
    background: #1548c4;
}

.btn-delete {
    background: var(--red);
    color: white;
    border: none;
}

.btn-delete:hover {
    background: #a02d25;
}

.empty-state {
    text-align: center;
    padding: 40px;
    color: var(--ink3);
    font-size: 13px;
}
</style>
@endpush

@section('content')

<a href="{{ route('admin.materi') }}" class="back-link">
    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <line x1="19" y1="12" x2="5" y2="12"/>
        <polyline points="12 19 5 12 12 5"/>
    </svg>
    Kembali ke Materi & Pembelajaran
</a>

<div class="page-head">
    <div>
        <h1>{{ $tugas->judul }}</h1>
        <p>Detail lengkap tugas dan quiz</p>
    </div>
    <div class="action-buttons">
        <a href="{{ route('admin.tugas.edit', $tugas->id) }}" class="btn-action btn-edit">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
                <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
            </svg>
            Edit Tugas
        </a>
        <form action="{{ route('admin.tugas.destroy', $tugas->id) }}" method="POST" style="display:inline;" onsubmit="return confirm('Yakin ingin menghapus tugas ini? Data tidak dapat dikembalikan!')">
            @csrf
            @method('DELETE')
            <button type="submit" class="btn-action btn-delete">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                    <polyline points="3 6 5 6 21 6"/>
                    <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                </svg>
                Hapus Tugas
            </button>
        </form>
    </div>
</div>

<!-- INFORMASI TUGAS -->
<div class="panel detail-section">
    <div class="card-body" style="padding:24px;">
        <div class="section-title">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                <rect x="9" y="3" width="6" height="4" rx="1"/>
            </svg>
            Informasi Tugas
        </div>

        <div class="info-grid">
            <div class="info-label">Deadline</div>
            <div class="info-value"><strong>{{ $tugas->deadline->format('d M Y') }}</strong></div>

            <div class="info-label">Deskripsi</div>
            <div class="info-value">{{ $tugas->deskripsi }}</div>
        </div>
    </div>
</div>

<!-- FILE TUGAS -->
@php $semuaFile = $tugas->semuaFile(); @endphp
@if($semuaFile->isNotEmpty())
<div class="panel detail-section">
    <div class="card-body" style="padding:24px;">
        <div class="section-title">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            File Tugas ({{ $semuaFile->count() }})
        </div>

        @foreach($semuaFile as $f)
        @php
            $fileUrl = asset('storage/' . $f->file);
            $fileExtension = pathinfo($f->file, PATHINFO_EXTENSION);
        @endphp
        <div class="file-preview-box" style="margin-bottom:16px;">
            @if(in_array(strtolower($fileExtension), ['pdf']))
                <!-- Preview PDF langsung -->
                <p style="margin-bottom:8px; color:var(--ink2); font-size:12.5px; font-weight:600;">{{ $f->nama_asli }}</p>
                <iframe src="{{ $fileUrl }}"></iframe>
            @elseif(in_array(strtolower($fileExtension), ['jpg', 'jpeg', 'png', 'gif']))
                <!-- Preview Gambar -->
                <p style="margin-bottom:8px; color:var(--ink2); font-size:12.5px; font-weight:600;">{{ $f->nama_asli }}</p>
                <img src="{{ $fileUrl }}" style="max-width:100%; border-radius:6px;" alt="{{ $f->nama_asli }}">
            @else
                <!-- Download untuk file lain -->
                <div style="text-align:center; padding:40px;">
                    <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:16px; color:var(--ink3);">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <p style="margin-bottom:12px; color:var(--ink2);">{{ $f->nama_asli }}</p>
                    <a href="{{ $fileUrl }}" download class="btn-action btn-edit" style="display:inline-flex;">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="7 10 12 15 17 10"/>
                            <line x1="12" y1="15" x2="12" y2="3"/>
                        </svg>
                        Download File
                    </a>
                </div>
            @endif
        </div>
        @endforeach
    </div>
</div>
@endif

<!-- LINK EKSTERNAL -->
@if($tugas->link)
<div class="panel detail-section">
    <div class="card-body" style="padding:24px;">
        <div class="section-title">
            <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
            </svg>
            Link Eksternal
        </div>

        <div class="link-box">
            <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                <path d="M18 13v6a2 2 0 01-2 2H5a2 2 0 01-2-2V8a2 2 0 012-2h6"/>
                <polyline points="15 3 21 3 21 9" fill="none" stroke="currentColor" stroke-width="2"/>
                <line x1="10" y1="14" x2="21" y2="3" stroke="currentColor" stroke-width="2"/>
            </svg>
            <a href="{{ $tugas->link }}" target="_blank">{{ $tugas->link }}</a>
        </div>
    </div>
</div>
@endif

<!-- QUIZ -->
@if($tugas->quiz->count() > 0)
<div class="panel detail-section">
    <div class="card-body" style="padding:24px;">
        <div class="section-title">
            <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                <circle cx="12" cy="12" r="10"/>
                <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3" stroke="white" stroke-width="2" fill="none"/>
                <line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2"/>
            </svg>
            Quiz ({{ $tugas->quiz->count() }} Soal)
        </div>

        <div class="quiz-list">
            @foreach($tugas->quiz as $index => $q)
                <div class="quiz-item-detail">
                    <div class="quiz-number">{{ $index + 1 }}</div>
                    <div class="quiz-question">{{ $q->pertanyaan }}</div>

                    <div class="quiz-options">
                        <div class="quiz-option {{ $q->jawaban == 'A' ? 'correct' : '' }}">
                            <div class="option-letter">A</div>
                            <span>{{ $q->opsi_a }}</span>
                        </div>
                        <div class="quiz-option {{ $q->jawaban == 'B' ? 'correct' : '' }}">
                            <div class="option-letter">B</div>
                            <span>{{ $q->opsi_b }}</span>
                        </div>
                        <div class="quiz-option {{ $q->jawaban == 'C' ? 'correct' : '' }}">
                            <div class="option-letter">C</div>
                            <span>{{ $q->opsi_c }}</span>
                        </div>
                        <div class="quiz-option {{ $q->jawaban == 'D' ? 'correct' : '' }}">
                            <div class="option-letter">D</div>
                            <span>{{ $q->opsi_d }}</span>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</div>
@else
<div class="panel detail-section">
    <div class="card-body" style="padding:24px;">
        <div class="empty-state">
            <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            <p>Tugas ini tidak memiliki quiz</p>
        </div>
    </div>
</div>
@endif

@endsection