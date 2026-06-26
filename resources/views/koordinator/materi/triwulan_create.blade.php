@extends('layouts.koordinator')

@section('title', 'Buat Materi & Quiz Triwulan')

@section('breadcrumb')
    <span>PST</span><span>›</span>
    <a href="{{ route('koordinator.materi.triwulan') }}" style="color:var(--blue);text-decoration:none">Quiz Triwulan</a>
    <span>›</span><strong>Buat Baru</strong>
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

    @media (max-width:600px) {
        .form-row { grid-template-columns:1fr; }
        .opsi-grid { grid-template-columns:1fr; }
    }
</style>
@endpush

@section('content')

<div style="margin-bottom:20px">
    <h1 style="font-size:19px;font-weight:600;color:var(--ink)">Buat Materi & Quiz Triwulan</h1>
    <p style="font-size:12px;color:var(--ink3);margin-top:3px">Buat materi pembelajaran beserta soal quiz untuk periode triwulan tertentu</p>
</div>

<form method="POST" action="{{ route('koordinator.materi.triwulan.store') }}" enctype="multipart/form-data" id="tw-form">
@csrf

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
            <input type="text" name="judul" class="form-control" placeholder="Contoh: Etika Layanan Pelanggan TW2 2026" required value="{{ old('judul') }}">
        </div>
        <div class="form-group">
            <label class="form-label req">Periode Triwulan</label>
            <select name="periode" class="periode-select-big" required>
                @php
                    $periodeSekarang = \App\Helpers\SurveyInternalHelper::periodeTriwulanSekarang();
                    $tahunAwal = 2026;
                    $tahunAkhir = now()->year + 1;
                @endphp
                @for($y = $tahunAwal; $y <= $tahunAkhir; $y++)
                    @for($tw = 1; $tw <= 4; $tw++)
                        @php $val = "{$y}-TW{$tw}"; @endphp
                        <option value="{{ $val }}" {{ old('periode', $periodeSekarang) === $val ? 'selected' : '' }}>
                            Triwulan {{ $tw }} Tahun {{ $y }}
                        </option>
                    @endfor
                @endfor
            </select>
            <div class="form-hint">Petugas bisa mengisi saat toggle Survey Internal aktif pada periode ini</div>
        </div>
    </div>

    <div class="form-group">
        <label class="form-label">Deskripsi / Ringkasan Materi</label>
        <textarea name="deskripsi" class="form-control" rows="4" placeholder="Jelaskan isi materi, tujuan pembelajaran, dll.">{{ old('deskripsi') }}</textarea>
    </div>

    <div class="form-row">
        <div class="form-group">
            <label class="form-label">File Materi (PDF/DOC/PPT)</label>
            <input type="file" name="file[]" multiple class="form-control" accept=".pdf,.doc,.docx,.ppt,.pptx">
            <div class="form-hint">Opsional. Bisa pilih lebih dari 1 file. Maks 10MB per file</div>
        </div>
        <div class="form-group">
            <label class="form-label">Link Referensi</label>
            <input type="url" name="link" class="form-control" placeholder="https://..." value="{{ old('link') }}">
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
        <span style="font-size:10.5px;background:var(--blue-lt);color:var(--blue);padding:1px 8px;border-radius:3px;margin-left:4px" id="soal-count">0 soal</span>
    </div>

    <div class="quiz-wrap" id="quiz-container">
        {{-- Soal dirender JS --}}
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
        Simpan Materi & Quiz
    </button>
    <a href="{{ route('koordinator.materi.triwulan') }}" class="btn-cancel">Batal</a>
    <span style="font-size:11px;color:var(--ink3);margin-left:4px">Materi tanpa quiz juga bisa disimpan</span>
</div>

</form>

@endsection

@push('scripts')
<script>
let soalIndex = 0;
const container = document.getElementById('quiz-container');

function updateCount() {
    const n = container.querySelectorAll('.soal-card').length;
    document.getElementById('soal-count').textContent = n + ' soal';
}

function tambahSoal() {
    const idx = soalIndex++;
    const card = document.createElement('div');
    card.className = 'soal-card';
    card.dataset.idx = idx;
    card.innerHTML = `
        <div class="soal-num">Soal ${container.querySelectorAll('.soal-card').length + 1}</div>
        <button type="button" class="btn-hapus-soal" onclick="hapusSoal(this)">Hapus</button>
        <div class="form-group" style="margin-bottom:8px">
            <label class="form-label req">Pertanyaan</label>
            <textarea name="quiz[${idx}][pertanyaan]" class="form-control" rows="2" placeholder="Tulis pertanyaan..." required></textarea>
        </div>
        <div class="opsi-grid">
            ${['a','b','c','d'].map(o => `
                <div class="opsi-row">
                    <span class="opsi-label">${o.toUpperCase()}.</span>
                    <input type="text" name="quiz[${idx}][opsi_${o}]" class="opsi-input" placeholder="Opsi ${o.toUpperCase()}">
                </div>
            `).join('')}
        </div>
        <div class="kunci-wrap">
            <span class="kunci-label">Kunci Jawaban:</span>
            <select name="quiz[${idx}][jawaban]" class="kunci-select" required>
                <option value="">— Pilih —</option>
                <option value="a">A</option>
                <option value="b">B</option>
                <option value="c">C</option>
                <option value="d">D</option>
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

// Tambah 1 soal default saat load
tambahSoal();
</script>
@endpush