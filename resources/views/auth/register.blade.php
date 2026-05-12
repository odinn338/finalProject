<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>إنشاء حساب - Debt Mate</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    <style>
        :root {
            --primary: #6c63ff; --primary-dark: #5a52d5;
            --dark: #1a1a2e; --card-bg: #16213e;
            --border: rgba(255,255,255,0.07);
            --text: #eaeaea; --muted: #9ca3af;
            --danger: #e74c3c; --success: #2ecc71;
        }
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Cairo', sans-serif;
            background: var(--dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 30px 20px;
            position: relative;
        }
        body::before {
            content: '';
            position: fixed;
            width: 500px; height: 500px;
            background: radial-gradient(circle, rgba(108,99,255,0.12) 0%, transparent 70%);
            top: -100px; right: -100px;
            border-radius: 50%;
        }
        .auth-container { width: 100%; max-width: 520px; position: relative; z-index: 10; }
        .auth-card {
            background: var(--card-bg);
            border: 1px solid rgba(108,99,255,0.2);
            border-radius: 20px;
            padding: 36px;
            box-shadow: 0 20px 60px rgba(0,0,0,0.4);
        }
        .auth-logo {
            display: flex; flex-direction: column;
            align-items: center; gap: 10px; margin-bottom: 28px;
        }
        .logo-icon {
            width: 58px; height: 58px;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
            font-size: 1.6rem; color: white;
            box-shadow: 0 8px 20px rgba(108,99,255,0.4);
        }
        .auth-logo h1 { font-size: 1.5rem; font-weight: 900; color: #fff; }
        .auth-logo p  { font-size: 0.85rem; color: var(--muted); }
        .form-row { display: grid; grid-template-columns: 1fr 1fr; gap: 14px; }
        .form-group { margin-bottom: 16px; }
        .form-label { display: block; font-size: 0.86rem; font-weight: 700; color: var(--muted); margin-bottom: 7px; }
        .input-wrapper { position: relative; }
        .input-icon { position: absolute; right: 13px; top: 50%; transform: translateY(-50%); color: var(--muted); font-size: 0.9rem; }
        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border);
            border-radius: 9px;
            padding: 11px 40px 11px 14px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem; color: var(--text);
            outline: none;
            transition: border-color 0.3s, box-shadow 0.3s;
        }
        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15);
            background: rgba(108,99,255,0.05);
        }
        .form-control.is-invalid { border-color: var(--danger); }
        .invalid-feedback { font-size: 0.78rem; color: var(--danger); margin-top: 4px; display: flex; align-items: center; gap: 4px; }
        .btn-submit {
            width: 100%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white; border: none; border-radius: 10px;
            padding: 13px; font-family: 'Cairo', sans-serif;
            font-size: 0.98rem; font-weight: 700; cursor: pointer;
            display: flex; align-items: center; justify-content: center; gap: 8px;
            transition: all 0.3s; margin-top: 6px;
        }
        .btn-submit:hover { transform: translateY(-2px); box-shadow: 0 10px 28px rgba(108,99,255,0.45); }
        .auth-footer { text-align: center; margin-top: 20px; font-size: 0.86rem; color: var(--muted); }
        .auth-footer a { color: var(--primary); font-weight: 700; text-decoration: none; }
        .alert { padding: 12px 16px; border-radius: 8px; font-size: 0.86rem; font-weight: 600; display: flex; align-items: flex-start; gap: 8px; margin-bottom: 18px; flex-direction: column; }
        .alert-danger { background: rgba(231,76,60,0.12); border: 1px solid rgba(231,76,60,0.3); color: var(--danger); }
        .alert ul { padding-right: 18px; list-style: disc; }
        .alert ul li { margin-top: 3px; }
        .divider { height: 1px; background: var(--border); margin: 18px 0; }
        .section-label { font-size: 0.78rem; font-weight: 700; color: var(--muted); text-transform: uppercase; letter-spacing: 1px; margin-bottom: 14px; }
    </style>
</head>
<body>

<div class="auth-container">
    <div class="auth-card">
        <div class="auth-logo">
            <div class="logo-icon"><i class="fas fa-coins"></i></div>
            <h1>إنشاء حساب جديد</h1>
            <p>انضم إلى نظام Debt Mate لإدارة ديونك بذكاء</p>
        </div>

        @if($errors->any())
            <div class="alert alert-danger">
                <div style="display:flex;align-items:center;gap:6px;font-weight:700;">
                    <i class="fas fa-exclamation-triangle"></i> يرجى تصحيح الأخطاء:
                </div>
                <ul>
                    @foreach($errors->all() as $err)
                        <li>{{ $err }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form action="{{ route('register.post') }}" method="POST">
            @csrf

            <p class="section-label">المعلومات الأساسية</p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="name">الاسم الكامل *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-user input-icon"></i>
                        <input type="text" id="name" name="name"
                            class="form-control {{ $errors->has('name') ? 'is-invalid' : '' }}"
                            value="{{ old('name') }}" placeholder="أحمد محمد" required>
                    </div>
                    @error('name')<div class="invalid-feedback"><i class="fas fa-times-circle"></i>{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="national_id">رقم الهوية</label>
                    <div class="input-wrapper">
                        <i class="fas fa-id-card input-icon"></i>
                        <input type="text" id="national_id" name="national_id"
                            class="form-control {{ $errors->has('national_id') ? 'is-invalid' : '' }}"
                            value="{{ old('national_id') }}" placeholder="29XXXXXXXXX">
                    </div>
                    @error('national_id')<div class="invalid-feedback"><i class="fas fa-times-circle"></i>{{ $message }}</div>@enderror
                </div>
            </div>

            <div class="form-group">
                <label class="form-label" for="email">البريد الإلكتروني *</label>
                <div class="input-wrapper">
                    <i class="fas fa-envelope input-icon"></i>
                    <input type="email" id="email" name="email"
                        class="form-control {{ $errors->has('email') ? 'is-invalid' : '' }}"
                        value="{{ old('email') }}" placeholder="example@mail.com" required>
                </div>
                @error('email')<div class="invalid-feedback"><i class="fas fa-times-circle"></i>{{ $message }}</div>@enderror
            </div>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="phone">رقم الهاتف</label>
                    <div class="input-wrapper">
                        <i class="fas fa-phone input-icon"></i>
                        <input type="text" id="phone" name="phone"
                            class="form-control" value="{{ old('phone') }}" placeholder="01XXXXXXXXX">
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" for="address">العنوان</label>
                    <div class="input-wrapper">
                        <i class="fas fa-map-marker-alt input-icon"></i>
                        <input type="text" id="address" name="address"
                            class="form-control" value="{{ old('address') }}" placeholder="القاهرة، مصر">
                    </div>
                </div>
            </div>

            <div class="divider"></div>
            <p class="section-label">كلمة المرور</p>

            <div class="form-row">
                <div class="form-group">
                    <label class="form-label" for="password">كلمة المرور *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password" name="password"
                            class="form-control {{ $errors->has('password') ? 'is-invalid' : '' }}"
                            placeholder="8 أحرف وأرقام" required>
                    </div>
                    @error('password')<div class="invalid-feedback"><i class="fas fa-times-circle"></i>{{ $message }}</div>@enderror
                </div>

                <div class="form-group">
                    <label class="form-label" for="password_confirmation">تأكيد المرور *</label>
                    <div class="input-wrapper">
                        <i class="fas fa-lock input-icon"></i>
                        <input type="password" id="password_confirmation" name="password_confirmation"
                            class="form-control" placeholder="أعد كتابة كلمة المرور" required>
                    </div>
                </div>
            </div>

            <button type="submit" class="btn-submit">
                <i class="fas fa-user-plus"></i>
                إنشاء الحساب
            </button>
        </form>

        <div class="auth-footer">
            لديك حساب بالفعل؟ <a href="{{ route('login') }}">تسجيل الدخول</a>
        </div>
    </div>
</div>

</body>
</html>
