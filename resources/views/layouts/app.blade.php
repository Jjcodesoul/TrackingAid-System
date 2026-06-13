<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TrackingAid - Disaster Logistics System</title>

    {{-- FONTS --}}
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Plus+Jakarta+Sans:wght@500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <style>
        :root {
            --sidebar-bg: #1a202c;
            --accent: #2ecc71;
            --page-bg: #f7fafc;
            --text-dark: #1a202c;
            --text-muted: #64748b;
            --border: #e2e8f0;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }

        body {
            font-family: 'Inter', system-ui, sans-serif;
            font-size: 15px;
            line-height: 1.6;
            background: var(--page-bg);
            color: var(--text-dark);
            -webkit-font-smoothing: antialiased;
        }

        /* HEADINGS use Plus Jakarta Sans */
        h1, h2, h3, h4, h5, h6 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-weight: 700;
            line-height: 1.3;
            color: var(--text-dark);
        }

        /* LAYOUT */
        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: 260px;
            background: var(--sidebar-bg);
            color: white;
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            left: 0; top: 0;
            overflow-y: auto;
            z-index: 100;
        }

        .brand {
            padding: 0 24px 20px;
            border-bottom: 1px solid rgba(255,255,255,0.08);
            margin-bottom: 20px;
        }
        .brand h1 {
            font-family: 'Plus Jakarta Sans', sans-serif;
            font-size: 19px;
            font-weight: 800;
            color: white;
            letter-spacing: -.3px;
            margin-bottom: 3px;
        }
        .brand span {
            font-size: 11.5px;
            color: rgba(255,255,255,0.45);
            letter-spacing: .02em;
        }

        /* NAV */
        .nav-menu { list-style: none; padding: 0 12px; }
        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 11px;
            padding: 11px 14px;
            color: rgba(255,255,255,0.6);
            text-decoration: none;
            font-size: 13.5px;
            font-weight: 500;
            border-radius: 10px;
            margin-bottom: 2px;
            transition: all 0.15s ease;
            letter-spacing: .01em;
        }
        .nav-menu a i {
            width: 18px;
            text-align: center;
            font-size: 14px;
            color: rgba(255,255,255,0.4);
            flex-shrink: 0;
        }
        .nav-menu a:hover {
            background: rgba(255,255,255,0.07);
            color: white;
        }
        .nav-menu a:hover i { color: rgba(255,255,255,0.8); }
        .nav-menu a.active {
            background: rgba(46,204,113,0.15);
            color: #2ecc71;
            font-weight: 600;
        }
        .nav-menu a.active i { color: #2ecc71; }

        /* MAIN */
        .main { flex: 1; margin-left: 260px; display: flex; flex-direction: column; }

        .topbar {
            background: white;
            padding: 14px 32px;
            box-shadow: 0 1px 0 var(--border);
            display: flex;
            justify-content: space-between;
            align-items: center;
            position: sticky;
            top: 0;
            z-index: 50;
        }
        .topbar-left {
            font-size: 14px;
            font-weight: 500;
            color: var(--text-muted);
        }
        .topbar-left strong {
            color: var(--text-dark);
            font-weight: 600;
        }
        .topbar-right { display: flex; align-items: center; gap: 16px; }

        .user-profile { display: flex; align-items: center; gap: 10px; }
        .user-email { font-size: 13px; color: var(--text-muted); }
        .user-avatar {
            width: 34px; height: 34px;
            border-radius: 50%;
            background: linear-gradient(135deg, #2ecc71, #27ae60);
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 13px;
            font-weight: 700;
            font-family: 'Plus Jakarta Sans', sans-serif;
            flex-shrink: 0;
        }

        .logout-btn {
            background: none; border: none;
            color: var(--text-muted);
            cursor: pointer;
            font-size: 13px;
            font-weight: 500;
            font-family: 'Inter', sans-serif;
            transition: color 0.15s;
            padding: 0;
        }
        .logout-btn:hover { color: #e53e3e; }

        .page-content { flex: 1; padding: 28px 32px; overflow-y: auto; }

        /* CARDS */
        .card-ui {
            background: white;
            border: 1px solid var(--border);
            border-radius: 14px;
            padding: 22px;
        }

        /* GRID */
        .grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 18px;
        }

        /* BUTTONS */
        .btn-main {
            background: #2ecc71;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 9px;
            font-size: 13.5px;
            font-weight: 600;
            font-family: 'Inter', sans-serif;
            cursor: pointer;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
            transition: background 0.15s;
            letter-spacing: .01em;
        }
        .btn-main:hover { background: #27ae60; color: white; }

        .btn-edit {
            background: #EBF8FF;
            color: #2B6CB0;
            padding: 5px 13px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            text-decoration: none;
            transition: background 0.15s;
        }
        .btn-edit:hover { background: #BEE3F8; }

        .btn-danger-custom {
            background: #FEF2F2;
            color: #DC2626;
            border: none;
            padding: 5px 13px;
            border-radius: 7px;
            font-size: 12.5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.15s;
        }
        .btn-danger-custom:hover { background: #FECACA; }

        /* TABLES */
        table { width: 100%; border-collapse: collapse; }
        th {
            padding: 11px 16px;
            text-align: left;
            font-size: 11px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: .06em;
            color: #94a3b8;
            background: #f8fafc;
            border-bottom: 1px solid var(--border);
        }
        td {
            padding: 13px 16px;
            font-size: 13.5px;
            color: var(--text-dark);
            border-bottom: 1px solid #f1f5f9;
            vertical-align: middle;
        }
        tr:last-child td { border-bottom: none; }
        tr:hover td { background: #fafbfc; }

        /* FORMS */
        label {
            display: block;
            font-size: 12.5px;
            font-weight: 600;
            color: #374151;
            margin-bottom: 6px;
            letter-spacing: .01em;
        }
        input, select, textarea {
            width: 100%;
            padding: 9px 13px;
            border: 1px solid var(--border);
            border-radius: 8px;
            font-size: 13.5px;
            font-family: 'Inter', sans-serif;
            color: var(--text-dark);
            background: white;
            outline: none;
            transition: border-color .15s;
            margin-bottom: 14px;
        }
        input:focus, select:focus, textarea:focus {
            border-color: #2ecc71;
            box-shadow: 0 0 0 3px rgba(46,204,113,0.1);
        }

        /* BADGES */
        .badge-success { background: #ECFDF5; color: #059669; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-warning { background: #FFFBEB; color: #D97706; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }
        .badge-danger  { background: #FEF2F2; color: #DC2626; padding: 3px 10px; border-radius: 999px; font-size: 12px; font-weight: 600; }

        /* SCROLLBARS */
        .sidebar::-webkit-scrollbar { width: 5px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.15); border-radius: 3px; }
        .page-content::-webkit-scrollbar { width: 7px; }
        .page-content::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }

        /* RESPONSIVE */
        @media (max-width: 900px) {
            .sidebar { position: static; width: 100%; height: auto; }
            .app-shell { display: block; }
            .main { width: 100%; margin-left: 0; }
            .page-content { padding: 16px; }
            .grid { grid-template-columns: 1fr; }
        }
    </style>
</head>
<body>
<div class="app-shell">
    <aside class="sidebar">
        <div class="brand">
            <h1>TrackingAid</h1>
            <span>Disaster Logistics System</span>
        </div>
        <nav>
            <ul class="nav-menu">
                <li>
                    <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') || request()->routeIs('admin.dashboard') ? 'active' : '' }}">
                        <i class="fa-solid fa-table-cells-large"></i> Dashboard
                    </a>
                </li>
                <li>
                    <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'active' : '' }}">
                        <i class="fa-regular fa-clipboard"></i> Requests
                    </a>
                </li>
                <li>
                    <a href="/inventory" class="{{ request()->is('inventory*') ? 'active' : '' }}">
                        <i class="fa-solid fa-cube"></i> Inventory
                    </a>
                </li>
                @if(Auth::check() && Auth::user()->role === 'admin')
                <li>
                    <a href="/stock-in" class="{{ request()->is('stock-in*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrow-down"></i> Stock In
                    </a>
                </li>
                <li>
                    <a href="{{ route('borrow-release.create') }}" class="{{ request()->routeIs('borrow-release.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i> Borrow / Release
                    </a>
                </li>
                <li>
                    <a href="{{ route('returns.create') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-rotate-left"></i> Return Management
                    </a>
                </li>
                <li>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i> Users & Roles
                    </a>
                </li>
                @endif
            </ul>
        </nav>
    </aside>

    <div class="main">
        <div class="topbar">
            <div class="topbar-left">
                Welcome, <strong>{{ auth()->user()->name ?? 'Guest' }}</strong>
            </div>
            <div class="topbar-right">
                <div class="user-profile">
                    <span class="user-email">{{ auth()->user()->email ?? '' }}</span>
                    <div class="user-avatar">
                        {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                    </div>
                </div>
                @if(Auth::check())
                    <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                        @csrf
                        <button type="submit" class="logout-btn">
                            <i class="fa-solid fa-sign-out-alt"></i> Logout
                        </button>
                    </form>
                @endif
            </div>
        </div>

        <div class="page-content">
            {{ $slot ?? '' }}
            @yield('content')
        </div>
    </div>
</div>
</body>
</html>
