<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'TrackingAid')</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
        :root {
            --sidebar: #0f1a2e;
            --sidebar-muted: #9fb1c8;
            --accent: #16bf86;
            --page: #f4f7fb;
            --line: #dce4ef;
            --text: #06142b;
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            background: var(--page);
            color: var(--text);
            font-family: Arial, Helvetica, sans-serif;
        }

        .app-shell {
            min-height: 100vh;
            display: flex;
        }

        .sidebar {
            width: 320px;
            min-height: 100vh;
            background: var(--sidebar);
            color: #fff;
            display: flex;
            flex-direction: column;
            position: fixed;
            inset: 0 auto 0 0;
        }

        .brand {
            padding: 30px 30px 28px;
            border-bottom: 1px solid rgba(255, 255, 255, .08);
        }

        .brand h1 {
            font-size: 26px;
            margin: 0 0 6px;
            font-weight: 700;
        }

        .brand span {
            color: var(--sidebar-muted);
            font-size: 14px;
        }

        .nav-menu {
            padding: 26px 0;
        }

        .nav-menu a {
            min-height: 56px;
            display: flex;
            align-items: center;
            gap: 16px;
            padding: 0 30px;
            color: #a9bfd9;
            text-decoration: none;
            font-size: 18px;
            font-weight: 600;
        }

        .nav-menu a i {
            width: 24px;
            text-align: center;
            font-size: 20px;
        }

        .nav-menu a:hover,
        .nav-menu a.active {
            background: var(--accent);
            color: #fff;
        }

        .user-box {
            margin-top: auto;
            padding: 28px 30px;
            border-top: 1px solid rgba(255, 255, 255, .08);
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: #12c993;
            display: grid;
            place-items: center;
            font-weight: 700;
        }

        .user-box strong {
            display: block;
            font-size: 16px;
        }

        .user-box span {
            color: var(--sidebar-muted);
            font-size: 14px;
        }

        .main {
            width: calc(100% - 320px);
            margin-left: 320px;
            padding: 38px 40px;
        }

        .page-title {
            font-size: 30px;
            margin: 0 0 12px;
            font-weight: 700;
        }

        .page-subtitle {
            color: #5d708b;
            font-size: 20px;
            margin-bottom: 32px;
        }

        .panel,
        .card-ui {
            background: #fff;
            border: 1px solid var(--line);
            border-radius: 18px;
            box-shadow: 0 2px 6px rgba(15, 26, 46, .10);
            padding: 30px;
        }

        .grid {
            display: grid;
            grid-template-columns: repeat(3, minmax(0, 1fr));
            gap: 15px;
        }

        .form-label {
            font-weight: 700;
            margin-bottom: 10px;
        }

        .form-control,
        .form-select,
        input,
        select {
            min-height: 48px;
            border: 1px solid var(--line);
            border-radius: 12px;
            padding: 10px 14px;
        }

        textarea.form-control {
            min-height: 148px;
            resize: vertical;
        }

        .btn-main {
            background: var(--accent);
            border: 0;
            color: #fff;
            border-radius: 12px;
            min-height: 44px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .btn-main:hover {
            background: #0fae78;
            color: #fff;
        }

        .btn-soft {
            background: #fff;
            border: 2px solid var(--line);
            color: var(--text);
            border-radius: 12px;
            min-height: 44px;
            padding: 10px 18px;
            font-weight: 700;
        }

        .btn-danger-custom {
            background: #e11d48;
            color: white;
            border: none;
            padding: 7px 12px;
            border-radius: 8px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        table th {
            background: var(--sidebar);
            color: white;
            padding: 14px;
        }

        table td {
            padding: 14px;
            border-bottom: 1px solid var(--line);
        }

        .table {
            margin-bottom: 0;
        }

        .table th {
            color: #566986;
            background: transparent;
            font-size: 16px;
            padding: 18px 20px;
            white-space: nowrap;
        }

        .table td {
            padding: 20px;
            vertical-align: middle;
            font-size: 16px;
        }

        .badge-pill,
        .badge-success,
        .badge-warning,
        .badge-danger {
            border-radius: 999px;
            padding: 8px 16px;
            font-weight: 500;
        }

        .badge-success,
        .status-approved,
        .status-released {
            background: #d9f8e8;
            color: #087a45;
        }

        .badge-warning,
        .priority-medium {
            background: #fff4bf;
            color: #9a6a00;
        }

        .badge-danger,
        .priority-high,
        .status-rejected {
            background: #ffe0e4;
            color: #c01732;
        }

        .priority-low {
            background: #dcecff;
            color: #18529c;
        }

        .status-pending {
            background: #f0f2f5;
            color: #172033;
        }

        .note {
            border: 1px solid #f3d96b;
            background: #fffbe5;
            color: #9a5b00;
            border-radius: 18px;
            padding: 20px;
        }

        .modal-content {
            border-radius: 18px;
            border: none;
        }

        @media (max-width: 1100px) {
            .grid {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 900px) {
            .sidebar {
                position: static;
                width: 100%;
                min-height: auto;
            }

            .app-shell {
                display: block;
            }

            .main {
                width: 100%;
                margin-left: 0;
                padding: 24px;
            }
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
            <a href="#" class="{{ request()->is('dashboard') ? 'active' : '' }}"><i class="fa-solid fa-table-cells-large"></i>Dashboard</a>
            <a href="/inventory" class="{{ request()->is('inventory*') ? 'active' : '' }}"><i class="fa-solid fa-cube"></i>Inventory</a>
            <a href="/stock-in" class="{{ request()->is('stock-in*') ? 'active' : '' }}"><i class="fa-solid fa-arrow-down"></i>Stock In</a>
            <a href="{{ route('requests.index') }}" class="{{ request()->routeIs('requests.*') ? 'active' : '' }}"><i class="fa-regular fa-clipboard"></i>Requests</a>
            <a href="{{ route('borrow-release.create') }}" class="{{ request()->routeIs('borrow-release.*') ? 'active' : '' }}"><i class="fa-solid fa-arrow-right-arrow-left"></i>Borrow / Release</a>
            <a href="{{ route('returns.create') }}" class="{{ request()->routeIs('returns.*') ? 'active' : '' }}"><i class="fa-solid fa-rotate-left"></i>Return Management</a>
            <a href="#"><i class="fa-regular fa-file-lines"></i>Reports</a>
            <a href="#"><i class="fa-regular fa-bell"></i>Notifications</a>
            <a href="#"><i class="fa-solid fa-users"></i>Users & Roles</a>
        </nav>

        <div class="user-box">
            <div class="avatar">AD</div>
            <div>
                <strong>Admin User</strong>
                <span>admin@trackingaid.org</span>
            </div>
        </div>
    </aside>

    <main class="main">
        @if(session('success'))
            <div class="alert alert-success">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert alert-danger">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert alert-danger">
                <strong>Please fix the highlighted fields.</strong>
                <ul class="mb-0">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
<script>
function generateSKU() {
    const category = document.getElementById('category')?.value || '';
    let name = document.getElementById('name')?.value || '';
    const unit = document.getElementById('unit_type')?.value || '';
    let size = document.getElementById('size_weight')?.value || '';
    const target = document.getElementById('target_beneficiary')?.value || '';
    let variant = document.getElementById('variant')?.value || '';
    const skuPreview = document.getElementById('skuPreview');
    const skuInput = document.getElementById('sku');

    if (!skuPreview || !skuInput) {
        return;
    }

    name = name.replace(/\s+/g, '-').toUpperCase();
    size = size.replace(/\s+/g, '').toUpperCase();
    variant = variant.replace(/\s+/g, '-').toUpperCase();

    const sku = `${category}-${name}-${unit}-${size}-${target}-${variant}`.replace(/--+/g, '-');

    skuPreview.innerText = sku;
    skuInput.value = sku;
}

document.addEventListener('input', generateSKU);
document.addEventListener('change', generateSKU);
</script>
@stack('scripts')
</body>
</html>