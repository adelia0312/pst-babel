@extends('layouts.petugas')

@section('title', 'Checklist Harian Saya')

@section('breadcrumb')
    <span>Petugas</span>
    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="9 18 15 12 9 6"/></svg>
    <strong>Checklist Harian</strong>
@endsection

@push('styles')
<style>
.cl-header { display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:22px;padding-bottom:20px;border-bottom:1px solid var(--rule);flex-wrap:wrap;gap:12px; }
.cl-header h1 { font-size:19px;font-weight:600;letter-spacing:-.3px; }
.cl-header p  { font-size:12px;color:var(--ink3);margin-top:3px; }
.role-badge { display:inline-flex;align-items:center;gap:5px;font-size:10.5px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;background:#b4530918;color:var(--amber);border:1px solid #b4530928;padding:3px 10px;border-radius:20px; }

/* Shift panel */
.shift-panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden;margin-bottom:18px; }
.shift-head { display:flex;align-items:center;gap:10px;padding:13px 18px;border-bottom:2px solid; }
.shift-head.pagi  { border-color:#f59e0b;background:#fffbeb; }
.shift-head.siang { border-color:#3b82f6;background:#eff6ff; }
.shift-head.malam { border-color:#8b5cf6;background:#f5f3ff; }
.shift-icon { width:32px;height:32px;border-radius:6px;display:flex;align-items:center;justify-content:center;flex-shrink:0; }
.shift-icon.pagi  { background:#fef3c7;color:#d97706; }
.shift-icon.siang { background:#dbeafe;color:#2563eb; }
.shift-icon.malam { background:#ede9fe;color:#7c3aed; }
.shift-title { font-size:13px;font-weight:600; }

/* Checklist items */
.cl-form { padding:6px 0; }
.cl-row { display:flex;align-items:flex-start;gap:0;padding:9px 18px;border-bottom:1px solid var(--rule);transition:background .1s; }
.cl-row:last-child { border-bottom:none; }
.cl-row:hover { background:var(--wash); }
.cl-row input[type=checkbox] { display:none; }
.cl-row label { display:flex;align-items:flex-start;gap:11px;width:100%;cursor:pointer;user-select:none; }
.cb-box { width:16px;height:16px;border-radius:3px;flex-shrink:0;border:1.5px solid var(--rule);margin-top:2px;display:flex;align-items:center;justify-content:center;transition:all .15s; }
.cl-row input[type=checkbox]:checked ~ label .cb-box { background:var(--green);border-color:var(--green); }
.cl-row:has(input:checked) { background:#f0fdf4; }
.cb-check { display:none;color:#fff; }
.cl-row input[type=checkbox]:checked ~ label .cb-check { display:block; }
.cb-num { font-size:10px;color:var(--ink3);font-family:'IBM Plex Mono',monospace;padding-top:3px;width:16px;flex-shrink:0; }
.cb-text { font-size:12.5px;line-height:1.5;color:var(--ink); }
.cb-link { font-size:10.5px;color:var(--blue);display:block;margin-top:2px; }

/* Progress mini */
.progress-mini { display:flex;align-items:center;gap:8px; }
.pm-bar { flex:1;height:4px;background:var(--wash2);border-radius:2px; }
.pm-fill { height:100%;border-radius:2px;transition:width .4s; }
.pm-txt { font-size:10.5px;font-family:'IBM Plex Mono',monospace;color:var(--ink3); }

/* Footer actions */
.form-footer { display:flex;align-items:center;gap:8px;padding:14px 18px;background:var(--wash);border-top:1px solid var(--rule); }
.btn-draft { height:32px;padding:0 14px;font-size:12px;font-weight:500;background:var(--surface);color:var(--ink2);border:1px solid var(--rule);border-radius:5px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;transition:all .12s; }
.btn-draft:hover { border-color:var(--ink2);color:var(--ink); }
.btn-submit { height:32px;padding:0 16px;font-size:12px;font-weight:500;background:var(--blue);color:#fff;border:none;border-radius:5px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;display:inline-flex;align-items:center;gap:6px; }
.btn-submit:hover { opacity:.88; }

/* Modal overlay */
.modal-overlay { position:fixed;inset:0;background:rgba(0,0,0,.45);z-index:999;display:flex;align-items:center;justify-content:center;opacity:0;pointer-events:none;transition:opacity .2s; }
.modal-overlay.open { opacity:1;pointer-events:all; }
.modal-box { background:#fff;border-radius:12px;padding:28px 28px 22px;max-width:380px;width:90%;box-shadow:0 20px 60px rgba(0,0,0,.18);transform:translateY(8px);transition:transform .2s; }
.modal-overlay.open .modal-box { transform:translateY(0); }
.modal-icon { width:48px;height:48px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin:0 auto 16px; }
.modal-icon.warn { background:#fef3c7; }
.modal-icon.success { background:#d1fae5; }
.modal-title { font-size:15px;font-weight:600;text-align:center;margin-bottom:8px; }
.modal-desc { font-size:12.5px;color:var(--ink3);text-align:center;line-height:1.6;margin-bottom:20px; }
.modal-actions { display:flex;gap:8px;justify-content:center; }
.modal-btn-cancel { height:36px;padding:0 18px;font-size:12.5px;font-weight:500;background:var(--wash);color:var(--ink2);border:1px solid var(--rule);border-radius:6px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif; }
.modal-btn-cancel:hover { border-color:var(--ink2); }
.modal-btn-confirm { height:36px;padding:0 20px;font-size:12.5px;font-weight:600;background:var(--blue);color:#fff;border:none;border-radius:6px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif;display:inline-flex;align-items:center;gap:6px; }
.modal-btn-confirm:hover { opacity:.88; }

/* Success popup overlay */
.success-modal { position:fixed;inset:0;background:rgba(0,0,0,.5);z-index:1000;display:flex;align-items:center;justify-content:center; }
.success-box { background:#fff;border-radius:14px;padding:36px 32px 28px;max-width:360px;width:90%;box-shadow:0 24px 64px rgba(0,0,0,.2);text-align:center;animation:popIn .3s cubic-bezier(.34,1.56,.64,1); }
@keyframes popIn { from{transform:scale(.85);opacity:0} to{transform:scale(1);opacity:1} }
.success-ring { width:64px;height:64px;border-radius:50%;background:#d1fae5;display:flex;align-items:center;justify-content:center;margin:0 auto 18px; }
.success-title { font-size:16px;font-weight:700;margin-bottom:8px; }
.success-desc { font-size:12.5px;color:var(--ink3);line-height:1.6;margin-bottom:22px; }
.success-close { height:36px;padding:0 24px;font-size:12.5px;font-weight:600;background:var(--green);color:#fff;border:none;border-radius:6px;cursor:pointer;font-family:'IBM Plex Sans',sans-serif; }
.success-close:hover { opacity:.88; }

/* Submitted state banner */
.submitted-banner { display:flex;align-items:center;gap:10px;padding:12px 18px;background:#fffbeb;border-bottom:1px solid #fde68a;font-size:12px;color:#92400e;font-weight:500; }

/* Verified state banner - rapi dan jelas */
.verified-banner {
    display: flex; align-items: center; gap: 10px;
    padding: 13px 18px;
    background: linear-gradient(90deg, #f0fdf4 0%, #dcfce7 100%);
    border-bottom: 1px solid #86efac;
    font-size: 12.5px; font-weight: 600; color: #15803d;
}
.verified-banner .vb-icon {
    width: 30px; height: 30px; border-radius: 50%;
    background: #22c55e; display: flex; align-items: center; justify-content: center;
    flex-shrink: 0;
}
.verified-banner .vb-body { flex: 1; }
.verified-banner .vb-title { font-size: 12.5px; font-weight: 700; color: #166534; }
.verified-banner .vb-sub { font-size: 11px; font-weight: 400; color: #4ade80; margin-top: 1px; color: #16a34a; }


/* Riwayat */
.riwayat-row { display:flex;align-items:center;justify-content:space-between;padding:9px 16px;border-bottom:1px solid var(--rule); }
.riwayat-row:last-child { border-bottom:none; }
.pill { display:inline-block;font-size:10px;font-weight:600;padding:2px 8px;border-radius:20px; }
.p-verified { background:var(--green-lt);color:var(--green); }
.p-submit   { background:var(--amber-lt);color:var(--amber); }
.p-draft    { background:var(--wash2);color:var(--ink3); }
.sesi-chip  { font-size:10px;font-weight:600;padding:1px 7px;border-radius:3px;text-transform:uppercase;letter-spacing:.5px; }
.s-pagi  { background:#fff8e1;color:#b45309; }
.s-siang { background:#e8f4fd;color:#1a56db; }
.s-malam { background:#ede9fe;color:#7c3aed; }

.flash { display:flex;align-items:center;gap:8px;padding:10px 16px;border-radius:7px;margin-bottom:16px;font-size:12.5px;font-weight:500; }
.flash-ok  { background:var(--green-lt);color:var(--green);border:1px solid #0a7c4e22; }
.flash-err { background:var(--red-lt);color:var(--red);border:1px solid #c0392b22; }

.note-area { width:100%;padding:10px 12px;font-size:12.5px;border:1px solid var(--rule);border-radius:5px;background:var(--wash);resize:vertical;min-height:70px;font-family:'IBM Plex Sans',sans-serif;color:var(--ink);margin-top:4px;transition:border-color .15s; }
.note-area:focus { outline:none;border-color:var(--blue); }

.panel { background:var(--surface);border:1px solid var(--rule);border-radius:8px;overflow:hidden; }
.ph { display:flex;align-items:center;justify-content:space-between;padding:13px 18px;border-bottom:1px solid var(--rule); }
.ph-title { font-size:12.5px;font-weight:600; }
.ph-sub   { font-size:11px;color:var(--ink3);margin-top:1px; }

.shift-info-banner { display:flex;align-items:center;gap:8px;padding:9px 14px;border-radius:6px;margin-bottom:14px;font-size:12px;font-weight:500; }
.sib-pagi  { background:#fffbeb;color:#b45309;border:1px solid #fde68a; }
.sib-siang { background:#eff6ff;color:#1d4ed8;border:1px solid #bfdbfe; }
.sib-malam { background:#f5f3ff;color:#6d28d9;border:1px solid #ddd6fe; }
</style>
@endpush

@section('content')

@if(session('success'))
<div class="flash flash-ok">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/></svg>
    {{ session('success') }}
</div>
@endif
@if(session('error'))
<div class="flash flash-err">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/></svg>
    {{ session('error') }}
</div>
@endif

{{-- Page header --}}
<div class="cl-header">
    <div>
        <h1>Checklist Harian Saya</h1>
        <p>{{ $tanggal->translatedFormat('l, d F Y') }} — Isi & submit checklist sesuai shift aktif Anda</p>
    </div>
    <span class="role-badge">
        <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="8" r="4"/><path d="M4 20c0-4 3.6-7 8-7s8 3 8 7"/></svg>
        Petugas — Isi Mandiri
    </span>
</div>

{{-- INFO SHIFT AKTIF SEKARANG --}}
@if($shiftAktif === 'pagi' || $shiftAktif === 'siang')
<div class="shift-info-banner sib-{{ $shiftAktif }}">
    @if($shiftAktif === 'pagi')
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
    @else
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2"/></svg>
    @endif
    Shift aktif sekarang: <strong>{{ ucfirst($shiftAktif) }}</strong>
    &nbsp;— sesuai jadwal Anda hari ini.
</div>
@elseif($shiftAktif === 'tidak_terjadwal')
{{-- Dalam jam shift tapi koordinator belum isi jadwal --}}
<div class="shift-info-banner" style="background:#fff7ed;color:#9a3412;border:1px solid #fed7aa">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    <span>Koordinator belum mengisi jadwal Anda untuk shift hari ini. Silakan hubungi koordinator Anda.</span>
</div>
@else
{{-- Di luar jam shift --}}
<div class="shift-info-banner" style="background:#f8fafc;color:var(--ink3);border:1px solid var(--rule)">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
    Sekarang di luar jam shift. Checklist bisa diisi pada:
    <strong>Pagi 07.00–12.00</strong> atau <strong>Siang 12.00–17.00</strong>.
</div>
@endif

@php
    $isVerified  = $checklist && $checklist->exists && $checklist->status === 'verified';
    $isSubmitted = $checklist && $checklist->exists && $checklist->status === 'submit';
    $isDraft     = $checklist && $checklist->exists && $checklist->status === 'draft';
    $totalItems  = $templates->count();
    $checkedCount = ($checklist && $checklist->exists) ? $checklist->totalChecked() : 0;
    $pct = $totalItems > 0 ? round(($checkedCount / $totalItems) * 100) : 0;
    $shiftColor = ['pagi'=>'#d97706','siang'=>'#2563eb'][$shiftAktif ?? ''] ?? '#555';
@endphp

@if($shiftAktif === 'pagi' || $shiftAktif === 'siang')
{{-- SINGLE PANEL CHECKLIST --}}
<div class="shift-panel">

    {{-- Header shift --}}
    <div class="shift-head {{ $shiftAktif }}">
        <span class="shift-icon {{ $shiftAktif }}">
            @if($shiftAktif === 'pagi')
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="5"/><line x1="12" y1="1" x2="12" y2="3"/><line x1="12" y1="21" x2="12" y2="23"/><line x1="4.22" y1="4.22" x2="5.64" y2="5.64"/><line x1="18.36" y1="18.36" x2="19.78" y2="19.78"/><line x1="1" y1="12" x2="3" y2="12"/><line x1="21" y1="12" x2="23" y2="12"/></svg>
            @else
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="4"/><path d="M12 2v2m0 16v2M4.93 4.93l1.41 1.41m11.32 11.32l1.41 1.41M2 12h2m16 0h2M4.93 19.07l1.41-1.41m11.32-11.32l1.41-1.41"/></svg>
            @endif
        </span>
        <div style="flex:1">
            <div class="shift-title">Shift {{ ucfirst($shiftAktif) }}</div>
            <div style="margin-top:6px">
                @if($isVerified)
                    <span class="pill p-verified">✓ Terverifikasi</span>
                @elseif($isSubmitted)
                    <span class="pill p-submit">Menunggu Verifikasi</span>
                @elseif($isDraft)
                    <span class="pill p-draft">Draft tersimpan</span>
                @else
                    <span class="pill" style="background:var(--red-lt);color:var(--red)">Belum diisi</span>
                @endif
            </div>
            <div class="progress-mini" style="margin-top:8px">
                <div class="pm-bar"><div class="pm-fill" style="width:{{ $pct }}%;background:{{ $shiftColor }}"></div></div>
                <span class="pm-txt">{{ $pct }}% ({{ $checkedCount }}/{{ $totalItems }})</span>
            </div>
        </div>
    </div>

    {{-- Banner status: verified --}}
    @if($isVerified)
    <div class="verified-banner">
        <div class="vb-icon">
            <svg width="15" height="15" fill="none" stroke="#fff" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
        </div>
        <div class="vb-body">
            <div class="vb-title">Checklist Terverifikasi</div>
            <div class="vb-sub">Shift {{ ucfirst($shiftAktif) }} sudah diverifikasi oleh koordinator. Tidak bisa diubah.</div>
        </div>
        <span class="pill p-verified" style="font-size:11px;padding:4px 10px;">✓ Verified</span>
    </div>
    @endif

    {{-- Banner status: sudah submit, menunggu verifikasi --}}
    @if($isSubmitted)
    <div class="submitted-banner">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
        <div>
            <strong>Checklist sudah dikirim!</strong>
            Menunggu verifikasi koordinator. Kamu tidak bisa mengubah checklist ini sampai diverifikasi.
        </div>
    </div>
    @endif

    @if($templates->isEmpty())
    <div style="padding:24px 18px;text-align:center;color:var(--ink3);font-size:12px">
        Belum ada item checklist. Hubungi koordinator untuk mengatur template.
    </div>
    @else

    {{-- FORM TUNGGAL — shift otomatis dari server --}}
    <form id="cl-form" method="POST" action="{{ route('petugas.checklist.save') }}">
        @csrf
        <input type="hidden" name="sesi" value="{{ $shiftAktif }}">
        {{-- hidden input action, diisi via JS sebelum submit --}}
        <input type="hidden" name="action" id="cl-action-input" value="draft">

        <div class="cl-form">
            @foreach($templates as $idx => $tpl)
            @php $checked = $checklist->exists && isset($checklist->items[$tpl->id]) && $checklist->items[$tpl->id]; @endphp
            <div class="cl-row">
                <input type="checkbox"
                    id="cl_{{ $tpl->id }}"
                    name="items[{{ $tpl->id }}]"
                    value="1"
                    {{ $checked ? 'checked' : '' }}
                    {{ ($isVerified || $isSubmitted) ? 'disabled' : '' }}>
                <label for="cl_{{ $tpl->id }}">
                    <span class="cb-box">
                        <svg class="cb-check" width="10" height="10" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
                    </span>
                    <span class="cb-num">{{ $idx + 1 }}</span>
                    <span class="cb-text">
                        {{ $tpl->label }}
                        @if($tpl->link)
                            <a class="cb-link" href="{{ $tpl->link }}" target="_blank">{{ $tpl->link }}</a>
                        @endif
                    </span>
                </label>
            </div>
            @endforeach
        </div>

        <div style="padding:12px 18px 4px;border-top:1px solid var(--rule)">
            <label style="font-size:11px;font-weight:500;color:var(--ink3)">Catatan (opsional)</label>
            <textarea name="catatan" class="note-area"
                placeholder="Tuliskan catatan untuk shift {{ ucfirst($shiftAktif) }}..."
                {{ $isVerified ? 'disabled' : '' }}>{{ $checklist->catatan ?? '' }}</textarea>
        </div>

        @if(!$isVerified && !$isSubmitted)
        <div class="form-footer">
            <button type="button" class="btn-draft" onclick="doAction('draft')">
                {{ $isDraft ? 'Update Draft' : 'Simpan Draft' }}
            </button>
            <button type="button" class="btn-submit" onclick="openSubmitModal()">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
                Submit ke Koordinator
            </button>
        </div>
        @endif
    </form>
    @endif
</div>

{{-- ── MODAL: Konfirmasi Submit ── --}}
<div class="modal-overlay" id="modal-submit">
    <div class="modal-box">
        <div class="modal-icon warn">
            <svg width="24" height="24" fill="none" stroke="#d97706" stroke-width="2" viewBox="0 0 24 24">
                <path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/>
            </svg>
        </div>
        <div class="modal-title">Submit ke Koordinator?</div>
        <div class="modal-desc">
            Checklist Shift <strong>{{ ucfirst($shiftAktif ?? '') }}</strong> akan dikirim ke koordinator untuk diverifikasi.<br>
            Setelah disubmit, kamu <strong>tidak bisa mengubah</strong> isian ini lagi.
        </div>
        <div class="modal-actions">
            <button class="modal-btn-cancel" onclick="closeSubmitModal()">Batal</button>
            <button class="modal-btn-confirm" onclick="doAction('submit')">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 2L11 13"/><path d="M22 2L15 22l-4-9-9-4z"/></svg>
                Ya, Submit Sekarang
            </button>
        </div>
    </div>
</div>
@endif {{-- end @if($shiftAktif) --}}

{{-- Riwayat 7 hari --}}
@if($riwayat->count() > 0)
<div style="margin-top:18px">
<div class="panel">
    <div class="ph">
        <div>
            <div class="ph-title">Riwayat 7 Hari Terakhir</div>
            <div class="ph-sub">{{ $riwayat->count() }} entri</div>
        </div>
    </div>
    @foreach($riwayat as $r)
    @php
        $rChecked = $r->totalChecked();
        $rPct = $totalItems > 0 ? round(($rChecked / $totalItems) * 100) : 0;
    @endphp
    <div class="riwayat-row">
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:12px;font-family:'IBM Plex Mono',monospace;color:var(--ink2)">{{ $r->tanggal->format('d/m/Y') }}</span>
            <span class="sesi-chip s-{{ $r->sesi }}">{{ ucfirst($r->sesi) }}</span>
        </div>
        <div style="display:flex;align-items:center;gap:10px">
            <span style="font-size:11px;color:var(--ink3)">{{ $rChecked }}/{{ $totalItems }}</span>
            <div style="width:60px;height:4px;background:var(--wash2);border-radius:2px">
                <div style="width:{{ $rPct }}%;height:100%;border-radius:2px;background:{{ $rPct >= 80 ? 'var(--green)' : ($rPct >= 50 ? 'var(--amber)' : 'var(--red)') }}"></div>
            </div>
            <span class="pill {{ $r->status === 'verified' ? 'p-verified' : ($r->status === 'submit' ? 'p-submit' : 'p-draft') }}">{{ $r->status }}</span>
        </div>
    </div>
    @endforeach
</div>
</div>
@endif

@endsection

@push('modals')
{{-- ── MODAL: Berhasil Submit ── --}}
@if(session('success') && str_contains(session('success'), 'disubmit'))
<div class="success-modal" id="modal-success">
    <div class="success-box">
        <div class="success-ring">
            <svg width="32" height="32" fill="none" stroke="#059669" stroke-width="2.5" viewBox="0 0 24 24">
                <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/>
                <polyline points="22 4 12 14.01 9 11.01"/>
            </svg>
        </div>
        <div class="success-title">Checklist Berhasil Dikirim! 🎉</div>
        <div class="success-desc">
            {{ session('success') }}<br><br>
            Koordinator akan segera memverifikasi checklist shift kamu.
            Kamu bisa memantau statusnya di halaman ini.
        </div>
        <button class="success-close" onclick="document.getElementById('modal-success').remove()">
            Oke, Mengerti
        </button>
    </div>
</div>
@endif
@endpush

@push('scripts')
<script>
function openSubmitModal() {
    document.getElementById('modal-submit').classList.add('open');
}
function closeSubmitModal() {
    document.getElementById('modal-submit').classList.remove('open');
}
function doAction(action) {
    document.getElementById('cl-action-input').value = action;
    closeSubmitModal();
    document.getElementById('cl-form').submit();
}
// Tutup modal konfirmasi saat klik di luar kotak
document.getElementById('modal-submit')?.addEventListener('click', function(e) {
    if (e.target === this) closeSubmitModal();
});
// Progress bar live saat centang item
document.querySelectorAll('.cl-row input[type=checkbox]').forEach(function(cb) {
    cb.addEventListener('change', updateProgress);
});
function updateProgress() {
    const all     = document.querySelectorAll('.cl-row input[type=checkbox]');
    const checked = document.querySelectorAll('.cl-row input[type=checkbox]:checked');
    const pct = all.length > 0 ? Math.round((checked.length / all.length) * 100) : 0;
    const fill = document.querySelector('.pm-fill');
    const txt  = document.querySelector('.pm-txt');
    if (fill) fill.style.width = pct + '%';
    if (txt) txt.textContent = pct + '% (' + checked.length + '/' + all.length + ')';
}
</script>
@endpush