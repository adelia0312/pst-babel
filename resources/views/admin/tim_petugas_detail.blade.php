@extends('layouts.admin')

@section('title', $wilayah['nama'] . ' — Tim Petugas')

{{-- ── BREADCRUMB ── --}}
@section('breadcrumb')
    <a href="{{ url('/admin/dashboard') }}">PST</a>
    <span>›</span>
    <a href="{{ route('admin.tim-petugas') }}">Tim Petugas</a>
    <span>›</span>
    <strong>{{ $wilayah['nama'] }}</strong>
@endsection

@section('content')

    <a href="{{ route('admin.tim-petugas') }}" class="back-link">
        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
            <line x1="19" y1="12" x2="5" y2="12"/>
            <polyline points="12 19 5 12 12 5"/>
        </svg>
        Kembali ke daftar wilayah
    </a>

    <div class="page-head">
        <div>
            <h1>{{ $wilayah['nama'] }}</h1>
            <p>Daftar petugas wilayah ini &mdash; {{ $petugas->count() }} terdaftar</p>
        </div>
        {{-- Trigger Modal menggunakan Button type="button" --}}
        <button type="button" class="btn-add" id="btnOpenModal">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <line x1="12" y1="5" x2="12" y2="19"/>
                <line x1="5" y1="12" x2="19" y2="12"/>
            </svg>
            Tambah Petugas
        </button>
    </div>

    <div class="panel">
        <div class="ph">
            <div>
                <div class="ph-title">Petugas &mdash; {{ $wilayah['nama'] }}</div>
                <div class="ph-sub">Data berdasarkan wilayah yang dipilih</div>
            </div>
        </div>

        @if($petugas->isEmpty())
            <div class="empty">
                <svg width="32" height="32" fill="none" stroke="currentColor" stroke-width="1.2" viewBox="0 0 24 24">
                    <path d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/>
                    <circle cx="9" cy="7" r="4"/>
                    <path d="M23 21v-2a4 4 0 00-3-3.87M16 3.13a4 4 0 010 7.75"/>
                </svg>
                Belum ada petugas di wilayah ini.<br>
                Klik <strong>Tambah Petugas</strong> untuk menambahkan.
            </div>
        @else
            <table>
                <thead>
                    <tr>
                        <th style="width:35%">Nama</th>
                        <th>Username</th>
                        <th>Role</th>
                        <th>Status</th>
                        <th style="text-align:right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($petugas as $p)
                    <tr class="{{ $p->status === 'nonaktif' ? 'row-nonaktif' : '' }}">
                        <td>
                            <span class="mava">
                                {{ strtoupper(substr($p->user->name ?? 'P', 0, 2)) }}
                            </span>
                            <span class="td-main">{{ $p->user->name ?? '-' }}</span>
                        </td>
                        <td class="mono">{{ $p->user->username ?? '-' }}</td>
                        <td>
                            <span class="pill p-blue">{{ $p->user->role ?? '-' }}</span>
                        </td>
                        <td>
                            @php $status = $p->status ?? 'aktif'; @endphp
                            <span class="pill {{ $status === 'aktif' ? 'p-green' : 'p-gray' }}">
                                {{ ucfirst($status) }}
                            </span>
                        </td>
                        <td>
                            <div class="act-group">

    <!-- EDIT -->
    <a href="#" class="act-btn" title="Edit"
    onclick="openEditModal(
   '{{ $p->id }}',
   '{{ $p->user->name }}',
   '{{ $p->user->username }}',
   '{{ $p->user->no_hp }}',
   '{{ strtolower(trim($p->user->role)) }}',
   '{{ $p->status }}'
)">

        <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
            <path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/>
            <path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4 9.5-9.5z"/>
        </svg>

    </a>

    <!-- TOGGLE STATUS -->
    <form action="{{ route('petugas.toggle-status', $p->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('PATCH')
        <button type="submit" class="act-btn {{ $p->status === 'aktif' ? 'toggle-nonaktif' : 'toggle-aktif' }}"
            title="{{ $p->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} petugas"
            onclick="return confirm('{{ $p->status === 'aktif' ? 'Nonaktifkan' : 'Aktifkan' }} petugas {{ $p->user->name }}?')">
            @if($p->status === 'aktif')
                {{-- icon pause / nonaktifkan --}}
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <rect x="6" y="4" width="4" height="16" rx="1"/><rect x="14" y="4" width="4" height="16" rx="1"/>
                </svg>
            @else
                {{-- icon play / aktifkan --}}
                <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                    <polygon points="5 3 19 12 5 21 5 3"/>
                </svg>
            @endif
        </button>
    </form>

    <!-- HAPUS -->
    <form action="{{ route('petugas.destroy', $p->id) }}" method="POST" style="display:inline;">
        @csrf
        @method('DELETE')

        <button type="submit" class="act-btn del"
            onclick="return confirm('Yakin ingin hapus petugas ini?')">

            <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24">
                <polyline points="3 6 5 6 21 6"/>
                <path d="M19 6l-1 14a2 2 0 01-2 2H8a2 2 0 01-2-2L5 6"/>
                <path d="M10 11v6M14 11v6"/>
                <path d="M9 6V4a1 1 0 011-1h4a1 1 0 011 1v2"/>
            </svg>

        </button>
    </form>

</div>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        @endif
    </div>

    {{-- ── STRUKTUR MODAL (Diletakkan langsung di sini agar z-index aman) ── --}}
    {{-- MODAL TAMBAH --}}
<div id="modalAdd" class="pst-modal-overlay">
    <div class="pst-modal-card">
        <div class="pst-modal-head">
            <h3>Tambah Petugas Baru</h3>
            <button type="button" class="pst-close-x" id="btnCloseX">&times;</button>
        </div>

        <div class="pst-modal-body">
            <p class="pst-modal-info">Wilayah: <strong>{{ $wilayah['nama'] }}</strong></p>

            <form action="{{ route('petugas.store') }}" method="POST" id="formAddPetugas">
                @csrf
                <input type="hidden" name="wilayah_id" value="{{ $wilayah->id }}">

                <div class="pst-form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="name" required>
                </div>

                <div class="pst-form-group">
                    <label>Username</label>
                    <input type="text" name="username" required>
                </div>

                <div class="pst-form-group">
    <label>Password</label>

    <div style="position:relative;">
        <input type="password" name="password" id="passwordAdd" required style="width:100%; padding-right:40px;">

        <span onclick="togglePassword('passwordAdd', this)" 
              style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
            
            <!-- ICON MATA -->
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>

        </span>
    </div>
</div>

<div class="pst-form-group">
    <label>No HP</label>
    <input type="text" name="no_hp">
</div>

<div class="pst-form-group">
    <label>Role</label>
    <select name="role" required>
        <option value="">-- Pilih Role --</option>
        <option value="petugas">Petugas</option>
        <option value="koordinator">Koordinator</option>
    </select>
</div>

</div>
</form>

        <div class="pst-modal-foot">
            <button type="button" class="pst-btn-batal" id="btnCancel">Batal</button>
            <button type="submit" form="formAddPetugas" class="pst-btn-simpan">Simpan</button>
        </div>
    </div>
</div>


{{-- MODAL EDIT --}}
<div id="modalEdit" class="pst-modal-overlay">
    <div class="pst-modal-card">
        <div class="pst-modal-head">
            <h3>Edit Petugas</h3>
            <button type="button" class="pst-close-x" onclick="closeEditModal()">&times;</button>
        </div>

        <form method="POST" id="formEditPetugas">
            @csrf
            @method('PUT')

            <div class="pst-modal-body">
                <input type="hidden" name="wilayah_id" value="{{ $wilayah->id }}">

                <div class="pst-form-group">
                    <label>Nama</label>
                    <input type="text" name="name" id="edit_name" required>
                </div>

                <div class="pst-form-group">
                    <label>Username</label>
                    <input type="text" name="username" id="edit_username" required>
                </div>

                <div class="pst-form-group">
    <label>Password (opsional)</label>

    <div style="position:relative;">
        <input type="password" name="password" id="passwordEdit" placeholder="Kosongkan jika tidak diubah" style="width:100%; padding-right:40px;">

        <span onclick="togglePassword('passwordEdit', this)" 
              style="position:absolute; right:10px; top:50%; transform:translateY(-50%); cursor:pointer;">
            
            <!-- ICON MATA -->
            <svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7-11-7-11-7z"/>
                <circle cx="12" cy="12" r="3"/>
            </svg>

        </span>
    </div>
</div>

                <div class="pst-form-group">
    <label>No HP</label>
    <input type="text" name="no_hp" id="edit_nohp">
</div>

<div class="pst-form-group">
    <label>Role</label>
    <select name="role" id="edit_role" required>
    <option value="petugas">Petugas</option>
    <option value="koordinator">Koordinator</option>
</select>
</div>

<div class="pst-form-group">
    <label>Status</label>
    <select name="status" id="edit_status" required>
        <option value="aktif">Aktif</option>
        <option value="nonaktif">Nonaktif</option>
    </select>
</div>
</div>

            <div class="pst-modal-foot">
                <button type="button" onclick="closeEditModal()" class="pst-btn-batal">Batal</button>
                <button type="submit" class="pst-btn-simpan">Update</button>
            </div>
        </form>
    </div>
</div>

@endsection

@push('styles')
<style>
    /* Gunakan prefix unik 'pst-' agar tidak tabrakan dengan CSS admin.css */
    .pst-modal-overlay {
        position: fixed;
        top: 0; left: 0; 
        width: 100%; height: 100%;
        background: rgba(15, 23, 42, 0.6); /* Lebih gelap */
        backdrop-filter: blur(4px);
        display: none; 
        align-items: center;
        justify-content: center;
        z-index: 9999 !important; /* Pastikan di atas semua elemen */
        pointer-events: all; /* Pastikan bisa menerima klik */
    }

    .pst-modal-overlay.active {
        display: flex;
    }

    .pst-modal-card {
        background: #ffffff;
        width: 90%;
        max-width: 500px;
        border-radius: 12px;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 10px 10px -5px rgba(0, 0, 0, 0.04);
        position: relative;
        z-index: 10000;
        pointer-events: all;
    }

    .pst-modal-head {
        padding: 20px 24px;
        border-bottom: 1px solid #f1f5f9;
        display: flex;
        justify-content: space-between;
        align-items: center;
    }

    .pst-modal-head h3 { margin: 0; font-size: 1.15rem; color: #0f172a; font-weight: 600; }
    
    .pst-close-x {
        background: transparent; border: none; font-size: 24px; color: #94a3b8; cursor: pointer; line-height: 1;
    }

    .pst-modal-body { padding: 24px; }
    .pst-modal-info { font-size: 0.85rem; color: #64748b; margin-bottom: 20px; background: #f8fafc; padding: 8px 12px; border-radius: 6px; }

    .pst-form-group { margin-bottom: 16px; }
    .pst-form-group label { display: block; font-size: 0.85rem; font-weight: 500; color: #334155; margin-bottom: 6px; }
    
    .pst-form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px; }
    
    .pst-modal-body input, .pst-modal-body select {
        width: 100%; padding: 10px 12px; border: 1px solid #e2e8f0; border-radius: 8px; font-size: 0.9rem;
    }

    .pst-modal-body input:focus { outline: none; border-color: #3b82f6; box-shadow: 0 0 0 3px rgba(59, 130, 246, 0.1); }

    .pst-modal-foot {
        padding: 16px 24px;
        background: #f8fafc;
        border-top: 1px solid #f1f5f9;
        display: flex;
        justify-content: flex-end;
        gap: 12px;
        border-bottom-left-radius: 12px;
        border-bottom-right-radius: 12px;
    }

    .pst-btn-batal { background: #fff; border: 1px solid #e2e8f0; padding: 10px 18px; border-radius: 8px; font-weight: 500; cursor: pointer; color: #64748b; }
    .pst-btn-simpan { background: #1e293b; border: none; padding: 10px 18px; border-radius: 8px; font-weight: 500; cursor: pointer; color: #fff; }
    .pst-btn-simpan:hover { background: #0f172a; }

    /* Toggle status buttons */
    .act-btn.toggle-nonaktif { color: #d97706; background: #fffbeb; border: 1px solid #fde68a; }
    .act-btn.toggle-nonaktif:hover { background: #fef3c7; }
    .act-btn.toggle-aktif { color: #16a34a; background: #f0fdf4; border: 1px solid #bbf7d0; }
    .act-btn.toggle-aktif:hover { background: #dcfce7; }

    /* Row nonaktif redup */
    tr.row-nonaktif td { opacity: 0.55; }
</style>
@endpush

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function() {
    const modal = document.getElementById('modalAdd');
    const btnOpen = document.getElementById('btnOpenModal');
    const btnCancel = document.getElementById('btnCancel');
    const btnCloseX = document.getElementById('btnCloseX');
    const form = document.getElementById('formAddPetugas');

    // Fungsi Buka
    btnOpen.addEventListener('click', function(e) {
        e.preventDefault();
        modal.classList.add('active');
    });


    
    // Fungsi Tutup
    function closePSTModal() {
        modal.classList.remove('active');
        form.reset();
    }

    btnCancel.addEventListener('click', closePSTModal);
    btnCloseX.addEventListener('click', closePSTModal);

    // Klik Luar Modal untuk Tutup
    modal.addEventListener('click', function(e) {
        if (e.target === modal) {
            closePSTModal();
        }
    });
});

// Ganti seluruh fungsi openEditModal:
function openEditModal(id, name, username, nohp, role, status) {
    document.getElementById('formEditPetugas').action = '/admin/petugas/' + id;
    document.getElementById('edit_name').value     = name     || '';
    document.getElementById('edit_username').value = username || '';
    document.getElementById('edit_nohp').value     = nohp     || '';
    document.getElementById('edit_role').value     = (role || '').trim().toLowerCase();
    document.getElementById('edit_status').value   = (status || 'aktif').trim().toLowerCase();
    document.getElementById('modalEdit').classList.add('active');
}
function closeEditModal() {
    document.getElementById('modalEdit').classList.remove('active');
}

function togglePassword(id, el) {
    const input = document.getElementById(id);

    if (input.type === "password") {
        input.type = "text";
    } else {
        input.type = "password";
    }
}
</script>
@endpush