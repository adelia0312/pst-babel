@extends('layouts.admin')
@section('title', 'Kategori Penilaian')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">Dashboard</a> /
    <a href="{{ route('admin.survey.pertanyaan') }}">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Kategori Penilaian</strong>
@endsection

@push('styles')
<style>
    .kp-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 20px; border-bottom: 1px solid var(--rule);
        flex-wrap: wrap; gap: 12px;
    }
    .kp-topbar h1 { font-size: 19px; font-weight: 600; letter-spacing: -.3px; margin: 0; color: var(--ink); }
    .kp-topbar p  { font-size: 12px; color: var(--ink3); margin-top: 3px; max-width: 560px; }

    .kp-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s, background .15s;
    }
    .kp-btn-primary   { background: var(--blue); color: #fff; }
    .kp-btn-primary:hover { opacity: .88; }
    .kp-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .kp-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .kp-btn-sm    { height: 26px; padding: 0 10px; font-size: 11px; }
    .kp-btn-danger{ background: #fdecea; color: #c0392b; border: 1px solid #fbd5d5; }
    .kp-btn-danger:hover { background: #fbd5d5; }

    .kp-alert {
        display: flex; align-items: center; gap: 8px;
        padding: 10px 14px; border-radius: 6px; margin-bottom: 16px;
        font-size: 12.5px; font-weight: 500;
    }
    .kp-alert-success { background: var(--green-lt); color: var(--green); border: 1px solid #b7e4ce; }
    .kp-alert-error   { background: var(--red-lt);   color: var(--red);   border: 1px solid #f5c2c2; }

    .kp-komponen-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(280px, 1fr)); gap: 16px; margin-bottom: 24px; }
    .kp-panel { background: var(--surface); border: 1px solid var(--rule); border-radius: 8px; overflow: hidden; }
    .kp-ph { padding: 12px 16px; border-bottom: 1px solid var(--rule); background: var(--wash); }
    .kp-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }

    .kp-row {
        display: flex; align-items: center; justify-content: space-between;
        padding: 10px 16px; border-bottom: 1px solid var(--rule); gap: 10px;
    }
    .kp-row:last-child { border-bottom: none; }
    .kp-row-name { font-size: 12.5px; color: var(--ink); font-weight: 500; }
    .kp-row-meta { font-size: 10.5px; color: var(--ink3); margin-top: 2px; }
    .kp-row-actions { display: flex; gap: 6px; flex-shrink: 0; }

    .kp-pill { display: inline-block; font-size: 10px; font-weight: 500; padding: 2px 8px; border-radius: 3px; }
    .kp-pill-gray   { background: var(--wash2);    color: var(--ink3); }
    .kp-pill-purple { background: #f5f3ff;         color: #7c3aed; }

    .kp-empty { padding: 24px 16px; text-align: center; color: var(--ink3); font-size: 12px; }

    .kp-modal-bg {
        display: none; position: fixed; inset: 0; background: rgba(0,0,0,.4);
        align-items: center; justify-content: center; z-index: 1000;
    }
    .kp-modal {
        background: var(--surface); border-radius: 10px; padding: 22px;
        width: 420px; max-width: 92vw;
    }
    .kp-modal h3 { margin: 0 0 16px; font-size: 15px; color: var(--ink); }
    .kp-form-group { margin-bottom: 14px; }
    .kp-form-label { display: block; font-size: 12px; font-weight: 500; color: var(--ink2); margin-bottom: 5px; }
    .kp-form-control {
        width: 100%; padding: 8px 10px; border: 1px solid var(--rule); border-radius: 5px;
        font-size: 12.5px; font-family: 'IBM Plex Sans', sans-serif; color: var(--ink);
        background: var(--bg);
    }
    .kp-form-hint { font-size: 10.5px; color: var(--ink3); margin-top: 4px; }
    .kp-modal-footer { display: flex; justify-content: flex-end; gap: 8px; margin-top: 18px; }
</style>
@endpush

@section('content')
<div class="kp-topbar">
    <div>
        <h1>Kategori Penilaian</h1>
        <p>
            Kelola kategori penilaian Survey Internal. Setiap kategori baru wajib
            ditentukan komponen induknya (Sikap Kerja, Indikator Hasil, Indikator
            Proses, atau Mutu Pelayanan) agar otomatis ikut terhitung di komponen
            yang tepat. Kategori yang sudah pernah dipakai tidak bisa dihapus —
            nonaktifkan saja agar grafik triwulan lama tetap aman.
        </p>
    </div>
    <div style="display:flex;gap:8px">
        <a href="{{ route('admin.survey.pertanyaan') }}" class="kp-btn kp-btn-secondary">
            ← Kembali ke Survey Kepuasan
        </a>
        <button class="kp-btn kp-btn-primary" onclick="document.getElementById('modalTambah').style.display='flex'">
            + Tambah Kategori
        </button>
    </div>
</div>

@if(session('success'))
    <div class="kp-alert kp-alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="kp-alert kp-alert-error">{{ session('error') }}</div>
@endif

<div class="kp-komponen-grid">
    @foreach($komponenList as $komponenKey => $komponenLabel)
        @php $daftar = $kategoriPerKomponen->get($komponenKey, collect()); @endphp
        <div class="kp-panel">
            <div class="kp-ph">
                <span class="kp-ph-title">{{ $komponenLabel }}</span>
            </div>

            @forelse($daftar as $kategori)
                <div class="kp-row">
                    <div>
                        <div class="kp-row-name">
                            {{ $kategori->nama }}
                            @if(!$kategori->is_active)
                                <span class="kp-pill kp-pill-gray" style="margin-left:6px">Nonaktif</span>
                            @endif
                            @if($kategori->sumber === 'bawaan')
                                <span class="kp-pill kp-pill-purple" style="margin-left:6px">Bawaan</span>
                            @endif
                        </div>
                        <div class="kp-row-meta">kode: {{ $kategori->kode }}</div>
                    </div>
                    <div class="kp-row-actions">
                        <button class="kp-btn kp-btn-secondary kp-btn-sm"
                            onclick="openEditKategori({{ $kategori->id }}, {{ json_encode($kategori->nama) }}, '{{ $kategori->komponen }}')">
                            Edit
                        </button>
                        <form method="POST" action="{{ route('admin.kategori-penilaian.toggle', $kategori->id) }}" style="display:inline">
                            @csrf @method('PATCH')
                            <button type="submit" class="kp-btn kp-btn-secondary kp-btn-sm">
                                {{ $kategori->is_active ? 'Nonaktifkan' : 'Aktifkan' }}
                            </button>
                        </form>
                        @if($kategori->sumber !== 'bawaan')
                        <form method="POST" action="{{ route('admin.kategori-penilaian.destroy', $kategori->id) }}" style="display:inline"
                            onsubmit="return confirm('Hapus kategori ini? Hanya bisa dihapus jika belum pernah dipakai.')">
                            @csrf @method('DELETE')
                            <button type="submit" class="kp-btn kp-btn-danger kp-btn-sm">Hapus</button>
                        </form>
                        @endif
                    </div>
                </div>
            @empty
                <div class="kp-empty">Belum ada kategori pada komponen ini.</div>
            @endforelse
        </div>
    @endforeach
</div>

{{-- ── MODAL TAMBAH ── --}}
<div id="modalTambah" class="kp-modal-bg">
    <div class="kp-modal">
        <h3>Tambah Kategori Penilaian</h3>
        <form method="POST" action="{{ route('admin.kategori-penilaian.store') }}">
            @csrf
            <div class="kp-form-group">
                <label class="kp-form-label">Nama Kategori <span style="color:var(--red)">*</span></label>
                <input type="text" name="nama" class="kp-form-control" required maxlength="100"
                    placeholder="Contoh: Kepemimpinan">
            </div>
            <div class="kp-form-group">
                <label class="kp-form-label">Masuk Komponen <span style="color:var(--red)">*</span></label>
                <select name="komponen" class="kp-form-control" required>
                    <option value="">— Pilih komponen —</option>
                    @foreach($komponenList as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="kp-form-hint">
                    Nilai kategori ini akan otomatis ikut dirata-rata ke komponen yang dipilih.
                </div>
            </div>
            <div class="kp-modal-footer">
                <button type="button" class="kp-btn kp-btn-secondary" onclick="document.getElementById('modalTambah').style.display='none'">Batal</button>
                <button type="submit" class="kp-btn kp-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

{{-- ── MODAL EDIT ── --}}
<div id="modalEdit" class="kp-modal-bg">
    <div class="kp-modal">
        <h3>Edit Kategori Penilaian</h3>
        <form method="POST" id="formEditKategori">
            @csrf @method('PUT')
            <div class="kp-form-group">
                <label class="kp-form-label">Nama Kategori <span style="color:var(--red)">*</span></label>
                <input type="text" name="nama" id="editNama" class="kp-form-control" required maxlength="100">
            </div>
            <div class="kp-form-group">
                <label class="kp-form-label">Masuk Komponen <span style="color:var(--red)">*</span></label>
                <select name="komponen" id="editKomponen" class="kp-form-control" required>
                    @foreach($komponenList as $key => $label)
                        <option value="{{ $key }}">{{ $label }}</option>
                    @endforeach
                </select>
                <div class="kp-form-hint">
                    Mengubah komponen di sini tidak mengubah data evaluasi triwulan yang sudah tersimpan —
                    hanya berlaku untuk perhitungan ke depannya.
                </div>
            </div>
            <div class="kp-modal-footer">
                <button type="button" class="kp-btn kp-btn-secondary" onclick="document.getElementById('modalEdit').style.display='none'">Batal</button>
                <button type="submit" class="kp-btn kp-btn-primary">Simpan</button>
            </div>
        </form>
    </div>
</div>

<script>
const kpBaseUrlKategoriPenilaian = "{{ url('/admin/kategori-penilaian') }}";

function openEditKategori(id, nama, komponen) {
    document.getElementById('formEditKategori').action = kpBaseUrlKategoriPenilaian + '/' + id;
    document.getElementById('editNama').value = nama;
    document.getElementById('editKomponen').value = komponen;
    document.getElementById('modalEdit').style.display = 'flex';
}
</script>
@endsection