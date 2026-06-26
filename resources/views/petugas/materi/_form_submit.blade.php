{{--
    Partial: _form_submit.blade.php
    Dipakai di petugas/materi/show.blade.php (2x: submit pertama & resubmit)
    Variabel yang dibutuhkan: $tugas, $jawaban, $buttonLabel
--}}

<form id="form-submit"
      method="POST"
      action="{{ route('petugas.materi.submit', $tugas->id) }}"
      enctype="multipart/form-data"
      style="display:flex;flex-direction:column;gap:14px;">
    @csrf

    {{-- Upload File --}}
    <div class="form-section">
        <label class="form-label">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px">
                <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                <polyline points="17 8 12 3 7 8"/>
                <line x1="12" y1="3" x2="12" y2="15"/>
            </svg>
            Upload File
            <span style="color:var(--ink3);font-weight:400">(opsional)</span>
        </label>

        <div class="file-drop" onclick="document.getElementById('file-input').click()">
            <input type="file" id="file-input" name="file"
                   accept=".pdf,.doc,.docx,.ppt,.pptx,.jpg,.jpeg,.png"
                   onchange="showFileName(this)">
            <div class="file-drop-label" id="file-drop-label">
                <svg width="22" height="22" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="margin-bottom:6px;opacity:.4;display:block;margin-left:auto;margin-right:auto">
                    <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                    <polyline points="17 8 12 3 7 8"/>
                    <line x1="12" y1="3" x2="12" y2="15"/>
                </svg>
                <div>Klik untuk pilih file</div>
                <div style="font-size:10.5px;margin-top:3px">PDF, DOC, PPT, JPG — maks. 10MB</div>
            </div>
        </div>
        @if($jawaban && $jawaban->file)
            <div style="font-size:11px;color:var(--ink3)">
                📎 File saat ini tersimpan. Upload baru untuk mengganti.
            </div>
        @endif
        @error('file')
            <div style="font-size:11.5px;color:var(--red)">{{ $message }}</div>
        @enderror
    </div>

    {{-- Link --}}
    <div class="form-section">
        <label class="form-label" for="input-link">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="vertical-align:middle;margin-right:3px">
                <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
                <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
            </svg>
            Link
            <span style="color:var(--ink3);font-weight:400">(opsional)</span>
        </label>
        <input
            type="url"
            id="input-link"
            name="link"
            class="form-input"
            placeholder="https://..."
            value="{{ old('link', $jawaban->link ?? '') }}"
        >
        @error('link')
            <div style="font-size:11.5px;color:var(--red)">{{ $message }}</div>
        @enderror
    </div>

    {{-- Note kalau ada quiz, radio button ada di kolom kiri --}}
    @if($tugas->quiz->count())
        <div style="padding:10px 12px;background:var(--blue-lt);border-radius:6px;font-size:11.5px;color:var(--blue)">
            🧠 Jawab soal quiz di sebelah kiri, lalu klik tombol di bawah untuk mengumpulkan.
        </div>
    @endif

    <button type="submit" class="btn-submit">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24">
            <polyline points="20 6 9 17 4 12"/>
        </svg>
        {{ $buttonLabel ?? 'Kumpulkan Tugas' }}
    </button>

</form>

<script>
function showFileName(input) {
    const label = document.getElementById('file-drop-label');
    if (input.files && input.files[0]) {
        label.innerHTML = '<span class="file-selected">✔ ' + input.files[0].name + '</span>';
    }
}
</script>