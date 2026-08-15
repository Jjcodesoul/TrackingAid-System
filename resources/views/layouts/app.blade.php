@php
    $requestsCount = isset($requestsCount) ? $requestsCount : 0;
@endphp

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>TrackingAid - Disaster Logistics System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.2/css/bootstrap.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            corePlugins: {
                preflight: false,   /* prevent conflict with Bootstrap resets */
            },
            theme: {
                extend: {
                    colors: {
                        slate: {
                            900: '#1a202c',
                            800: '#2d3748',
                            950: '#0f172a'
                        },
                        emerald: {
                            500: '#2ecc71',
                            600: '#27ae60'
                        }
                    }
                }
            }
        }
    </script>

    <style>
        /* ── Shared custom components used across views ── */
        .panel {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
            padding: 24px;
        }

        .card-ui {
            background: #ffffff;
            border: 1px solid #edf2f7;
            border-radius: 12px;
            box-shadow: 0 1px 3px rgba(0,0,0,0.04);
        }

        .btn-main {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #1a202c;
            color: #fff;
            font-size: 13px;
            font-weight: 600;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease;
        }
        .btn-main:hover {
            background: #2d3748;
            color: #fff;
        }

        .btn-soft {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 10px 20px;
            background: #f1f5f9;
            color: #374151;
            font-size: 13px;
            font-weight: 600;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            cursor: pointer;
            text-decoration: none;
            transition: background 0.15s ease;
        }
        .btn-soft:hover {
            background: #e2e8f0;
        }

        .page-title {
            font-size: 22px;
            font-weight: 700;
            color: #1a202c;
            margin: 0;
        }
        .page-subtitle {
            font-size: 13px;
            color: #64748B;
            margin: 4px 0 0;
        }

        .note {
            background: #FFFBEB;
            border: 1px solid #FDE68A;
            color: #92400E;
            padding: 12px 16px;
            border-radius: 8px;
            font-size: 13px;
        }
    </style>
</head>
<body class="h-full font-sans antialiased bg-slate-50 text-slate-900 m-0 p-0">

    <div class="flex h-screen w-screen overflow-hidden">
        
        <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col shrink-0 border-r border-slate-800">
            
            <div class="flex items-center gap-3 px-6 py-5 border-b border-slate-800">
                <div class="w-9 h-9 bg-emerald-500 flex items-center justify-center text-white text-lg rounded-none shrink-0 shadow-sm">
                    <i class="fa-solid fa-cubes"></i>
                </div>
                <div class="flex flex-col min-w-0">
                    <h1 class="text-base font-bold text-white tracking-wide m-0 p-0 leading-tight truncate">TrackingAid</h1>
                    <span class="text-xs text-slate-400 font-medium mt-0.5 m-0 p-0 truncate">× ResqOperation</span>
                </div>
            </div>

            <nav class="flex-1 overflow-y-auto px-4 py-4">
                <ul class="space-y-1.5 list-none p-0 m-0">
                    
                    <li>
                        @php
                            $dashboardUrl = auth()->user()->role === 'admin' ? '/admin/dashboard' : '/dashboard';
                            $isActive = request()->is('admin/dashboard*') || (request()->is('dashboard*') && !request()->is('admin*'));
                        @endphp
                        <a href="{{ $dashboardUrl }}" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ $isActive ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="/inventory" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('inventory') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-box w-5 text-center text-base"></i>
                            <span>Inventory</span>
                        </a>
                    </li>

                    @if(auth()->user()->role === 'admin')

                    <li>
                        <a href="/stock-in" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('stock-in*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-arrow-down-1-9 w-5 text-center text-base"></i>
                            <span>Stock In</span>
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="/requests" class="flex items-center justify-between px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('requests*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-clipboard-list w-5 text-center text-base"></i>
                                <span>Requests</span>
                            </div>
                            @if($requestsCount > 0)
                                <span class="px-2 py-0.5 text-xs font-bold rounded-none bg-amber-500 text-slate-950">{{ $requestsCount }}</span>
                            @endif
                        </a>
                    </li>

                    <li>
                        <a href="/borrow-release" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('borrow-release*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-handshake w-5 text-center text-base"></i>
                            <span>Borrow / Release</span>
                        </a>
                    </li>

                    <li>
                        <a href="/returns" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('returns*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-rotate-left w-5 text-center text-base"></i>
                            <span>Return Management</span>
                        </a>
                    </li>

                    @if(auth()->user()->role === 'admin')
                    <li>
                        <a href="/reports" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('reports*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-chart-line w-5 text-center text-base"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    @endif

                    <li>
                        <a href="/notifications" class="flex items-center justify-between px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('notifications*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <div class="flex items-center gap-3">
                                <i class="fa-regular fa-bell w-5 text-center text-base"></i>
                                <span>Notifications</span>
                            </div>
                            @if($unreadCount > 0)
                                <span class="px-2 py-0.5 text-xs font-bold rounded-none bg-rose-500 text-white">{{ $unreadCount }}</span>
                            @endif
                        </a>
                    </li>

                    @if(auth()->user()->role === 'admin')
                    <li>
                        <a href="/users" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all {{ request()->is('users*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200' }}">
                            <i class="fa-solid fa-user-gear w-5 text-center text-base"></i>
                            <span>Users & Roles</span>
                        </a>
                    </li>
                    @endif
                </ul>
            </nav>
        </aside>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-slate-50">
            
            <header class="flex items-center justify-between bg-white border-b border-slate-200 px-8 h-16 shrink-0 box-border">
                
                <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                    <span>TrackingAid</span>
                    <span class="text-slate-300 font-normal">/</span>
                    <span class="text-slate-800 font-semibold capitalize">{{ Request::segment(2) ?? (Request::segment(1) ?? 'Dashboard') }}</span>
                </div>

                <div class="flex items-center gap-6">
                    
                    <a href="{{ route('notifications.index') }}" class="text-slate-400 hover:text-slate-600 relative border-none bg-transparent cursor-pointer p-1.5 flex items-center justify-center" title="Notifications">
                        <i class="fa-regular fa-bell text-lg"></i>
                        @if($unreadCount > 0)
                            <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full"></span>
                        @endif
                    </a>
                    
                    <div class="flex items-center gap-3 min-w-0 border-l border-slate-200 pl-6">
                        <div class="w-9 h-9 bg-emerald-600 text-white font-bold text-sm flex items-center justify-center rounded-none shrink-0 shadow-sm">
                            {{ strtoupper(substr(auth()->user()->name ?? 'US', 0, 2)) }}
                        </div>
                        <div class="flex flex-col min-w-0 leading-none">
                            <span class="text-sm font-semibold text-slate-800 truncate mb-1">{{ auth()->user()->name ?? 'User Account' }}</span>
                            <span class="text-xs text-slate-400 truncate capitalize">{{ auth()->user()->role ?? 'User' }}</span>
                        </div>
                    </div>
                    
                    @if(Auth::check())
                        <form method="POST" action="{{ route('logout') }}" class="m-0 p-0 flex items-center">
                            @csrf
                            <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors border-none bg-transparent cursor-pointer p-1">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            </button>
                        </form>
                    @endif
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 box-border">
                @isset($slot)
                    {{ $slot }}
                @else
                    @yield('content')
                @endisset
            </main>
        </div>

    </div>

    @stack('scripts')

    {{-- ── Real-time notification polling ── --}}
    <script>
        (function() {
            let previousCount = {{ $unreadCount }};
            let latestNotificationKey = null;
            let notifiedHashes = new Set();

            // Request permission for browser notifications on first visit
            if ('Notification' in window && Notification.permission === 'default') {
                Notification.requestPermission().then(function() {
                    latestNotificationKey = null;
                    poll();
                });
            }

            function updateBadge(count) {
                // Sidebar count badge
                const sidebarBadge = document.querySelector('a[href="/notifications"] .bg-rose-500');
                const sidebarLink = document.querySelector('a[href="/notifications"]');

                if (count > 0) {
                    if (sidebarBadge) {
                        sidebarBadge.textContent = count;
                    } else if (sidebarLink) {
                        const badge = document.createElement('span');
                        badge.className = 'px-2 py-0.5 text-xs font-bold rounded-none bg-rose-500 text-white';
                        badge.textContent = count;
                        sidebarLink.appendChild(badge);
                    }
                } else {
                    if (sidebarBadge) sidebarBadge.remove();
                }

                // Header bell red dot
                const bellLink = document.querySelector('a[title="Notifications"]');
                const existingDot = bellLink?.querySelector('.bell-dot');

                if (count > 0) {
                    if (!existingDot && bellLink) {
                        const dot = document.createElement('span');
                        dot.className = 'bell-dot absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full';
                        bellLink.appendChild(dot);
                    }
                } else {
                    if (existingDot) existingDot.remove();
                }
            }

            function showDesktopAlert(notification) {
                if (!('Notification' in window) || Notification.permission !== 'granted') return;
                if (!notification) return;

                // Avoid duplicate notifications for the same item
                const hash = notification.id;
                if (notifiedHashes.has(hash)) return;
                notifiedHashes.add(hash);

                const title = notification.title || 'New Notification';
                const body = notification.message || 'You have a new alert.';

                try {
                    const n = new Notification(title, {
                        body: body,
                        icon: '/favicon.ico',
                        tag: 'trackingaid-notification',
                    });

                    n.onclick = function () {
                        window.focus();
                        window.location.href = '{{ route('notifications.index') }}';
                        this.close();
                    };
                } catch (e) {
                    // Silently fail if notification API throws
                }
            }

            function poll() {
                fetch('{{ route('notifications.unread-json') }}', {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                })
                .then(function (r) { return r.json(); })
                .then(function (data) {
                    const count = data.unreadCount || 0;
                    const latest = data.latest;
                    const latestKey = latest ? latest.id + ':' + latest.updated_at : null;

                    // Alert when the latest notification changes, even if the total count stays the same.
                    if (latest && latestKey !== latestNotificationKey) {
                        showDesktopAlert(latest);
                    }

                    previousCount = count;
                    latestNotificationKey = latestKey;
                    updateBadge(count);
                })
                .catch(function () {
                    // Silently ignore polling errors (network down, etc.)
                });
            }

            // Poll every 30 seconds
            setInterval(poll, 30000);

            // Also poll immediately on page load so the badge is fresh
            poll();
        })();
    </script>
</body>
</html>