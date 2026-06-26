@extends('layouts.admin')

@section('title', 'Pengaturan Akun')

@section('breadcrumb')
    <span>PST</span><span>›</span><span>Admin</span><span>›</span><strong>Pengaturan</strong>
@endsection

@section('content')
<style>
/* ── PENGATURAN STYLES ── */
.page-head { 
    display:flex; 
    align-items:flex-end; 
    justify-content:space-between; 
    margin-bottom:22px; 
    padding-bottom:20px; 
    border-bottom:1px solid var(--rule); 
}
.page-head h1 { 
    font-size:19px; 
    font-weight:600; 
    letter-spacing:-.3px; 
}
.page-head p { 
    font-size:12px; 
    color:var(--ink3); 
    margin-top:3px; 
}

.settings-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
    max-width: 1200px;
}

.panel {
    background: var(--surface);
    border: 1px solid var(--rule);
    border-radius: 8px;
    overflow: hidden;
}

.panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid var(--rule);
    background: var(--wash);
}

.panel-title {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
    display: flex;
    align-items: center;
    gap: 8px;
}

.panel-title svg {
    color: var(--blue);
}

.panel-subtitle {
    font-size: 11px;
    color: var(--ink3);
    margin-top: 2px;
}

.panel-body {
    padding: 24px 20px;
}

.form-group {
    margin-bottom: 18px;
}

.form-group:last-child {
    margin-bottom: 0;
}

.form-label {
    display: block;
    font-size: 11.5px;
    font-weight: 600;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    color: var(--ink2);
    margin-bottom: 7px;
}

.form-input {
    width: 100%;
    padding: 10px 14px;
    border: 1.5px solid var(--rule);
    border-radius: 6px;
    font-size: 13px;
    font-family: 'IBM Plex Sans', sans-serif;
    color: var(--ink);
    background: var(--surface);
    transition: border-color 0.15s, box-shadow 0.15s;
}

.form-input:focus {
    outline: none;
    border-color: var(--blue);
    box-shadow: 0 0 0 3px var(--blue-lt);
}

.form-input:disabled {
    background: var(--wash);
    color: var(--ink3);
    cursor: not-allowed;
}

.form-input::placeholder {
    color: var(--ink3);
    opacity: 0.6;
}

.input-hint {
    font-size: 10.5px;
    color: var(--ink3);
    margin-top: 4px;
    font-family: 'IBM Plex Mono', monospace;
}

.btn-group {
    display: flex;
    gap: 10px;
    margin-top: 24px;
    padding-top: 20px;
    border-top: 1px solid var(--rule);
}

.btn {
    padding: 9px 20px;
    border-radius: 6px;
    font-size: 12.5px;
    font-weight: 600;
    font-family: 'IBM Plex Sans', sans-serif;
    cursor: pointer;
    transition: all 0.15s;
    border: none;
    display: inline-flex;
    align-items: center;
    gap: 6px;
}

.btn-primary {
    background: var(--blue);
    color: white;
}

.btn-primary:hover {
    background: #1548c4;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(26, 86, 219, 0.25);
}

.btn-secondary {
    background: var(--wash2);
    color: var(--ink2);
    border: 1px solid var(--rule);
}

.btn-secondary:hover {
    background: var(--wash);
    border-color: var(--ink3);
}

.btn-danger {
    background: var(--red);
    color: white;
}

.btn-danger:hover {
    background: #a82e23;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(192, 57, 43, 0.25);
}

.user-badge {
    display: inline-flex;
    align-items: center;
    gap: 10px;
    padding: 12px 16px;
    background: var(--blue-lt);
    border: 1px solid rgba(26, 86, 219, 0.2);
    border-radius: 6px;
    margin-bottom: 20px;
}

.user-ava {
    width: 40px;
    height: 40px;
    border-radius: 6px;
    background: var(--blue);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 14px;
    font-weight: 600;
    color: white;
}

.user-info {
    flex: 1;
}

.user-name {
    font-size: 13.5px;
    font-weight: 600;
    color: var(--ink);
}

.user-role {
    font-size: 11px;
    color: var(--ink3);
    text-transform: capitalize;
}

.alert {
    padding: 12px 16px;
    border-radius: 6px;
    font-size: 12px;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
    gap: 8px;
}

.alert-info {
    background: var(--blue-lt);
    color: var(--blue);
    border: 1px solid rgba(26, 86, 219, 0.2);
}

.password-strength {
    height: 3px;
    background: var(--wash2);
    border-radius: 2px;
    overflow: hidden;
    margin-top: 6px;
}

.strength-bar {
    height: 100%;
    width: 0;
    transition: width 0.3s, background 0.3s;
}

.strength-weak { width: 33%; background: var(--red); }
.strength-medium { width: 66%; background: var(--amber); }
.strength-strong { width: 100%; background: var(--green); }

@media (max-width: 900px) {
    .settings-grid {
        grid-template-columns: 1fr;
    }
}

@keyframes fadeIn {
    from { opacity: 0; transform: translateY(10px); }
    to { opacity: 1; transform: none; }
}

.panel {
    animation: fadeIn 0.3s ease both;
}

.panel:nth-child(2) {
    animation-delay: 0.1s;
}
</style>


    @if(session('success'))
    <div style="background:#e6f4ea; color:#137333; padding:10px; border-radius:6px; margin-bottom:15px;">
        {{ session('success') }}
    </div>
@endif

@if($errors->any())
    <div style="background:#fdecea; color:#b3261e; padding:10px; border-radius:6px; margin-bottom:15px;">
        {{ $errors->first() }}
    </div>
@endif

<div class="page-head">

    <div>
        <h1>Pengaturan Akun</h1>
        <p>Kelola informasi profil dan keamanan akun Anda</p>
    </div>
</div>

<div class="settings-grid">
    
    {{-- CARD 1: INFORMASI PROFIL --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/>
                    <circle cx="12" cy="7" r="4"/>
                </svg>
                Informasi Profil
            </div>
            <div class="panel-subtitle">Perbarui data pribadi Anda</div>
        </div>
        <div class="panel-body">
            
            {{-- User Badge --}}
            <div class="user-badge">
                <div class="user-ava">{{ strtoupper(substr(Auth::user()->name, 0, 2)) }}</div>
                <div class="user-info">
                    <div class="user-name">{{ Auth::user()->name }}</div>
                    <div class="user-role">{{ Auth::user()->role }}</div>
                </div>
            </div>

            <form method="POST" action="{{ route('admin.updateProfil') }}">
                @csrf

                {{-- Nama Lengkap --}}
                <div class="form-group">
                    <label class="form-label" for="name">Nama Lengkap</label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-input"
                        value="{{ Auth::user()->name }}"
                        placeholder="Masukkan nama lengkap"
                    >
                </div>

                {{-- Username --}}
                <div class="form-group">
                    <label class="form-label" for="username">Username</label>
                    <input 
                        type="text" 
                        id="username" 
                        name="username" 
                        class="form-input"
                        value="{{ Auth::user()->username }}"
                        placeholder="Masukkan username"
                    >
                    <div class="input-hint">Username digunakan untuk login ke sistem</div>
                </div>

                {{-- No HP --}}
                <div class="form-group">
                    <label class="form-label" for="no_hp">Nomor HP</label>
                    <input 
                        type="text" 
                        id="no_hp" 
                        name="no_hp" 
                        class="form-input"
                        value="{{ Auth::user()->no_hp ?? '' }}"
                        placeholder="Contoh: 081234567890"
                    >
                    <div class="input-hint">Format: 08xxxxxxxxxx (tanpa spasi atau strip)</div>
                </div>

                {{-- Tombol --}}
                <div class="btn-group">
                    <button type="submit" class="btn btn-primary">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <path d="M19 21H5a2 2 0 01-2-2V5a2 2 0 012-2h11l5 5v11a2 2 0 01-2 2z"/>
                            <polyline points="17 21 17 13 7 13 7 21"/>
                            <polyline points="7 3 7 8 15 8"/>
                        </svg>
                        Simpan Perubahan
                    </button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>

    {{-- CARD 2: GANTI PASSWORD --}}
    <div class="panel">
        <div class="panel-header">
            <div class="panel-title">
                <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="1.5" viewBox="0 0 24 24">
                    <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                    <path d="M7 11V7a5 5 0 0110 0v4"/>
                </svg>
                Keamanan Akun
            </div>
            <div class="panel-subtitle">Ubah password untuk keamanan akun</div>
        </div>
        <div class="panel-body">
            
            {{-- Alert Info --}}
            <div class="alert alert-info">
                <svg width="16" height="16" fill="currentColor" viewBox="0 0 24 24">
                    <circle cx="12" cy="12" r="10"/>
                    <line x1="12" y1="16" x2="12" y2="12" stroke="white" stroke-width="2"/>
                    <line x1="12" y1="8" x2="12.01" y2="8" stroke="white" stroke-width="2"/>
                </svg>
                Password minimal 8 karakter dengan kombinasi huruf dan angka
            </div>

            <form method="POST" action="{{ route('admin.updatePassword') }}" id="password-form">
                @csrf

                {{-- Password Lama --}}
             <div style="position:relative;">
    <input 
        type="password" 
        id="current_password" 
        name="current_password" 
        class="form-input"
        placeholder="Masukkan password lama"
    >

    <span onclick="togglePassword('current_password', this)" 
          style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--ink3);">

        <!-- ICON EYE -->
        <svg class="eye-open" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        <!-- ICON EYE OFF -->
        <svg class="eye-close" style="display:none;" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M17.94 17.94A10.94 10.94 0 0112 19C5 19 1 12 1 12a21.77 21.77 0 015.06-5.94"/>
            <path d="M9.9 4.24A10.94 10.94 0 0112 5c7 0 11 7 11 7a21.8 21.8 0 01-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>

    </span>
</div>

                {{-- Password Baru --}}
<div style="position:relative;">
    <input 
        type="password" 
        id="new_password" 
        name="new_password" 
        class="form-input"
        placeholder="Masukkan password baru"
        oninput="checkPasswordStrength(this.value)"
    >

    <span onclick="togglePassword('new_password', this)" 
          style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--ink3);">

        <!-- ICON EYE -->
        <svg class="eye-open" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        <!-- ICON EYE OFF -->
        <svg class="eye-close" style="display:none;" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M17.94 17.94A10.94 10.94 0 0112 19C5 19 1 12 1 12a21.77 21.77 0 015.06-5.94"/>
            <path d="M9.9 4.24A10.94 10.94 0 0112 5c7 0 11 7 11 7a21.8 21.8 0 01-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>

    </span>
</div>

                {{-- Konfirmasi Password --}}
                <div style="position:relative;">
    <input 
        type="password" 
        id="confirm_password" 
        name="confirm_password" 
        class="form-input"
        placeholder="Ketik ulang password baru"
    >

    <span onclick="togglePassword('confirm_password', this)" 
          style="position:absolute; right:12px; top:50%; transform:translateY(-50%); cursor:pointer; color:var(--ink3);">

        <!-- ICON EYE -->
        <svg class="eye-open" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M1 12s4-7 11-7 11 7 11 7-4 7-11 7S1 12 1 12z"/>
            <circle cx="12" cy="12" r="3"/>
        </svg>

        <!-- ICON EYE OFF -->
        <svg class="eye-close" style="display:none;" width="18" height="18" fill="none" stroke="currentColor" stroke-width="1.6" viewBox="0 0 24 24">
            <path d="M17.94 17.94A10.94 10.94 0 0112 19C5 19 1 12 1 12a21.77 21.77 0 015.06-5.94"/>
            <path d="M9.9 4.24A10.94 10.94 0 0112 5c7 0 11 7 11 7a21.8 21.8 0 01-2.16 3.19"/>
            <line x1="1" y1="1" x2="23" y2="23"/>
        </svg>

    </span>
</div>

                {{-- Tombol --}}
                <div class="btn-group">
                    <button type="submit" class="btn btn-danger">
                        <svg width="14" height="14" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                            <rect x="3" y="11" width="18" height="11" rx="2" ry="2"/>
                            <path d="M7 11V7a5 5 0 0110 0v4"/>
                        </svg>
                        Ganti Password
                    </button>
                    <button type="reset" class="btn btn-secondary">Reset</button>
                </div>
            </form>
        </div>
    </div>

</div>
@endsection

@push('scripts')
<script>
// Password Strength Checker
function checkPasswordStrength(password) {
    const bar = document.getElementById('strength-bar');
    const text = document.getElementById('strength-text');
    
    if (!password) {
        bar.className = 'strength-bar';
        text.textContent = '';
        return;
    }
    
    let strength = 0;
    
    // Cek panjang
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    
    // Cek kompleksitas
    if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;
    
    // Set visual
    if (strength <= 2) {
        bar.className = 'strength-bar strength-weak';
        text.textContent = '⚠️ Password lemah';
        text.style.color = 'var(--red)';
    } else if (strength <= 4) {
        bar.className = 'strength-bar strength-medium';
        text.textContent = '⚡ Password cukup kuat';
        text.style.color = 'var(--amber)';
    } else {
        bar.className = 'strength-bar strength-strong';
        text.textContent = '✓ Password kuat';
        text.style.color = 'var(--green)';
    }
}
</script>

<script>
function togglePassword(id, el) {
    const input = document.getElementById(id);
    const eyeOpen = el.querySelector('.eye-open');
    const eyeClose = el.querySelector('.eye-close');

    if (input.type === "password") {
        input.type = "text";
        eyeOpen.style.display = "none";
        eyeClose.style.display = "block";
    } else {
        input.type = "password";
        eyeOpen.style.display = "block";
        eyeClose.style.display = "none";
    }
}
</script>

@endpush