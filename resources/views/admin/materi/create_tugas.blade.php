@extends('layouts.admin')

@section('title', 'Tambah Tugas Baru')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <a href="{{ route('admin.materi') }}">Materi & Pembelajaran</a>
    <span>›</span>
    <strong>Tambah Tugas</strong>
@endsection

@push('styles')
<style>
/* Back Link */
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

/* Form Container */
.form-container {
    max-width: 900px;
}

/* Section Header */
.section-header {
    display: flex;
    align-items: center;
    gap: 10px;
    margin-bottom: 18px;
    padding-bottom: 12px;
    border-bottom: 2px solid var(--rule);
}

.section-header svg {
    color: var(--blue);
}

.section-title {
    font-size: 14px;
    font-weight: 600;
    color: var(--ink);
}

/* Form Group */
.form-group {
    margin-bottom: 20px;
}

.form-label {
    display: block;
    font-size: 12px;
    font-weight: 600;
    color: var(--ink2);
    margin-bottom: 8px;
    letter-spacing: 0.3px;
}

.form-label.required::after {
    content: "*";
    color: var(--red);
    margin-left: 4px;
}

.form-control, .form-textarea {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 13px;
    font-family: 'IBM Plex Sans', sans-serif;
    color: var(--ink);
    transition: border-color 0.2s, box-shadow 0.2s;
}

.form-control:focus, .form-textarea:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px var(--blue-lt);
}

.form-textarea {
    resize: vertical;
    min-height: 100px;
    line-height: 1.6;
}

.form-hint {
    font-size: 11px;
    color: var(--ink3);
    margin-top: 6px;
    font-family: 'IBM Plex Mono', monospace;
}

/* File Upload Zone */
.file-upload-zone {
    border: 2px dashed var(--rule);
    border-radius: 8px;
    padding: 30px 20px;
    text-align: center;
    transition: border-color 0.2s, background 0.2s;
    cursor: pointer;
}

.file-upload-zone:hover {
    border-color: var(--blue);
    background: var(--blue-lt);
}

.file-upload-zone svg {
    color: var(--ink3);
    margin-bottom: 12px;
}

.upload-text {
    font-size: 13px;
    color: var(--ink2);
    font-weight: 500;
}

.upload-hint {
    font-size: 11px;
    color: var(--ink3);
    margin-top: 6px;
}

.file-preview {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 10px 14px;
    background: var(--wash);
    border: 1px solid var(--rule);
    border-radius: 6px;
    margin-top: 12px;
    font-size: 12px;
}

.file-preview svg {
    color: var(--blue);
}

.file-remove {
    background: none;
    border: none;
    color: var(--red);
    cursor: pointer;
    padding: 2px;
    margin-left: 8px;
}

/* Quiz Container */
.quiz-container {
    display: flex;
    flex-direction: column;
    gap: 18px;
}

.quiz-item {
    background: var(--wash);
    border: 1px solid var(--rule);
    border-radius: 8px;
    padding: 18px;
    position: relative;
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
    margin-bottom: 12px;
}

.quiz-remove {
    position: absolute;
    top: 14px;
    right: 14px;
    background: var(--red-lt);
    border: none;
    color: var(--red);
    padding: 6px 10px;
    border-radius: 5px;
    font-size: 11px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.quiz-remove:hover {
    background: var(--red);
    color: white;
}

.option-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 12px;
    margin-bottom: 12px;
}

.option-item {
    display: flex;
    align-items: center;
    gap: 8px;
}

.option-label {
    display: flex;
    align-items: center;
    justify-content: center;
    width: 28px;
    height: 28px;
    background: var(--surface);
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 12px;
    font-weight: 600;
    color: var(--ink2);
    flex-shrink: 0;
}

.option-input {
    flex: 1;
    padding: 8px 12px;
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 12.5px;
}

.option-input:focus {
    outline: none;
    border-color: var(--blue);
}

/* Button Add Quiz */
.btn-add-quiz {
    display: inline-flex;
    align-items: center;
    gap: 8px;
    padding: 12px 20px;
    background: var(--surface);
    border: 2px dashed var(--rule);
    border-radius: 8px;
    color: var(--ink2);
    font-size: 12.5px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
    width: 100%;
    justify-content: center;
}

.btn-add-quiz:hover {
    border-color: var(--blue);
    color: var(--blue);
    background: var(--blue-lt);
}

/* Action Buttons */
.action-buttons {
    display: flex;
    gap: 12px;
    padding-top: 24px;
    margin-top: 24px;
    border-top: 2px solid var(--rule);
}

.btn-primary {
    flex: 1;
    padding: 12px 24px;
    background: var(--blue);
    color: white;
    border: none;
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: background 0.2s;
}

.btn-primary:hover {
    background: #1548c4;
}

.btn-secondary {
    flex: 1;
    padding: 12px 24px;
    background: var(--surface);
    color: var(--ink2);
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 13px;
    font-weight: 600;
    cursor: pointer;
    transition: all 0.2s;
}

.btn-secondary:hover {
    border-color: var(--ink3);
    background: var(--wash);
}

/* Empty State */
.empty-quiz {
    text-align: center;
    padding: 40px 20px;
    color: var(--ink3);
}

.empty-quiz svg {
    margin-bottom: 12px;
    opacity: 0.3;
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
        <h1>Tambah Tugas Baru</h1>
        <p>Buat tugas baru untuk petugas dengan materi dan quiz</p>
    </div>
</div>

<div class="form-container">

    <form id="formTugas" method="POST" action="{{ route('admin.tugas.store') }}" enctype="multipart/form-data">
    @csrf

        <!-- SECTION: INFORMASI DASAR -->
        <div class="panel" style="margin-bottom:24px;">
            <div class="card-body" style="padding:24px;">
                
                <div class="section-header">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2"/>
                        <rect x="9" y="3" width="6" height="4" rx="1"/>
                    </svg>
                    <div class="section-title">Informasi Tugas</div>
                </div>

                <!-- Judul -->
                <div class="form-group">
                    <label class="form-label required">Judul Tugas</label>
                    <input type="text" name="judul" class="form-control" placeholder="Contoh: Laporan Bulanan PST Maret 2026" required>
                    <div class="form-hint">Judul yang jelas dan deskriptif</div>
                </div>

                <!-- Deskripsi -->
                <div class="form-group">
                    <label class="form-label required">Deskripsi</label>
                    <textarea name="deskripsi" class="form-textarea" placeholder="Jelaskan detail tugas yang harus dikerjakan petugas..." required></textarea>
                    <div class="form-hint">Berikan instruksi yang jelas dan lengkap</div>
                </div>

                <!-- Deadline & Wilayah -->
                <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                    <div class="form-group">
                        <label class="form-label required">Deadline</label>
                        <input type="date" name="deadline" class="form-control" required>
                    </div>

                </div>

            </div>
        </div>

        <!-- SECTION: UPLOAD FILE -->
        <div class="panel" style="margin-bottom:24px;">
            <div class="card-body" style="padding:24px;">
                
                <div class="section-header">
                    <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <div class="section-title">Materi Tugas (Opsional)</div>
                </div>

                <!-- Upload File -->
                <div class="form-group">
                    <label class="form-label">Upload File Tugas</label>
                    <div class="file-upload-zone" onclick="document.getElementById('fileInput').click()">
                        <svg width="40" height="40" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                            <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                            <polyline points="17 8 12 3 7 8"/>
                            <line x1="12" y1="3" x2="12" y2="15"/>
                        </svg>
                        <div class="upload-text">Klik untuk upload file (bisa pilih lebih dari 1)</div>
                        <div class="upload-hint">PDF, DOC, DOCX, PPT (Max 10MB per file)</div>
                    </div>
                    <input type="file" id="fileInput" name="file[]" multiple style="display:none;" accept=".pdf,.doc,.docx,.ppt,.pptx" onchange="showFilePreview(this)">

                    <div id="filePreviewList" style="display:flex; flex-direction:column; gap:8px; margin-top:12px;"></div>
                </div>

                <!-- Link Eksternal -->
                <div class="form-group" style="margin-bottom:0;">
                    <label class="form-label">Link Eksternal (Opsional)</label>
                    <input type="url" name="link" class="form-control" placeholder="https://example.com/materi">
                    <div class="form-hint">Link referensi atau video pembelajaran</div>
                </div>

            </div>
        </div>

        <!-- SECTION: QUIZ -->
        <div class="panel" style="margin-bottom:24px;">
            <div class="card-body" style="padding:24px;">
                
                <div class="section-header">
                    <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                        <circle cx="12" cy="12" r="10"/>
                        <path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3" stroke="white" stroke-width="2" fill="none"/>
                        <line x1="12" y1="17" x2="12.01" y2="17" stroke="white" stroke-width="2"/>
                    </svg>
                    <div class="section-title">Quiz (Opsional)</div>
                </div>

                <div class="quiz-container" id="quizContainer">
                    <!-- Quiz items akan ditambahkan di sini via JavaScript -->
                    <div class="empty-quiz" id="emptyQuiz">
                        <svg width="48" height="48" fill="none" stroke="currentColor" stroke-width="1" viewBox="0 0 24 24">
                            <circle cx="12" cy="12" r="10"/>
                            <line x1="12" y1="8" x2="12" y2="12"/>
                            <line x1="12" y1="16" x2="12.01" y2="16"/>
                        </svg>
                        <p>Belum ada soal quiz. Klik tombol di bawah untuk menambahkan.</p>
                    </div>
                </div>

                <button type="button" class="btn-add-quiz" onclick="addQuiz()">
                    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="12" y1="5" x2="12" y2="19"/>
                        <line x1="5" y1="12" x2="19" y2="12"/>
                    </svg>
                    Tambah Soal Quiz
                </button>

            </div>
        </div>

        <!-- ACTION BUTTONS -->
        <div class="action-buttons">
            <button type="submit" class="btn-primary">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:inline;vertical-align:middle;margin-right:6px;">
                    <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                    <polyline points="17 21 17 13 7 13 7 21"/>
                    <polyline points="7 3 7 8 15 8"/>
                </svg>
                Simpan Tugas
            </button>
            <a href="{{ route('admin.materi') }}" class="btn-secondary" style="text-align:center; text-decoration:none; line-height:inherit;">
                Batal
            </a>
        </div>

    </form>

</div>

@endsection

@push('scripts')
<script>
let quizCount = 0;

// Fungsi Tambah Quiz
function addQuiz() {
    quizCount++;
    
    // Hide empty state
    document.getElementById('emptyQuiz').style.display = 'none';
    
    const quizHTML = `
        <div class="quiz-item" id="quiz-${quizCount}">
            <div class="quiz-number">${quizCount}</div>
            <button type="button" class="quiz-remove" onclick="removeQuiz(${quizCount})">
                <svg width="11" height="11" fill="currentColor" viewBox="0 0 24 24">
                    <line x1="18" y1="6" x2="6" y2="18" stroke="currentColor" stroke-width="2"/>
                    <line x1="6" y1="6" x2="18" y2="18" stroke="currentColor" stroke-width="2"/>
                </svg>
                Hapus
            </button>

            <div class="form-group">
                <label class="form-label">Pertanyaan</label>
                <input type="text" name="quiz[${quizCount}][pertanyaan]" class="form-control" placeholder="Tuliskan pertanyaan..." required>
            </div>

            <div class="option-grid">
                <div class="option-item">
                    <div class="option-label">A</div>
                    <input type="text" name="quiz[${quizCount}][opsi_a]" class="option-input" placeholder="Opsi A" required>
                </div>
                <div class="option-item">
                    <div class="option-label">B</div>
                    <input type="text" name="quiz[${quizCount}][opsi_b]" class="option-input" placeholder="Opsi B" required>
                </div>
                <div class="option-item">
                    <div class="option-label">C</div>
                    <input type="text" name="quiz[${quizCount}][opsi_c]" class="option-input" placeholder="Opsi C" required>
                </div>
                <div class="option-item">
                    <div class="option-label">D</div>
                    <input type="text" name="quiz[${quizCount}][opsi_d]" class="option-input" placeholder="Opsi D" required>
                </div>
            </div>

            <div class="form-group" style="margin-bottom:0;">
                <label class="form-label">Jawaban Benar</label>
                <select name="quiz[${quizCount}][jawaban]" class="form-control" required>
                    <option value="">-- Pilih Jawaban --</option>
                    <option value="A">A</option>
                    <option value="B">B</option>
                    <option value="C">C</option>
                    <option value="D">D</option>
                </select>
            </div>
        </div>
    `;
    
    document.getElementById('quizContainer').insertAdjacentHTML('beforeend', quizHTML);
}

// Fungsi Hapus Quiz
function removeQuiz(id) {
    const quizItem = document.getElementById('quiz-' + id);
    quizItem.remove();
    
    // Update nomor quiz
    updateQuizNumbers();
    
    // Show empty state if no quiz
    const remainingQuiz = document.querySelectorAll('.quiz-item').length;
    if (remainingQuiz === 0) {
        document.getElementById('emptyQuiz').style.display = 'block';
    }
}

// Update Nomor Quiz
function updateQuizNumbers() {
    const quizItems = document.querySelectorAll('.quiz-item');
    quizItems.forEach((item, index) => {
        const numberBadge = item.querySelector('.quiz-number');
        numberBadge.textContent = index + 1;
    });
}

// Menyimpan semua file yang sudah dipilih (akumulasi lintas beberapa kali buka dialog)
let selectedFiles = [];

// Show File Preview — dipanggil saat user memilih file dari dialog.
// input.files HANYA berisi seleksi TERBARU (browser selalu mengganti, bukan menambah),
// jadi kita gabungkan manual ke variabel selectedFiles, lalu tulis ulang ke input.files.
function showFilePreview(input) {
    Array.from(input.files).forEach(file => {
        // Hindari duplikat (nama + ukuran + last modified sama)
        const isDuplicate = selectedFiles.some(f =>
            f.name === file.name && f.size === file.size && f.lastModified === file.lastModified
        );
        if (!isDuplicate) selectedFiles.push(file);
    });

    syncInputFiles();
    renderFilePreviewList();
}

// Tulis ulang selectedFiles ke input#fileInput supaya ikut terkirim saat form di-submit
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
        item.style.display = 'inline-flex';
        item.innerHTML = `
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                <polyline points="14 2 14 8 20 8"/>
            </svg>
            <span>${file.name}</span>
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

// Hapus 1 file dari pilihan sebelum submit (tidak mempengaruhi file lain)
function removeFileAt(index) {
    selectedFiles.splice(index, 1);
    syncInputFiles();
    renderFilePreviewList();
}

// Form Validation
document.getElementById('formTugas').addEventListener('submit', function(e) {
    // Bisa tambahkan validasi custom di sini
    console.log('Form submitted!');
});
</script>
@endpush