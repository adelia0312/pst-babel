@extends('layouts.admin')

@section('title', 'Kelola Template Checklist — Admin')

@section('breadcrumb')
    <span>Admin</span>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <a href="{{ route('admin.checklist.index') }}">Checklist Harian</a>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Kelola Template</strong>
@endsection

@push('styles')
<style>
.page-header { display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule);flex-wrap:wrap;gap:12px; }
.page-header h1 { font-size:19px;font-weight:600;letter-spacing:-.3px; }
.page-header p  { font-size:12px;color:var(--ink3);margin-top:3px; }
.role-badge { display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;background:#1a56db18;color:var(--blue);border:1px solid #1a56db28;padding:3px 10px;border-radius:20px; }

/* Wilayah tabs */
.wilayah-tabs { display:flex;gap:6px;margin-bottom:18px;flex-wrap:wrap; }
.w-tab { height:30px;padding:0 14px;font-size:12px;font-weight:500;border:1px solid var(--rule);border-radius:5px;text-decoration:none;color:var(--ink2);background:var(--surface);display:inline-flex;align-items:center;transition:all .12s; }
.w-tab:hover { border-color:var(--blue);color:var(--blue); }
.w-tab.active { background:var(--blue);color:#fff;border-color:var(--blue); }

.panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:18px; }
.ph { display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px;font-weight:600; }
.ph-sub   { font-size:11px;color:var(--ink3);margin-top:1px; }

.add-form { display:flex;gap:8px;padding:14px 18px;background:var(--wash);border-bottom:1px solid var(--rule);flex-wrap:wrap; }
.add-form input[type=text] { flex:1;min-width:180px;height:34px;padding:0 12px;font-size:12.5px;border:1px solid var(--rule);border-radius:5px;background:var(--surface);color:var(--ink);font-family:'IBM Plex Sans',sans-serif; }
.add-form input[type=url]  { width:220px;height:34px;padding:0 12px;font-size:12px;border:1px solid var(--rule);border-radius:5px;background:var(--surface);color:var(--ink);font-family:'IBM Plex Sans',sans-serif; }
.btn-add { height:34px;padding:0 16px;font-size:12px;font-weight:500;background:var(--blue);color:#fff;border:none;border-radius:5px;cursor:pointer;display:inline-flex;align-items:center;gap:6px;white-space:nowrap; }

.item-row { display:flex;align-items:center;gap:10px;padding:10px 18px;border-bottom:1px solid var(--rule);transition:background .1s; }
.item-row:last-child { border-bottom:none; }
.item-row:hover { background:var(--wash); }
.drag-handle { color:var(--ink3);cursor:grab; }
.item-num { font-size:11px;font-family:'IBM Plex Mono',monospace;color:var(--ink3);width:20px;text-align:right;flex-shrink:0; }
.item-info { flex:1;min-width:0; }
.item-label { font-size:12.5px;font-weight:500; }
.item-link  { font-size:11px;color:var(--blue);margin-top:2px; }
.inactive-badge { display:inline-block;font-size:10px;background:var(--wash2);color:var(--ink3);padding:1px 7px;border-radius:3px;margin-left:6px; }
.act-btns { display:flex;gap:5px;flex-shrink:0; }
.btn-icon { width:28px;height:28px;display:flex;align-items:center;justify-content:center;border:1px solid var(--rule);background:var(--surface);border-radius:4px;cursor:pointer;color:var(--ink3);transition:all .12s; }
.btn-icon:hover { border-color:var(--ink2);color:var(--ink); }
.btn-icon.del:hover { border-color:#c0392b44;color:var(--red);background:var(--red-lt); }

.tpl-modal-bg { display:none;position:fixed;inset:0;background:#00000044;z-index:1100;align-items:center;justify-content:center; }
.tpl-modal-bg.open { display:flex; }
.tpl-modal { background:var(--surface);border:1px solid var(--rule);border-radius:10px;padding:24px;width:460px;max-width:calc(100vw - 32px);box-shadow:0 8px 32px #0002; }
.tpl-modal h2 { font-size:15px;font-weight:600;margin-bottom:16px; }
.f-group { margin-bottom:12px; }
.f-label { font-size:11px;font-weight:500;color:var(--ink3);margin-bottom:4px; }
.f-input { width:100%;height:36px;padding:0 12px;font-size:12.5px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);color:var(--ink);font-family:'IBM Plex Sans',sans-serif; }
.f-input:focus { outline:none;border-color:var(--blue); }
.f-row { display:flex;gap:8px;margin-top:16px;justify-content:flex-end; }
.btn-cancel { height:34px;padding:0 14px;font-size:12px;background:var(--surface);color:var(--ink2);border:1px solid var(--rule);border-radius:5px;cursor:pointer; }
.btn-save   { height:34px;padding:0 16px;font-size:12px;font-weight:500;background:var(--blue);color:#fff;border:none;border-radius:5px;cursor:pointer; }

.flash { display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:7px;margin-bottom:16px;font-size:12.5px;font-weight:500; }
.flash-ok  { background:var(--green-lt);color:var(--green);border:1px solid #0a7c4e22; }
.flash-err { background:var(--red-lt);color:var(--red);border:1px solid #c0392b22; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/></svg> {{ session('success') }}</div>
@endif
@if(session('error'))
<div class="flash flash-err"><svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/></svg> {{ session('error') }}</div>
@endif

<div class="page-header">
    <div>
        <h1>Kelola Template Checklist</h1>
        <p>Atur item checklist per wilayah — koordinator hanya bisa kelola wilayahnya sendiri</p>
    </div>
    <span class="role-badge">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Admin — Semua Wilayah
    </span>
</div>

{{-- Filter wilayah --}}
<div class="wilayah-tabs">
    <a href="{{ route('admin.checklist.template') }}"
        class="w-tab {{ !$wilayahId ? 'active' : '' }}">Global (Tanpa Wilayah)</a>
    @foreach($wilayahList as $w)
    <a href="{{ route('admin.checklist.template', ['wilayah_id' => $w->id]) }}"
        class="w-tab {{ $wilayahId == $w->id ? 'active' : '' }}">{{ $w->nama }}</a>
    @endforeach
</div>

<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">
                Item Checklist — {{ $wilayahDipilih ? $wilayahDipilih->nama : 'Global' }}
            </div>
            <div class="ph-sub">{{ $templates->count() }} item</div>
        </div>
        <a href="{{ route('admin.checklist.index') }}" style="font-size:12px;color:var(--blue);text-decoration:none">← Monitor Checklist</a>
    </div>

    {{-- Tambah item --}}
    <form method="POST" action="{{ route('admin.checklist.template.store') }}" class="add-form">
        @csrf
        <input type="hidden" name="wilayah_id" value="{{ $wilayahId }}">
        <input type="text" name="label" placeholder="Nama item checklist baru..." required>
        <input type="url" name="link" placeholder="Link terkait (opsional)">
        <button type="submit" class="btn-add">
            <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg>
            Tambah Item
        </button>
    </form>

    @if($templates->isEmpty())
    <div style="padding:40px 20px;text-align:center;color:var(--ink3);font-size:12px">Belum ada item. Tambahkan di atas.</div>
    @else
    <div id="sortable-list">
        @foreach($templates as $idx => $tpl)
        <div class="item-row" data-id="{{ $tpl->id }}">
            <span class="drag-handle">
                <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="8" y1="6" x2="16" y2="6"/><line x1="8" y1="12" x2="16" y2="12"/><line x1="8" y1="18" x2="16" y2="18"/></svg>
            </span>
            <span class="item-num">{{ $idx + 1 }}</span>
            <div class="item-info">
                <div class="item-label">{{ $tpl->label }}@if(!$tpl->is_active)<span class="inactive-badge">nonaktif</span>@endif</div>
                @if($tpl->link)<div class="item-link">{{ $tpl->link }}</div>@endif
            </div>
            <div class="act-btns">
                <button type="button" class="btn-icon"
                    onclick="openEdit({{ $tpl->id }}, {{ json_encode($tpl->label) }}, {{ json_encode($tpl->link) }}, {{ $tpl->is_active ? 'true' : 'false' }})">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M11 4H4a2 2 0 00-2 2v14a2 2 0 002 2h14a2 2 0 002-2v-7"/><path d="M18.5 2.5a2.121 2.121 0 013 3L12 15l-4 1 1-4z"/></svg>
                </button>
                <form method="POST" action="{{ route('admin.checklist.template.destroy', $tpl->id) }}" style="display:inline" onsubmit="return confirm('Hapus item ini?')">
                    @csrf @method('DELETE')
                    <button type="submit" class="btn-icon del">
                        <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="3 6 5 6 21 6"/><path d="M19 6v14a2 2 0 01-2 2H7a2 2 0 01-2-2V6m3 0V4a1 1 0 011-1h4a1 1 0 011 1v2"/></svg>
                    </button>
                </form>
            </div>
        </div>
        @endforeach
    </div>
    @endif
</div>

{{-- Modal Edit --}}
<div class="tpl-modal-bg" id="editModal">
    <div class="tpl-modal">
        <h2>Edit Item Checklist</h2>
        <form method="POST" id="editForm">
            @csrf @method('PUT')
            <div class="f-group">
                <div class="f-label">Label Item *</div>
                <input type="text" name="label" id="editLabel" class="f-input" required>
            </div>
            <div class="f-group">
                <div class="f-label">Link Terkait</div>
                <input type="text" name="link" id="editLink" class="f-input" placeholder="https://..." pattern="https?://.+" title="Masukkan URL yang valid (diawali https:// atau http://)">
            </div>
            <div class="f-group" style="display:flex;align-items:center;gap:8px;margin-top:12px">
                <input type="checkbox" name="is_active" id="editActive" value="1" style="width:14px;height:14px">
                <label for="editActive" style="font-size:12.5px">Item aktif</label>
            </div>
            <div class="f-row">
                <button type="button" class="btn-cancel" onclick="closeEdit()">Batal</button>
                <button type="submit" class="btn-save">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function openEdit(id, label, link, isActive) {
    document.getElementById('editLabel').value    = label || '';
    document.getElementById('editLink').value     = (link && link !== 'null') ? link : '';
    document.getElementById('editActive').checked = isActive;
    document.getElementById('editForm').action    = '/admin/checklist-template/' + id;
    document.getElementById('editModal').classList.add('open');
    setTimeout(() => document.getElementById('editLabel').focus(), 100);
}
function closeEdit() { document.getElementById('editModal').classList.remove('open'); }
document.getElementById('editModal').addEventListener('click', function(e){ if(e.target===this) closeEdit(); });

const list = document.getElementById('sortable-list');
if (list) {
    let dragging = null;
    list.querySelectorAll('.item-row').forEach(row => {
        row.draggable = true;
        row.addEventListener('dragstart', () => { dragging = row; row.style.opacity = '.4'; });
        row.addEventListener('dragend',   () => { dragging = null; row.style.opacity = ''; saveOrder(); });
        row.addEventListener('dragover',  e => {
            e.preventDefault();
            const after = getDragAfterElement(list, e.clientY);
            if (after == null) list.appendChild(dragging);
            else list.insertBefore(dragging, after);
        });
    });
    function getDragAfterElement(c, y) {
        return [...c.querySelectorAll('.item-row:not([style*="opacity: 0.4"])')].reduce((cl, ch) => {
            const box = ch.getBoundingClientRect(), off = y - box.top - box.height/2;
            return (off < 0 && off > cl.offset) ? {offset:off,element:ch} : cl;
        }, {offset:Number.NEGATIVE_INFINITY}).element;
    }
    function saveOrder() {
        const ids = [...list.querySelectorAll('.item-row')].map(r => r.dataset.id);
        fetch('{{ route('admin.checklist.template.reorder') }}', {
            method:'POST',
            headers:{'Content-Type':'application/json','X-CSRF-TOKEN':'{{ csrf_token() }}'},
            body: JSON.stringify({order:ids})
        });
        list.querySelectorAll('.item-num').forEach((el,i) => el.textContent = i+1);
    }
}
</script>
@endpush

@endsection