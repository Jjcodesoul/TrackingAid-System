<?php
    $unreadCount = isset($notifications) ? $notifications->where('read', false)->count() : 0;
    $requestsCount = isset($requestsCount) ? $requestsCount : 0;
?>

<!DOCTYPE html>
<html lang="en" class="h-full bg-slate-900">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="<?php echo e(csrf_token()); ?>">
    <title>TrackingAid - Disaster Logistics System</title>

    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">

    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
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
                        <?php
                            $dashboardUrl = auth()->user()->role === 'admin' ? '/admin/dashboard' : '/dashboard';
                            $isActive = request()->is('admin/dashboard*') || (request()->is('dashboard*') && !request()->is('admin*'));
                        ?>
                        <a href="<?php echo e($dashboardUrl); ?>" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e($isActive ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-chart-pie w-5 text-center text-base"></i>
                            <span>Dashboard</span>
                        </a>
                    </li>

                    <li>
                        <a href="/inventory" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('inventory') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-box w-5 text-center text-base"></i>
                            <span>Inventory</span>
                        </a>
                    </li>

                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li>
                        <a href="/inventory/create" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('inventory/create*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-plus w-5 text-center text-base"></i>
                            <span>Add Item (SKU)</span>
                        </a>
                    </li>

                    <li>
                        <a href="/stock-in" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('stock-in*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-arrow-down-1-9 w-5 text-center text-base"></i>
                            <span>Stock In</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li>
                        <a href="/requests" class="flex items-center justify-between px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('requests*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <div class="flex items-center gap-3">
                                <i class="fa-solid fa-clipboard-list w-5 text-center text-base"></i>
                                <span>Requests</span>
                            </div>
                            <?php if($requestsCount > 0): ?>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-none bg-amber-500 text-slate-950"><?php echo e($requestsCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <li>
                        <a href="/borrow-release" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('borrow-release*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-handshake w-5 text-center text-base"></i>
                            <span>Borrow / Release</span>
                        </a>
                    </li>

                    <li>
                        <a href="/returns" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('returns*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-rotate-left w-5 text-center text-base"></i>
                            <span>Return Management</span>
                        </a>
                    </li>

                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li>
                        <a href="/reports" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('reports*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-chart-line w-5 text-center text-base"></i>
                            <span>Reports</span>
                        </a>
                    </li>
                    <?php endif; ?>

                    <li>
                        <a href="/notifications" class="flex items-center justify-between px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('notifications*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <div class="flex items-center gap-3">
                                <i class="fa-regular fa-bell w-5 text-center text-base"></i>
                                <span>Notifications</span>
                            </div>
                            <?php if($unreadCount > 0): ?>
                                <span class="px-2 py-0.5 text-xs font-bold rounded-none bg-rose-500 text-white"><?php echo e($unreadCount); ?></span>
                            <?php endif; ?>
                        </a>
                    </li>

                    <?php if(auth()->user()->role === 'admin'): ?>
                    <li>
                        <a href="/users" class="flex items-center gap-3 px-4 py-2 rounded-none text-sm font-medium transition-all <?php echo e(request()->is('users*') ? 'bg-slate-800 text-emerald-400 font-semibold border-l-4 border-emerald-500' : 'text-slate-400 hover:bg-slate-800/50 hover:text-slate-200'); ?>">
                            <i class="fa-solid fa-user-gear w-5 text-center text-base"></i>
                            <span>Users & Roles</span>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
        </aside>

        <div class="flex flex-col flex-1 min-w-0 overflow-hidden bg-slate-50">
            
            <header class="flex items-center justify-between bg-white border-b border-slate-200 px-8 h-16 shrink-0 box-border">
                
                <div class="flex items-center gap-2 text-sm text-slate-500 font-medium">
                    <span>TrackingAid</span>
                    <span class="text-slate-300 font-normal">/</span>
                    <span class="text-slate-800 font-semibold capitalize"><?php echo e(Request::segment(2) ?? (Request::segment(1) ?? 'Dashboard')); ?></span>
                </div>

                <div class="flex items-center gap-6">
                    
                    <button class="text-slate-400 hover:text-slate-600 relative border-none bg-transparent cursor-pointer p-1.5 flex items-center justify-center">
                        <i class="fa-regular fa-bell text-lg"></i>
                        <?php if($unreadCount > 0): ?>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-rose-500 rounded-full"></span>
                        <?php endif; ?>
                    </button>
                    
                    <div class="flex items-center gap-3 min-w-0 border-l border-slate-200 pl-6">
                        <div class="w-9 h-9 bg-emerald-600 text-white font-bold text-sm flex items-center justify-center rounded-none shrink-0 shadow-sm">
                            <?php echo e(strtoupper(substr(auth()->user()->name ?? 'US', 0, 2))); ?>

                        </div>
                        <div class="flex flex-col min-w-0 leading-none">
                            <span class="text-sm font-semibold text-slate-800 truncate mb-1"><?php echo e(auth()->user()->name ?? 'User Account'); ?></span>
                            <span class="text-xs text-slate-400 truncate capitalize"><?php echo e(auth()->user()->role ?? 'User'); ?></span>
                        </div>
                    </div>
                    
                    <?php if(Auth::check()): ?>
                        <form method="POST" action="<?php echo e(route('logout')); ?>" class="m-0 p-0 flex items-center">
                            <?php echo csrf_field(); ?>
                            <button type="submit" class="text-slate-400 hover:text-rose-500 transition-colors border-none bg-transparent cursor-pointer p-1">
                                <i class="fa-solid fa-arrow-right-from-bracket text-base"></i>
                            </button>
                        </form>
                    <?php endif; ?>
                </div>
            </header>

            <main class="flex-1 overflow-y-auto p-8 box-border">
                <?php if(isset($slot)): ?>
                    <?php echo e($slot); ?>

                <?php else: ?>
                    <?php echo $__env->yieldContent('content'); ?>
                <?php endif; ?>
            </main>
        </div>

    </div>

</body>
</html><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/layouts/app.blade.php ENDPATH**/ ?>