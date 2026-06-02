<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TrackingAid - Dashboard</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 font-sans">

    <div class="flex min-h-screen">
        <aside class="w-64 bg-[#1a202c] text-white hidden lg:block">
            <div class="p-6">
                <h1 class="text-xl font-bold">TrackingAid</h1>
                <p class="text-xs text-gray-400">Disaster Logistics System</p>
            </div>
            <nav class="mt-6">
                <a href="{{ route('dashboard') }}" class="flex items-center px-6 py-3 border-l-4 border-emerald-400 bg-emerald-400/10 text-emerald-400 font-medium">
                    <span class="mr-3">📊</span> Dashboard
                </a>
                <a href="{{ route('users.index') }}" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800 transition">
                    <span class="mr-3">👥</span> Users & Roles
                </a>
                <a href="#" class="flex items-center px-6 py-3 text-gray-400 hover:bg-gray-800 transition">
                    <span class="mr-3">📦</span> Inventory
                </a>
            </nav>
        </aside>

        <main class="flex-1 p-8">
            <header class="mb-8">
                <h2 class="text-2xl font-bold text-gray-900">Dashboard</h2>
                <p class="text-gray-500">Overview of TrackingAid logistics system</p>
            </header>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Total Inventory Stock</p>
                        <h3 class="text-3xl font-bold mt-1 text-gray-900">{{ number_format($stats['total_stock'] ?? 0) }}</h3>
                        <p class="text-xs mt-2 font-medium text-emerald-500">↗ +12.5% from last month</p>
                    </div>
                    <div class="p-3 rounded-lg bg-emerald-50 h-fit">
                        <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Active Requests</p>
                        <h3 class="text-3xl font-bold mt-1 text-gray-900">{{ $stats['active_requests'] ?? 0 }}</h3>
                        <p class="text-xs mt-2 font-medium text-gray-400">From ResqOperation</p>
                    </div>
                    <div class="p-3 rounded-lg bg-blue-50 h-fit">
                        <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path></svg>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm flex justify-between">
                    <div>
                        <p class="text-gray-500 text-sm font-medium">Low Stock Alerts</p>
                        <h3 class="text-3xl font-bold mt-1 text-red-600">{{ $stats['low_stock'] ?? 0 }}</h3>
                        <p class="text-xs mt-2 font-medium text-red-500">Requires attention</p>
                    </div>
                    <div class="p-3 rounded-lg bg-red-50 h-fit">
                        <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    </div>
                </div>

            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mt-8">
                <div class="lg:col-span-2 bg-white p-6 rounded-xl border border-gray-100 shadow-sm min-h-[300px]">
                    <h4 class="font-bold text-gray-900 mb-4">Inventory Trend</h4>
                    <div class="w-full h-48 bg-gray-50 rounded flex items-center justify-center border border-dashed border-gray-300">
                        <p class="text-gray-400 text-sm italic">Chart data loading from model...</p>
                    </div>
                </div>
                <div class="bg-white p-6 rounded-xl border border-gray-100 shadow-sm">
                    <h4 class="font-bold text-gray-900 mb-4">Request Status Overview</h4>
                    <div class="mb-4">
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span>Pending</span>
                            <span>15</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-orange-400 h-2 rounded-full" style="width: 35%"></div>
                        </div>
                    </div>
                    <div class="mb-4">
                        <div class="flex justify-between text-xs font-medium mb-1">
                            <span>Approved</span>
                            <span>42</span>
                        </div>
                        <div class="w-full bg-gray-100 rounded-full h-2">
                            <div class="bg-emerald-400 h-2 rounded-full" style="width: 80%"></div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

</body>
</html>