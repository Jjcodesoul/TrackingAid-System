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

    /* ── Category tabs ── */
    .inv-tabs-wrap {
        position: relative;
        display: flex;
        align-items: stretch;
        border-bottom: 1px solid #e2e8f0;
    }
    .inv-tabs {
        flex: 1;
        min-width: 0;
        display: flex;
        align-items: flex-end;
        gap: 4px;
        padding: 0 16px;
        overflow-x: auto;
        scrollbar-width: none;
    }
    .inv-tabs::-webkit-scrollbar { display: none; }
    .inv-tabs-arrow {
        display: none;
        flex: 0 0 auto;
        align-items: center;
        justify-content: center;
        width: 36px;
        border: 0;
        background: #fff;
        color: #475569;
        cursor: pointer;
        font-size: 12px;
        z-index: 1;
    }
    .inv-tabs-arrow:hover { background: #f8fafc; color: #0f172a; }
    .inv-tabs-arrow.left  { box-shadow: 6px 0 8px -6px rgba(15, 23, 42, .18); }
    .inv-tabs-arrow.right { box-shadow: -6px 0 8px -6px rgba(15, 23, 42, .18); }

    .inv-pagination {
        display: flex;
        align-items: center;
        justify-content: space-between;
        flex-wrap: wrap;
        gap: 10px;
        padding: 12px 20px;
        border-top: 1px solid #f1f5f9;
    }
    .inv-pagination-info { font-size: 12px; color: #64748b; }
    .inv-pagination-nav { display: inline-flex; align-items: center; gap: 4px; }
    .inv-page-btn {
        min-width: 32px;
        height: 32px;
        padding: 0 10px;
        border: 1px solid #e2e8f0;
        border-radius: 6px;
        background: #fff;
        color: #374151;
        font-size: 12px;
        font-weight: 600;
        cursor: pointer;
    }
    .inv-page-btn:hover:not(:disabled):not(.is-current) { background: #f8fafc; }
    .inv-page-btn.is-current { background: #10B981; border-color: #10B981; color: #fff; cursor: default; }
    .inv-page-btn:disabled { color: #cbd5e1; cursor: not-allowed; }
    .inv-page-gap { padding: 0 4px; color: #94a3b8; font-size: 12px; }
    .inv-tab {
        flex: 0 0 auto;
        padding: 14px 14px 12px;
        background: transparent;
        border: 0;
        border-bottom: 2px solid transparent;
        margin-bottom: -1px;
        color: #64748b;
        font-size: 13px;
        font-weight: 600;
        white-space: nowrap;
        cursor: pointer;
        transition: color .15s ease, border-color .15s ease;
    }
    .inv-tab:hover { color: #0f172a; }
    .inv-tab .inv-tab-count {
        margin-left: 6px;
        padding: 1px 7px;
        border-radius: 999px;
        background: #f1f5f9;
        color: #64748b;
        font-size: 11px;
        font-weight: 600;
    }
    .inv-tab.is-active {
        color: #10B981;
        border-bottom-color: #10B981;
    }
    .inv-tab.is-active .inv-tab-count {
        background: #ECFDF5;
        color: #047857;
    }

    @media (max-width: 1100px) { .inventory-summary { grid-template-columns: repeat(2, minmax(0, 1fr)); } }
    @media (max-width: 480px) { .inventory-summary { grid-template-columns: 1fr; } }
    @media (max-width: 640px) {
        .inventory-toolbar { flex-wrap: wrap; padding: 12px !important; }
        .inventory-search { flex: 1 1 100% !important; max-width: none !important; }
        .inventory-toolbar select { flex: 1 1 120px; }
    }
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

    {{-- CATEGORY TABS --}}
    <div class="inv-tabs-wrap">
    <button type="button" class="inv-tabs-arrow left" id="invTabsLeft" onclick="scrollTabs(-1)" aria-label="Scroll categories left">
        <i class="fa-solid fa-chevron-left"></i>
    </button>
    <div class="inv-tabs" id="invTabs" role="tablist" aria-label="Inventory categories">
        <button type="button" class="inv-tab is-active" role="tab" aria-selected="true"
            data-tab="all" onclick="switchTab('all')">
            All <span class="inv-tab-count">{{ number_format($inventoryStats['totalItems']) }}</span>
        </button>
        @foreach($items as $category => $categoryItems)
            <button type="button" class="inv-tab" role="tab" aria-selected="false"
                data-tab="{{ \Illuminate\Support\Str::slug($category) }}"
                onclick="switchTab('{{ \Illuminate\Support\Str::slug($category) }}')">
                {{ ucfirst(strtolower($category)) }}
                <span class="inv-tab-count">{{ $categoryItems->count() }}</span>
            </button>
        @endforeach
    </div>
    <button type="button" class="inv-tabs-arrow right" id="invTabsRight" onclick="scrollTabs(1)" aria-label="Scroll categories right">
        <i class="fa-solid fa-chevron-right"></i>
    </button>
    </div>

    {{-- SEARCH / STATUS FILTER --}}
    <div class="inventory-toolbar" style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
        <div class="inventory-search" style="position:relative; flex:1; max-width:320px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="search" id="searchInput" aria-label="Search inventory items" placeholder="Search items..." oninput="filterTable()"
                style="width:100%; padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
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
        <span id="resultCount" style="margin-left:auto; font-size:12px; color:#94a3b8; white-space:nowrap;"></span>
    </div>

    {{-- TABLE --}}
    <div class="table-wrap">
        <table class="table-ui inventory-table">
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
            @forelse($items as $category => $categoryItems)

                @php
                    $catSlug = \Illuminate\Support\Str::slug($category);
                @endphp

                @foreach($categoryItems as $item)
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
                    <tr style="border-bottom:1px solid #f1f5f9;"
                        class="table-row"
                        data-category="{{ strtoupper($item->category) }}"
                        data-cat-slug="{{ $catSlug }}"
                        data-status="{{ implode(' ', $statusFilters) }}">

                        <td style="padding:14px 16px;">
                            <span class="sku-code">{{ $item->sku }}</span>
                        </td>

                        <td style="padding:14px 16px;">
                            <span style="font-weight:600; color:#1a202c; font-size:14px;">{{ $item->name }}</span>
                        </td>

                        <td style="padding:14px 16px;">
                            <span class="category-tag">
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
                                <span class="type-badge type-consumable">Consumable</span>
                            @else
                                <span class="type-badge type-reusable">Reusable</span>
                            @endif
                        </td>

                        <td style="padding:14px 16px;">
                            @if($stockQuantity <= 0)
                                <span class="status-badge status-out">Out of Stock</span>
                            @elseif($stockQuantity < 10)
                                <span class="status-badge status-low">Low Stock</span>
                            @else
                                <span class="status-badge status-in">In Stock</span>
                            @endif
                            @if($isExpired)
                                <span class="status-badge status-expired">Expired</span>
                            @elseif($isExpiring)
                                <span class="status-badge status-expiring">Expiring</span>
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
                                    <a href="{{ route('inventory.edit', $item->id) }}" class="btn-soft btn-compact">
                                        Edit
                                    </a>
                                    <form method="POST" action="{{ route('inventory.delete', $item->id) }}"
                                        onsubmit="return confirm('Delete this item?')" style="margin:0; padding:0; display:inline-block;">
                                        @csrf
                                        <button type="submit" class="btn-danger btn-compact">
                                            Delete
                                        </button>
                                    </form>
                                </div>
                            </td>
                        @endif

                    </tr>
                @endforeach

            @empty
                <tr>
                    <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                        No inventory items found.
                    </td>
                </tr>
            @endforelse
            <tr id="inventoryNoResults" style="display:none;">
                <td colspan="{{ auth()->user()->role === 'admin' ? 10 : 9 }}" style="text-align:center; padding:32px; color:#64748b;">
                    No items match these filters. Try another search, category tab, or stock status.
                </td>
            </tr>
            </tbody>
        </table>
    </div>

    {{-- PAGINATION (10 items per page, per tab) --}}
    <div class="inv-pagination" id="inventoryPagination" style="display:none;">
        <span class="inv-pagination-info" id="paginationInfo"></span>
        <nav class="inv-pagination-nav" id="paginationNav" aria-label="Inventory pages"></nav>
    </div>
</div>

<script>
const PER_PAGE = 10;
let activeTab = 'all';
let currentPage = 1;

/* ───────── Tabs ───────── */
function switchTab(tab, instant) {
    activeTab = tab;

    let activeBtn = null;
    document.querySelectorAll('.inv-tab').forEach(btn => {
        const isActive = btn.dataset.tab === tab;
        btn.classList.toggle('is-active', isActive);
        btn.setAttribute('aria-selected', String(isActive));
        if (isActive) activeBtn = btn;
    });

    // Keep the selected tab visible when there are many categories
    if (activeBtn) {
        activeBtn.scrollIntoView({ block: 'nearest', inline: 'center', behavior: instant ? 'auto' : 'smooth' });
    }

    try { sessionStorage.setItem('inventoryActiveTab', tab); } catch (e) {}

    filterTable(true);
}

function scrollTabs(direction) {
    const el = document.getElementById('invTabs');
    el.scrollBy({ left: direction * 240, behavior: 'smooth' });
}

function updateTabArrows() {
    const el = document.getElementById('invTabs');
    const left = document.getElementById('invTabsLeft');
    const right = document.getElementById('invTabsRight');
    if (!el || !left || !right) return;

    left.style.display  = el.scrollLeft > 4 ? 'flex' : 'none';
    right.style.display = (el.scrollLeft + el.clientWidth < el.scrollWidth - 4) ? 'flex' : 'none';
}

/* ───────── Filter + paginate ───────── */
function filterTable(resetPage) {
    if (resetPage !== false) currentPage = 1;

    const search = document.getElementById('searchInput').value.toLowerCase().trim();
    const status = document.getElementById('statusFilter').value;
    const rows = Array.from(document.querySelectorAll('#inventoryTable tr.table-row'));

    const matches = rows.filter(row => {
        const matchTab = activeTab === 'all' || row.dataset.catSlug === activeTab;
        const matchSearch = search === '' || row.innerText.toLowerCase().includes(search);
        const matchStatus = status === '' || row.dataset.status.split(' ').includes(status);
        return matchTab && matchSearch && matchStatus;
    });

    const totalPages = Math.max(1, Math.ceil(matches.length / PER_PAGE));
    if (currentPage > totalPages) currentPage = totalPages;

    const start = (currentPage - 1) * PER_PAGE;
    const end = start + PER_PAGE;

    rows.forEach(row => row.style.display = 'none');
    matches.forEach((row, i) => {
        row.style.display = (i >= start && i < end) ? '' : 'none';
    });

    const countEl = document.getElementById('resultCount');
    if (countEl) countEl.textContent = matches.length + (matches.length === 1 ? ' item' : ' items');

    const noResults = document.getElementById('inventoryNoResults');
    if (noResults) noResults.style.display = (rows.length > 0 && matches.length === 0) ? '' : 'none';

    renderPagination(matches.length, totalPages, start, end);
}

function goToPage(page) {
    currentPage = page;
    filterTable(false);
}

function pageList(current, total) {
    if (total <= 7) return Array.from({ length: total }, (_, i) => i + 1);

    let from = Math.max(2, current - 1);
    let to = Math.min(total - 1, current + 1);
    if (current <= 3) { from = 2; to = 4; }
    if (current >= total - 2) { from = total - 3; to = total - 1; }

    const pages = [1];
    if (from > 2) pages.push('gap-left');
    for (let i = from; i <= to; i++) pages.push(i);
    if (to < total - 1) pages.push('gap-right');
    pages.push(total);
    return pages;
}

function makePageButton(label, onClick, opts) {
    const btn = document.createElement('button');
    btn.type = 'button';
    btn.className = 'inv-page-btn' + (opts && opts.current ? ' is-current' : '');
    btn.textContent = label;
    if (opts && opts.disabled) btn.disabled = true;
    if (opts && opts.current) btn.setAttribute('aria-current', 'page');
    if (opts && opts.aria) btn.setAttribute('aria-label', opts.aria);
    if (onClick && !(opts && opts.disabled)) btn.addEventListener('click', onClick);
    return btn;
}

function renderPagination(totalMatches, totalPages, start, end) {
    const wrap = document.getElementById('inventoryPagination');
    const info = document.getElementById('paginationInfo');
    const nav = document.getElementById('paginationNav');
    if (!wrap || !info || !nav) return;

    nav.innerHTML = '';

    if (totalMatches === 0) {
        wrap.style.display = 'none';
        return;
    }

    wrap.style.display = 'flex';
    info.textContent = 'Showing ' + (start + 1) + '–' + Math.min(end, totalMatches) + ' of ' + totalMatches + (totalMatches === 1 ? ' item' : ' items');

    // No buttons needed when everything fits on one page
    if (totalPages <= 1) return;

    nav.appendChild(makePageButton('Previous', () => goToPage(currentPage - 1), { disabled: currentPage === 1, aria: 'Previous page' }));

    pageList(currentPage, totalPages).forEach(p => {
        if (typeof p === 'string') {
            const gap = document.createElement('span');
            gap.className = 'inv-page-gap';
            gap.textContent = '…';
            nav.appendChild(gap);
        } else {
            nav.appendChild(makePageButton(String(p), () => goToPage(p), { current: p === currentPage, aria: 'Page ' + p }));
        }
    });

    nav.appendChild(makePageButton('Next', () => goToPage(currentPage + 1), { disabled: currentPage === totalPages, aria: 'Next page' }));
}

document.addEventListener('DOMContentLoaded', function () {
    const tabsEl = document.getElementById('invTabs');
    if (tabsEl) tabsEl.addEventListener('scroll', updateTabArrows, { passive: true });
    window.addEventListener('resize', updateTabArrows);

    let saved = 'all';
    try { saved = sessionStorage.getItem('inventoryActiveTab') || 'all'; } catch (e) {}

    // Only restore a saved tab if it still exists (e.g. a category might be gone after a data reset)
    const exists = document.querySelector('.inv-tab[data-tab="' + saved + '"]');
    switchTab(exists ? saved : 'all', true);
    updateTabArrows();
});
</script>

@endsection