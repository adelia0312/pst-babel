@extends('layouts.petugas')
@section('title', 'Cetak Barcode Survey')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('petugas.survey.index') }}" style="color:var(--ink2);text-decoration:none">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Cetak Barcode</strong>
@endsection

@push('styles')
<style>
    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 32px; padding: 0 14px; border-radius: 5px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif; text-decoration: none;
        border: none; transition: opacity .15s; white-space: nowrap;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-green  { background: var(--green); color: #fff; }
    .sv-btn-green:hover { opacity: .88; }

    /* ── Layout ── */
    .sv-topbar {
        display: flex; align-items: flex-start; justify-content: space-between;
        margin-bottom: 22px; padding-bottom: 18px; border-bottom: 1px solid var(--rule);
        gap: 12px; flex-wrap: wrap;
    }
    .sv-topbar h1 { font-size: 17px; font-weight: 600; color: var(--ink); margin: 0 0 3px; }
    .sv-topbar p  { font-size: 12px; color: var(--ink3); margin: 0; }

    .sv-grid { display: grid; grid-template-columns: 1fr 1fr; gap: 16px; }
    @media(max-width:640px) { .sv-grid { grid-template-columns: 1fr; } }

    /* ── Card ── */
    .sv-card {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 8px; overflow: hidden;
    }
    .sv-card-head {
        padding: 12px 16px; border-bottom: 1px solid var(--rule);
        font-size: 12px; font-weight: 600; color: var(--ink);
        display: flex; align-items: center; justify-content: space-between;
    }
    .sv-card-body { padding: 16px; }

    /* ── QR preview box ── */
    .sv-qr-box {
        display: flex; flex-direction: column; align-items: center;
        gap: 12px; padding: 20px 16px;
        background: var(--wash); border: 1px solid var(--rule);
        border-radius: 8px; margin-bottom: 14px; text-align: center;
    }
    .sv-qr-org   { font-size: 10px; font-weight: 700; letter-spacing: 1px; text-transform: uppercase; color: var(--ink3); }
    .sv-qr-nama  { font-size: 15px; font-weight: 700; color: var(--ink); }
    .sv-qr-label { font-size: 11.5px; font-weight: 500; color: var(--ink2); }
    .sv-qr-sub   { font-size: 10.5px; color: var(--ink3); line-height: 1.6; max-width: 200px; }
    .sv-qr-divider { width: 100%; height: 1px; background: var(--rule); }

    /* ── Link row ── */
    .sv-link-row { display: flex; gap: 8px; align-items: center; margin-bottom: 5px; }
    .sv-link-input {
        flex: 1; height: 30px; padding: 0 9px;
        font-size: 11px; font-family: 'IBM Plex Mono', monospace;
        border: 1px solid var(--rule); border-radius: 5px;
        background: var(--wash); color: var(--ink2);
    }
    .sv-copied { font-size: 11px; color: var(--green); margin-top: 4px; display: none; }

    .sv-action-row { display: flex; gap: 8px; flex-wrap: wrap; margin-top: 12px; }

    /* ── Info ── */
    .sv-info {
        display: flex; gap: 8px; align-items: flex-start;
        background: #eff6ff; border: 1px solid #bfdbfe; border-radius: 6px;
        padding: 10px 13px; font-size: 11.5px; color: #1e40af; line-height: 1.7;
        margin-top: 14px;
    }

    /* ── PRINT AREA (hidden on screen, visible when print) ── */
    #print-area {
        display: none;
    }

    @media print {
        body > * { display: none !important; }
        #print-area {
            display: flex !important;
            flex-direction: column; align-items: center; justify-content: center;
            padding: 32px; text-align: center; gap: 14px;
            font-family: 'IBM Plex Sans', sans-serif;
        }
        .sv-topbar, .sv-grid, .sv-info { display: none !important; }
    }
</style>
@endpush

@section('content')

<div class="sv-topbar">
    <div>
        <h1>Cetak Barcode Survey</h1>
        <p>Cetak dan pasang di loket — pengunjung scan QR langsung ke form survey.</p>
    </div>
    <a href="{{ route('petugas.survey.index') }}" class="sv-btn sv-btn-secondary">← Kembali</a>
</div>

<div class="sv-grid">

    {{-- Kiri: Preview QR + aksi --}}
    <div class="sv-card">
        <div class="sv-card-head">
            QR Code — {{ $wilayah->nama }}
        </div>
        <div class="sv-card-body">

            {{-- Preview card seperti tampilan cetak --}}
            <div class="sv-qr-box">
                <div class="sv-qr-org">BPS · Sistem PST</div>
                <div class="sv-qr-nama">{{ $wilayah->nama }}</div>
                <div class="sv-qr-divider"></div>
                <div class="sv-qr-label">Scan untuk Survey Kepuasan</div>
                <div id="qr-preview"></div>
                <div class="sv-qr-sub">
                    Pindai QR untuk memberikan penilaian layanan.<br>
                    Petugas tercatat otomatis dari jadwal hari ini.
                </div>
                <div class="sv-qr-divider"></div>
                <div style="font-size:10px;color:var(--ink3)">BPS Bangka Belitung</div>
            </div>

            {{-- Tombol aksi --}}
            <div class="sv-action-row">
                <button class="sv-btn sv-btn-primary" onclick="window.print()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <polyline points="6 9 6 2 18 2 18 9"/><path d="M6 18H4a2 2 0 01-2-2v-5a2 2 0 012-2h16a2 2 0 012 2v5a2 2 0 01-2 2h-2"/>
                        <rect x="6" y="14" width="12" height="8"/>
                    </svg>
                    Cetak
                </button>
                <button class="sv-btn sv-btn-green" onclick="unduhQR()">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 15v4a2 2 0 01-2 2H5a2 2 0 01-2-2v-4"/>
                        <polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/>
                    </svg>
                    Unduh PNG
                </button>
            </div>

        </div>
    </div>

    {{-- Kanan: Link online --}}
    <div class="sv-card">
        <div class="sv-card-head">
            Link Online (untuk WA / Email)
        </div>
        <div class="sv-card-body">
            <p style="font-size:12px;color:var(--ink2);line-height:1.7;margin-bottom:14px">
                Gunakan link di bawah jika ingin mengirim survey ke pengunjung secara online.
                Link sama dengan QR — bisa dikirim lewat WhatsApp atau chat.
            </p>

            <div style="font-size:11px;font-weight:600;letter-spacing:.4px;text-transform:uppercase;color:var(--ink3);margin-bottom:6px">Link Survey</div>
            <div class="sv-link-row">
                <input type="text" id="linkOnline" class="sv-link-input" value="{{ $linkOnline }}" readonly>
                <button class="sv-btn sv-btn-secondary" style="height:30px;padding:0 10px;font-size:11px" onclick="salin('linkOnline','infoLink')">Salin</button>
            </div>
            <div id="infoLink" class="sv-copied">✓ Link disalin!</div>

            <div style="margin-top:14px">
                <a href="https://wa.me/?text={{ urlencode('Halo, silakan isi survey kepuasan layanan BPS ' . $wilayah->nama . ': ' . $linkOnline) }}"
                   target="_blank" class="sv-btn sv-btn-secondary">
                    <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                        <path d="M21 11.5a8.38 8.38 0 01-.9 3.8 8.5 8.5 0 01-7.6 4.7 8.38 8.38 0 01-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 01-.9-3.8 8.5 8.5 0 014.7-7.6 8.38 8.38 0 013.8-.9h.5a8.48 8.48 0 018 8v.5z"/>
                    </svg>
                    Bagikan via WhatsApp
                </a>
            </div>

            <div class="sv-info">
                <svg width="13" height="13" fill="none" stroke="currentColor" stroke-width="1.7" viewBox="0 0 24 24" style="flex-shrink:0;margin-top:1px"><circle cx="12" cy="12" r="10"/><line x1="12" y1="8" x2="12" y2="12"/><line x1="12" y1="16" x2="12.01" y2="16"/></svg>
                <span>QR dan link bersifat <strong>permanen</strong> — tidak perlu cetak ulang meskipun pertanyaan survey diubah.</span>
            </div>
        </div>
    </div>

</div>

{{-- Area print (hanya muncul saat Ctrl+P) --}}
<div id="print-area">
    <div style="font-size:11px;font-weight:700;letter-spacing:1px;text-transform:uppercase;color:#6b7280">BPS · Sistem PST</div>
    <div style="font-size:20px;font-weight:700;color:#111">{{ $wilayah->nama }}</div>
    <div style="width:200px;height:1px;background:#e5e7eb"></div>
    <div style="font-size:14px;font-weight:600;color:#111">Scan untuk Survey Kepuasan</div>
    <div id="qr-print"></div>
    <div style="font-size:11px;color:#9ca3af;max-width:220px;line-height:1.65">
        Pindai QR di atas untuk memberikan penilaian layanan.<br>
        Petugas yang bertugas hari ini akan tercatat otomatis.
    </div>
    <div style="width:200px;height:1px;background:#e5e7eb"></div>
    <div style="font-size:11px;color:#9ca3af">BPS Bangka Belitung</div>
</div>

@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script>
const urlBarcode  = @json($urlBarcode);
const wilayahNama = @json($wilayah->nama);

// Generate QR preview (di kartu kiri)
new QRCode(document.getElementById('qr-preview'), {
    text: urlBarcode, width: 160, height: 160,
    colorDark: '#0d1117', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
});

// Generate QR untuk print (hidden)
new QRCode(document.getElementById('qr-print'), {
    text: urlBarcode, width: 184, height: 184,
    colorDark: '#0d1117', colorLight: '#ffffff',
    correctLevel: QRCode.CorrectLevel.H,
});

// Salin teks
function salin(elId, infoId) {
    const el  = document.getElementById(elId);
    const txt = el.value;
    const show = () => {
        const info = document.getElementById(infoId);
        info.style.display = 'block';
        setTimeout(() => info.style.display = 'none', 2000);
    };
    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(txt).then(show).catch(() => fallback(el, show));
    } else { fallback(el, show); }
}
function fallback(el, cb) {
    el.removeAttribute('readonly'); el.select(); el.setSelectionRange(0, 99999);
    try { document.execCommand('copy'); if (cb) cb(); }
    catch(e) { alert('Gagal menyalin. Blok teks lalu Ctrl+C.'); }
    el.setAttribute('readonly', true);
    window.getSelection()?.removeAllRanges();
}

// Unduh QR sebagai PNG
function unduhQR() {
    const wrap = document.getElementById('qr-preview');
    const img  = wrap.querySelector('canvas') || wrap.querySelector('img');
    if (!img) return;
    let dataUrl;
    if (img.tagName === 'CANVAS') {
        dataUrl = img.toDataURL('image/png');
    } else {
        const c = document.createElement('canvas');
        c.width = 160; c.height = 160;
        c.getContext('2d').drawImage(img, 0, 0);
        dataUrl = c.toDataURL('image/png');
    }
    const a = document.createElement('a');
    a.href     = dataUrl;
    a.download = 'qr-survey-' + wilayahNama.replace(/\s+/g, '-').toLowerCase() + '.png';
    a.click();
}
</script>
@endpush