@extends('layouts.admin')
@section('title', 'Template Pesan Survey')

@section('breadcrumb')
    <span>PST</span>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <a href="{{ route('admin.survey.pertanyaan') }}">Survey Kepuasan</a>
    <span style="margin:0 6px;color:var(--ink3)">/</span>
    <strong>Template & Link</strong>
@endsection

@push('styles')
<style>
    .sv-page-title { font-size: 18px; font-weight: 600; color: var(--ink); margin: 0 0 4px; }
    .sv-page-sub   { font-size: 12.5px; color: var(--ink3); margin: 0 0 28px; }

    .sv-section { margin-bottom: 32px; }
    .sv-section-title {
        font-size: 13px; font-weight: 600; color: var(--ink);
        display: flex; align-items: center; gap: 8px;
        margin-bottom: 14px; padding-bottom: 10px;
        border-bottom: 1px solid var(--rule);
    }

    .sv-card {
        background: var(--surface); border: 1px solid var(--rule);
        border-radius: 10px; padding: 22px; max-width: 620px;
    }

    .sv-label { font-size: 11px; font-weight: 600; letter-spacing: .4px; text-transform: uppercase; color: var(--ink3); margin-bottom: 6px; }
    .sv-hint  { font-size: 11.5px; color: var(--ink3); margin-bottom: 10px; line-height: 1.6; }

    .sv-textarea {
        width: 100%; min-height: 160px; padding: 12px 14px;
        font-size: 13px; line-height: 1.75; font-family: 'IBM Plex Sans', sans-serif;
        color: var(--ink); border: 1px solid var(--rule); border-radius: 7px;
        background: var(--wash); resize: vertical; box-sizing: border-box;
    }
    .sv-textarea:focus { outline: none; border-color: var(--blue); }

    .sv-btn {
        display: inline-flex; align-items: center; gap: 6px;
        height: 34px; padding: 0 16px; border-radius: 6px;
        font-size: 12px; font-weight: 500; cursor: pointer;
        font-family: 'IBM Plex Sans', sans-serif;
        border: none; text-decoration: none; transition: opacity .15s; white-space: nowrap;
    }
    .sv-btn-primary   { background: var(--blue); color: #fff; }
    .sv-btn-primary:hover { opacity: .88; }
    .sv-btn-secondary { background: var(--surface); color: var(--ink2); border: 1px solid var(--rule); }
    .sv-btn-secondary:hover { border-color: var(--ink3); color: var(--ink); }
    .sv-btn-green { background: #16a34a; color: #fff; }
    .sv-btn-green:hover { opacity: .88; }

    /* Tabel link per wilayah */
    .sv-table { width: 100%; border-collapse: collapse; font-size: 12.5px; }
    .sv-table th {
        text-align: left; padding: 8px 12px;
        font-size: 10.5px; font-weight: 600; letter-spacing: .4px;
        text-transform: uppercase; color: var(--ink3);
        border-bottom: 1px solid var(--rule); background: var(--wash);
    }
    .sv-table td {
        padding: 10px 12px; border-bottom: 1px solid var(--rule);
        color: var(--ink); vertical-align: middle;
    }
    .sv-table tr:last-child td { border-bottom: none; }
    .sv-table tr:hover td { background: var(--wash); }

    .sv-link-cell {
        font-family: 'IBM Plex Mono', monospace; font-size: 11px;
        color: var(--ink3); word-break: break-all;
    }
    .sv-badge-none {
        font-size: 10.5px; font-weight: 600; padding: 2px 8px;
        border-radius: 10px; background: #fee2e2; color: #991b1b;
    }

    .sv-alert-success {
        background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 7px;
        padding: 11px 14px; font-size: 12px; color: #166534;
        margin-bottom: 18px; display: flex; align-items: center; gap: 8px;
    }

    .sv-copied { font-size: 11px; color: #16a34a; display: none; margin-left: 6px; }
</style>
@endpush

@section('content')

<div class="sv-page-title">Template Pesan & Link Survey</div>
<div class="sv-page-sub">Atur template pesan yang digunakan petugas, dan pantau link survey tiap wilayah.</div>

@if(session('success'))
<div class="sv-alert-success">
    <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
        <path d="M22 11.08V12a10 10 0 11-5.93-9.14"/><polyline points="22 4 12 14.01 9 11.01"/>
    </svg>
    {{ session('success') }}
</div>
@endif

{{-- ── BAGIAN 1: TEMPLATE PESAN ── --}}
<div class="sv-section">
    <div class="sv-section-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M21 15a2 2 0 01-2 2H7l-4 4V5a2 2 0 012-2h14a2 2 0 012 2z"/>
        </svg>
        Template Pesan
    </div>

    <div class="sv-card">
        <div class="sv-hint">
            Tulis template pesan yang akan ditampilkan ke petugas. Gunakan <code>{link}</code> sebagai penanda — sistem akan menggantinya otomatis dengan link survey wilayah petugas yang sedang login.
        </div>

        <form method="POST" action="{{ route('admin.survey.template.simpan') }}">
            @csrf
            <div class="sv-label">Isi Template</div>
            <textarea name="template_pesan" class="sv-textarea" required>{{ old('template_pesan', $template) }}</textarea>
            @error('template_pesan')
                <div style="font-size:11px;color:#dc2626;margin-top:4px">{{ $message }}</div>
            @enderror
            <div style="margin-top:12px">
                <button type="submit" class="sv-btn sv-btn-primary">
                    <svg width="12" height="12" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                        <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                        <polyline points="17 21 17 13 7 13 7 21"/><polyline points="7 3 7 8 15 8"/>
                    </svg>
                    Simpan Template
                </button>
            </div>
        </form>
    </div>
</div>

{{-- ── BAGIAN 2: LINK PER WILAYAH ── --}}
<div class="sv-section">
    <div class="sv-section-title">
        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
            <path d="M10 13a5 5 0 007.54.54l3-3a5 5 0 00-7.07-7.07l-1.72 1.71"/>
            <path d="M14 11a5 5 0 00-7.54-.54l-3 3a5 5 0 007.07 7.07l1.71-1.71"/>
        </svg>
        Link Survey Per Wilayah
    </div>

    <div style="border:1px solid var(--rule);border-radius:10px;overflow:hidden;">
        <table class="sv-table">
            <thead>
                <tr>
                    <th style="width:22%">Wilayah</th>
                    <th>Link Survey</th>
                    <th style="width:18%">Aksi</th>
                </tr>
            </thead>
            <tbody>
                @foreach($linkPerWilayah as $item)
                <tr>
                    <td style="font-weight:500">{{ $item['wilayah']->nama }}</td>
                    <td>
                        @if($item['link'])
                            <span class="sv-link-cell" id="link-{{ $item['wilayah']->id }}">{{ $item['link'] }}</span>
                        @else
                            <span class="sv-badge-none">Belum dibuat</span>
                        @endif
                    </td>
                    <td>
                        @if($item['link'])
                            <div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap">
                                <button class="sv-btn sv-btn-secondary"
                                    onclick="salinLink('link-{{ $item['wilayah']->id }}', 'copied-{{ $item['wilayah']->id }}')">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                                        <path d="M5 15H4a2 2 0 01-2-2V4a2 2 0 012-2h9a2 2 0 012 2v1"/>
                                    </svg>
                                    Salin
                                </button>
                                <span class="sv-copied" id="copied-{{ $item['wilayah']->id }}">✓ Tersalin</span>
                            </div>
                        @else
                            <form method="POST" action="{{ route('admin.survey.generate-token', $item['wilayah']->id) }}">
                                @csrf
                                <button type="submit" class="sv-btn sv-btn-green">
                                    <svg width="11" height="11" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                                        <polyline points="23 4 23 10 17 10"/>
                                        <path d="M20.49 15a9 9 0 11-2.12-9.36L23 10"/>
                                    </svg>
                                    Generate
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>

@endsection

@push('scripts')
<script>
function salinLink(elId, infoId) {
    const el  = document.getElementById(elId);
    const val = el.textContent.trim();

    const showInfo = () => {
        const info = document.getElementById(infoId);
        info.style.display = 'inline';
        setTimeout(() => info.style.display = 'none', 2000);
    };

    if (navigator.clipboard && window.isSecureContext) {
        navigator.clipboard.writeText(val).then(showInfo).catch(() => fallbackSalin(el, showInfo));
    } else {
        // Fallback untuk HTTP lokal
        const tmp = document.createElement('textarea');
        tmp.value = val;
        tmp.style.position = 'fixed';
        tmp.style.opacity  = '0';
        document.body.appendChild(tmp);
        tmp.select();
        try { document.execCommand('copy'); showInfo(); } catch(e) {}
        document.body.removeChild(tmp);
    }
}
</script>
@endpush