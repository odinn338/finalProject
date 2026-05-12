<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>تسجيل الدخول - Debt Mate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff;
            --primary-dark: #5a52d5;
            --dark: #1a1a2e;
            --dark-2: #16213e;
            --card-bg: #16213e;
            --border: rgba(255,255,255,0.07);
            --text: #eaeaea;
            --muted: #9ca3af;
            --danger: #e74c3c;
            --success: #2ecc71;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        /* خلفية متحركة */
        body::before {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(108,99,255,0.12) 0%, transparent 70%);
            top: -100px; right: -100px;
            border-radius: 50%;
            animation: pulse 8s ease-in-out infinite;
        }

        body::after {
            content: '';
            position: fixed;
            width: 400px; height: 400px;
            background: radial-gradient(circle, rgba(46,204,113,0.08) 0%, transparent 70%);
            bottom: -80px; left: -80px;
            border-radius: 50%;
            animation: pulse 10s ease-in-out infinite reverse;
        }

        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.15); opacity: 0.7; }
        }

        .auth-container {
            width: 100%;
            max-width: 440px;
            padding: 20px;
            position: relative;
            z-index: 10;
        }

        .auth-card {
            background: var(--card-bg);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: 20px;
            padding: 40px 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }

        .auth-logo {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 12px;
            margin-bottom: 32px;
        }

        .logo-icon {
            width: 64px;
            height: 64px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.8rem;
            color: white;
            box-shadow: 0 8px 24px rgba(108,99,255,0.4);
        }

        .auth-logo h1 {
            font-size: 1.6rem;
            font-weight: 900;
            color: #fff;
        }

        .auth-logo p {
            font-size: 0.88rem;
            color: var(--muted);
            text-align: center;
        }

        .form-group { margin-bottom: 18px; }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--muted);
            margin-bottom: 8px;
        }

        .input-wrapper {
            position: relative;
        }

        .input-icon {
            position: absolute;
            right: 14px;
            top: 50%;
            transform: translateY(-50%);
            color: var(--muted);
            font-size: 0.95rem;
        }

        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 10px;
            padding: 12px 44px 12px 16px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.92rem;
            color: var(--text);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s, background 0.3s;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15);
            background: rgba(108,99,255,0.06);
        }

        .form-control.is-invalid { border-color: var(--danger); }

        .invalid-feedback {
            font-size: 0.8rem;
            color: var(--danger);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .form-check {
            display: flex;
            align-items: center;
            gap: 8px;
            cursor: pointer;
        }

        .form-check input[type="checkbox"] {
            width: 16px;
            height: 16px;
            accent-color: var(--primary);
            cursor: pointer;
        }

        .form-check label {
            font-size: 0.88rem;
            color: var(--muted);
            cursor: pointer;
        }

        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            border-radius: 10px;
            padding: 14px;
            font-family: 'Cairo', sans-serif;
            font-size: 1rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
            transition: all 0.3s;
            margin-top: 8px;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 28px rgba(108,99,255,0.45);
        }

        .auth-footer {
            text-align: center;
            margin-top: 24px;
            font-size: 0.88rem;
            color: var(--muted);
        }

        .auth-footer a {
            color: var(--primary);
            font-weight: 700;
            text-decoration: none;
        }

        .auth-footer a:hover { text-decoration: underline; }

        .alert {
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 0.88rem;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 20px;
        }

        .alert-danger  { background: rgba(231,76,60,0.15);  border: 1px solid rgba(231,76,60,0.3);  color: var(--danger); }
        .alert-success { background: rgba(46,204,113,0.15); border: 1px solid rgba(46,204,113,0.3); color: var(--success); }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">

        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-coins"></i></div>
            <h1>Debt Mate</h1>
            <p>نظام إدارة الديون الذكي</p>
        </div>

        @if(session('success'))
            <div class="alert alert-success">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif

        @if($errors->has('email'))
            <div class="alert alert-danger">
                <i class="fas fa-exclamation-circle"></i>
                {{ $errors->first('email') }}
            </div>
        @endif

        <form action="{{ route('login.post') }}" method="POST">
            @csrf

            <div class="form-group">
                <label class="form-label" for="email">البريد الإلكتروني</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}"
                        placeholder="example@mail.com"
                        autocomplete="email"
                        required
                    >
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="password">كلمة المرور</label>
                <div class="input-wrapper">
                    <i class="fas fa-lock input-icon"></i>
                    <input
                        type="password"
                        id="password"
                        name="password"
                        class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                        placeholder="••••••••"
                        autocomplete="current-password"
                        required
                    >
                </div>
                @error('password')
                    <div class="invalid-feedback">
                        <i class="fas fa-exclamation-circle"></i> {{ $message }}
                    </div>
                @enderror
            </div>

            <div class="form-group">
                <label class="form-check">
                    <input type="checkbox" name="remember" id="remember" {{ old('remember') ? 'checked' : '' }}>
                    <label for="remember">تذكرني</label>
                </label>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-sign-in-alt"></i>
                تسجيل الدخول
            </button>
        </form>

        <div class="auth-footer">
            ليس لديك حساب؟
            <a href="{{ route('register') }}">إنشاء حساب جديد</a>
        </div>
    </div>
</div>

</body>
</html>
