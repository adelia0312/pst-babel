<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Redirecting - Sistem PST BPS Babel</title>
    <link href="https://fonts.googleapis.com/css2?family=Inter:opsz,wght@14..32,300;14..32,400;14..32,500;14..32,600;14..32,700&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            margin: 0;
            padding: 0;
            min-height: 100vh;
            background: linear-gradient(135deg, #001533 0%, #00204d 40%, #003580 75%, #1a4d9a 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            font-family: 'Inter', sans-serif;
            position: relative;
            overflow: hidden;
        }

        .bg-orb {
            position: fixed;
            border-radius: 50%;
            filter: blur(80px);
            pointer-events: none;
            z-index: 0;
        }
        .orb-1 { 
            width: 400px; 
            height: 400px; 
            background: radial-gradient(circle, rgba(232,185,49,0.12) 0%, transparent 70%); 
            top: -100px; 
            right: -80px; 
            animation: float 8s ease-in-out infinite; 
        }
        .orb-2 { 
            width: 300px; 
            height: 300px; 
            background: radial-gradient(circle, rgba(26,77,154,0.25) 0%, transparent 70%); 
            bottom: -60px; 
            left: -40px; 
            animation: float 10s ease-in-out infinite reverse; 
        }

        @keyframes float {
            0%, 100% { transform: translateY(0) scale(1); }
            50% { transform: translateY(-15px) scale(1.03); }
        }

        .container {
            position: relative;
            z-index: 1;
            width: 100%;
            max-width: 340px;
            padding: 16px;
        }

        .card {
            background: rgba(255, 255, 255, 0.97);
            backdrop-filter: blur(10px);
            border-radius: 24px;
            padding: 28px 24px;
            text-align: center;
            box-shadow: 0 20px 40px -12px rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.3);
            animation: fadeInUp 0.4s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .logo {
            margin-bottom: 20px;
        }

        .logo-circle {
            width: 52px;
            height: 52px;
            background: #f8fafc;
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.04);
        }

        .logo-circle img {
            width: 32px;
            height: 32px;
        }

        .greeting {
            font-size: 20px;
            font-weight: 600;
            color: #1e293b;
            margin-bottom: 6px;
            letter-spacing: -0.2px;
        }

        .user-name {
            font-size: 16px;
            font-weight: 500;
            color: #475569;
            margin-bottom: 14px;
        }

        .role-badge {
            display: inline-block;
            background: linear-gradient(135deg, #e8b931 0%, #d4a020 100%);
            color: #1e293b;
            font-size: 12px;
            font-weight: 600;
            padding: 4px 14px;
            border-radius: 30px;
            margin-bottom: 24px;
            letter-spacing: 0.2px;
        }

        .spinner {
            display: inline-block;
            width: 30px;
            height: 30px;
            border: 2.5px solid #e2e8f0;
            border-radius: 50%;
            border-top-color: #e8b931;
            animation: spin 0.7s linear infinite;
            margin-bottom: 16px;
        }

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .loading-text {
            font-size: 12px;
            color: #94a3b8;
            font-weight: 500;
        }

        .progress-wrapper {
            margin-top: 20px;
        }

        .progress-bar {
            width: 100%;
            height: 2px;
            background: #e2e8f0;
            border-radius: 10px;
            overflow: hidden;
        }

        .progress-fill {
            width: 0%;
            height: 100%;
            background: linear-gradient(90deg, #e8b931, #f0c94a);
            border-radius: 10px;
            animation: fillProgress 1.5s ease-out forwards;
        }

        @keyframes fillProgress {
            from { width: 0%; }
            to { width: 100%; }
        }
    </style>
</head>
<body>

<div class="bg-orb orb-1"></div>
<div class="bg-orb orb-2"></div>

<div class="container">
    <div class="card">
        <div class="logo">
            <div class="logo-circle">
                <img src="{{ asset('images/logo-bps.png') }}" alt="Logo BPS">
            </div>
        </div>

        <div class="greeting">Selamat datang</div>
        <div class="user-name" id="userNameText">-</div>
        <div class="role-badge" id="roleText">-</div>

        <div class="spinner"></div>
        <div class="loading-text">Mengalihkan</div>

        <div class="progress-wrapper">
            <div class="progress-bar">
                <div class="progress-fill"></div>
            </div>
        </div>
    </div>
</div>

<script>
    // Ambil data dari session
    const userName = @json(session('user_name', ''));
    const roleDisplay = @json(session('role_display', ''));
    const redirectUrl = @json(session('redirect_url', '/'));

    // Update tampilan
    if (userName) {
        document.getElementById('userNameText').innerText = userName;
    }
    
    if (roleDisplay) {
        document.getElementById('roleText').innerText = roleDisplay;
    } else {
        document.getElementById('roleText').style.display = 'none';
    }

    // Tampilkan SweetAlert - UKURAN KECIL, TIDAK DOUBLE ROLE
    setTimeout(() => {
        if (!userName || !roleDisplay) {
            window.location.href = redirectUrl;
            return;
        }

        Swal.fire({
            title: 'Login Berhasil',
            html: `<div style="font-size:15px;color:#475569;">Selamat datang,</div><div style="font-size:20px;font-weight:700;color:#1e3a8a;margin-top:4px;">${userName}</div>`,
            icon: 'success',
            confirmButtonText: 'Masuk ke Dashboard',
            confirmButtonColor: '#1e3a8a',
            background: '#ffffff',
            showConfirmButton: true,
            timer: 1500,
            timerProgressBar: true,
            allowOutsideClick: false,
            allowEscapeKey: false,
            width: '480px',
            padding: '2rem',
            customClass: {
                title: 'sweet-title',
                popup: 'sweet-popup',
                confirmButton: 'sweet-button'
            }
        }).then(() => {
            window.location.href = redirectUrl;
        });
    }, 1500);

    // Fallback
    setTimeout(() => {
        window.location.href = redirectUrl;
    }, 4000);
</script>

<style>
    /* Style tambahan untuk SweetAlert yang lebih kecil */
    .sweet-popup {
        font-size: 15px;
        border-radius: 20px !important;
    }
    .sweet-title {
        font-size: 24px !important;
        padding-bottom: 8px !important;
    }
    .sweet-button {
        font-size: 15px !important;
        padding: 10px 28px !important;
        border-radius: 10px !important;
    }
</style>

</body>
</html>