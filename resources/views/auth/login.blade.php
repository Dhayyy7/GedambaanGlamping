<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    @php
        $appSetting = \App\Models\Setting::getSetting();
    @endphp

    <title>Login Admin - {{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }}</title>

    <!-- Favicon Icon Tab Web -->
    @if($appSetting->logo && file_exists(public_path($appSetting->logo)))
        <link rel="icon" type="image/x-icon" href="/{{ $appSetting->logo }}">
        <link rel="shortcut icon" href="/{{ $appSetting->logo }}">
        <link rel="apple-touch-icon" href="/{{ $appSetting->logo }}">
    @endif
    
    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- FontAwesome Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        :root {
            --primary: #4f46e5;
            --primary-hover: #4338ca;
            --bg-gradient-1: #0f172a;
            --bg-gradient-2: #1e1b4b;
            --glass-bg: rgba(30, 41, 59, 0.7);
            --glass-border: rgba(255, 255, 255, 0.1);
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
            font-family: 'Plus Jakarta Sans', sans-serif;
        }

        body {
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            background: radial-gradient(circle at top right, #1e1b4b, #0f172a, #020617);
            padding: 1.5rem;
            color: var(--text-main);
            position: relative;
            overflow-x: hidden;
        }

        /* Ambient Glow Spheres */
        .glow-1 {
            position: absolute;
            top: -10%;
            right: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(99, 102, 241, 0.25), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .glow-2 {
            position: absolute;
            bottom: -10%;
            left: -10%;
            width: 450px;
            height: 450px;
            background: radial-gradient(circle, rgba(236, 72, 153, 0.15), transparent 70%);
            border-radius: 50%;
            pointer-events: none;
        }

        .login-card {
            width: 100%;
            max-width: 440px;
            background: var(--glass-bg);
            backdrop-filter: blur(16px);
            -webkit-backdrop-filter: blur(16px);
            border: 1px solid var(--glass-border);
            border-radius: 24px;
            padding: 2.5rem 2rem;
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.5);
            z-index: 10;
        }

        .brand-header {
            text-align: center;
            margin-bottom: 2rem;
        }

        .brand-logo {
            width: 60px;
            height: 60px;
            background: linear-gradient(135deg, #6366f1, #a855f7);
            border-radius: 16px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            color: #ffffff;
            font-size: 1.75rem;
            margin-bottom: 1rem;
            box-shadow: 0 10px 20px rgba(99, 102, 241, 0.3);
        }

        .brand-title {
            font-size: 1.5rem;
            font-weight: 700;
            color: #ffffff;
            letter-spacing: -0.02em;
        }

        .brand-subtitle {
            font-size: 0.875rem;
            color: var(--text-muted);
            margin-top: 0.25rem;
        }

        .alert {
            padding: 0.875rem 1rem;
            border-radius: 12px;
            font-size: 0.875rem;
            margin-bottom: 1.5rem;
            display: flex;
            align-items: center;
            gap: 0.75rem;
        }

        .alert-danger {
            background: rgba(239, 68, 68, 0.15);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(34, 197, 94, 0.15);
            border: 1px solid rgba(34, 197, 94, 0.3);
            color: #86efac;
        }

        .form-group {
            margin-bottom: 1.25rem;
        }

        .form-label {
            display: block;
            font-size: 0.85rem;
            font-weight: 600;
            color: #cbd5e1;
            margin-bottom: 0.5rem;
        }

        .input-group {
            position: relative;
        }

        .input-icon {
            position: absolute;
            left: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .form-input {
            width: 100%;
            padding: 0.875rem 1rem 0.875rem 2.75rem;
            background: rgba(15, 23, 42, 0.6);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: 12px;
            color: #ffffff;
            font-size: 0.95rem;
            outline: none;
            transition: all 0.2s ease;
        }

        .form-input:focus {
            border-color: #6366f1;
            box-shadow: 0 0 0 4px rgba(99, 102, 241, 0.15);
            background: rgba(15, 23, 42, 0.8);
        }

        .form-input:focus + .input-icon {
            color: #818cf8;
        }

        .toggle-password {
            position: absolute;
            right: 1rem;
            top: 50%;
            transform: translateY(-50%);
            color: #64748b;
            cursor: pointer;
            border: none;
            background: none;
            font-size: 1rem;
            transition: color 0.2s;
        }

        .toggle-password:hover {
            color: #94a3b8;
        }

        .form-options {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 1.5rem;
            font-size: 0.85rem;
        }

        .remember-me {
            display: flex;
            align-items: center;
            gap: 0.5rem;
            color: #94a3b8;
            cursor: pointer;
        }

        .remember-me input[type="checkbox"] {
            accent-color: #6366f1;
            width: 16px;
            height: 16px;
            border-radius: 4px;
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            padding: 0.875rem;
            background: linear-gradient(135deg, #6366f1, #4f46e5);
            border: none;
            border-radius: 12px;
            color: #ffffff;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            box-shadow: 0 10px 20px -5px rgba(99, 102, 241, 0.4);
            transition: all 0.2s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
        }

        .btn-submit:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca);
            transform: translateY(-1px);
            box-shadow: 0 12px 24px -5px rgba(99, 102, 241, 0.5);
        }

        .btn-submit:active {
            transform: translateY(0);
        }

        .footer-note {
            text-align: center;
            margin-top: 1.75rem;
            font-size: 0.8rem;
            color: #64748b;
        }
    </style>
</head>
<body>

    <div class="glow-1"></div>
    <div class="glow-2"></div>

    <div class="login-card">
        <div class="brand-header">
            <div class="brand-logo">
                <i class="fa-solid fa-house-chimney"></i>
            </div>
            <h1 class="brand-title">{{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }}</h1>
            <p class="brand-subtitle">Panel Kontrol Admin & Manajemen</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fa-solid fa-circle-check"></i>
                <div>{{ session('success') }}</div>
            </div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <i class="fa-solid fa-triangle-exclamation"></i>
                <div>{{ $errors->first() }}</div>
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label for="login_id" class="form-label">Username atau Email</label>
                <div class="input-group">
                    <input type="text" id="login_id" name="login_id" class="form-input" placeholder="Masukkan username atau email" value="{{ old('login_id') }}" required autofocus>
                    <i class="fa-solid fa-user input-icon"></i>
                </div>
            </div>

            <div class="form-group">
                <label for="password" class="form-label">Password</label>
                <div class="input-group">
                    <input type="password" id="password" name="password" class="form-input" placeholder="••••••••" required>
                    <i class="fa-solid fa-lock input-icon"></i>
                    <button type="button" class="toggle-password" onclick="togglePasswordVisibility()">
                        <i class="fa-solid fa-eye" id="toggleIcon"></i>
                    </button>
                </div>
            </div>

            <div class="form-options">
                <label class="remember-me">
                    <input type="checkbox" name="remember"> Ingat Saya
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <span>Masuk Sekarang</span>
                <i class="fa-solid fa-arrow-right"></i>
            </button>
        </form>

        <div class="footer-note">
            &copy; {{ date('Y') }} {{ $appSetting->homestay_name ?? 'Gedambaan Glamping' }}. All rights reserved.
        </div>
    </div>

    <script>
        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const toggleIcon = document.getElementById('toggleIcon');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleIcon.classList.remove('fa-eye');
                toggleIcon.classList.add('fa-eye-slash');
            } else {
                passwordInput.type = 'password';
                toggleIcon.classList.remove('fa-eye-slash');
                toggleIcon.classList.add('fa-eye');
            }
        }
    </script>
</body>
</html>
