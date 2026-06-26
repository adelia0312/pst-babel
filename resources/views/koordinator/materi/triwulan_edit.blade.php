@extends('layouts.koordinator')

@section('title', 'Edit Materi & Quiz Triwulan')

@section('breadcrumb')
    <span>PST</span><span>›</span>
    <a href="{{ route('koordinator.materi.triwulan') }}" style="color:var(--blue);text-decoration:none">Quiz Triwulan</a>
    <span>›</span><strong>Edit</strong>
@endsection

@push('styles')
<style>
    .form-panel { background:var(--surface); border:1px solid var(--rule); border-radius:10px; padding:24px; margin-bottom:20px; }
    .form-title { font-size:14px; font-weight:600; color:var(--ink); margin-bottom:16px; padding-bottom:12px; border-bottom:1px solid var(--rule); display:flex; align-items:center; gap:8px; }
    .form-group { margin-bottom:16px; }
    .form-label { font-size:11.5px; font-weight:600; color:var(--ink); margin-bottom:5px; display:block; }
    .form-label.req::after { content:' *'; color:var(--red); }
    .form-control {
        width:100%; padding:8px 11px; border:1px solid var(--rule); border-radius:6px;
        font-size:12.5px; font-family:'IBM Plex Sans',sans-serif; color:var(--ink);
        background:var(--wash); transition:border-color .15s; box-sizing:border-box;
    }
    .form-control:focus { outline:none; border-color:var(--blue); background:#fff; }
    .form-hint { font-size:11px; color:var(--ink3); margin-top:4px; }
    .form-row { display:grid; grid-template-columns:1fr 1fr; gap:14px; }

    /* Quiz soal builder */
    .quiz-wrap { display:flex; flex-direction:column; gap:14px; }
    .soal-card {
        border:1px solid var(--rule); border-radius:8px; padding:16px;
        background:var(--wash); position:relative;
    }
    .soal-num { font-size:11px; font-weight:700; color:var(--blue); margin-bottom:10px; text-transform:uppercase; letter-spacing:.5px; }
    .opsi-grid { display:grid; grid-template-columns:1fr 1fr; gap:8px; margin-top:8px; }
    .opsi-row { display:flex; align-items:center; gap:8px; }
    .opsi-label { font-size:11.5px; font-weight:700; color:var(--ink2); width:14px; flex-shrink:0; }
    .opsi-input { flex:1; padding:6px 10px; border:1px solid var(--rule); border-radius:5px; font-size:12px; font-family:inherit; background:#fff; }
    .opsi-input:focus { outline:none; border-color:var(--blue); }
    .kunci-wrap { margin-top:10px; display:flex; align-items:center; gap:10px; }
    .kunci-label { font-size:11.5px; font-weight:600; color:var(--ink); }
    .kunci-select { padding:5px 10px; border:1px solid var(--rule); border-radius:5px; font-size:12px; font-family:inherit; background:var(--wash); cursor:pointer; }
    .kunci-select:focus { outline:none; border-color:var(--blue); }
    .btn-hapus-soal {
        position:absolute; top:12px; right:12px;
        background:none; border:1px solid var(--rule); border-radius:4px;
        padding:3px 8px; font-size:10.5px; color:var(--red); cursor:pointer; transition:all .12s;
    }
    .btn-hapus-soal:hover { background:var(--red-lt); border-color:var(--red); }
    .btn-tambah-soal {
        display:flex; align-items:center; gap:7px; padding:9px 14px;
        border:1px dashed var(--rule); border-radius:7px; background:none;
        font-size:12px; color:var(--blue); cursor:pointer; font-family:inherit; transition:all .15s;
        width:100%;
    }
    .btn-tambah-soal:hover { border-color:var(--blue); background:var(--blue-lt); }

    /* Action btns */
    .form-actions { display:flex; gap:10px; align-items:center; flex-wrap:wrap; }
    .btn-submit { padding:9px 20px; background:var(--green); color:#fff; border:none; border-radius:6px; font-size:13px; font-weight:600; cursor:pointer; font-family:inherit; transition:opacity .2s; }
    .btn-submit:hover { opacity:.88; }
    .btn-cancel { padding:9px 16px; background:var(--surface); color:var(--ink2); border:1px solid var(--rule); border-radius:6px; font-size:12.5px; cursor:pointer; font-family:inherit; text-decoration:none; display:inline-flex; align-items:center; }
    .btn-cancel:hover { border-color:var(--ink3); color:var(--ink); }

    /* Periode select big */
    .periode-select-big {
        width:100%; padding:9px 12px; border:1px solid var(--rule); border-radius:7px;
        font-size:13px; font-family:'IBM Plex Sans',sans-serif; color:var(--ink);
        background:var(--wash); cursor:pointer; box-sizing:border-box;
    }
    .periode-select-big:focus { outline:none; border-color:var(--blue); background:#fff; }

    /* File list */
    .file-preview {
        display:flex; align-items:center; justify-content:space-between; gap:8px;
        padding:9px 12px; background:var(--wash); border:1px solid var(--rule);
        border-radius:6px; font-size:12px;
    }
    .file-preview .left { display:flex; align-items:center; gap:8px; min-width:0; }
    .file-preview a { color:var(--ink); text-decoration:none; }
    .file-preview a:hover { color:var(--blue); }
    .file-remove {
        background:none; border:none; color:var(--red); cursor:pointer; padding:2px; flex-shrink:0;
    }
    .file-upload-zone {
        border:2px dashed var(--rule); border-radius:8px; padding:24px 16px;
        text-align:center; cursor:pointer; transition:all .15s;
    }
    .file-upload-zone:hover { border-color:var(--blue); background:var(--blue-lt); }
    .upload-text { font-size:12.5px; color:var(--ink2); font-weight:500; }
    .upload-hint { font-size:11px; color:var(--ink3); margin-top:4px; }

    @media (max-width:600px) {
        .form-row { grid-template-columns:1fr; }
        .opsi-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:19px;font-weight:600;color:var(--ink)">Edit Materi & Quiz Triwulan</h1>
    <p style="font-size:12px;color:var(--ink3);margin-top:3px">Perbarui informasi materi, file, link, dan soal quiz</p>
</div>

<form method="POST" action="{{ route('koordinator.materi.triwulan.update', $materi->id) }}" enctype="multipart/form-data" id="tw-form">
@csrf
@method('PUT')

{{-- Info Materi --}}
<div class="form-panel">
    <div class="form-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/>
            <path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/>
        </svg>
        Informasi Materi
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label req">Judul Materi</label>
            <input type="text" name="judul" class="form-control" value="{{ old('judul', $materi->judul) }}" required>
        </div>
        <div class="form-group">
            <label class="form-label req">Periode Triwulan</label>
            <select name="periode" class="periode-select-big" required>
                @foreach($periodeOptions as $val => $label)
                    <option value="{{ $val }}" {{ old('periode', $materi->periode) === $val ? 'selected' : '' }}>
                        {{ $label }}
                    </option>
                @endforeach
            </select>
            <div class="form-hint">Petugas bisa mengisi saat toggle Survey Internal aktif pada periode ini</div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi / Ringkasan Materi</label>
        <textarea name="deskripsi" class="form-control" rows="4">{{ old('deskripsi', $materi->deskripsi) }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            @php $existingFiles = $materi->semuaFile(); @endphp

            <label class="form-label">File Materi</label>

            @if($existingFiles->isNotEmpty())
            <div id="existingFilesList" style="display:flex; flex-direction:column; gap:8px; margin-bottom:12px;">
                @foreach($existingFiles as $f)
                <div class="file-preview" id="existing-file-{{ $f->legacy ? 'legacy' : $f->id }}">
                    <span class="left">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0">
                            <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                            <polyline points="14 2 14 8 20 8"/>
                        </svg>
                        <a href="{{ asset('storage/' . $f->file) }}" target="_blank">{{ $f->nama_asli }}</a>
                    </span>
                    <button type="button" class="file-remove" title="Hapus file ini"
                        onclick="markFileForDeletion('{{ $f->legacy ? 'legacy' : $f->id }}', {{ $f->legacy ? 'true' : 'false' }}, this)">
                        <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                            <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/>
                            <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/>
                        </svg>
                    </button>
                </div>
                @endforeach
            </div>
            @endif

            <div id="hapusFileInputs"></div>

            <div class="file-upload-zone" onclick="document.getElementById('fileInput').click()">
                <div class="upload-text">+ Tambah file (bisa pilih lebih dari 1)</div>
                <div class="upload-hint">PDF, DOC, DOCX, PPT (Max 10MB per file)</div>
            </div>
            <input type="file" id="fileInput" name="file[]" multiple style="display:none" accept=".pdf,.doc,.docx,.ppt,.pptx" onchange="showFilePreview(this)">
            <div id="filePreviewList" style="display:flex; flex-direction:column; gap:8px; margin-top:10px;"></div>
        </div>
        <div class="form-group">
            <label class="form-label">Link Referensi</label>
            <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link', $materi->link) }}">
            <div class="form-hint">Opsional. Link ke materi online</div>
        </div>
    </div>
</div>

{{-- Quiz Builder --}}
<div class="form-panel">
    <div class="form-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/>
        </svg>
        Soal Quiz
        <span style="font-size:10.5px;background:var(--blue-lt);color:var(--blue);padding:1px 8px;border-radius:3px;margin-left:4px" id="soal-count">{{ $materi->quiz->count() }} soal</span>
    </div>

    <div class="quiz-wrap" id="quiz-container">
        {{-- Soal existing dirender PHP, soal baru dirender JS --}}
    </div>

    <button type="button" class="btn-tambah-soal" onclick="tambahSoal()" style="margin-top:12px">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
        Tambah Soal
    </button>
</div>

{{-- Actions --}}
<div class="form-actions">
    <button type="submit" class="btn-submit">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:4px">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        Update Materi & Quiz
    </button>
    <a href="{{ route('koordinator.materi.triwulan', ['periode' => $materi->periode]) }}" class="btn-cancel">Batal</a>
</div>

</form>

@endsection

@php
    $existingSoalForJs = $materi->quiz->map(function ($q) {
        return [
            'pertanyaan' => $q->pertanyaan,
            'opsi_a' => $q->opsi_a,
            'opsi_b' => $q->opsi_b,
            'opsi_c' => $q->opsi_c,
            'opsi_d' => $q->opsi_d,
            'jawaban' => $q->jawaban,
        ];
    });
@endphp

@push('scripts')
<script>
let soalIndex = 0;
const container = document.getElementById('quiz-container');

// Data soal yang sudah ada (dari server), dipakai untuk pre-fill saat load
const existingSoal = @json($existingSoalForJs);

function updateCount() {
    const n = container.querySelectorAll('.soal-card').length;
    document.getElementById('soal-count').textContent = n + ' soal';
}

function esc(str) {
    if (!str) return '';
    return String(str).replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
}

function tambahSoal(data) {
    data = data || {};
    const idx = soalIndex++;
    const card = document.createElement('div');
    card.className = 'soal-card';
    card.dataset.idx = idx;
    card.innerHTML = `
        <div class="soal-num">Soal ${container.querySelectorAll('.soal-card').length + 1}</div>
        <button type="button" class="btn-hapus-soal" onclick="hapusSoal(this)">Hapus</button>
        <div class="form-group" style="margin-bottom:8px">
            <label class="form-label req">Pertanyaan</label>
            <textarea name="quiz[${idx}][pertanyaan]" class="form-control" rows="2" placeholder="Tulis pertanyaan..." required>${esc(data.pertanyaan)}</textarea>
        </div>
        <div class="opsi-grid">
            ${['a','b','c','d'].map(o => `
                <div class="opsi-row">
                    <span class="opsi-label">${o.toUpperCase()}.</span>
                    <input type="text" name="quiz[${idx}][opsi_${o}]" class="opsi-input" placeholder="Opsi ${o.toUpperCase()}" value="${esc(data['opsi_'+o])}">
                </div>
            `).join('')}
        </div>
        <div class="kunci-wrap">
            <span class="kunci-label">Kunci Jawaban:</span>
            <select name="quiz[${idx}][jawaban]" class="kunci-select" required>
                <option value="">— Pilih —</option>
                <option value="a" ${data.jawaban === 'a' ? 'selected' : ''}>A</option>
                <option value="b" ${data.jawaban === 'b' ? 'selected' : ''}>B</option>
                <option value="c" ${data.jawaban === 'c' ? 'selected' : ''}>C</option>
                <option value="d" ${data.jawaban === 'd' ? 'selected' : ''}>D</option>
            </select>
        </div>
    `;
    container.appendChild(card);
    updateCount();
    renumberSoal();
}

function hapusSoal(btn) {
    btn.closest('.soal-card').remove();
    updateCount();
    renumberSoal();
}

function renumberSoal() {
    container.querySelectorAll('.soal-card').forEach((c, i) => {
        const num = c.querySelector('.soal-num');
        if (num) num.textContent = 'Soal ' + (i + 1);
    });
}

// Pre-fill soal yang sudah ada saat halaman dimuat
if (existingSoal.length > 0) {
    existingSoal.forEach(s => tambahSoal(s));
} else {
    tambahSoal();
}

// ── File handling ──────────────────────────────────────────
// Menyimpan semua file baru yang dipilih (akumulasi lintas beberapa kali buka dialog).
// input.files HANYA berisi seleksi TERBARU (browser selalu mengganti, bukan menambah).
let selectedFiles = [];

function showFilePreview(input) {
    Array.from(input.files).forEach(file => {
        const isDuplicate = selectedFiles.some(f =>
            f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
        );
        if (!isDuplicate) selectedFiles.push(file);
    });

    syncInputFiles();
    renderFilePreviewList();
}

function syncInputFiles() {
    const input = document.getElementById('fileInput');
    const dt = new DataTransfer();
    selectedFiles.forEach(file => dt.items.add(file));
    input.files = dt.files;
}

function renderFilePreviewList() {
    const list = document.getElementById('filePreviewList');
    list.innerHTML = '';

    selectedFiles.forEach((file, idx) => {
        const item = document.createElement('div');
        item.className = 'file-preview';
        item.innerHTML = `
            <span class="left">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0">
                    <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                    <polyline points="14 2 14 8 20 8"/>
                </svg>
                <span>${file.name}</span>
            </span>
            <button type="button" class="file-remove" onclick="removeFileAt(${idx})">
                <svg width="14" height="14" fill="currentColor" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/>
                </svg>
            </button>
        `;
        list.appendChild(item);
    });
}

function removeFileAt(index) {
    selectedFiles.splice(index, 1);
    syncInputFiles();
    renderFilePreviewList();
}

function markFileForDeletion(idOrLegacy, isLegacy, btn) {
    const row = btn.closest('[id^="existing-file-"]');
    if (row) {
        row.style.opacity = '0.4';
        row.style.pointerEvents = 'none';
    }

    const hiddenContainer = document.getElementById('hapusFileInputs');

    if (isLegacy) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'hapus_file_legacy';
        input.value = '1';
        hiddenContainer.appendChild(input);
    } else {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = 'hapus_file_ids[]';
        input.value = idOrLegacy;
        hiddenContainer.appendChild(input);
    }
}
</script>
@endpush