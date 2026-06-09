<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TrackingAid - Disaster Logistics System</title>
    
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700&display=swap" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <style>
        :root {
            --sidebar-bg: #1a202c;
            --accent: #2ecc71;
            --page-bg: #f7fafc;
            --text-dark: #1b1b18;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }
        html, body { width: 100%; height: 100%; }
        body { font-family: 'Instrument Sans', system-ui, sans-serif; background: var(--page-bg); color: var(--text-dark); }

        /* Unified Layout Structure */
        .app-shell { display: flex; min-height: 100vh; }

        .sidebar {
            width: 280px;
            background: var(--sidebar-bg);
            color: white;
            padding: 24px 0;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            overflow-y: auto;
            z-index: 100;
        }

        .brand { padding: 0 24px 24px; border-bottom: 1px solid rgba(255,255,255,0.1); margin-bottom: 24px; }
        .brand h1 { font-size: 20px; font-weight: 700; margin: 0 0 4px; color: white; }
        .brand span { font-size: 12px; color: rgba(255,255,255,0.6); display: block; }

        .nav-menu { list-style: none; padding: 0; }
        .nav-menu a {
            display: flex;
            align-items: center;
            gap: 12px;
            padding: 14px 24px;
            color: rgba(255,255,255,0.7);
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.2s ease;
            white-space: nowrap;
        }

        .nav-menu a i { width: 20px; text-align: center; font-size: 16px; color: rgba(255,255,255,0.5); }
        .nav-menu a:hover { background: rgba(255,255,255,0.05); color: white; }
        .nav-menu a:hover i { color: white; }
        
        .nav-menu a.active { background: rgba(46,204,113,0.15); color: var(--accent); border-left: 3px solid var(--accent); padding-left: 21px; }
        .nav-menu a.active i { color: var(--accent); }

        .main { flex: 1; margin-left: 280px; display: flex; flex-direction: column; }

        /* Navigation Topbar Styles */
        .topbar {
            background: white;
            padding: 16px 32px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.08);
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 1px solid #e2e8f0;
        }

        .topbar-left { font-weight: 600; color: var(--text-dark); }
        .topbar-right { display: flex; align-items: center; gap: 20px; }

        .user-profile { display: flex; align-items: center; gap: 12px; }
        .user-avatar { width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, var(--accent) 0%, #27ae60 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; flex-shrink: 0; }

        .logout-btn { background: none; border: none; color: #718096; cursor: pointer; font-size: 13px; font-weight: 500; transition: color 0.2s ease; padding: 0; }
        .logout-btn:hover { color: #e74c3c; }

        .page-content { flex: 1; padding: 32px; overflow-y: auto; }

        /* Reusable Dashboard UI Elements */
        .table td { padding: 20px; vertical-align: middle; font-size: 16px; }
        .badge-pill, .badge-success, .badge-warning, .badge-danger { border-radius: 999px; padding: 8px 16px; font-weight: 500; display: inline-block; }
        .badge-success, .status-approved, .status-released { background: #d9f8e8; color: #087a45; }
        .badge-warning, .priority-medium { background: #fff4bf; color: #9a6a00; }
        .badge-danger, .priority-high, .status-rejected { background: #ffe0e4; color: #c01732; }
        .priority-low { background: #dcecff; color: #18529c; }
        .status-pending { background: #f0f2f5; color: #172033; }
        .note { border: 1px solid #f3d96b; background: #fffbe5; color: #9a5b00; border-radius: 18px; padding: 20px; }
        .modal-content { border-radius: 18px; border: none; }

        /* Custom Scrollbars */
        .sidebar::-webkit-scrollbar { width: 6px; }
        .sidebar::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.2); border-radius: 3px; }
        .page-content::-webkit-scrollbar { width: 8px; }
        .page-content::-webkit-scrollbar-thumb { background: #cbd5e0; border-radius: 4px; }

        /* Responsive Layout Breakers */
        @media (max-width: 1100px) {
            .grid { grid-template-columns: 1fr; }
        }

        @media (max-width: 900px) {
            .sidebar { position: static; width: 100%; min-height: auto; height: auto; }
            .app-shell { display: block; }
            .main { width: 100%; margin-left: 0; padding: 0; }
            .topbar { padding: 12px 16px; }
            .page-content { padding: 16px; }
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

            <nav class="nav-menu">
                <a href="{{ route('dashboard') }}" class="{{ request()->routeIs('dashboard') ? 'active' : '' }}">
                    <i class="fa-solid fa-table-cells-large"></i>Dashboard
                </a>
                <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'active' : '' }}">
                    <i class="fa-regular fa-clipboard"></i>Requests
                </a>
                <a href="/inventory" class="{{ request()->is('inventory*') ? 'active' : '' }}">
                    <i class="fa-solid fa-cube"></i>Inventory
                </a>
                
                @if(Auth::check() && Auth::user()->role === 'admin')
                    <a href="/stock-in" class="{{ request()->is('stock-in*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrow-down"></i>Stock In
                    </a>
                    <a href="{{ route('borrow-release.create') }}" class="{{ request()->routeIs('borrow-release.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-arrow-right-arrow-left"></i>Borrow / Release
                    </a>
                    <a href="{{ route('returns.create') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-rotate-left"></i>Return Management
                    </a>
                    <a href="{{ route('users.index') }}" class="{{ request()->routeIs('users.*') ? 'active' : '' }}">
                        <i class="fa-solid fa-users"></i>Users & Roles
                    </a>
                @endif
            </nav>
        </aside>

        <div class="main">
            <div class="topbar">
                <div class="topbar-left">
                    Welcome, <strong>{{ auth()->user()->name ?? 'Guest User' }}</strong>
                </div>
                <div class="topbar-right">
                    <div class="user-profile">
                        <span style="font-size:13px; color:#4a5568;">{{ auth()->user()->email ?? 'guest@trackingaid.org' }}</span>
                        <div class="user-avatar">
                            {{ strtoupper(substr(auth()->user()->name ?? 'G', 0, 1)) }}
                        </div>
                    </div>
                    @if(Auth::check())
                        <form method="POST" action="{{ route('logout') }}" style="margin:0;">
                            @csrf
                            <button type="submit" class="logout-btn"><i class="fa-solid fa-sign-out-alt"></i> Logout</button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="logout-btn" style="text-decoration: none;"><i class="fa-solid fa-sign-in-alt"></i> Login</a>
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