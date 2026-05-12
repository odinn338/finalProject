<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Debt Mate') - نظام إدارة الديون</title>

    {{-- Font Awesome --}}
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    {{-- Google Fonts Cairo --}}
    <link href="https://fonts.googleapis.com/css2?family=Cairo:wght@300;400;600;700;900&display=swap" rel="stylesheet">
    {{-- Chart.js --}}
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

    <style>
        /* ════════════════════════════════════════
           CSS Variables - هوية بصرية DebtMate
        ════════════════════════════════════════ */
        :root {
            --primary:      #6c63ff;
            --primary-dark: #5a52d5;
            --primary-light:#8b85ff;
            --success:      #2ecc71;
            --danger:       #e74c3c;
            --warning:      #f39c12;
            --info:         #3498db;
            --dark:         #1a1a2e;
            --dark-2:       #16213e;
            --dark-3:       #0f3460;
            --sidebar-bg:   #1a1a2e;
            --card-bg:      #16213e;
            --card-border:  rgba(108,99,255,0.15);
            --text-primary: #eaeaea;
            --text-muted:   #9ca3af;
            --text-light:   #ffffff;
            --border-color: rgba(255,255,255,0.07);
            --shadow:       0 4px 24px rgba(0,0,0,0.3);
            --radius:       14px;
            --radius-sm:    8px;
            --sidebar-w:    260px;
            --transition:   0.3s ease;
        }

        /* ════════════════════════════════════════
           Reset & Base
        ════════════════════════════════════════ */
        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
        html { font-size: 15px; }

        body {
            font-family: 'Cairo', sans-serif;
            background: var(--dark);
            color: var(--text-primary);
            display: flex;
            min-height: 100vh;
            overflow-x: hidden;
        }

        a { text-decoration: none; color: inherit; }
        ul { list-style: none; }

        /* ════════════════════════════════════════
           Sidebar - القائمة الجانبية
        ════════════════════════════════════════ */
        .sidebar {
            width: var(--sidebar-w);
            min-height: 100vh;
            background: var(--sidebar-bg);
            border-left: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            position: fixed;
            top: 0;
            right: 0;
            z-index: 100;
            transition: transform var(--transition);
        }

        .logo {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 28px 24px 20px;
            border-bottom: 1px solid var(--border-color);
        }

        .logo i {
            font-size: 28px;
            color: var(--primary);
            background: rgba(108,99,255,0.15);
            padding: 10px;
            border-radius: 10px;
        }

        .logo h2 {
            font-size: 1.3rem;
            font-weight: 700;
            color: var(--text-light);
            letter-spacing: 0.5px;
        }

        .nav-menu {
            flex: 1;
            padding: 20px 12px;
            display: flex;
            flex-direction: column;
            gap: 4px;
        }

        .nav-item {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 12px 16px;
            border-radius: var(--radius-sm);
            color: var(--text-muted);
            font-size: 0.95rem;
            font-weight: 600;
            transition: all var(--transition);
            position: relative;
        }

        .nav-item:hover {
            background: rgba(108,99,255,0.1);
            color: var(--text-primary);
        }

        .nav-item.active {
            background: linear-gradient(135deg, rgba(108,99,255,0.25), rgba(108,99,255,0.1));
            color: var(--primary-light);
            border-right: 3px solid var(--primary);
        }

        .nav-item i {
            width: 20px;
            text-align: center;
            font-size: 1rem;
        }

        .nav-badge {
            margin-right: auto;
            background: var(--danger);
            color: white;
            font-size: 0.7rem;
            padding: 2px 7px;
            border-radius: 20px;
            font-weight: 700;
        }

        .user-profile {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 16px 20px;
            border-top: 1px solid var(--border-color);
            cursor: pointer;
            transition: background var(--transition);
        }

        .user-profile:hover { background: rgba(255,255,255,0.04); }

        .user-avatar {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.2rem;
            color: white;
            flex-shrink: 0;
        }

        .user-info h4 {
            font-size: 0.9rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .user-info p {
            font-size: 0.78rem;
            color: var(--text-muted);
        }

        /* ════════════════════════════════════════
           Main Content
        ════════════════════════════════════════ */
        .main-content {
            margin-right: var(--sidebar-w);
            flex: 1;
            padding: 0;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
        }

        /* ════════════════════════════════════════
           Dashboard Header
        ════════════════════════════════════════ */
        .dashboard-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 24px 32px;
            background: var(--dark-2);
            border-bottom: 1px solid var(--border-color);
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .welcome-section h1 {
            font-size: 1.4rem;
            font-weight: 700;
            color: var(--text-light);
        }

        .welcome-section p {
            font-size: 0.88rem;
            color: var(--text-muted);
            margin-top: 3px;
        }

        .header-actions {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* ════════════════════════════════════════
           Buttons
        ════════════════════════════════════════ */
        .btn-primary {
            background: linear-gradient(135deg, var(--primary), var(--primary-dark));
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 20px rgba(108,99,255,0.4);
            color: white;
        }

        .btn-secondary {
            background: rgba(255,255,255,0.07);
            color: var(--text-primary);
            border: 1px solid var(--border-color);
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            font-weight: 600;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: rgba(255,255,255,0.12);
            color: var(--text-light);
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #27ae60);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-success:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(46,204,113,0.35); color: white; }

        .btn-danger {
            background: linear-gradient(135deg, var(--danger), #c0392b);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-danger:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(231,76,60,0.35); color: white; }

        .btn-warning {
            background: linear-gradient(135deg, var(--warning), #e67e22);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: var(--radius-sm);
            font-family: 'Cairo', sans-serif;
            font-size: 0.9rem;
            font-weight: 700;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all var(--transition);
            text-decoration: none;
        }

        .btn-warning:hover { transform: translateY(-2px); color: white; }

        .btn-sm { padding: 6px 14px; font-size: 0.82rem; }
        .btn-icon { padding: 8px; border-radius: var(--radius-sm); }

        .btn-notification {
            background: rgba(255,255,255,0.07);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            width: 40px;
            height: 40px;
            border-radius: var(--radius-sm);
            cursor: pointer;
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            transition: all var(--transition);
        }

        .btn-notification:hover { background: rgba(108,99,255,0.15); color: var(--primary); }

        .notification-dot {
            position: absolute;
            top: 8px;
            left: 8px;
            width: 8px;
            height: 8px;
            background: var(--danger);
            border-radius: 50%;
            border: 2px solid var(--dark-2);
        }

        /* ════════════════════════════════════════
           Cards
        ════════════════════════════════════════ */
        .card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 24px;
        }

        .card-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 20px;
        }

        .card-header h3 {
            font-size: 1.05rem;
            font-weight: 700;
            color: var(--text-light);
        }

        /* ════════════════════════════════════════
           Statistics Cards
        ════════════════════════════════════════ */
        .statistics-section {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 20px;
            padding: 28px 32px 0;
        }

        .stat-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 22px;
            display: flex;
            align-items: center;
            gap: 16px;
            position: relative;
            overflow: hidden;
            transition: transform var(--transition), box-shadow var(--transition);
        }

        .stat-card::before {
            content: '';
            position: absolute;
            top: 0;
            right: 0;
            width: 4px;
            height: 100%;
            border-radius: 0 var(--radius) var(--radius) 0;
        }

        .stat-card.total-debt::before  { background: var(--primary); }
        .stat-card.paid::before         { background: var(--success); }
        .stat-card.remaining::before    { background: var(--warning); }
        .stat-card.overdue::before      { background: var(--danger); }

        .stat-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow);
        }

        .stat-icon {
            width: 52px;
            height: 52px;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1.3rem;
            flex-shrink: 0;
        }

        .stat-card.total-debt .stat-icon  { background: rgba(108,99,255,0.18); color: var(--primary); }
        .stat-card.paid .stat-icon         { background: rgba(46,204,113,0.18); color: var(--success); }
        .stat-card.remaining .stat-icon    { background: rgba(243,156,18,0.18);  color: var(--warning); }
        .stat-card.overdue .stat-icon      { background: rgba(231,76,60,0.18);   color: var(--danger); }

        .stat-content { flex: 1; }

        .stat-content h3 {
            font-size: 0.82rem;
            font-weight: 600;
            color: var(--text-muted);
            margin-bottom: 6px;
        }

        .stat-number {
            font-size: 1.45rem;
            font-weight: 900;
            color: var(--text-light);
            line-height: 1;
        }

        .stat-label {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
            display: block;
        }

        .stat-trend {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 3px;
            font-size: 0.78rem;
            font-weight: 700;
        }

        .stat-trend.up    { color: var(--success); }
        .stat-trend.down  { color: var(--danger); }
        .stat-trend.alert { color: var(--warning); }

        /* ════════════════════════════════════════
           Alerts / Flash Messages
        ════════════════════════════════════════ */
        .alert {
            padding: 14px 20px;
            border-radius: var(--radius-sm);
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
        }

        .alert-success { background: rgba(46,204,113,0.15); border: 1px solid rgba(46,204,113,0.3); color: #2ecc71; }
        .alert-error,
        .alert-danger   { background: rgba(231,76,60,0.15);  border: 1px solid rgba(231,76,60,0.3);  color: #e74c3c; }
        .alert-warning  { background: rgba(243,156,18,0.15); border: 1px solid rgba(243,156,18,0.3); color: #f39c12; }
        .alert-info     { background: rgba(52,152,219,0.15); border: 1px solid rgba(52,152,219,0.3); color: #3498db; }

        /* ════════════════════════════════════════
           Forms
        ════════════════════════════════════════ */
        .form-group {
            margin-bottom: 20px;
        }

        .form-label {
            display: block;
            font-size: 0.88rem;
            font-weight: 700;
            color: var(--text-muted);
            margin-bottom: 8px;
        }

        .form-control {
            width: 100%;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-sm);
            padding: 11px 16px;
            font-family: 'Cairo', sans-serif;
            font-size: 0.92rem;
            color: var(--text-primary);
            transition: border-color var(--transition), box-shadow var(--transition);
            outline: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(108,99,255,0.15);
            background: rgba(108,99,255,0.06);
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-control.is-invalid {
            border-color: var(--danger);
            box-shadow: 0 0 0 3px rgba(231,76,60,0.1);
        }

        .invalid-feedback {
            font-size: 0.82rem;
            color: var(--danger);
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        select.form-control { cursor: pointer; }

        /* ════════════════════════════════════════
           Tables
        ════════════════════════════════════════ */
        .table-wrapper {
            overflow-x: auto;
            border-radius: var(--radius);
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        thead th {
            background: rgba(108,99,255,0.08);
            padding: 13px 16px;
            font-size: 0.82rem;
            font-weight: 700;
            color: var(--text-muted);
            text-align: right;
            border-bottom: 1px solid var(--border-color);
            white-space: nowrap;
        }

        tbody td {
            padding: 13px 16px;
            font-size: 0.9rem;
            color: var(--text-primary);
            border-bottom: 1px solid var(--border-color);
            vertical-align: middle;
        }

        tbody tr:hover { background: rgba(255,255,255,0.03); }
        tbody tr:last-child td { border-bottom: none; }

        /* ════════════════════════════════════════
           Badges
        ════════════════════════════════════════ */
        .badge {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 0.78rem;
            font-weight: 700;
            gap: 5px;
        }

        .badge-primary   { background: rgba(108,99,255,0.2); color: var(--primary-light); }
        .badge-success   { background: rgba(46,204,113,0.2);  color: #2ecc71; }
        .badge-danger    { background: rgba(231,76,60,0.2);   color: #e74c3c; }
        .badge-warning   { background: rgba(243,156,18,0.2);  color: #f39c12; }
        .badge-info      { background: rgba(52,152,219,0.2);  color: #3498db; }
        .badge-secondary { background: rgba(255,255,255,0.1); color: var(--text-muted); }

        /* ════════════════════════════════════════
           Progress Bar
        ════════════════════════════════════════ */
        .progress {
            background: rgba(255,255,255,0.08);
            border-radius: 20px;
            height: 8px;
            overflow: hidden;
        }

        .progress-bar {
            height: 100%;
            border-radius: 20px;
            background: linear-gradient(90deg, var(--primary), var(--primary-light));
            transition: width 1s ease;
        }

        .progress-bar.success { background: linear-gradient(90deg, var(--success), #27ae60); }
        .progress-bar.danger  { background: linear-gradient(90deg, var(--danger), #c0392b); }

        /* ════════════════════════════════════════
           Page Content
        ════════════════════════════════════════ */
        .page-content {
            padding: 28px 32px;
            flex: 1;
        }

        .section-title {
            font-size: 1.2rem;
            font-weight: 900;
            color: var(--text-light);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--primary);
        }

        /* ════════════════════════════════════════
           Pagination
        ════════════════════════════════════════ */
        .pagination {
            display: flex;
            justify-content: center;
            gap: 6px;
            padding: 20px 0;
        }

        .pagination a, .pagination span {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 36px;
            height: 36px;
            border-radius: var(--radius-sm);
            font-size: 0.88rem;
            font-weight: 600;
            background: rgba(255,255,255,0.05);
            border: 1px solid var(--border-color);
            color: var(--text-muted);
            transition: all var(--transition);
        }

        .pagination a:hover { background: rgba(108,99,255,0.15); color: var(--primary); border-color: var(--primary); }

        .pagination .active span {
            background: var(--primary);
            color: white;
            border-color: var(--primary);
        }

        /* ════════════════════════════════════════
           Charts section
        ════════════════════════════════════════ */
        .charts-section {
            display: grid;
            grid-template-columns: 1fr 2fr;
            gap: 20px;
            padding: 20px 32px 0;
        }

        .chart-card {
            background: var(--card-bg);
            border: 1px solid var(--card-border);
            border-radius: var(--radius);
            padding: 22px;
        }

        .chart-container {
            position: relative;
            margin-top: 16px;
        }

        .chart-center-text {
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            text-align: center;
            pointer-events: none;
        }

        .chart-center-text h2 { font-size: 1.8rem; font-weight: 900; color: var(--text-light); }
        .chart-center-text p  { font-size: 0.82rem; color: var(--text-muted); }

        /* ════════════════════════════════════════
           Responsive
        ════════════════════════════════════════ */
        @media (max-width: 1100px) {
            .statistics-section { grid-template-columns: repeat(2, 1fr); }
            .charts-section { grid-template-columns: 1fr; }
        }

        @media (max-width: 768px) {
            :root { --sidebar-w: 0; }
            .sidebar { transform: translateX(100%); }
            .sidebar.open { transform: translateX(0); }
            .main-content { margin-right: 0; }
            .statistics-section { grid-template-columns: 1fr; padding: 16px; }
            .page-content { padding: 16px; }
            .dashboard-header { padding: 16px; }
        }
    </style>

    @stack('styles')
</head>
<body>

{{-- ════════════════════════════════════════
     Sidebar - القائمة الجانبية
════════════════════════════════════════ --}}
<aside class="sidebar" id="sidebar">
    <div class="logo">
        <i class="fas fa-coins"></i>
        <h2>Debt Mate</h2>
    </div>

    <nav class="nav-menu">
        <a href="{{ route('dashboard') }}"
           class="nav-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="fas fa-home"></i>
            <span>الرئيسية</span>
        </a>

        @if(auth()->user()->isAdmin())
            <a href="{{ route('admin.requests.index') }}"
               class="nav-item {{ request()->routeIs('admin.requests.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>طلبات الديون</span>
                @php $pending = \App\Models\DebtRequest::where('status','pending')->count() @endphp
                @if($pending > 0)
                    <span class="nav-badge">{{ $pending }}</span>
                @endif
            </a>
            <a href="{{ route('admin.rescheduling.index') }}"
               class="nav-item {{ request()->routeIs('admin.rescheduling.*') ? 'active' : '' }}">
                <i class="fas fa-sync-alt"></i>
                <span>إعادة الجدولة</span>
                @php $pendingR = \App\Models\ReschedulingRequest::where('status','pending')->count() @endphp
                @if($pendingR > 0)
                    <span class="nav-badge">{{ $pendingR }}</span>
                @endif
            </a>
        @else
            <a href="{{ route('debts.index') }}"
               class="nav-item {{ request()->routeIs('debts.*') ? 'active' : '' }}">
                <i class="fas fa-file-invoice-dollar"></i>
                <span>ديوني</span>
            </a>
            <a href="{{ route('debt-requests.index') }}"
               class="nav-item {{ request()->routeIs('debt-requests.*') ? 'active' : '' }}">
                <i class="fas fa-plus-circle"></i>
                <span>طلباتي</span>
            </a>
        @endif

        <a href="{{ route('reports.index') }}"
           class="nav-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="fas fa-chart-line"></i>
            <span>التقارير</span>
        </a>
    </nav>

    <a href="#" class="user-profile">
        <div class="user-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="user-info">
            <h4>{{ auth()->user()->name }}</h4>
            <p>{{ auth()->user()->role_arabic }}</p>
        </div>
    </a>
</aside>

{{-- ════════════════════════════════════════
     Main Content
════════════════════════════════════════ --}}
<main class="main-content">

    {{-- Header --}}
    <header class="dashboard-header">
        <div class="welcome-section">
            <h1>@yield('page-title', 'لوحة التحكم')</h1>
            <p>@yield('page-subtitle', 'مرحباً بك في نظام Debt Mate لإدارة الديون')</p>
        </div>
        <div class="header-actions">
            <button class="btn-notification">
                <i class="fas fa-bell"></i>
                @php
                    $hasNotif = \App\Models\Installment::where('user_id', auth()->id())
                        ->where('status','overdue')->exists();
                @endphp
                @if($hasNotif)
                    <span class="notification-dot"></span>
                @endif
            </button>

            @if(!auth()->user()->isAdmin())
                <a href="{{ route('debt-requests.create') }}" class="btn-primary">
                    <i class="fas fa-plus"></i>
                    طلب دين جديد
                </a>
            @endif

            <form action="{{ route('logout') }}" method="POST" style="display:inline;">
                @csrf
                <button type="submit" class="btn-secondary" style="height:40px;">
                    <i class="fas fa-sign-out-alt"></i>
                    خروج
                </button>
            </form>
        </div>
    </header>

    {{-- Flash Messages --}}
    <div style="padding: 0 32px;">
        @if(session('success'))
            <div class="alert alert-success" style="margin-top: 20px;">
                <i class="fas fa-check-circle"></i>
                {{ session('success') }}
            </div>
        @endif
        @if(session('error'))
            <div class="alert alert-error" style="margin-top: 20px;">
                <i class="fas fa-exclamation-circle"></i>
                {{ session('error') }}
            </div>
        @endif
        @if($errors->any() && !request()->routeIs('dashboard'))
            <div class="alert alert-danger" style="margin-top: 20px; flex-direction: column; align-items: flex-start;">
                <div style="display:flex; align-items:center; gap:8px; font-weight:700;">
                    <i class="fas fa-exclamation-triangle"></i>
                    يرجى تصحيح الأخطاء التالية:
                </div>
                <ul style="margin-top:8px; padding-right:20px; list-style:disc;">
                    @foreach($errors->all() as $error)
                        <li style="margin-top:4px; font-size:0.88rem;">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    @yield('content')

</main>

<script>
    // إغلاق الإشعارات تلقائياً بعد 5 ثوانٍ
    setTimeout(() => {
        document.querySelectorAll('.alert').forEach(el => {
            el.style.transition = 'opacity 0.5s';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 500);
        });
    }, 5000);
</script>

@stack('scripts')
</body>
</html>
