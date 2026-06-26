@extends('layouts.koordinator')

@section('title', 'Tampilan QR Absensi')

@section('breadcrumb')
    <span>PST</span><span>›</span>
    <a href="{{ route('koordinator.absensi.index') }}">Monitoring Absensi</a>
    <span>›</span><strong>Tampilan QR</strong>
@endsection

@push('styles')
<style>
    .qr-page { display: flex; flex-direction: column; gap: 16px; }

    .qr-header {
        background: var(--surface); border: 1px solid var(--rule); border-radius: 10px;
        padding: 16px 20px; display: flex; align-items: center; gap: 16px;
        flex-wrap: wrap;
    }
    .qr-header-title { flex: 1; min-width: 180px; }
    .qr-header-title h2 { font-size: 15px; font-weight: 600; color: var(--ink); }
    .qr-header-title p  { font-size: 12px; color: var(--ink3); margin-top: 2px; }

    /* Shift Selector */
    .shift-selector {
        display: flex;
        gap: 8px;
        background: var(--surface);
        border: 1px solid var(--rule);
        border-radius: 10px;
        padding: 8px 12px;
        flex-wrap: wrap;
    }
    .shift-btn {
        padding: 6px 16px;
        border-radius: 6px;
        font-size: 12px;
        font-weight: 500;
        background: var(--wash2);
        color: var(--ink2);
        border: none;
        cursor: pointer;
        transition: all .12s;
        font-family: 'IBM Plex Sans', sans-serif;
    }
    .shift-btn.active {
        background: var(--blue);
        color: white;
    }
    .shift-btn:hover:not(.active) {
        background: var(--wash);
        color: var(--ink);
    }

    .qr-grid {
        display: grid;
        grid-template-columns: repeat(2, 1fr);
        gap: 16px;
    }
    @media(min-width: 900px) { .qr-grid { grid-template-columns: repeat(4, 1fr); } }

    .qr-card {
        background: var(--surface); border: 2px solid var(--green);
        border-radius: 12px; padding: 20px 16px;
        display: flex; flex-direction: column; align-items: center; gap: 12px;
        position: relative; overflow: hidden;
    }

    .qr-badge {
        position: absolute; top: 10px; right: 10px;
        font-size: 9.5px; font-weight: 700; text-transform: uppercase;
        letter-spacing: .8px; padding: 2px 7px; border-radius: 3px;
        background: var(--green-lt); color: var(--green);
    }

    .qr-label {
        font-size: 13px; font-weight: 600; color: var(--ink);
        text-align: center;
    }
    .qr-sesi-badge {
        font-size: 10px; font-weight: 600; padding: 2px 8px; border-radius: 3px;
    }
    .sesi-pagi  { background: #fff8e1; color: #e65100; }
    .sesi-siang { background: #e3f2fd; color: #0277bd; }

    .qr-canvas-wrap {
        width: 160px; height: 160px;
        display: flex; align-items: center; justify-content: center;
        border: 1px solid var(--rule); border-radius: 8px;
        background: #fff; padding: 6px;
    }

    .qr-window {
        font-size: 10.5px; color: var(--ink3);
        text-align: center; line-height: 1.7;
    }
    .qr-window span {
        display: inline-block; background: var(--wash2); border-radius: 3px;
        padding: 0 5px; font-family: 'IBM Plex Mono', monospace; font-size: 10px;
    }

    .qr-countdown {
        font-family: 'IBM Plex Mono', monospace;
        font-size: 11px; color: var(--green); font-weight: 600;
    }

    .qr-token-str {
        font-family: 'IBM Plex Mono', monospace; font-size: 9px;
        color: var(--ink3); word-break: break-all; text-align: center;
        max-width: 160px;
    }

    .qr-placeholder {
        background: var(--surface); border: 2px dashed var(--rule);
        border-radius: 12px; padding: 32px 20px;
        display: flex; flex-direction: column; align-items: center; gap: 10px;
        grid-column: 1 / -1;
    }
    .qr-placeholder-icon { font-size: 36px; opacity: .3; }
    .qr-placeholder p { font-size: 13px; color: var(--ink3); text-align: center; margin: 0; }

    .qr-info-box {
        background: var(--wash); border: 1px solid var(--rule); border-radius: 8px;
        padding: 14px 18px; font-size: 11.5px; color: var(--ink2); line-height: 1.9;
    }
    .qr-info-box strong { color: var(--ink); }

    .refresh-bar {
        height: 3px; background: var(--rule); border-radius: 2px;
        overflow: hidden;
    }
    .refresh-fill {
        height: 100%; background: var(--blue); border-radius: 2px;
        transition: width .1s linear;
    }
</style>
@endpush

@section('content')
<div class="qr-page">

    <div class="qr-header">
        <div class="qr-header-title">
            <h2>Tampilan QR Absensi — {{ $wilayah->nama ?? 'Wilayah' }}</h2>
            <p>
                Pilih shift yang akan ditampilkan QR-nya. QR otomatis berubah sesuai sesi.<br>
                Tanggal: {{ $now->isoFormat('dddd, D MMMM Y') }}
            </p>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <a href="{{ route('koordinator.absensi.index') }}"
               style="display:inline-flex;align-items:center;gap:5px;padding:7px 13px;background:var(--wash2);color:var(--ink2);border:1px solid var(--rule);border-radius:7px;font-size:12px;font-weight:500;text-decoration:none;">
                &lsaquo; Kembali
            </a>
        </div>
    </div>

    {{-- Pilih Shift --}}
    <div class="shift-selector">
        <button class="shift-btn {{ $selectedSesi == 'pagi' ? 'active' : '' }}" data-sesi="pagi">
            🏙️ Shift Pagi
        </button>
        <button class="shift-btn {{ $selectedSesi == 'siang' ? 'active' : '' }}" data-sesi="siang">
            🌞 Shift Siang
        </button>
        <button class="shift-btn {{ $selectedSesi == null ? 'active' : '' }}" data-sesi="">
            📱 Semua Aktif
        </button>
    </div>

    {{-- Refresh bar --}}
    <div class="refresh-bar" id="refreshBar" style="display: {{ count($qrStatus) > 0 ? 'block' : 'none' }};">
        <div class="refresh-fill" id="refreshFill" style="width:0%"></div>
    </div>

    {{-- Grid QR --}}
    <div class="qr-grid" id="qrGrid">
        @if(count($qrStatus) > 0)
            @foreach($qrStatus as $jenis => $info)
                <div class="qr-card" id="card-{{ $jenis }}">
                    <div class="qr-badge">Aktif</div>
                    <div class="qr-label">{{ $info['label'] }}</div>
                    <div class="qr-sesi-badge {{ $info['sesi'] == 'pagi' ? 'sesi-pagi' : 'sesi-siang' }}">
                        Shift {{ ucfirst($info['sesi']) }}
                    </div>
                    <div class="qr-canvas-wrap">
                        <div id="qr-wrap-{{ $jenis }}" style="width:148px;height:148px;"></div>
                    </div>
                    <div class="qr-token-str" id="token-str-{{ $jenis }}">{{ $info['token'] }}</div>
                    <div class="qr-window">
                        Berlaku: <span>{{ $info['qr_mulai'] }}</span> – <span>{{ $info['qr_selesai'] }}</span><br>
                        @if($info['toleransi'] > 0)
                            Toleransi: <span>{{ $info['toleransi'] }} menit</span><br>
                        @endif
                        <div class="qr-countdown" id="countdown-{{ $jenis }}"></div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="qr-placeholder">
                <div class="qr-placeholder-icon">📱</div>
                <p>Tidak ada QR yang aktif untuk shift yang dipilih saat ini.</p>
                <p style="font-size:11px;">Coba pilih shift lain atau tunggu jadwal absensi.</p>
            </div>
        @endif
    </div>

    <div class="qr-info-box">
        <strong>📋 Jadwal Absensi:</strong><br>
        • <strong>Shift Pagi Masuk:</strong> 07:00 – 08:10 (lewat = terlambat)<br>
        • <strong>Shift Pagi Keluar:</strong> 12:00 – 13:00<br>
        • <strong>Shift Siang Masuk:</strong> 12:00 – 16:30 (toleransi 10 menit)<br>
        • <strong>Shift Siang Keluar:</strong> Senin–Kamis 15:30 – 16:00, Jumat 16:00 – 16:30<br><br>
        <strong>⚠️ Catatan:</strong> QR hanya bisa digunakan oleh petugas yang memiliki jadwal shift sesuai.
    </div>

</div>
@endsection

@push('scripts')
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>

<script>
const REFRESH_INTERVAL = 30; // detik
let refreshTimer = null;
let refreshCountdown = REFRESH_INTERVAL;
let currentSesi = '{{ $selectedSesi ?? '' }}';

function renderQrInto(containerId, token) {
    const wrap = document.getElementById(containerId);
    if (!wrap) return;
    wrap.innerHTML = '';
    try {
        new QRCode(wrap, {
            text: token,
            width: 148,
            height: 148,
            colorDark: '#0d1117',
            colorLight: '#ffffff',
            correctLevel: QRCode.CorrectLevel.M,
        });
    } catch (e) {
        console.warn('QR render error:', e);
    }
}

async function refreshTokens() {
    try {
        const url = '{{ route("koordinator.absensi.qrJson") }}' + (currentSesi ? '?sesi=' + currentSesi : '');
        const res = await fetch(url);
        const data = await res.json();
        if (!data.success) return;

        const grid = document.getElementById('qrGrid');
        
        if (Object.keys(data.qr_status).length === 0) {
            grid.innerHTML = `
                <div class="qr-placeholder">
                    <div class="qr-placeholder-icon">📱</div>
                    <p>Tidak ada QR yang aktif untuk shift yang dipilih saat ini.</p>
                    <p style="font-size:11px;">Coba pilih shift lain atau tunggu jadwal absensi.</p>
                </div>`;
            return;
        }

        // Build ulang grid
        let html = '';
        for (const [jenis, info] of Object.entries(data.qr_status)) {
            const sesiClass = info.sesi === 'pagi' ? 'sesi-pagi' : 'sesi-siang';
            const toleransiHtml = info.toleransi > 0 ? `Toleransi: <span>${info.toleransi} menit</span><br>` : '';
            html += `
                <div class="qr-card" id="card-${jenis}">
                    <div class="qr-badge">Aktif</div>
                    <div class="qr-label">${info.label}</div>
                    <div class="qr-sesi-badge ${sesiClass}">Shift ${info.sesi.charAt(0).toUpperCase() + info.sesi.slice(1)}</div>
                    <div class="qr-canvas-wrap">
                        <div id="qr-wrap-${jenis}" style="width:148px;height:148px;"></div>
                    </div>
                    <div class="qr-token-str" id="token-str-${jenis}">${info.token}</div>
                    <div class="qr-window">
                        Berlaku: <span>${info.qr_mulai}</span> – <span>${info.qr_selesai}</span><br>
                        ${toleransiHtml}
                        <div class="qr-countdown" id="countdown-${jenis}"></div>
                    </div>
                </div>`;
        }
        grid.innerHTML = html;

        // Render QR codes
        for (const [jenis, info] of Object.entries(data.qr_status)) {
            if (info.token) {
                renderQrInto('qr-wrap-' + jenis, info.token);
            }
        }

        updateCountdowns(data.qr_status);
    } catch (e) {
        console.warn('Refresh gagal:', e);
    }
}

function updateCountdowns(qrStatus) {
    if (!qrStatus) return;
    const now = new Date();
    const nowMin = now.getHours() * 60 + now.getMinutes();

    for (const [jenis, info] of Object.entries(qrStatus)) {
        const el = document.getElementById('countdown-' + jenis);
        if (!el) continue;
        
        const [h, m] = info.qr_selesai.split(':').map(Number);
        const endMin = h * 60 + m;
        const sisa = endMin - nowMin;
        
        if (sisa <= 0) {
            el.textContent = '⏰ Waktu habis';
            el.style.color = 'var(--red)';
        } else if (sisa <= 5) {
            const mnt = sisa;
            el.textContent = `⚠️ Sisa ${mnt} menit`;
            el.style.color = 'var(--red)';
        } else {
            const jam = Math.floor(sisa / 60);
            const mnt = sisa % 60;
            el.textContent = jam > 0 ? `⏱️ Sisa ${jam}j ${mnt}m` : `⏱️ Sisa ${mnt} menit`;
            el.style.color = 'var(--green)';
        }
    }
}

function tickRefreshBar() {
    refreshCountdown--;
    if (refreshCountdown <= 0) {
        refreshTokens();
        refreshCountdown = REFRESH_INTERVAL;
    }
    const pct = ((REFRESH_INTERVAL - refreshCountdown) / REFRESH_INTERVAL) * 100;
    const fill = document.getElementById('refreshFill');
    if (fill) fill.style.width = pct + '%';
}

async function changeSesi(sesi) {
    currentSesi = sesi;
    
    // Update active class pada tombol
    document.querySelectorAll('.shift-btn').forEach(btn => {
        if (btn.dataset.sesi === sesi) {
            btn.classList.add('active');
        } else {
            btn.classList.remove('active');
        }
    });
    
    // Tampilkan loading
    const grid = document.getElementById('qrGrid');
    grid.innerHTML = `
        <div class="qr-placeholder">
            <div class="qr-placeholder-icon">⏳</div>
            <p>Memuat QR untuk shift ${sesi === 'pagi' ? 'Pagi' : (sesi === 'siang' ? 'Siang' : 'Semua')}...</p>
        </div>`;
    
    // Tampilkan refresh bar
    document.getElementById('refreshBar').style.display = 'block';
    
    // Refresh data
    await refreshTokens();
    refreshCountdown = REFRESH_INTERVAL;
}

// Event listeners untuk tombol shift
document.addEventListener('DOMContentLoaded', function() {
    document.querySelectorAll('.shift-btn').forEach(btn => {
        btn.addEventListener('click', () => {
            changeSesi(btn.dataset.sesi);
        });
    });
});

// Mulai auto-refresh jika ada QR
@if(count($qrStatus) > 0)
    // Render QR awal
    @foreach($qrStatus as $jenis => $info)
        renderQrInto('qr-wrap-{{ $jenis }}', '{{ $info['token'] }}');
    @endforeach
    
    updateCountdowns(@json($qrStatus));
    
    // Mulai timer refresh
    refreshTimer = setInterval(() => {
        tickRefreshBar();
    }, 1000);
@endif
</script>
@endpush