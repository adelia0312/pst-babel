@extends('layouts.petugas')
@section('title', 'Survey Kepuasan')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Survey Kepuasan</strong>
@endsection

@push('styles')
<style>
    .sv-page-title { font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 4px; }
    .sv-page-sub   { font-size: 12.5px; color: var(--ink3); margin: 0 0 24px; }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 34px; padding: 0 14px; border-radius: 6px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif;
        border: none; text-decoration: none; transition: opacity .15s; white-space: nowrap;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-sm { height: 28px; padding: 0 11px; font-size: 11.5px; }

    /* ── Panel ── */
    .sv-panel {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; overflow: hidden; width: 100%; margin-bottom: 16px;
    }
    .sv-ph {
        display: flex; align-items: center; justify-content: space-between;
        padding: 12px 18px; border-bottom: 1px solid var(--rule);
    }
    .sv-ph-title { font-size: 12.5px; font-weight: 600; color: var(--ink); }
    .sv-ph-sub   { font-size: 11px; color: var(--ink3); margin-top: 1px; }
    .sv-body { padding: 16px 18px; }

    .sv-link-row { display: flex; gap: 8px; align-items: center; margin-bottom: 6px; }
    .sv-link-input {
        flex: 1; height: 32px; padding: 0 10px;
        font-size: 11.5px; font-family: 'IBM Plex Mono', monospace;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink2);
    }
    .sv-copied { font-size: 11px; color: var(--green); margin-top: 4px; display: none; }

    .sv-divider { height: 1px; background: var(--rule); margin: 14px 0; }

    .sv-template-box {
        width: 100%; min-height: 130px;
        padding: 10px 12px; font-size: 12.5px; line-height: 1.75;
        font-family: 'IBM Plex Sans', sans-serif; color: var(--ink);
        border: 1px solid var(--rule); border-radius: 6px;
        background: var(--wash); resize: none; box-sizing: border-box;
        cursor: default;
    }
    .sv-template-box:focus { outline: none; }

    .sv-action-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 10px; }

    /* ── Barcode entry card ── */
    .sv-barcode-entry {
        display: flex; align-items: center; justify-content: space-between;
        gap: 12px; flex-wrap: wrap;
    }
    .sv-barcode-desc { font-size: 12px; color: var(--ink2); line-height: 1.65; }
    .sv-barcode-desc strong { color: var(--ink); }

    /* ── Not connected ── */
    .sv-empty-card {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; padding: 28px 22px;
    }
    .sv-empty-title { font-size: 13.5px; font-weight: 600; color: var(--ink); margin-bottom: 6px; display:flex;align-items:center;gap:8px; }
    .sv-empty-sub   { font-size: 12px; color: var(--ink3); line-height: 1.7; }
</style>
@endpush

@section('content')

<div class="sv-page-title">Survey Kepuasan</div>
<div class="sv-page-sub">Kirim link survey ke pengunjung yang Anda layani secara online, atau cetak barcode QR untuk dipasang di loket.</div>

@if($linkSurvey)

    {{-- Panel: Link & Template pesan --}}
    <div class="sv-panel">
        <div class="sv-ph">
            <div>
                <div class="sv-ph-title">Kirim ke Pengunjung Online</div>
                <div class="sv-ph-sub">Salin link atau pesan, lalu kirimkan lewat WhatsApp</div>
            </div>
        </div>
        <div class="sv-body">

            {{-- Link --}}
            <div style="font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--ink3);margin-bottom:6px">Link Survey</div>
            <div class="sv-link-row">
                <input type="text" id="inputLink" class="sv-link-input" value="{{ $linkSurvey }}" readonly>
                <button class="sv-btn sv-btn-secondary sv-btn-sm" onclick="salin('inputLink','infoLink')">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Salin Link
                </button>
                <a href="{{ $linkSurvey }}" target="_blank" class="sv-btn sv-btn-secondary sv-btn-sm">Preview</a>
            </div>
            <div id="infoLink" class="sv-copied">✓ Link berhasil disalin!</div>

            <div class="sv-divider"></div>

            {{-- Template pesan --}}
            <div style="font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--ink3);margin-bottom:6px">Template Pesan WhatsApp</div>
            <textarea id="templatePesan" class="sv-template-box" readonly>{{ $template }}</textarea>
            <div class="sv-action-row">
                <button class="sv-btn sv-btn-primary" onclick="salin('templatePesan','infoPesan')">
                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="9" y="9" width="13" height="13" rx="2"/><path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/></svg>
                    Salin Pesan
                </button>
            </div>
            <div id="infoPesan" class="sv-copied">✓ Pesan berhasil disalin! Paste ke chat WhatsApp pengunjung.</div>

        </div>
    </div>

    {{-- Panel: Barcode QR untuk loket ── BARU dipindahkan dari koordinator --}}
    <div class="sv-panel">
        <div class="sv-ph">
            <div>
                <div class="sv-ph-title">Barcode QR untuk Loket</div>
                <div class="sv-ph-sub">Cetak dan pasang di meja/loket agar pengunjung bisa scan langsung</div>
            </div>
        </div>
        <div class="sv-body">
            <div class="sv-barcode-entry">
                <div class="sv-barcode-desc">
                    <strong>QR Code {{ $wilayah?->nama }}</strong><br>
                    Pengunjung cukup scan menggunakan kamera HP — langsung terbuka form survey.
                    QR tidak perlu dicetak ulang jika pertanyaan berubah.
                </div>
                <a href="{{ route('petugas.survey.cetak-barcode') }}" class="sv-btn sv-btn-secondary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <rect x="3" y="3" width="7" height="7"/><rect x="14" y="3" width="7" height="7"/>
                        <rect x="3" y="14" width="7" height="7"/><rect x="14" y="14" width="3" height="3"/>
                        <rect x="18" y="14" width="3" height="3"/><rect x="14" y="18" width="3" height="3"/>
                        <rect x="18" y="18" width="3" height="3"/>
                    </svg>
                    Cetak / Unduh Barcode
                </a>
            </div>
        </div>
    </div>

@else

    <div class="sv-empty-card">
        <div class="sv-empty-title">
            <svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
            Link Belum Tersedia
        </div>
        <div class="sv-empty-sub">
            Akun Anda belum terdaftar di wilayah, atau survey belum diaktifkan.<br>
            Hubungi admin untuk informasi lebih lanjut.
        </div>
    </div>

@endif

@endsection

@push('scripts')
<script>
function salin(elId, infoId) {
    const el   = document.getElementById(elId);
    const teks = el.value;
    const show = () => {
        const info = document.getElementById(infoId);
        info.style.display = 'block';
        setTimeout(() => info.style.display = 'none', 2500);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(teks).then(show).catch(() => fallbackSalin(el, show));
    } else {
        fallbackSalin(el, show);
    }
}
function fallbackSalin(el, cb) {
    el.removeAttribute('readonly');
    el.select(); el.setSelectionRange(0, 99999);
    try { document.execCommand('copy'); if (cb) cb(); }
    catch(e) { alert('Gagal menyalin. Blok teks lalu Ctrl+C.'); }
    el.setAttribute('readonly', true);
    window.getSelection()?.removeAllRanges();
}
</script>
@endpush