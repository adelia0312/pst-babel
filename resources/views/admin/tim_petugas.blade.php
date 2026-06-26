@extends('layouts.admin')

@section('title', 'Tim Petugas')

@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <strong>Tim Petugas</strong>
@endsection

@section('content')

    <div class="page-head">
        <div>
            <h1>Tim Petugas</h1>
            <p>Pilih wilayah untuk melihat daftar petugas</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert alert-success" id="flash-msg">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <polyline points="20 6 9 17 4 12"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="alert alert-error" id="flash-msg">
            <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <circle cx="12" cy="12" r="10"/>
                <line x1="12" y1="8" x2="12" y2="12"/>
                <line x1="12" y1="16" x2="12.01" y2="16"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Daftar Wilayah</div>
                <div class="ph-sub">{{ $wilayahList->count() }} wilayah terdaftar</div>
            </div>
        </div>

        @if($wilayahList->isEmpty())
            <div class="empty">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="8" x2="12" y2="12"/>
                    <line x1="12" y1="16" x2="12.01" y2="16"/>
                </svg>
                Belum ada wilayah yang terdaftar.<br>
                Klik tombol <strong>+</strong> untuk menambahkan.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:60px">#</th>
                        <th>Nama Wilayah</th>
                        <th>Lokasi</th>
                        <th>Status</th>
                        <th style="text-align:right; width:260px">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($wilayahList as $w)
                    <tr>
                        <td class="td-id">{{ str_pad($loop->iteration, 2, '0', STR_PAD_LEFT) }}</td>
                        <td><div class="td-main">{{ $w->nama }}</div></td>
                        <td><div style="color:#64748b; font-size:.85rem">{{ $w->lokasi ?? '-' }}</div></td>
                        <td><span class="badge badge-{{ $w->status }}">{{ ucfirst($w->status) }}</span></td>
                        <td style="text-align:right">
                            <div style="display:flex; gap:6px; justify-content:flex-end; align-items:center">

                                <button class="btn-icon btn-edit"
                                    onclick="modalEdit({{ $w->id }}, '{{ addslashes($w->nama) }}', '{{ addslashes($w->lokasi ?? '') }}', '{{ addslashes($w->alamat ?? '') }}', '{{ $w->status }}')">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <path d="M11 4H4a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h14a2 2 0 0 0 2-2v-7"/>
                                        <path d="M18.5 2.5a2.121 2.121 0 0 1 3 3L12 15l-4 1 1-4 9.5-9.5z"/>
                                    </svg>
                                    Edit
                                </button>

                                <button class="btn-icon btn-hapus"
                                    onclick="modalHapus({{ $w->id }}, '{{ addslashes($w->nama) }}')">
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="3 6 5 6 21 6"/>
                                        <path d="M19 6l-1 14a2 2 0 0 1-2 2H8a2 2 0 0 1-2-2L5 6"/>
                                        <path d="M10 11v6M14 11v6M9 6V4a1 1 0 0 1 1-1h4a1 1 0 0 1 1 1v2"/>
                                    </svg>
                                    Hapus
                                </button>

                                <a href="{{ route('admin.tim-petugas.detail', $w->id) }}" class="btn-detail">
                                    Lihat Petugas
                                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <line x1="5" y1="12" x2="19" y2="12"/>
                                        <polyline points="12 5 19 12 12 19"/>
                                    </svg>
                                </a>

                            </div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

@endsection

@push('modals')

    {{-- FAB Tambah --}}
    <button class="fab" onclick="modalOpen('modalTambah')" title="Tambah Wilayah">
        <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24">
            <line x1="12" y1="5" x2="12" y2="19"/>
            <line x1="5" y1="12" x2="19" y2="12"/>
        </svg>
    </button>

    {{-- MODAL TAMBAH --}}
    <div class="modal-overlay" id="modalTambah" onclick="overlayClose(event,'modalTambah')">
        <div class="modal">
            <div class="modal-head">
                <div class="modal-title">Tambah Wilayah Baru</div>
                <button class="modal-close" onclick="modalClose('modalTambah')">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <form method="POST" action="{{ route('admin.wilayah.store') }}">
                @csrf
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="t-nama">Nama Wilayah <span>*</span></label>
                        <input class="form-input @error('nama') error @enderror"
                               type="text" id="t-nama" name="nama"
                               value="{{ old('nama') }}"
                               placeholder="cth. Pangkalpinang" autocomplete="off">
                        @error('nama')
                            <div class="form-hint show">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="t-lokasi">Lokasi / Kabupaten</label>
                        <input class="form-input" type="text" id="t-lokasi" name="lokasi"
                               value="{{ old('lokasi') }}"
                               placeholder="cth. Kota Pangkalpinang" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="t-alamat">Alamat Lengkap</label>
                        <textarea class="form-textarea" id="t-alamat" name="alamat"
                                  placeholder="Jl. ...">{{ old('alamat') }}</textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="t-status">Status</label>
                        <select class="form-select" id="t-status" name="status">
                            <option value="aktif"    {{ old('status','aktif') == 'aktif'    ? 'selected' : '' }}>Aktif</option>
                            <option value="nonaktif" {{ old('status')         == 'nonaktif' ? 'selected' : '' }}>Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-cancel" onclick="modalClose('modalTambah')">Batal</button>
                    <button type="submit" class="btn-submit">Simpan Wilayah</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL EDIT --}}
    <div class="modal-overlay" id="modalEdit" onclick="overlayClose(event,'modalEdit')">
        <div class="modal">
            <div class="modal-head">
                <div class="modal-title">Edit Wilayah</div>
                <button class="modal-close" onclick="modalClose('modalEdit')">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <form method="POST" id="formEdit">
                @csrf
                @method('PUT')
                <div class="modal-body">
                    <div class="form-group">
                        <label class="form-label" for="e-nama">Nama Wilayah <span>*</span></label>
                        <input class="form-input" type="text" id="e-nama" name="nama"
                               placeholder="cth. Pangkalpinang" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="e-lokasi">Lokasi / Kabupaten</label>
                        <input class="form-input" type="text" id="e-lokasi" name="lokasi"
                               placeholder="cth. Kota Pangkalpinang" autocomplete="off">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="e-alamat">Alamat Lengkap</label>
                        <textarea class="form-textarea" id="e-alamat" name="alamat"
                                  placeholder="Jl. ..."></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="e-status">Status</label>
                        <select class="form-select" id="e-status" name="status">
                            <option value="aktif">Aktif</option>
                            <option value="nonaktif">Nonaktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-foot">
                    <button type="button" class="btn-cancel" onclick="modalClose('modalEdit')">Batal</button>
                    <button type="submit" class="btn-submit">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    {{-- MODAL KONFIRMASI HAPUS --}}
    <div class="modal-overlay" id="modalHapus" onclick="overlayClose(event,'modalHapus')">
        <div class="modal" style="max-width:420px">
            <div class="modal-head">
                <div class="modal-title" style="color:#dc2626">Hapus Wilayah</div>
                <button class="modal-close" onclick="modalClose('modalHapus')">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <line x1="18" y1="6" x2="6" y2="18"/>
                        <line x1="6" y1="6" x2="18" y2="18"/>
                    </svg>
                </button>
            </div>
            <div class="modal-body">
                <p style="margin:0; color:#374151; line-height:1.7">
                    Apakah Anda yakin ingin menghapus wilayah<br>
                    <strong id="hapus-nama" style="color:#111827"></strong>?
                </p>
                <p style="margin:10px 0 0; font-size:.83rem; color:#6b7280">
                    ⚠️ Wilayah yang masih memiliki petugas tidak dapat dihapus.
                </p>
            </div>
            <div class="modal-foot">
                <button type="button" class="btn-cancel" onclick="modalClose('modalHapus')">Batal</button>
                <form method="POST" id="formHapus" style="display:inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn-submit" style="background:#dc2626">Ya, Hapus</button>
                </form>
            </div>
        </div>
    </div>

@endpush

@push('styles')
<style>
.alert {
    display: flex; align-items: center; gap: 8px;
    padding: 10px 16px; border-radius: 8px;
    margin-bottom: 16px; font-size: .875rem; font-weight: 500;
}
.alert-success { background:#f0fdf4; color:#166534; border:1px solid #bbf7d0; }
.alert-error   { background:#fef2f2; color:#991b1b; border:1px solid #fecaca; }

.badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:.78rem; font-weight:600; }
.badge-aktif    { background:#dcfce7; color:#15803d; }
.badge-nonaktif { background:#f1f5f9; color:#64748b; }

.btn-icon {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 10px; border-radius: 6px;
    font-size: .78rem; font-weight: 500;
    cursor: pointer; border: 1px solid transparent;
    transition: background .15s; background: none;
}
.btn-edit         { background:#eff6ff; color:#2563eb; border-color:#bfdbfe; }
.btn-edit:hover   { background:#dbeafe; }
.btn-hapus        { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
.btn-hapus:hover  { background:#fee2e2; }
</style>
@endpush

@push('scripts')
<script>
function modalOpen(id)  { document.getElementById(id).classList.add('open'); }
function modalClose(id) { document.getElementById(id).classList.remove('open'); }
function overlayClose(e, id) {
    if (e.target === document.getElementById(id)) modalClose(id);
}

document.addEventListener('keydown', e => {
    if (e.key === 'Escape') ['modalTambah','modalEdit','modalHapus'].forEach(modalClose);
});

function modalEdit(id, nama, lokasi, alamat, status) {
    document.getElementById('formEdit').action = '/admin/wilayah/' + id;
    document.getElementById('e-nama').value   = nama;
    document.getElementById('e-lokasi').value = lokasi;
    document.getElementById('e-alamat').value = alamat;
    document.getElementById('e-status').value = status;
    modalOpen('modalEdit');
    setTimeout(() => document.getElementById('e-nama').focus(), 150);
}

function modalHapus(id, nama) {
    document.getElementById('hapus-nama').textContent = nama;
    document.getElementById('formHapus').action = '/admin/wilayah/' + id;
    modalOpen('modalHapus');
}

// Auto dismiss flash setelah 4 detik
setTimeout(() => {
    const el = document.getElementById('flash-msg');
    if (el) {
        el.style.transition = 'opacity .5s';
        el.style.opacity = '0';
        setTimeout(() => el.remove(), 500);
    }
}, 4000);

// Buka modal tambah otomatis jika ada error validasi
@if($errors->any())
    window.addEventListener('DOMContentLoaded', () => modalOpen('modalTambah'));
@endif
</script>
@endpush