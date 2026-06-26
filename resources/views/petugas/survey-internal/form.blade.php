@extends('layouts.petugas')
@section('title', 'Nilai ' . $dinilai->name)

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('petugas.survey-internal.index') }}" style="color:var(--ink2);text-decoration:none">Survey Internal</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Isi Penilaian</strong>
@endsection

@push('styles')
<style>
    /* ── Layout full width ── */
    .sv-wrap { width: 100%; }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); }
    .sv-btn-lg { height: 38px; padding: 0 20px; font-size: 13px; }
    .sv-btn:disabled { opacity: .5; cursor: not-allowed; }

    /* ── Topbar dalam konten ── */
    .sv-content-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 20px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);
        gap: 12px; flex-wrap: wrap;
    }
    .sv-content-topbar h1 { font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 3px; }
    .sv-content-topbar p  { font-size: 12.5px; color: var(--ink3); margin: 0; }

    /* ── Kartu info siapa yang dinilai ── */
    .sv-target-row {
        display: flex; align-items: center; gap: 14px;
        background: var(--wash); border: 1px solid var(--rule);
        border-radius: 8px; padding: 14px 18px; margin-bottom: 16px;
    }
    .sv-ava {
        width: 40px; height: 40px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-size: 14px; font-weight: 700; color: #fff; flex-shrink: 0;
    }
    .sv-target-lbl  { font-size: 10.5px; color: var(--ink3); font-weight: 600; text-transform: uppercase; letter-spacing: .4px; }
    .sv-target-name { font-size: 15px; font-weight: 600; color: var(--ink); }
    .sv-pill { display:inline-block;font-size:10px;font-weight:500;padding:2px 8px;border-radius:3px; }
    .sv-pill-blue { background:var(--blue-lt);color:var(--blue); }

    /* ── Info anonim ── */
    .sv-info-sm {
        display: flex; gap: 8px; align-items: center;
        background: var(--wash); border: 1px solid var(--rule);
        border-radius: 6px; padding: 9px 13px;
        font-size: 12px; color: var(--ink3); margin-bottom: 18px;
    }

    /* ── Grid 2 kolom untuk banyak pertanyaan ── */
    .sv-q-grid {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(420px, 1fr));
        gap: 12px;
        margin-bottom: 12px;
    }

    /* ── Section per kategori penilaian ── */
    .sv-kategori-section { margin-bottom: 22px; }
    .sv-kategori-title {
        font-size: 12.5px; font-weight: 700; color: var(--blue);
        text-transform: uppercase; letter-spacing: .5px;
        margin-bottom: 10px; padding-bottom: 8px;
        border-bottom: 2px solid var(--blue-lt);
    }

    /* ── Panel pertanyaan ── */
    .sv-q-panel {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; overflow: hidden;
        display: flex; flex-direction: column;
    }
    .sv-q-head {
        padding: 10px 16px; background: var(--wash);
        border-bottom: 1px solid var(--rule);
        display: flex; align-items: center; justify-content: space-between;
        font-size: 10.5px; font-weight: 600; color: var(--ink3);
        letter-spacing: .6px; text-transform: uppercase;
        flex-shrink: 0;
    }
    .sv-q-type { font-size:10px;font-weight:500;padding:1px 7px;border-radius:3px;text-transform:none;letter-spacing:0; }
    .sv-q-body { padding: 16px 18px; flex: 1; }
    .sv-q-text { font-size: 13.5px; font-weight: 500; color: var(--ink); margin-bottom: 14px; line-height: 1.55; }

    /* ── Rating bintang ── */
    .sv-star-row { display:flex;flex-direction:row-reverse;justify-content:flex-end;gap:4px;margin-bottom:6px; }
    .sv-star-input { display:none; }
    .sv-star-label { font-size:30px;color:#d1d5db;cursor:pointer;transition:color .1s,transform .1s;line-height:1; }
    .sv-star-label:hover,
    .sv-star-label:hover ~ .sv-star-label,
    .sv-star-input:checked ~ .sv-star-label { color:#f59e0b; }
    .sv-star-label:hover { transform:scale(1.12); }
    .sv-star-hint { font-size:11.5px;color:var(--ink3); }

    /* ── Pilihan ganda ── */
    .sv-pilihan-list { display:flex;flex-direction:column;gap:7px; }
    .sv-pilihan-opt {
        display:flex;align-items:center;gap:10px;
        padding:10px 13px;border:1px solid var(--rule);border-radius:6px;
        cursor:pointer;font-size:13px;color:var(--ink);transition:border-color .12s,background .12s;
    }
    .sv-pilihan-opt:hover { border-color:var(--blue);background:var(--blue-lt); }
    .sv-pilihan-opt:has(input:checked) { border-color:var(--blue);background:var(--blue-lt); }
    .sv-pilihan-opt input { accent-color:var(--blue);width:14px;height:14px;flex-shrink:0; }

    /* ── Teks bebas ── */
    .sv-textarea {
        width:100%;padding:10px 12px;
        border:1px solid var(--rule);border-radius:5px;
        font-size:13px;font-family:'IBM Plex Sans',sans-serif;
        color:var(--ink);background:var(--wash);resize:vertical;min-height:90px;transition:border-color .15s;
        box-sizing: border-box;
    }
    .sv-textarea:focus { outline:none;border-color:var(--blue);background:var(--surface); }
    .sv-textarea-hint { font-size:11px;color:var(--ink3);margin-top:5px; }

    /* ── Error ── */
    .sv-field-error { color:#dc2626;font-size:11.5px;margin-top:6px; }

    /* ── Submit area ── */
    .sv-submit-panel {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; overflow: hidden;
    }
    .sv-submit-area {
        border-top: 1px solid var(--rule);
        background: var(--wash); padding: 16px 20px;
        display: flex; align-items: center; justify-content: space-between; gap: 12px; flex-wrap: wrap;
    }
    .sv-submit-note { font-size: 12px; color: var(--ink3); line-height: 1.6; }
</style>
@endpush

@section('content')
<div class="sv-wrap">

    {{-- Topbar --}}
    <div class="sv-content-topbar">
        <div>
            <h1>Form Penilaian</h1>
            <p>Isi dengan jujur dan objektif. Jawaban Anda bersifat anonim.</p>
        </div>
        <a href="{{ route('petugas.survey-internal.index') }}" class="sv-btn sv-btn-secondary">← Kembali</a>
    </div>

    {{-- Siapa yang dinilai --}}
    @php
        $warna    = ['#2563eb','#7c3aed','#0891b2','#d97706','#16a34a','#dc2626'];
        $warnaAva = $warna[abs(crc32($dinilai->name)) % count($warna)];
    @endphp
    <div class="sv-target-row">
        <div class="sv-ava" style="background:{{ $warnaAva }}">{{ strtoupper(substr($dinilai->name,0,1)) }}</div>
        <div>
            <div class="sv-target-lbl">Anda sedang menilai</div>
            <div class="sv-target-name">{{ $dinilai->name }}</div>
        </div>
        <div style="margin-left:auto">
            <span class="sv-pill sv-pill-blue">{{ $labelPeriode }}</span>
        </div>
    </div>

    {{-- Note anonim --}}
    <div class="sv-info-sm">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24" style="flex-shrink:0"><path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/></svg>
        Nama Anda tidak akan ditampilkan di hasil penilaian. Identitas penilai dirahasiakan.
    </div>

    {{-- Form --}}
    <form action="{{ route('petugas.survey-internal.submit', $petugas->id) }}" method="POST" id="siForm">
        @csrf

        {{-- Pertanyaan dikelompokkan per kategori --}}
        @php $totalPertanyaan = $pertanyaan->count(); $nomorUrut = 0; @endphp
        @foreach($pertanyaanPerKategori as $grup)
        <div class="sv-kategori-section">
            <div class="sv-kategori-title">{{ $grup['label'] }}</div>
            <div class="sv-q-grid">
            @foreach($grup['pertanyaan'] as $p)
            @php $nomorUrut++; @endphp
            <div class="sv-q-panel">
                <div class="sv-q-head">
                    <span>Pertanyaan {{ $nomorUrut }} dari {{ $totalPertanyaan }}</span>
                    @if($p->tipe==='rating')
                        <span class="sv-q-type sv-pill sv-pill-blue">Rating (bintang)</span>
                    @elseif($p->tipe==='pilihan')
                        <span class="sv-q-type" style="background:var(--amber-lt);color:var(--amber)">Pilihan</span>
                    @else
                        <span class="sv-q-type" style="background:var(--wash2);color:var(--ink3)">Teks bebas</span>
                    @endif
                </div>
                <div class="sv-q-body">
                    <div class="sv-q-text">
                        {{ $p->pertanyaan }}
                        @if($p->tipe!=='teks')<span style="color:#dc2626"> *</span>@endif
                    </div>

                    @if($p->tipe==='rating')
                        <div class="sv-star-row">
                            @for($s=5;$s>=1;$s--)
                                <input class="sv-star-input" type="radio" name="jawaban[{{ $p->id }}]"
                                       id="star_{{ $p->id }}_{{ $s }}" value="{{ $s }}" required>
                                <label class="sv-star-label" for="star_{{ $p->id }}_{{ $s }}" title="{{ $s }} bintang">★</label>
                            @endfor
                        </div>
                        <div class="sv-star-hint">1 = sangat kurang &nbsp;·&nbsp; 5 = sangat baik</div>
                        @error('jawaban.'.$p->id)<div class="sv-field-error">{{ $message }}</div>@enderror

                    @elseif($p->tipe==='pilihan')
                        <div class="sv-pilihan-list">
                            @foreach($p->opsi_pilihan??[] as $opsi)
                            <label class="sv-pilihan-opt">
                                <input type="radio" name="jawaban[{{ $p->id }}]" value="{{ $opsi }}" required>
                                {{ $opsi }}
                            </label>
                            @endforeach
                        </div>
                        @error('jawaban.'.$p->id)<div class="sv-field-error">{{ $message }}</div>@enderror

                    @else
                        <textarea name="jawaban[{{ $p->id }}]" class="sv-textarea"
                                  placeholder="Tulis jawaban Anda di sini… (opsional)">{{ old('jawaban.'.$p->id) }}</textarea>
                        <div class="sv-textarea-hint">Tidak wajib. Gunakan bahasa yang sopan dan membangun.</div>
                        @error('jawaban.'.$p->id)<div class="sv-field-error">{{ $message }}</div>@enderror
                    @endif
                </div>
            </div>
            @endforeach
            </div>{{-- end sv-q-grid --}}
        </div>{{-- end sv-kategori-section --}}
        @endforeach

        {{-- Submit --}}
        <div class="sv-submit-panel">
            <div class="sv-submit-area">
                <div class="sv-submit-note">
                    Setelah dikirim, jawaban <strong>tidak dapat diubah</strong>.<br>
                    Pastikan semua pertanyaan wajib sudah dijawab.
                </div>
                <div style="display:flex;gap:8px">
                    <a href="{{ route('petugas.survey-internal.index') }}" class="sv-btn sv-btn-secondary">Batal</a>
                    <button type="submit" class="sv-btn sv-btn-primary sv-btn-lg" id="siSubmitBtn">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                        Kirim Penilaian
                    </button>
                </div>
            </div>
        </div>

    </form>
</div>
@endsection

@push('scripts')
<script>
document.getElementById('siForm').addEventListener('submit', function() {
    const btn = document.getElementById('siSubmitBtn');
    btn.disabled = true;
    btn.innerHTML = `<svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"
        style="animation:spin 1s linear infinite"><path d="M21 12a9 9 0 11-6.219-8.56"/></svg> Mengirim…`;
});
</script>
<style>
@keyframes spin { to { transform:rotate(360deg); } }
</style>
@endpush