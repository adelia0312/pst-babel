<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PST BPS Babel — Sistem Pengelolaan Petugas</title>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    :root {
        --blue:      #1a4dcc;
        --blue-dark: #0f2f8a;
        --blue-mid:  #1e56d9;
        --blue-lt:   #2563eb;
        --gold:      #e8b931;
        --gold-lt:   #f5d060;
        --white:     #ffffff;
        --gray-50:   #f9fafb;
        --gray-100:  #f3f4f6;
        --gray-200:  #e5e7eb;
        --gray-400:  #9ca3af;
        --gray-600:  #6b7280;
        --gray-800:  #1f2937;
        --gray-900:  #111827;
    }

    html, body {
        height: 100%;
        font-family: 'Plus Jakarta Sans', sans-serif;
    }

    .page {
        display: grid;
        grid-template-columns: 3fr 2fr;
        min-height: 100vh;
        position: relative;
        overflow: hidden;
    }

    .card {
        display: contents;
    }

    /* LEFT PANEL — BIRU */
    .left {
        background: radial-gradient(circle at 10% 20%, #1e4ad0, #0c2768);
        padding: 56px 56px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        position: relative;
        overflow: hidden;
    }

    /* Grid pattern subtle */
    .grid-overlay {
        position: absolute;
        top: 0; left: 0; width: 100%; height: 100%;
        background-image: radial-gradient(rgba(255,255,255,0.06) 1px, transparent 1px);
        background-size: 32px 32px;
        pointer-events: none;
    }

    /* Logo area */
    .left-logo {
        display: flex; align-items: center; gap: 18px;
        position: relative; z-index: 2;
        margin-bottom: 56px;
        width: fit-content;
    }
    .left-logo img {
        height: 56px;
        filter: drop-shadow(0 4px 12px rgba(0,0,0,0.25));
    }
    .left-logo-text .brand {
        font-size: 16px; font-weight: 800; color: #fff; line-height: 1.3;
        letter-spacing: -0.2px;
    }
    .left-logo-text .sub {
        font-size: 12px; color: rgba(255,255,255,0.75); font-weight: 500; margin-top: 4px;
        letter-spacing: 0.2px;
    }

    /* Welcome content */
    .left-body {
        position: relative; z-index: 2;
        max-width: 500px;
    }

    .greeting-badge {
        display: inline-flex;
        align-items: center;
        gap: 8px;
        background: rgba(232,185,49,0.15);
        border: 1px solid rgba(232,185,49,0.35);
        padding: 6px 16px;
        border-radius: 40px;
        margin-bottom: 28px;
        font-size: 11px;
        font-weight: 700;
        letter-spacing: 1.5px;
        color: #f5d060;
        text-transform: uppercase;
    }
    .greeting-badge::before {
        content: "●";
        font-size: 8px;
        color: #f5d060;
    }

    .welcome-title {
        font-size: 48px;
        font-weight: 800;
        color: #fff;
        line-height: 1.15;
        letter-spacing: -0.02em;
        margin-bottom: 20px;
    }
    .welcome-title span {
        background: linear-gradient(135deg, #FFE484, #F9B81B);
        -webkit-background-clip: text;
        background-clip: text;
        color: transparent;
        display: inline-block;
    }

    .welcome-desc {
        font-size: 15px;
        color: rgba(255,255,255,0.7);
        line-height: 1.65;
        margin-bottom: 0;
    }


    /* RIGHT PANEL */
    .right {
        padding: 48px 44px;
        display: flex;
        flex-direction: column;
        justify-content: center;
        background: #fff;
        overflow-y: auto;
        position: relative;
    }

    .right h2 {
        font-size: 26px;
        font-weight: 800;
        color: #0f2f5c;
        letter-spacing: -0.02em;
        margin-bottom: 8px;
    }
    .right .subtitle {
        font-size: 13px;
        color: #6c86a3;
        margin-bottom: 28px;
        border-left: 3px solid #2563eb;
        padding-left: 14px;
        font-weight: 450;
    }

    /* Alert */
    .alert {
        padding: 12px 16px;
        border-radius: 14px;
        font-size: 12.5px;
        line-height: 1.45;
        margin-bottom: 22px;
    }
    .alert-error {
        background: #fef2f2;
        border: 1px solid #fecaca;
        color: #dc2626;
    }
    .alert-success {
        background: #f0fdf4;
        border: 1px solid #bbf7d0;
        color: #16a34a;
    }

    /* Input fields */
    .field { margin-bottom: 18px; }
    .field label {
        display: block;
        font-size: 11px;
        font-weight: 700;
        color: #4a5b7a;
        letter-spacing: 0.5px;
        text-transform: uppercase;
        margin-bottom: 6px;
    }
    .input-wrap { position: relative; }
    .input-icon {
        position: absolute;
        left: 14px;
        top: 50%;
        transform: translateY(-50%);
        color: #9aaec9;
        pointer-events: none;
        width: 17px;
        height: 17px;
    }
    .field input {
        width: 100%;
        height: 50px;
        padding: 0 48px 0 46px;
        background: #f8fafd;
        border: 1.5px solid #e2e8f0;
        border-radius: 14px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        font-weight: 500;
        color: #1e293b;
        transition: all 0.2s;
    }
    .field input::placeholder { color: #b9c8e8; font-weight: 450; }
    .field input:focus {
        outline: none;
        border-color: #2563eb;
        background: #ffffff;
        box-shadow: 0 0 0 3px rgba(37,99,235,0.1);
    }
    .field input.is-invalid {
        border-color: #ef4444;
        background: #fffbfb;
    }
    .err-msg {
        color: #ef4444;
        font-size: 11px;
        margin-top: 5px;
        display: block;
        margin-left: 4px;
    }

    /* Eye button */
    .eye-btn {
        position: absolute;
        right: 14px;
        top: 50%;
        transform: translateY(-50%);
        background: none;
        border: none;
        cursor: pointer;
        padding: 6px;
        color: #9aaec9;
        line-height: 0;
        transition: color 0.2s;
        border-radius: 30px;
    }
    .eye-btn:hover {
        color: #2563eb;
        background: #eff4ff;
    }

    /* Remember me */
    .rem-row {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin: 20px 0 26px;
    }
    .rem-left {
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .rem-left input[type="checkbox"] {
        width: 16px;
        height: 16px;
        accent-color: #2563eb;
        cursor: pointer;
        border-radius: 4px;
    }
    .rem-left label {
        font-size: 13px;
        color: #3a546d;
        font-weight: 500;
        cursor: pointer;
    }

    /* Login button */
    .btn-login {
        width: 100%;
        height: 52px;
        background: linear-gradient(105deg, #1f4096, #2563eb);
        color: white;
        border: none;
        border-radius: 40px;
        font-family: 'Plus Jakarta Sans', sans-serif;
        font-size: 14px;
        font-weight: 700;
        letter-spacing: 0.3px;
        cursor: pointer;
        transition: all 0.25s;
        box-shadow: 0 6px 18px rgba(37,99,235,0.3);
        margin-bottom: 20px;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
    }
    .btn-login:hover {
        transform: translateY(-2px);
        box-shadow: 0 12px 28px rgba(37,99,235,0.4);
        background: linear-gradient(105deg, #1a3790, #1e56db);
    }
    .btn-login:active { transform: translateY(1px); }

    .form-foot {
        text-align: center;
        font-size: 11.5px;
        color: #8aa0bc;
        border-top: 1px solid #edf2f7;
        padding-top: 22px;
        margin-top: 6px;
    }
    .form-foot strong {
        color: #2c4f8c;
        font-weight: 700;
    }

    /* ===== RESPONSIVE MOBILE ===== */
    @media (max-width: 900px) {
        .page {
            grid-template-columns: 1fr;
            grid-template-rows: auto 1fr;
            overflow: auto;
            min-height: 100vh;
        }

        /* Panel biru di atas, lebih compact */
        .left {
            padding: 36px 24px 40px;
            justify-content: center;
            align-items: flex-start;
        }

        .left-logo {
            margin-bottom: 32px;
        }
        .left-logo img { height: 44px; }
        .left-logo-text .brand { font-size: 14px; }
        .left-logo-text .sub { font-size: 11px; }

        .welcome-title { font-size: 32px; }
        .welcome-desc { font-size: 14px; }
        .greeting-badge { font-size: 10px; margin-bottom: 20px; }

        /* Panel putih mengisi sisa layar */
        .right {
            padding: 36px 24px 48px;
            min-height: unset;
            justify-content: flex-start;
        }

        .right h2 { font-size: 22px; }
        .right .subtitle { font-size: 12.5px; margin-bottom: 24px; }
    }

    @media (max-width: 480px) {
        .left { padding: 28px 20px 32px; }
        .right { padding: 28px 20px 40px; }
        .welcome-title { font-size: 28px; }
        .left-logo {
            gap: 12px;
        }
        .left-logo img { height: 40px; }
        .left-logo-text .brand { font-size: 13px; }
        .greeting-badge { padding: 5px 12px; }
        .btn-login { height: 48px; font-size: 13px; }
    }
    </style>
</head>
<body>

<div class="page">
    <div class="card">

        <!-- LEFT PANEL -->
        <div class="left">
            <div class="grid-overlay"></div>

            <!-- Logo -->
            <div class="left-logo">
                <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS">
                <div class="left-logo-text">
                    <div class="brand">Sistem Pengelolaan Petugas</div>
                    <div class="sub">Pelayanan Statistik Terpadu (PST)</div>
                </div>
            </div>

            <div class="left-body">
                <div class="greeting-badge">Akses Terpusat & Aman</div>
                <h1 class="welcome-title">
                    Selamat Datang di<br>
                    <span>Sistem Pengelolan Kinerja Petugas PST</span>
                </h1>
                <p class="welcome-desc">
                    Platform Manajemen Petugas Pelayanan Statistik Terpadu — Provinsi Kepulauan Bangka Belitung.
                </p>
            </div>
        </div>

        <!-- RIGHT PANEL – FORM LOGIN -->
        <div class="right">

            <h2>Masuk ke Akun</h2>
            <p class="subtitle">Silakan login untuk melanjutkan aktivitas Anda</p>

            @if($errors->has('login'))
            <div class="alert alert-error">
                ⚠️ {{ $errors->first('login') }}
            </div>
            @endif

            @if(session('success'))
            <div class="alert alert-success">
                ✔️ {{ session('success') }}
            </div>
            @endif

            <form action="{{ route('login') }}" method="POST" autocomplete="off">
                @csrf

                <div class="field">
                    <label for="username">Username</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <path d="M20 21v-2a4 4 0 0 0-4-4H8a4 4 0 0 0-4 4v2"/><circle cx="12" cy="7" r="4"/>
                        </svg>
                        <input type="text" id="username" name="username" value="{{ old('username') }}"
                               class="{{ $errors->has('username') ? 'is-invalid' : '' }}"
                               placeholder="Masukkan username" autofocus autocomplete="username" required>
                    </div>
                    @error('username')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                <div class="field">
                    <label for="password">Password</label>
                    <div class="input-wrap">
                        <svg class="input-icon" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                            <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>
                        <input type="password" id="password" name="password"
                               class="{{ $errors->has('password') ? 'is-invalid' : '' }}"
                               placeholder="Masukkan password" autocomplete="current-password" required>
                        <button type="button" class="eye-btn" onclick="togglePwd()">
                            <svg id="eyeShow" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8">
                                <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg id="eyeHide" width="17" height="17" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" style="display:none">
                                <path d="M17.94 17.94A10.07 10.07 0 0 1 12 20c-7 0-11-8-11-8a18.45 18.45 0 0 1 5.06-5.94M9.9 4.24A9.12 9.12 0 0 1 12 4c7 0 11 8 11 8a18.5 18.5 0 0 1-2.16 3.19m-6.72-1.07a3 3 0 1 1-4.24-4.24"/>
                                <line x1="1" y1="1" x2="23" y2="23"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')<span class="err-msg">{{ $message }}</span>@enderror
                </div>

                

                <button type="submit" class="btn-login">
                    → Masuk ke Sistem
                </button>
            </form>

            <div class="form-foot">
                Sistem PST · <strong>BPS Provinsi Kepulauan Bangka Belitung</strong>
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
function togglePwd() {
    const inp = document.getElementById('password');
    const show = document.getElementById('eyeShow');
    const hide = document.getElementById('eyeHide');
    if (inp.type === 'password') {
        inp.type = 'text';
        show.style.display = 'none';
        hide.style.display = '';
    } else {
        inp.type = 'password';
        show.style.display = '';
        hide.style.display = 'none';
    }
}

@if(session('login_success'))
const redirectUrl = "{{ session('redirect_url') }}";
const st = document.createElement('style');
st.textContent = `.swal-ok{border-radius:20px!important;background:#fff!important;box-shadow:0 20px 40px rgba(0,0,0,0.1)!important}
.swal-ok .swal2-success-line-tip,.swal-ok .swal2-success-line-long{background:#10b981!important}
.swal-ok .swal2-success-ring{border-color:rgba(16,185,129,0.2)!important}
.swal-ok-bar{background:linear-gradient(90deg,#1a4dcc,#2563eb)!important;height:3px!important}`;
document.head.appendChild(st);

Swal.fire({
    title: '<span style="font-size:16px;font-weight:800;color:#0f2f5c;">Login Berhasil!</span>',
    html: `<div style="margin-top:6px">
        <p style="font-size:12px;color:#7e95b0;margin:0 0 5px">Selamat datang kembali,</p>
        <p style="font-size:15px;font-weight:700;color:#1a4dcc;margin:0">{{ session('user_name') }}</p>
    </div>`,
    icon: 'success',
    showConfirmButton: false,
    allowOutsideClick: false,
    allowEscapeKey: false,
    width: '260px',
    padding: '1.3em',
    timer: 2000,
    timerProgressBar: true,
    background: '#fff',
    backdrop: 'rgba(10, 35, 70, 0.65)',
    customClass: { popup: 'swal-ok', timerProgressBar: 'swal-ok-bar' },
    didOpen: () => { setTimeout(() => { window.location.href = redirectUrl; }, 2000); }
});
@endif
</script>
</body>
</html>