<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Dashboard') — Budget Control System</title>
    <meta name="description" content="Sistem Kontrol Budget Operasional — Monitor dan kelola anggaran departemen secara real-time.">

    <!-- Google Fonts -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    <!-- Bootstrap 5 -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Bootstrap Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
    <!-- DataTables -->
    <link href="https://cdn.datatables.net/1.13.7/css/dataTables.bootstrap5.min.css" rel="stylesheet">
    <link href="https://cdn.datatables.net/buttons/2.4.2/css/buttons.bootstrap5.min.css" rel="stylesheet">

    <style>
        :root {
            --bg-dark: #0d1117;
            --bg-card: #161b22;
            --bg-sidebar: #010409;
            --border-color: #30363d;
            --text-primary: #e6edf3;
            --text-secondary: #8b949e;
            --accent-blue: #58a6ff;
            --accent-green: #3fb950;
            --accent-red: #f85149;
            --accent-orange: #f0883e;
            --accent-purple: #bc8cff;
            --sidebar-width: 260px;
            --header-height: 60px;
            --transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
        }

        * { box-sizing: border-box; margin: 0; padding: 0; }
        body {
            font-family: 'Inter', sans-serif;
            background: var(--bg-dark);
            color: var(--text-primary);
            min-height: 100vh;
            overflow-x: hidden;
        }

        /* ---- SIDEBAR ---- */
        #sidebar {
            position: fixed;
            top: 0; left: 0;
            width: var(--sidebar-width);
            height: 100vh;
            background: var(--bg-sidebar);
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: var(--transition);
            overflow-y: auto;
            overflow-x: hidden;
        }
        #sidebar.collapsed { width: 64px; }
        #sidebar.collapsed .sidebar-label,
        #sidebar.collapsed .sidebar-brand-text,
        #sidebar.collapsed .sidebar-section-title { opacity: 0; width: 0; overflow: hidden; }

        .sidebar-brand {
            padding: 18px 20px;
            display: flex;
            align-items: center;
            gap: 12px;
            border-bottom: 1px solid var(--border-color);
            text-decoration: none;
            min-height: var(--header-height);
        }
        .sidebar-brand-icon {
            width: 36px; height: 36px;
            background: linear-gradient(135deg, #1f6feb, #388bfd);
            border-radius: 10px;
            display: flex; align-items: center; justify-content: center;
            font-size: 18px; color: white; flex-shrink: 0;
        }
        .sidebar-brand-text { font-size: 14px; font-weight: 700; color: var(--text-primary); line-height: 1.3; }
        .sidebar-brand-sub  { font-size: 10px; color: var(--text-secondary); font-weight: 400; }

        .sidebar-nav { padding: 12px 0; flex: 1; }
        .sidebar-section-title {
            font-size: 10px; font-weight: 600; letter-spacing: 1.2px;
            color: var(--text-secondary); padding: 16px 20px 6px;
            text-transform: uppercase; white-space: nowrap;
        }
        .sidebar-item {
            display: flex; align-items: center; gap: 12px;
            padding: 9px 20px; color: var(--text-secondary);
            text-decoration: none; font-size: 13.5px; font-weight: 500;
            transition: var(--transition); border-left: 3px solid transparent;
            white-space: nowrap; position: relative;
        }
        .sidebar-item:hover { color: var(--text-primary); background: rgba(56,139,253,.1); }
        .sidebar-item.active { color: var(--accent-blue); background: rgba(31,111,235,.15); border-left-color: var(--accent-blue); }
        .sidebar-item .nav-icon { font-size: 17px; flex-shrink: 0; width: 20px; text-align: center; }
        .sidebar-label { transition: var(--transition); }

        /* ---- HEADER ---- */
        #header {
            position: fixed; top: 0;
            left: var(--sidebar-width); right: 0;
            height: var(--header-height);
            background: rgba(22, 27, 34, 0.9);
            backdrop-filter: blur(12px);
            border-bottom: 1px solid var(--border-color);
            display: flex; align-items: center;
            padding: 0 24px; gap: 16px;
            z-index: 999; transition: var(--transition);
        }
        #header.expanded { left: 64px; }
        .header-toggle { background: none; border: none; color: var(--text-secondary); font-size: 20px; cursor: pointer; padding: 4px; }
        .header-breadcrumb { font-size: 14px; color: var(--text-secondary); flex: 1; }
        .header-breadcrumb span { color: var(--text-primary); font-weight: 600; }

        .header-user {
            display: flex; align-items: center; gap: 10px;
            padding: 6px 14px; background: var(--bg-card);
            border: 1px solid var(--border-color); border-radius: 8px;
            cursor: pointer; text-decoration: none; color: var(--text-primary);
        }
        .header-avatar {
            width: 30px; height: 30px; border-radius: 50%;
            background: linear-gradient(135deg, #1f6feb, #388bfd);
            display: flex; align-items: center; justify-content: center;
            font-size: 13px; font-weight: 700; color: white;
        }
        .header-user-info { font-size: 12px; line-height: 1.4; }
        .header-user-name { font-weight: 600; }
        .header-user-role { color: var(--text-secondary); font-size: 11px; }

        /* ---- MAIN CONTENT ---- */
        #main-content {
            margin-left: var(--sidebar-width);
            margin-top: var(--header-height);
            padding: 28px 28px;
            transition: var(--transition);
            min-height: calc(100vh - var(--header-height));
        }
        #main-content.expanded { margin-left: 64px; }

        /* ---- CARDS ---- */
        .card-dark {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 24px;
        }
        .card-dark .card-title { font-size: 15px; font-weight: 600; margin-bottom: 20px; color: var(--text-primary); }

        .stat-card {
            background: var(--bg-card);
            border: 1px solid var(--border-color);
            border-radius: 12px; padding: 20px 24px;
            display: flex; align-items: center; gap: 16px;
            transition: var(--transition); position: relative; overflow: hidden;
        }
        .stat-card:hover { border-color: rgba(56, 139, 253, 0.4); transform: translateY(-2px); }
        .stat-card::before {
            content: ''; position: absolute; top: 0; left: 0;
            width: 4px; height: 100%; background: var(--card-accent, var(--accent-blue));
        }
        .stat-icon {
            width: 52px; height: 52px; border-radius: 12px;
            display: flex; align-items: center; justify-content: center;
            font-size: 24px; flex-shrink: 0;
            background: var(--icon-bg, rgba(31,111,235,.15));
            color: var(--icon-color, var(--accent-blue));
        }
        .stat-label { font-size: 12px; color: var(--text-secondary); margin-bottom: 4px; font-weight: 500; }
        .stat-value { font-size: 22px; font-weight: 700; color: var(--text-primary); line-height: 1; }
        .stat-sub { font-size: 11px; color: var(--text-secondary); margin-top: 4px; }

        /* ---- BADGES ---- */
        .badge-over  { background: rgba(248,81,73,.15); color: #f85149; border: 1px solid rgba(248,81,73,.3); padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-ok    { background: rgba(63,185,80,.15); color: #3fb950; border: 1px solid rgba(63,185,80,.3); padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }
        .badge-warn  { background: rgba(240,136,62,.15); color: #f0883e; border: 1px solid rgba(240,136,62,.3); padding: 3px 9px; border-radius: 20px; font-size: 11px; font-weight: 600; }

        /* ---- PROGRESS BAR ---- */
        .budget-bar { height: 8px; background: rgba(255,255,255,.08); border-radius: 4px; overflow: hidden; }
        .budget-bar-fill {
            height: 100%; border-radius: 4px; transition: width 1s ease;
            background: linear-gradient(90deg, var(--accent-blue), #388bfd);
        }
        .budget-bar-fill.over { background: linear-gradient(90deg, #f85149, #ff7b72); }
        .budget-bar-fill.warn { background: linear-gradient(90deg, #f0883e, #ffa657); }

        /* ---- TABLES ---- */
        .table-dark-custom { color: var(--text-primary); width: 100%; }
        .table-dark-custom thead th {
            background: rgba(255,255,255,.04); color: var(--text-secondary);
            font-size: 11px; font-weight: 600; letter-spacing: 0.8px;
            text-transform: uppercase; border-bottom: 1px solid var(--border-color);
            padding: 12px 16px; white-space: nowrap;
        }
        .table-dark-custom tbody td {
            padding: 12px 16px; border-bottom: 1px solid rgba(48,54,61,.5);
            font-size: 13.5px; vertical-align: middle;
        }
        .table-dark-custom tbody tr:hover td { background: rgba(56,139,253,.04); }
        .table-dark-custom tbody tr:last-child td { border-bottom: none; }

        /* DataTables override */
        .dataTables_wrapper .dataTables_filter input,
        .dataTables_wrapper .dataTables_length select {
            background: var(--bg-dark) !important; color: var(--text-primary) !important;
            border: 1px solid var(--border-color) !important; border-radius: 8px !important;
            padding: 6px 12px !important; font-size: 13px;
        }
        .dataTables_wrapper .dataTables_info,
        .dataTables_wrapper .dataTables_paginate { color: var(--text-secondary) !important; font-size: 12px; }
        .dataTables_wrapper .paginate_button { color: var(--text-secondary) !important; border-radius: 6px !important; }
        .dataTables_wrapper .paginate_button.current { background: var(--accent-blue) !important; color: white !important; border: none !important; }
        .dataTables_wrapper .paginate_button:hover { background: rgba(56,139,253,.1) !important; color: var(--accent-blue) !important; }
        div.dt-buttons { margin-bottom: 0 !important; }
        .dt-button { background: var(--bg-dark) !important; color: var(--text-secondary) !important; border: 1px solid var(--border-color) !important; border-radius: 8px !important; padding: 6px 14px !important; font-size: 12px !important; margin-right: 4px !important; transition: var(--transition) !important; }
        .dt-button:hover { background: rgba(56,139,253,.1) !important; color: var(--accent-blue) !important; border-color: rgba(56,139,253,.4) !important; }

        /* ---- FORMS ---- */
        .form-control-dark, .form-select-dark {
            background: var(--bg-dark) !important; color: var(--text-primary) !important;
            border: 1px solid var(--border-color) !important; border-radius: 8px !important;
        }
        .form-control-dark:focus, .form-select-dark:focus {
            border-color: var(--accent-blue) !important; box-shadow: 0 0 0 3px rgba(88,166,255,.15) !important;
        }
        .form-label-dark { font-size: 12px; font-weight: 600; color: var(--text-secondary); letter-spacing: 0.5px; text-transform: uppercase; margin-bottom: 6px; }

        /* ---- BUTTONS ---- */
        .btn-primary-dark { background: #1f6feb; border: none; color: white; border-radius: 8px; padding: 8px 18px; font-size: 13px; font-weight: 600; transition: var(--transition); }
        .btn-primary-dark:hover { background: #388bfd; transform: translateY(-1px); }
        .btn-danger-dark { background: rgba(248,81,73,.15); border: 1px solid rgba(248,81,73,.4); color: #f85149; border-radius: 8px; padding: 6px 14px; font-size: 12px; font-weight: 600; transition: var(--transition); }
        .btn-danger-dark:hover { background: #f85149; color: white; }
        .btn-outline-dark { background: transparent; border: 1px solid var(--border-color); color: var(--text-secondary); border-radius: 8px; padding: 7px 16px; font-size: 13px; font-weight: 500; transition: var(--transition); }
        .btn-outline-dark:hover { border-color: var(--accent-blue); color: var(--accent-blue); background: rgba(88,166,255,.05); }

        /* ---- ALERTS ---- */
        .alert-success-dark { background: rgba(63,185,80,.1); border: 1px solid rgba(63,185,80,.3); color: #3fb950; border-radius: 10px; padding: 12px 18px; font-size: 13px; }
        .alert-danger-dark  { background: rgba(248,81,73,.1); border: 1px solid rgba(248,81,73,.3); color: #f85149; border-radius: 10px; padding: 12px 18px; font-size: 13px; }

        /* ---- MODAL ---- */
        .modal-dark .modal-content {
            background: var(--bg-card); border: 1px solid var(--border-color);
            border-radius: 14px; color: var(--text-primary);
        }
        .modal-dark .modal-header { border-bottom: 1px solid var(--border-color); padding: 18px 24px; }
        .modal-dark .modal-footer { border-top: 1px solid var(--border-color); }
        .modal-dark .btn-close { filter: invert(1) brightness(0.7); }

        /* ---- PAGE TITLE ---- */
        .page-header { margin-bottom: 28px; }
        .page-title { font-size: 22px; font-weight: 700; color: var(--text-primary); }
        .page-subtitle { font-size: 13px; color: var(--text-secondary); margin-top: 4px; }

        /* ---- MOBILE RESPONSIVE ---- */
        @media (max-width: 768px) {
            #sidebar { transform: translateX(-100%); width: 260px !important; z-index: 1050; }
            #sidebar.mobile-open { transform: translateX(0); }
            #header { left: 0 !important; padding: 0 16px; }
            #main-content { margin-left: 0 !important; padding: 16px; }
            .stat-card { flex-direction: column; text-align: center; }
            .sidebar-overlay {
                display: none; position: fixed; inset: 0;
                background: rgba(0,0,0,.6); z-index: 1049;
            }
            .sidebar-overlay.active { display: block; }
        }

        /* ---- SCROLLBAR ---- */
        ::-webkit-scrollbar { width: 6px; height: 6px; }
        ::-webkit-scrollbar-track { background: transparent; }
        ::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 3px; }
        ::-webkit-scrollbar-thumb:hover { background: var(--text-secondary); }

        /* ---- MISC ---- */
        .text-accent-blue   { color: var(--accent-blue); }
        .text-accent-green  { color: var(--accent-green); }
        .text-accent-red    { color: var(--accent-red); }
        .text-accent-orange { color: var(--accent-orange); }
        .text-muted-custom  { color: var(--text-secondary); }
        .border-dark-custom { border-color: var(--border-color) !important; }
        .pct-high { color: var(--accent-green); }
        .pct-warn { color: var(--accent-orange); }
        .pct-over { color: var(--accent-red); font-weight: 700; }
    </style>
    @stack('styles')
</head>
<body>

<!-- Sidebar Overlay for Mobile -->
<div class="sidebar-overlay" id="sidebarOverlay" onclick="closeSidebar()"></div>

<!-- SIDEBAR -->
<nav id="sidebar">
    <a href="{{ route('dashboard') }}" class="sidebar-brand">
        <div class="sidebar-brand-icon"><i class="bi bi-bar-chart-fill"></i></div>
        <div class="sidebar-brand-text">
            Budget Control
            <div class="sidebar-brand-sub">Sistem Kontrol Anggaran</div>
        </div>
    </a>

    <div class="sidebar-nav">
        <div class="sidebar-section-title">Menu Utama</div>
        <a href="{{ route('dashboard') }}" class="sidebar-item {{ request()->routeIs('dashboard') ? 'active' : '' }}">
            <i class="bi bi-speedometer2 nav-icon"></i><span class="sidebar-label">Dashboard</span>
        </a>
        <a href="{{ route('reports.index') }}" class="sidebar-item {{ request()->routeIs('reports.*') ? 'active' : '' }}">
            <i class="bi bi-file-earmark-bar-graph nav-icon"></i><span class="sidebar-label">Laporan</span>
        </a>

        <div class="sidebar-section-title">Transaksi</div>
        <a href="{{ route('budget.plans.index') }}" class="sidebar-item {{ request()->routeIs('budget.plans.*') ? 'active' : '' }}">
            <i class="bi bi-calendar3 nav-icon"></i><span class="sidebar-label">Budget Plan</span>
        </a>
        <a href="{{ route('budget.actuals.index') }}" class="sidebar-item {{ request()->routeIs('budget.actuals.*') ? 'active' : '' }}">
            <i class="bi bi-cart3 nav-icon"></i><span class="sidebar-label">Actual Pengeluaran</span>
        </a>

        @can('master.view')
        <div class="sidebar-section-title">Master Data</div>
        <a href="{{ route('master.departments.index') }}" class="sidebar-item {{ request()->routeIs('master.departments.*') ? 'active' : '' }}">
            <i class="bi bi-building nav-icon"></i><span class="sidebar-label">Departemen</span>
        </a>
        <a href="{{ route('master.items.index') }}" class="sidebar-item {{ request()->routeIs('master.items.*') ? 'active' : '' }}">
            <i class="bi bi-box-seam nav-icon"></i><span class="sidebar-label">Master Item</span>
        </a>
        @endcan

        @can('user.view')
        <div class="sidebar-section-title">Administrasi</div>
        <a href="{{ route('admin.users.index') }}" class="sidebar-item {{ request()->routeIs('admin.users.*') ? 'active' : '' }}">
            <i class="bi bi-people nav-icon"></i><span class="sidebar-label">Manajemen User</span>
        </a>
        <a href="{{ route('admin.roles.index') }}" class="sidebar-item {{ request()->routeIs('admin.roles.*') ? 'active' : '' }}">
            <i class="bi bi-shield-lock nav-icon"></i><span class="sidebar-label">Roles</span>
        </a>
        <a href="{{ route('admin.permissions.index') }}" class="sidebar-item {{ request()->routeIs('admin.permissions.*') ? 'active' : '' }}">
            <i class="bi bi-key nav-icon"></i><span class="sidebar-label">Permissions</span>
        </a>
        @endcan
    </div>
</nav>

<!-- HEADER -->
<header id="header">
    <button class="header-toggle" onclick="toggleSidebar()" id="sidebarToggleBtn">
        <i class="bi bi-list"></i>
    </button>
    <div class="header-breadcrumb">
        @if(request()->routeIs('admin.users.*'))
            Administrasi / <span>User</span>
        @else
            @yield('breadcrumb', '<span>' . config('app.name') . '</span>')
        @endif
    </div>

    <div class="dropdown">
        <div class="header-user dropdown-toggle" data-bs-toggle="dropdown">
            <div class="header-avatar">{{ strtoupper(substr(auth()->user()->name ?? 'U', 0, 1)) }}</div>
            <div class="header-user-info d-none d-sm-block">
                <div class="header-user-name">{{ auth()->user()->name ?? 'User' }}</div>
                <div class="header-user-role">{{ auth()->user()->getRoleNames()->first() ?? 'Role' }}</div>
            </div>
        </div>
        <ul class="dropdown-menu dropdown-menu-end" style="background: var(--bg-card); border-color: var(--border-color); min-width: 180px;">
            <li><h6 class="dropdown-header" style="color: var(--text-secondary); font-size:11px;">{{ auth()->user()->username }}</h6></li>
            <li><hr class="dropdown-divider" style="border-color: var(--border-color);"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item" style="color: #f85149; font-size: 13px;">
                        <i class="bi bi-box-arrow-right me-2"></i>Logout
                    </button>
                </form>
            </li>
        </ul>
    </div>
</header>

<!-- MAIN CONTENT -->
<main id="main-content">
    @if(session('success'))
    <div class="alert-success-dark mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-check-circle-fill"></i> {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="alert-danger-dark mb-4 d-flex align-items-center gap-2">
        <i class="bi bi-exclamation-triangle-fill"></i> {{ session('error') }}
    </div>
    @endif

    @yield('content')
</main>

<!-- Bootstrap 5 JS -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<!-- jQuery -->
<script src="https://code.jquery.com/jquery-3.7.0.min.js"></script>
<!-- DataTables -->
<script src="https://cdn.datatables.net/1.13.7/js/jquery.dataTables.min.js"></script>
<script src="https://cdn.datatables.net/1.13.7/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/dataTables.buttons.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.html5.min.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.2/js/buttons.print.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.2.7/vfs_fonts.js"></script>
<!-- Chart.js -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.1/dist/chart.umd.min.js"></script>

<script>
    // Sidebar Toggle
    let sidebarCollapsed = false;
    function toggleSidebar() {
        if (window.innerWidth <= 768) {
            document.getElementById('sidebar').classList.toggle('mobile-open');
            document.getElementById('sidebarOverlay').classList.toggle('active');
        } else {
            sidebarCollapsed = !sidebarCollapsed;
            document.getElementById('sidebar').classList.toggle('collapsed', sidebarCollapsed);
            document.getElementById('header').classList.toggle('expanded', sidebarCollapsed);
            document.getElementById('main-content').classList.toggle('expanded', sidebarCollapsed);
        }
    }
    function closeSidebar() {
        document.getElementById('sidebar').classList.remove('mobile-open');
        document.getElementById('sidebarOverlay').classList.remove('active');
    }

    // Auto-dismiss alerts
    setTimeout(() => {
        document.querySelectorAll('.alert-success-dark, .alert-danger-dark').forEach(el => {
            el.style.transition = 'opacity 0.4s ease';
            el.style.opacity = '0';
            setTimeout(() => el.remove(), 400);
        });
    }, 5000);

    // Format number as IDR
    function formatRupiah(num) {
        return 'Rp ' + Math.round(num).toLocaleString('id-ID');
    }

    // CSRF token helper for AJAX
    $.ajaxSetup({ headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') } });
</script>
@stack('scripts')
</body>
</html>
