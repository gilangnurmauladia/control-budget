<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login — Budget Control System</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            min-height: 100vh;
            background: #0d1117;
            display: flex;
            align-items: center;
            justify-content: center;
            overflow: hidden;
            position: relative;
        }
        /* Animated background */
        .bg-grid {
            position: fixed; inset: 0;
            background-image:
                linear-gradient(rgba(56,139,253,.06) 1px, transparent 1px),
                linear-gradient(90deg, rgba(56,139,253,.06) 1px, transparent 1px);
            background-size: 50px 50px;
            pointer-events: none;
        }
        .bg-glow-1 {
            position: fixed; top: -200px; left: -200px; width: 600px; height: 600px;
            background: radial-gradient(circle, rgba(31,111,235,.2) 0%, transparent 70%);
            pointer-events: none; animation: float1 8s ease-in-out infinite;
        }
        .bg-glow-2 {
            position: fixed; bottom: -200px; right: -200px; width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(188,140,255,.15) 0%, transparent 70%);
            pointer-events: none; animation: float2 10s ease-in-out infinite;
        }
        @keyframes float1 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(40px, 30px); } }
        @keyframes float2 { 0%,100% { transform: translate(0,0); } 50% { transform: translate(-30px, -40px); } }

        .login-container {
            position: relative; z-index: 10;
            width: 100%; max-width: 420px;
            padding: 0 20px;
        }
        .login-card {
            background: #161b22;
            border: 1px solid #30363d;
            border-radius: 16px;
            padding: 40px 36px;
            box-shadow: 0 24px 64px rgba(0,0,0,.6);
        }
        .login-logo {
            width: 52px; height: 52px;
            background: linear-gradient(135deg, #1f6feb, #388bfd);
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 26px; color: white;
            margin: 0 auto 20px;
            box-shadow: 0 8px 24px rgba(31,111,235,.35);
        }
        .login-title {
            font-size: 22px; font-weight: 700;
            color: #e6edf3; text-align: center; margin-bottom: 4px;
        }
        .login-subtitle { font-size: 13px; color: #8b949e; text-align: center; margin-bottom: 32px; }

        .form-group { margin-bottom: 18px; }
        .form-label-custom {
            font-size: 12px; font-weight: 600; letter-spacing: .5px;
            color: #8b949e; text-transform: uppercase; display: block; margin-bottom: 8px;
        }
        .input-group-custom {
            position: relative;
        }
        .input-icon {
            position: absolute; left: 14px; top: 50%; transform: translateY(-50%);
            color: #8b949e; font-size: 16px; pointer-events: none; z-index: 2;
        }
        .form-input {
            width: 100%;
            background: #0d1117 !important; color: #e6edf3 !important;
            border: 1px solid #30363d !important; border-radius: 10px !important;
            padding: 12px 14px 12px 42px !important;
            font-size: 14px; font-family: 'Inter', sans-serif;
            transition: all .2s;
            outline: none;
        }
        .form-input:focus {
            border-color: #388bfd !important;
            box-shadow: 0 0 0 3px rgba(56,139,253,.15) !important;
        }
        .form-input::placeholder { color: #484f58; }

        .btn-login {
            width: 100%; padding: 13px;
            background: linear-gradient(135deg, #1f6feb, #388bfd);
            color: white; border: none; border-radius: 10px;
            font-size: 14px; font-weight: 600; font-family: 'Inter', sans-serif;
            cursor: pointer; transition: all .2s;
            margin-top: 8px;
        }
        .btn-login:hover { transform: translateY(-1px); box-shadow: 0 8px 20px rgba(31,111,235,.35); }
        .btn-login:active { transform: translateY(0); }

        .alert-error {
            background: rgba(248,81,73,.1); border: 1px solid rgba(248,81,73,.3);
            color: #f85149; border-radius: 10px; padding: 12px 16px;
            font-size: 13px; margin-bottom: 20px;
            display: flex; align-items: center; gap: 8px;
        }
        .demo-accounts {
            margin-top: 24px; padding-top: 20px;
            border-top: 1px solid #30363d;
        }
        .demo-title { font-size: 11px; color: #8b949e; text-align: center; margin-bottom: 12px; text-transform: uppercase; letter-spacing: .8px; font-weight: 600; }
        .demo-badge {
            display: inline-flex; align-items: center; gap: 6px;
            background: rgba(56,139,253,.1); border: 1px solid rgba(56,139,253,.25);
            color: #58a6ff; border-radius: 6px; padding: 4px 10px;
            font-size: 11px; cursor: pointer; transition: all .2s; margin: 3px;
        }
        .demo-badge:hover { background: rgba(56,139,253,.2); }
        .login-footer { text-align: center; font-size: 11px; color: #484f58; margin-top: 22px; }
    </style>
</head>
<body>
<div class="bg-grid"></div>
<div class="bg-glow-1"></div>
<div class="bg-glow-2"></div>

<div class="login-container">
    <div class="login-logo"><i class="bi bi-bar-chart-fill"></i></div>
    <div class="login-card">
        <h1 class="login-title">Budget Control System</h1>
        <p class="login-subtitle">Sistem Kontrol Anggaran Operasional</p>

        @if(session('error'))
        <div class="alert-error"><i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}</div>
        @endif

        <form method="POST" action="{{ route('login.post') }}">
            @csrf
            <div class="form-group">
                <label class="form-label-custom" for="username">Username</label>
                <div class="input-group-custom">
                    <i class="bi bi-person input-icon"></i>
                    <input type="text" id="username" name="username"
                        class="form-input" placeholder="Masukkan username"
                        value="{{ old('username') }}" autocomplete="username" required>
                </div>
            </div>
            <div class="form-group">
                <label class="form-label-custom" for="password">Password</label>
                <div class="input-group-custom">
                    <i class="bi bi-lock input-icon"></i>
                    <input type="password" id="password" name="password"
                        class="form-input" placeholder="Masukkan password"
                        autocomplete="current-password" required>
                </div>
            </div>
            <button type="submit" class="btn-login">
                <i class="bi bi-box-arrow-in-right me-2"></i>Masuk ke Sistem
            </button>
        </form>

        <div class="demo-accounts">
            <div class="demo-title">Akun Demo (Password: password123)</div>
            <div class="text-center">
                <span class="demo-badge" onclick="fillLogin('superadmin')"><i class="bi bi-shield-check"></i> Super Admin</span>
                <span class="demo-badge" onclick="fillLogin('pic_produksi')"><i class="bi bi-person-badge"></i> PIC Dept</span>
                <span class="demo-badge" onclick="fillLogin('admin_gudang')"><i class="bi bi-box"></i> Admin Gudang</span>
                <span class="demo-badge" onclick="fillLogin('manager')"><i class="bi bi-graph-up"></i> Manajemen</span>
            </div>
        </div>
    </div>
    <div class="login-footer">© {{ date('Y') }} Budget Control System. All rights reserved.</div>
</div>

<script>
function fillLogin(username) {
    document.getElementById('username').value = username;
    document.getElementById('password').value = 'password123';
}
</script>
</body>
</html>
