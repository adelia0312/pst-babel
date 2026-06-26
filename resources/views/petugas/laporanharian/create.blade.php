@extends('layouts.petugas')
@section('title', 'Input Laporan Harian')

@push('styles')
<style>
.lh-header { margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule); }
.lh-header h1 { font-size:18px;font-weight:600; }
.lh-header p  { font-size:12px;color:var(--ink3);margin-top:3px; }

.meta-bar { display:grid;grid-template-columns:repeat(4,1fr);gap:1px;background:var(--rule);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:20px; }
.meta-cell { background:var(--surface);padding:12px 16px; }
.meta-label { font-size:10px;font-weight:600;text-transform:uppercase;letter-spacing:.6px;color:var(--ink3);margin-bottom:4px; }
.meta-val   { font-size:14px;font-weight:600;color:var(--ink); }
.meta-auto  { font-size:9.5px;color:var(--ink3);margin-top:3px;font-style:italic; }

.panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:16px; }
.ph { display:flex;align-items:center;padding:13px 18px;border-bottom:1px solid var(--rule);gap:10px; }
.ph-num { width:22px;height:22px;border-radius:50%;background:var(--blue);color:#fff;font-size:11px;font-weight:600;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.ph-title { font-size:13px;font-weight:600;flex:1; }
.ph-wajib { font-size:10px;color:var(--red);font-weight:600; }

.q-body { padding:14px 18px; }
.q-desc { font-size:11.5px;color:var(--ink3);margin-bottom:10px;font-style:italic; }
.q-input textarea {
    width:100%;padding:10px 12px;font-size:12.5px;border:1px solid var(--rule);
    border-radius:5px;background:var(--wash);resize:vertical;min-height:80px;
    font-family:'IBM Plex Sans',sans-serif;color:var(--ink);transition:border-color .15s;
}
.q-input textarea:focus { outline:none;border-color:var(--blue); }
.q-input select {
    width:100%;height:38px;padding:0 12px;font-size:12.5px;border:1px solid var(--rule);
    border-radius:5px;background:var(--wash);color:var(--ink);
    font-family:'IBM Plex Sans',sans-serif;cursor:pointer;
    appearance:none;
    background-image:url("data:image/svg+xml,%3Csvg xmlns='http://www.w3.org/2000/svg' width='12' height='12' fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24'%3E%3Cpolyline points='6 9 12 15 18 9'/%3E%3C/svg%3E");
    background-repeat:no-repeat;
    background-position:right 12px center;
    padding-right:32px;
}
.q-input select:focus { outline:none;border-color:var(--blue); }

.form-footer { display:flex;align-items:center;gap:8px;padding:16px 0; }
.btn-draft  { height:34px;padding:0 16px;font-size:12.5px;font-weight:500;background:var(--surface);color:var(--ink2);border:1px solid var(--rule);border-radius:5px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif; }
.btn-submit { height:34px;padding:0 18px;font-size:12.5px;font-weight:600;background:var(--blue);color:#fff;border:none;border-radius:5px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;display:inline-flex;align-items:center;gap:6px; }
.btn-submit:hover { opacity:.88; }
.btn-cancel { height:34px;padding:0 16px;font-size:12.5px;color:var(--ink3);border:1px solid var(--rule);border-radius:5px;text-decoration:none;display:inline-flex;align-items:center;background:var(--surface); }

.already-alert { background:var(--amber-lt);border:1px solid #d9770022;border-radius:8px;padding:14px 18px;margin-bottom:20px;font-size:12.5px;color:#92400e; }
.already-alert strong { display:block;margin-bottom:2px;font-size:13px; }
</style>
@endpush

@section('breadcrumb')
    <a href="{{ route('petugas.laporan.harian.index') }}" style="color:var(--ink3);text-decoration:none">Laporan Harian</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <span>Input Baru</span>
@endsection

@section('content')

<div class="lh-header">
    <h1>Input Laporan Harian PST</h1>
    <p>Data petugas, tanggal, dan hari diisi otomatis oleh sistem</p>
</div>

@if(session('error'))
    <div style="background:var(--red-lt);color:var(--red);padding:10px 16px;border-radius:7px;margin-bottom:16px;font-size:12.5px">
        {{ session('error') }}
    </div>
@endif

{{-- Meta otomatis dari sistem --}}
<div class="meta-bar">
    <div class="meta-cell">
        <div class="meta-label">Tanggal</div>
        <div class="meta-val">{{ $tanggal->format('d/m/Y') }}</div>
        <div class="meta-auto">Otomatis</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Hari</div>
        <div class="meta-val">{{ $tanggal->locale('id')->isoFormat('dddd') }}</div>
        <div class="meta-auto">Otomatis</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Nama Petugas</div>
        <div class="meta-val">{{ $user->name }}</div>
        <div class="meta-auto">Otomatis</div>
    </div>
    <div class="meta-cell">
        <div class="meta-label">Wilayah</div>
        <div class="meta-val">{{ $petugas?->wilayah?->nama ?? '-' }}</div>
        <div class="meta-auto">Otomatis</div>
    </div>
</div>

@if($templates->isEmpty())
    <div class="panel" style="padding:32px;text-align:center;color:var(--ink3)">
        <p style="font-size:13px">Belum ada pertanyaan laporan yang dibuat admin.</p>
        <p style="font-size:12px;margin-top:4px">Hubungi admin untuk menambahkan pertanyaan terlebih dahulu.</p>
    </div>
@else

<form id="form-laporan" method="POST" action="{{ route('petugas.laporan.harian.store') }}">
    @csrf
    <input type="hidden" name="tanggal" value="{{ $tanggal->toDateString() }}">
    <input type="hidden" name="action" id="action-input" value="draft">
    <input type="hidden" name="sesi" value="{{ $sesiAktif }}">

    @if($sudahAda)
    <div class="already-alert">
        <strong>Laporan sesi {{ $sesiAktif }} hari ini sudah ada.</strong>
        Silakan tunggu sesi berikutnya atau hubungi koordinator jika ada kesalahan.
    </div>
    @endif

    @foreach($templates as $idx => $tpl)
    <div class="panel">
        <div class="ph">
            <span class="ph-num">{{ $idx + 1 }}</span>
            <span class="ph-title">{{ $tpl->judul }}</span>
            @if($tpl->wajib) <span class="ph-wajib">Wajib</span> @endif
        </div>
        <div class="q-body">
            @if($tpl->deskripsi)
                <div class="q-desc">{{ $tpl->deskripsi }}</div>
            @endif
            <div class="q-input">
                @if($tpl->tipe === 'pilihan')
                    <select name="jawaban_{{ $tpl->id }}" {{ $tpl->wajib ? 'required' : '' }}>
                        <option value="">— Pilih jawaban —</option>
                        @foreach($tpl->opsi ?? [] as $opsi)
                            <option value="{{ $opsi }}" {{ old('jawaban_'.$tpl->id) === $opsi ? 'selected' : '' }}>
                                {{ $opsi }}
                            </option>
                        @endforeach
                    </select>
                @else
                    <textarea
                        name="jawaban_{{ $tpl->id }}"
                        placeholder="Tulis jawaban Anda di sini..."
                        {{ $tpl->wajib ? 'required' : '' }}>{{ old('jawaban_'.$tpl->id) }}</textarea>
                @endif
            </div>
        </div>
    </div>
    @endforeach

    <div class="form-footer">
        <a href="{{ route('petugas.laporan.harian.index') }}" class="btn-cancel">Batal</a>
        <button type="button" class="btn-draft" onclick="doAction('draft')">Simpan Draft</button>
        <button type="button" class="btn-submit" onclick="doAction('submit')">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/>
            </svg>
            Kirim ke Koordinator
        </button>
    </div>
</form>

@endif

@endsection

@push('scripts')
<script>
function doAction(action) {
    document.getElementById('action-input').value = action;
    document.getElementById('form-laporan').submit();
}
</script>
@endpush