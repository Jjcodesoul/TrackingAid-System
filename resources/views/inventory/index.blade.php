@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <div>
        <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Inventory</h2>
        <p style="color:#64748B; font-size:13px; margin:4px 0 0;">All registered items and current stock levels</p>
    </div>
    
    @if(auth()->user()->role === 'admin')
        <a href="{{ route('inventory.create') }}" class="btn-main">+ Add Item</a>
    @endif
</div>

{{-- ALERTS --}}
@if(session('success'))
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

{{-- TABLE CARD --}}
<div class="card-ui" style="padding:0; overflow:hidden;">

    {{-- SEARCH / FILTER --}}
    <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
        <div style="position:relative; flex:1; max-width:320px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="text" id="searchInput" placeholder="Search items..." onkeyup="filterTable()"
                style="width:100%; padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <select id="categoryFilter" onchange="filterTable()"
            style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#374151; outline:none; background:#fff;">
            <option value="">All</option>
            <option value="FOOD">Food</option>
            <option value="MEDICAL">Medical</option>
            <option value="RESCUE">Rescue</option>
            <option value="RELIEF">Relief</option>
        </select>
        <button type="button" onclick="toggleAllCategories()" id="toggleAllBtn"
            style="margin-left:auto; padding:8px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; color:#374151; background:#fff; cursor:pointer; white-space:nowrap;">
            Collapse All
        </button>
    </div>

    {{-- TABLE --}}
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">SKU</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Name</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Category</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Stock</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Unit</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Type</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Flags</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Location</th>
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
                <tr class="category-header-row" onclick="toggleCategory('{{ $catSlug }}')" style="cursor:pointer;">
                    <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 8 }}"
                        style="padding:10px 16px; background:{{ $cc['bg'] }};">
                        <span style="display:inline-flex; align-items:center; gap:8px; font-size:12px; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:{{ $cc['color'] }};">
                            <i class="fa-solid fa-chevron-down chevron-icon" id="chevron-{{ $catSlug }}" style="font-size:10px; transition:transform .15s ease;"></i>
                            {{ ucfirst(strtolower($category)) }}
                            <span style="font-weight:500; color:#94a3b8; text-transform:none; letter-spacing:normal;">
                                ({{ $categoryItems->count() }} {{ Str::plural('item', $categoryItems->count()) }})
                            </span>
                        </span>
                    </td>
                </tr>

                {{-- ITEMS IN THIS CATEGORY, CHUNKED INTO PAGES OF 10 --}}
                @foreach($categoryItems->chunk($perPage) as $pageIndex => $pageOfItems)
                    @foreach($pageOfItems as $item)
                        @php
                            $nextExpirationDate = $item->next_expiration_date;
                        @endphp
                        <tr style="border-bottom:1px solid #f1f5f9; {{ $pageIndex > 0 ? 'display:none;' : '' }}"
                            class="table-row cat-group-{{ $catSlug }} cat-page-{{ $catSlug }}-{{ $pageIndex }}"
                            data-category="{{ strtoupper($item->category) }}"
                            data-cat-slug="{{ $catSlug }}"
                            data-page="{{ $pageIndex }}">

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
                                <span style="font-weight:700; font-size:15px; color:{{ $item->total_stock <= 0 ? '#dc2626' : ($item->total_stock < 10 ? '#d97706' : '#1a202c') }};">
                                    {{ number_format($item->total_stock) }}
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
                                @if($item->total_stock <= 0)
                                    <span style="background:#FEF2F2; color:#DC2626; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Out of Stock</span>
                                @elseif($item->total_stock < 10)
                                    <span style="background:#FEF2F2; color:#DC2626; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Low Stock</span>
                                @elseif($nextExpirationDate && \Carbon\Carbon::parse($nextExpirationDate)->lte(now()->addDays(30)))
                                    <span style="background:#FFFBEB; color:#D97706; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Expiring</span>
                                @else
                                    <span style="color:#94a3b8; font-size:13px;">—</span>
                                @endif
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
                        <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 8 }}" style="padding:10px 16px; text-align:right; border-bottom:1px solid #f1f5f9;">
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
                    <td colspan="{{ auth()->user()->role === 'admin' ? 9 : 8 }}" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                        No inventory items found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
const catCurrentPage = {};

function toggleCategory(catSlug) {
    const rows = document.querySelectorAll('.cat-group-' + catSlug);
    const chevron = document.getElementById('chevron-' + catSlug);
    if (!rows.length) return;

    // Check current state using the first data row (page 0)
    const firstRow = document.querySelector('.cat-page-' + catSlug + '-0');
    const isHidden = firstRow && firstRow.style.display === 'none';

    if (isHidden) {
        // Expand: show only the current page's rows
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
    } else {
        // Collapse: hide everything in this group
        rows.forEach(row => row.style.display = 'none');
        if (chevron) chevron.style.transform = 'rotate(-90deg)';
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
        const chevron = headerRow.querySelector('.chevron-icon');
        if (!chevron) return;
        const catSlug = chevron.id.replace('chevron-', '');
        const firstRow = document.querySelector('.cat-page-' + catSlug + '-0');
        const isCurrentlyHidden = firstRow && firstRow.style.display === 'none';

        if (willCollapse && !isCurrentlyHidden) {
            toggleCategory(catSlug);
        } else if (!willCollapse && isCurrentlyHidden) {
            toggleCategory(catSlug);
        }
    });

    btn.textContent = willCollapse ? 'Expand All' : 'Collapse All';
}

function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value.toUpperCase();
    const searching = search.length > 0 || category.length > 0;

    const rows = document.querySelectorAll('#inventoryTable tr');
    let lastHeaderRow = null;
    let hasVisibleItemInGroup = false;

    rows.forEach(row => {
        if (row.classList.contains('category-header-row')) {
            if (lastHeaderRow) lastHeaderRow.style.display = hasVisibleItemInGroup ? '' : 'none';
            lastHeaderRow = row;
            hasVisibleItemInGroup = false;
            return;
        }
        if (row.classList.contains('cat-pagination-row')) {
            row.style.display = searching ? 'none' : '';
            return;
        }
        if (!row.classList.contains('table-row')) return;

        if (!searching) {
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
        const visible = matchSearch && matchCat;

        row.style.display = visible ? '' : 'none';
        if (visible) hasVisibleItemInGroup = true;
    });

    if (lastHeaderRow) lastHeaderRow.style.display = hasVisibleItemInGroup ? '' : 'none';
}
</script>

@endsection