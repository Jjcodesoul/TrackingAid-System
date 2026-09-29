@extends('layouts.app')

@section('content')

<style>
    .inventory-summary {
        display: grid;
        grid-template-columns: repeat(4, minmax(0, 1fr));
        gap: 14px;
        margin-bottom: 20px;
    }

    .inventory-summary-card {
        min-width: 0;
        padding: 16px;
        background: #fff;
        border: 1px solid #e2e8f0;
        border-radius: 12px;
        box-shadow: 0 1px 3px rgba(15, 23, 42, .04);
    }

    .inventory-summary-label { margin: 0; color: #64748b; font-size: 11px; font-weight: 700; letter-spacing: .05em; text-transform: uppercase; }
    .inventory-summary-value { margin: 7px 0 3px; color: #0f172a; font-size: 25px; font-weight: 800; line-height: 1.15; }
    .inventory-summary-hint { margin: 0; color: #64748b; font-size: 12px; }

    @media (max-width: 1100px) { .inventory-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 480px) { .inventory-summary { grid-template-columns: 1fr; } }
</style>

{{-- HEADER --}}
<div class="page-header" style="margin-bottom:8px;">
    <div>
        <h2 class="page-title">Inventory</h2>
        <p class="page-subtitle">All registered items and current stock levels</p>
    </div>

    @if(auth()->user()->role === 'admin')
        <a href="{{ route('inventory.create') }}" class="btn-main"><i class="fa-solid fa-plus"></i> Add Item</a>
    @endif
</div>

<section class="inventory-summary" aria-label="Inventory summary">
    <div class="inventory-summary-card">
        <p class="inventory-summary-label">Registered Items</p>
        <p class="inventory-summary-value">{{ number_format($inventoryStats['totalItems']) }}</p>
        <p class="inventory-summary-hint">Active item records</p>
    </div>
    <div class="inventory-summary-card">
        <p class="inventory-summary-label">On-hand Units</p>
        <p class="inventory-summary-value">{{ number_format($inventoryStats['totalStock']) }}</p>
        <p class="inventory-summary-hint">Across batches, including expired stock</p>
    </div>
    <div class="inventory-summary-card">
        <p class="inventory-summary-label">Stock Alerts</p>
        <p class="inventory-summary-value">{{ number_format($inventoryStats['lowStock'] + $inventoryStats['outOfStock']) }}</p>
        <p class="inventory-summary-hint">{{ number_format($inventoryStats['lowStock']) }} low · {{ number_format($inventoryStats['outOfStock']) }} out of stock</p>
    </div>
    <div class="inventory-summary-card">
        <p class="inventory-summary-label">Expiry Alerts</p>
        <p class="inventory-summary-value">{{ number_format($inventoryStats['expired'] + $inventoryStats['expiring']) }}</p>
        <p class="inventory-summary-hint">{{ number_format($inventoryStats['expired']) }} expired · {{ number_format($inventoryStats['expiring']) }} within 30 days</p>
    </div>
</section>

{{-- ALERTS --}}
@if(session('success'))
    <div class="alert-box alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-box alert-error">{{ session('error') }}</div>
@endif

{{-- TABLE CARD --}}
<div class="card-ui" style="padding:0; overflow:hidden;">

    {{-- SEARCH / FILTER --}}
    <div class="inventory-toolbar" style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
        <div class="inventory-search" style="position:relative; flex:1; max-width:320px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="search" id="searchInput" aria-label="Search inventory items" placeholder="Search items..." oninput="filterTable()"
                style="width:100%; padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <label for="categoryFilter" class="sr-only">Filter by category</label>
        <select id="categoryFilter" onchange="filterTable()" aria-label="Filter by category"
            style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#374151; outline:none; background:#fff;">
            <option value="">All categories</option>
            @foreach($items->keys() as $category)
                <option value="{{ strtoupper($category) }}">{{ ucfirst(strtolower($category)) }}</option>
            @endforeach
        </select>
        <label for="statusFilter" class="sr-only">Filter by stock and expiration status</label>
        <select id="statusFilter" onchange="filterTable()" aria-label="Filter by stock and expiration status"
            style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#374151; outline:none; background:#fff;">
            <option value="">All statuses</option>
            <option value="in-stock">In stock</option>
            <option value="low-stock">Low stock</option>
            <option value="out-of-stock">Out of stock</option>
            <option value="expiring">Expiring within 30 days</option>
            <option value="expired">Expired</option>
        </select>
        <button type="button" onclick="toggleAllCategories()" id="toggleAllBtn" aria-expanded="true"
            style="margin-left:auto; padding:8px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; color:#374151; background:#fff; cursor:pointer; white-space:nowrap;">
            Collapse All
        </button>
    </div>

    {{-- TABLE --}}
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <caption class="sr-only">Inventory items by category, stock, expiry and location</caption>
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">SKU</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Name</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Category</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Stock</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Unit</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Type</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Flags</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Expiration</th>
                    <th scope="col" style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Location</th>
                    @if(auth()->user()->role === 'admin')
                        <th style="padding:12px 16px; text-align:right; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Actions</th>
                    @endif
                </tr>
            </thead>
            <tbody id="inventoryTable">
            @php $perPage = 10; @endphp
            @forelse($items as $category => $categoryItems)

                @php
                    $catColors = [
                        'FOOD'    => ['bg'=>'#F0FFF4','color'=>'#276749'],
                        'MEDICAL' => ['bg'=>'#EBF8FF','color'=>'#2B6CB0'],
                        'RESCUE'  => ['bg'=>'#FFF5F5','color'=>'#C53030'],
                        'RELIEF'  => ['bg'=>'#FFFAF0','color'=>'#C05621'],
                    ];
                    $catUpper = strtoupper($category);
                    $cc = $catColors[$catUpper] ?? ['bg'=>'#F7FAFC','color'=>'#4A5568'];
                    $catSlug = \Illuminate\Support\Str::slug($category);
                    $totalPages = (int) ceil($categoryItems->count() / $perPage);
                @endphp

                {{-- CATEGORY HEADER ROW (CLICKABLE) --}}
        <tr class="category-header-row">
                    <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}"
                        style="padding:10px 16px; background:{{ $cc['bg'] }};">
                        <button type="button" class="category-toggle" onclick="toggleCategory('{{ $catSlug }}')" aria-expanded="true" data-category-name="{{ ucfirst(strtolower($category)) }}" aria-label="Collapse {{ ucfirst(strtolower($category)) }} category" style="display:inline-flex; align-items:center; gap:8px; border:0; padding:0; background:transparent; cursor:pointer; font:inherit; color:inherit; text-align:left;">
                            <i class="fa-solid fa-chevron-down chevron-icon" id="chevron-{{ $catSlug }}" aria-hidden="true" style="font-size:10px; transition:transform .15s ease;"></i>
                            {{ ucfirst(strtolower($category)) }}
                            <span style="font-weight:500; color:#94a3b8; text-transform:none; letter-spacing:normal;">
                                ({{ $categoryItems->count() }} {{ Str::plural('item', $categoryItems->count()) }})
                            </span>
                        </button>
                    </td>
                </tr>

                {{-- ITEMS IN THIS CATEGORY, CHUNKED INTO PAGES OF 10 --}}
                @foreach($categoryItems->chunk($perPage) as $pageIndex => $pageOfItems)
                    @foreach($pageOfItems as $item)
                        @php
                            $nextExpirationDate = $item->next_expiration_date;
                            $stockQuantity = $item->total_stock;
                            $expirationDate = $nextExpirationDate ? \Carbon\Carbon::parse($nextExpirationDate)->startOfDay() : null;
                            $today = now()->startOfDay();
                            $isExpired = $stockQuantity > 0 && $expirationDate && $expirationDate->lt($today);
                            $isExpiring = $stockQuantity > 0 && $expirationDate && $expirationDate->between($today, $today->copy()->addDays(30), true);
                            $statusFilters = [$stockQuantity <= 0 ? 'out-of-stock' : ($stockQuantity < 10 ? 'low-stock' : 'in-stock')];
                            if ($isExpired) $statusFilters[] = 'expired';
                            elseif ($isExpiring) $statusFilters[] = 'expiring';
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9; {{ $pageIndex > 0 ? 'display:none;' : '' }}"
                            class="table-row cat-group-{{ $catSlug }} cat-page-{{ $catSlug }}-{{ $pageIndex }}"
                            data-category="{{ strtoupper($item->category) }}"
                            data-cat-slug="{{ $catSlug }}"
                            data-page="{{ $pageIndex }}"
                            data-status="{{ implode(' ', $statusFilters) }}">

                            <td style="padding:14px 16px;">
                                <span style="font-size:11px; font-weight:600; color:#64748b; font-family:monospace;">{{ $item->sku }}</span>
                            </td>

                            <td style="padding:14px 16px;">
                                <span style="font-weight:600; color:#1a202c; font-size:14px;">{{ $item->name }}</span>
                            </td>

                            <td style="padding:14px 16px;">
                                <span style="background:{{ $cc['bg'] }}; color:{{ $cc['color'] }}; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                                    {{ ucfirst(strtolower($item->category)) }}
                                </span>
                            </td>

                            <td style="padding:14px 16px;">
                                <span style="font-weight:700; font-size:15px; color:{{ $stockQuantity <= 0 ? '#dc2626' : ($stockQuantity < 10 ? '#d97706' : '#1a202c') }};">
                                    {{ number_format($stockQuantity) }}
                                </span>
                            </td>

                            <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                {{ $item->unit_type ?? '—' }}
                            </td>

                            <td style="padding:14px 16px;">
                                @if(strtolower($item->type) === 'consumable')
                                    <span style="background:#EBF8FF; color:#2B6CB0; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">Consumable</span>
                                @else
                                    <span style="background:#FAF5FF; color:#6B46C1; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">Returnable</span>
                                @endif
                            </td>

                            <td style="padding:14px 16px;">
                                @if($stockQuantity <= 0)
                                    <span style="background:#FEF2F2; color:#DC2626; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Out of Stock</span>
                                @elseif($stockQuantity < 10)
                                    <span style="background:#FFFBEB; color:#B45309; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Low Stock</span>
                                @else
                                    <span style="background:#ECFDF5; color:#047857; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">In Stock</span>
                                @endif
                                @if($isExpired)
                                    <span style="display:inline-block; margin:2px 0; background:#FEF2F2; color:#B91C1C; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700;">Expired</span>
                                @elseif($isExpiring)
                                    <span style="background:#FFFBEB; color:#D97706; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Expiring</span>
                                @endif
                            </td>

                            <td style="padding:14px 16px; font-size:13px; color:{{ $isExpired ? '#b91c1c' : '#4a5568' }}; font-weight:{{ $isExpired ? '600' : '400' }};">
                                {{ $expirationDate?->format('M d, Y') ?? '—' }}
                            </td>

                            <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                {{ $item->storage_location ?? '—' }}
                            </td>

                            @if(auth()->user()->role === 'admin')
                                <td style="padding:14px 16px; text-align:right;">
                                    <div style="display:flex; gap:8px; justify-content:flex-end; align-items:center;">
                                        <a href="{{ route('inventory.edit', $item->id) }}"
                                            style="padding:5px 12px; background:#EBF8FF; color:#2B6CB0; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">
                                            Edit
                                        </a>
                                        <form method="POST" action="{{ route('inventory.delete', $item->id) }}"
                                            onsubmit="return confirm('Delete this item?')" style="margin:0; padding:0; display:inline-block;">
                                            @csrf
                                            <button type="submit"
                                                style="padding:5px 12px; background:#FEF2F2; color:#DC2626; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                                Delete
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            @endif

                        </tr>
                    @endforeach
                @endforeach

                {{-- PER-CATEGORY PAGINATION CONTROLS --}}
                @if($totalPages > 1)
                    <tr class="cat-group-{{ $catSlug }} cat-pagination-row" data-cat-slug="{{ $catSlug }}">
                        <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" style="padding:10px 16px; text-align:right; border-bottom:1px solid #f1f5f9;">
                            <div style="display:inline-flex; align-items:center; gap:6px;">
                                <button type="button" onclick="event.stopPropagation(); changeCatPage('{{ $catSlug }}', -1, {{ $totalPages }})"
                                    id="prevBtn-{{ $catSlug }}"
                                    style="padding:5px 10px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; background:#fff; cursor:pointer; color:#cbd5e1;" disabled>
                                    Previous
                                </button>
                                <span style="font-size:12px; color:#64748b;" id="pageLabel-{{ $catSlug }}">Page 1 of {{ $totalPages }}</span>
                                <button type="button" onclick="event.stopPropagation(); changeCatPage('{{ $catSlug }}', 1, {{ $totalPages }})"
                                    id="nextBtn-{{ $catSlug }}"
                                    style="padding:5px 10px; border:1px solid #e2e8f0; border-radius:6px; font-size:12px; background:#fff; cursor:pointer; color:#374151;">
                                    Next
                                </button>
                            </div>
                        </td>
                    </tr>
                @endif

            @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                        No inventory items found.
                    </td>
                </tr>
            @endforelse
            <tr id="inventoryNoResults" style="display:none;">
                <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" style="text-align:center; padding:32px; color:#64748b;">
                    No items match these filters. Try another search, category, or stock status.
                </td>
            </tr>
            </tbody>
        </table>
    </div>
</div>

<script>
const catCurrentPage = {};

function toggleCategory(catSlug) {
    const rows = document.querySelectorAll('.cat-group-' + catSlug);
    const chevron = document.getElementById('chevron-' + catSlug);
    const toggle = chevron?.closest('.category-toggle');
    if (!rows.length) return;

    const isExpanded = toggle?.getAttribute('aria-expanded') === 'true';

    if (!isExpanded) {
        const currentPage = catCurrentPage[catSlug] || 0;
        rows.forEach(row => {
            if (row.classList.contains('cat-pagination-row')) {
                row.style.display = '';
            } else if (row.dataset.page == currentPage) {
                row.style.display = '';
            } else {
                row.style.display = 'none';
            }
        });
        if (chevron) chevron.style.transform = 'rotate(0deg)';
        toggle?.setAttribute('aria-expanded', 'true');
        toggle?.setAttribute('aria-label', 'Collapse ' + toggle.dataset.categoryName + ' category');
    } else {
        rows.forEach(row => row.style.display = 'none');
        if (chevron) chevron.style.transform = 'rotate(-90deg)';
        toggle?.setAttribute('aria-expanded', 'false');
        toggle?.setAttribute('aria-label', 'Expand ' + toggle.dataset.categoryName + ' category');
    }
}

function changeCatPage(catSlug, direction, totalPages) {
    let current = catCurrentPage[catSlug] || 0;
    current += direction;
    if (current < 0) current = 0;
    if (current > totalPages - 1) current = totalPages - 1;
    catCurrentPage[catSlug] = current;

    document.querySelectorAll('.cat-group-' + catSlug + '.table-row').forEach(row => {
        row.style.display = (row.dataset.page == current) ? '' : 'none';
    });

    document.getElementById('pageLabel-' + catSlug).textContent = 'Page ' + (current + 1) + ' of ' + totalPages;

    const prevBtn = document.getElementById('prevBtn-' + catSlug);
    const nextBtn = document.getElementById('nextBtn-' + catSlug);
    prevBtn.disabled = current === 0;
    prevBtn.style.color = current === 0 ? '#cbd5e1' : '#374151';
    nextBtn.disabled = current === totalPages - 1;
    nextBtn.style.color = current === totalPages - 1 ? '#cbd5e1' : '#374151';
}

function toggleAllCategories() {
    const btn = document.getElementById('toggleAllBtn');
    const willCollapse = btn.textContent === 'Collapse All';

    document.querySelectorAll('.category-header-row').forEach(headerRow => {
        const toggle = headerRow.querySelector('.category-toggle');
        const chevron = headerRow.querySelector('.chevron-icon');
        if (!toggle || !chevron) return;
        const catSlug = chevron.id.replace('chevron-', '');
        const isCurrentlyHidden = toggle.getAttribute('aria-expanded') !== 'true';

        if (willCollapse && !isCurrentlyHidden) {
            toggleCategory(catSlug);
        } else if (!willCollapse && isCurrentlyHidden) {
            toggleCategory(catSlug);
        }
    });

    btn.textContent = willCollapse ? 'Expand All' : 'Collapse All';
    btn.setAttribute('aria-expanded', String(!willCollapse));
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value.toUpperCase();
    const status = document.getElementById('statusFilter').value;
    const filtering = search.length > 0 || category.length > 0 || status.length > 0;

    const rows = document.querySelectorAll('#inventoryTable tr');
    let lastHeaderRow = null;
    let hasVisibleItemInGroup = false;
    let visibleItems = 0;

    rows.forEach(row => {
        if (row.classList.contains('category-header-row')) {
            if (lastHeaderRow) lastHeaderRow.style.display = hasVisibleItemInGroup ? '' : 'none';
            lastHeaderRow = row;
            hasVisibleItemInGroup = false;
            return;
        }
        if (row.classList.contains('cat-pagination-row')) {
            row.style.display = filtering ? 'none' : '';
            return;
        }
        if (!row.classList.contains('table-row')) return;

        if (!filtering) {
            // Reset to page-based visibility when search is cleared
            const catSlug = row.dataset.catSlug;
            const currentPage = catCurrentPage[catSlug] || 0;
            row.style.display = (row.dataset.page == currentPage) ? '' : 'none';
            if (row.style.display !== 'none') hasVisibleItemInGroup = true;
            return;
        }

        const text = row.innerText.toLowerCase();
        const rowCat = row.getAttribute('data-category');
        const matchSearch = text.includes(search);
        const matchCat = category === '' || rowCat === category;
        const matchStatus = status === '' || row.dataset.status.split(' ').includes(status);
        const visible = matchSearch && matchCat && matchStatus;

        row.style.display = visible ? '' : 'none';
        if (visible) {
            hasVisibleItemInGroup = true;
            visibleItems++;
        }
    });

    if (lastHeaderRow) lastHeaderRow.style.display = hasVisibleItemInGroup ? '' : 'none';
    document.getElementById('inventoryNoResults').style.display = filtering && visibleItems === 0 ? '' : 'none';
}
</script>

<style>
    @media (max-width: 640px) {
        .inventory-toolbar { flex-wrap: wrap; padding: 12px !important; }
        .inventory-search { flex: 1 1 100% !important; max-width: none !important; }
        .inventory-toolbar select { flex: 1 1 120px; }
        .inventory-toolbar #toggleAllBtn { margin-left: 0 !important; }
    }
</style>

@endsection
