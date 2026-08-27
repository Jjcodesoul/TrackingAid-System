@extends('layouts.app')

@section('content')

{{-- BREADCRUMB --}}
<div class="breadcrumb">
    <a href="/admin/dashboard">Dashboard</a>
    <span>›</span>
    <span class="current">Stock In</span>
</div>

{{-- HEADER --}}
<div style="margin-bottom:24px;">
    <h2 class="page-title">Stock In</h2>
    <p class="page-subtitle">Record incoming inventory from suppliers or donors</p>
</div>

@if(session('success'))
    <div class="alert-box alert-success">{{ session('success') }}</div>
@endif
@if(session('error'))
    <div class="alert-box alert-error">{{ session('error') }}</div>
    </div>
@endif

<form method="POST" action="/stock-in/store" id="stockInForm">
@csrf

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">

    {{-- LEFT: FORM --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <h4 style="font-size:15px; font-weight:700; color:#1a202c; margin:0 0 20px;">Stock Details</h4>

        {{-- CATEGORY FILTER --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Filter by Category</label>
            <select id="categoryFilter" onchange="onCategoryChange()"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; background:#fff; color:#374151; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ strtoupper($cat) }}">{{ ucfirst(strtolower($cat)) }}</option>
                @endforeach
            </select>
        </div>

        {{-- SEARCHABLE ITEM SKU --}}
        <div style="margin-bottom:20px; position:relative;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Select Item (SKU)</label>

            <input type="text" id="itemSearchInput" placeholder="Type SKU or item name to search..."
                autocomplete="off"
                oninput="onSearchInput()" onfocus="onSearchFocus()"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">

            <input type="hidden" name="item_id" id="itemIdField" required>

            <div id="itemDropdownList" style="display:none; position:absolute; z-index:20; top:calc(100% + 4px); left:0; right:0; max-height:260px; overflow-y:auto; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.08);"></div>

            <p id="noItemsMsg" style="display:none; font-size:12px; color:#DC2626; margin:6px 0 0;">
                No matching items found.
            </p>
        </div>

        {{-- QUANTITY + SUPPLIER --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Quantity Added</label>
                <input type="number" name="quantity" min="1" placeholder="0" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Supplier / Donor</label>
                <input type="text" name="supplier" placeholder="e.g. DSWD, Red Cross"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>

        {{-- DATE RECEIVED + EXPIRATION DATE --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; align-items:start;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Date Received</label>
                <input type="date" name="date_received" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            <div id="expirationWrapper" style="overflow:hidden; transition:max-height .25s ease, opacity .25s ease; max-height:100px; opacity:1;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
                </label>
                <input type="date" name="expiration_date" id="expirationInput"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>
    </div>

    {{-- RIGHT: SELECTED ITEM SUMMARY --}}
    <div style="display:flex; flex-direction:column; gap:16px; position:sticky; top:24px;">

        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
            <h4 style="font-size:13px; font-weight:700; color:#1a202c; margin:0 0 16px;">Selected Item</h4>

            {{-- EMPTY STATE --}}
            <div id="itemEmptyState" style="text-align:center; padding:24px 10px;">
                <i class="fa-solid fa-box-open" style="font-size:24px; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
                <p style="font-size:12px; color:#94a3b8; margin:0;">Search and select an item to see its details here.</p>
            </div>

            {{-- FILLED STATE --}}
            <div id="itemSummaryCard" style="display:none;">

                {{-- TYPE BANNER (prominent, color-coded) --}}
                <div id="summaryTypeBanner" style="display:flex; align-items:center; gap:10px; border-radius:10px; padding:12px 14px; margin-bottom:14px;">
                    <span id="summaryTypeIcon" style="width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:14px; flex-shrink:0;"></span>
                    <div>
                        <p id="summaryTypeLabel" style="margin:0; font-size:13px; font-weight:700;"></p>
                        <p id="summaryTypeNote" style="margin:1px 0 0; font-size:11px;"></p>
                    </div>
                </div>

                <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:14px 16px; margin-bottom:14px;">
                    <p id="summarySku" style="color:#10B981; font-weight:700; font-family:monospace; font-size:13px; margin:0; word-break:break-all;"></p>
                </div>

                <div style="display:flex; flex-direction:column; gap:10px;">
                    <div>
                        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">Name</span>
                        <p id="summaryName" style="margin:2px 0 0; font-size:14px; font-weight:600; color:#1a202c;"></p>
                    </div>

                    <div>
                        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">Category</span>
                        <p id="summaryCategory" style="margin:2px 0 0; font-size:13px; color:#374151;"></p>
                    </div>

                    <div>
                        <span style="font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; letter-spacing:.05em;">Current Stock</span>
                        <p id="summaryStock" style="margin:2px 0 0; font-size:13px; color:#374151;"></p>
                    </div>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-main btn-block" style="padding:14px; font-size:14px;">
            Submit Stock In
        </button>

    </div>

</div>
</form>

<script>
// Full item list passed from backend, now including type, category, and current stock
const allItems = [
    @foreach($items as $item)
    {
        id: {{ $item->id }},
        sku: @json($item->sku),
        name: @json($item->name),
        category: @json(strtoupper($item->category)),
        type: @json($item->type),
        stock: {{ $item->stockBatches->sum('quantity') ?? 0 }}
    },
    @endforeach
];

const searchInput = document.getElementById('itemSearchInput');
const dropdownList = document.getElementById('itemDropdownList');
const itemIdField = document.getElementById('itemIdField');
const noItemsMsg = document.getElementById('noItemsMsg');
const categoryFilter = document.getElementById('categoryFilter');
const expirationWrapper = document.getElementById('expirationWrapper');
const expirationInput = document.getElementById('expirationInput');

const itemEmptyState = document.getElementById('itemEmptyState');
const itemSummaryCard = document.getElementById('itemSummaryCard');
const summarySku = document.getElementById('summarySku');
const summaryName = document.getElementById('summaryName');
const summaryCategory = document.getElementById('summaryCategory');
const summaryStock = document.getElementById('summaryStock');

function getFilteredItems() {
    const category = categoryFilter.value;
    const query = searchInput.value.trim().toLowerCase();

    return allItems.filter(item => {
        const matchCategory = category === '' || item.category === category;
        const matchQuery = query === '' ||
            item.sku.toLowerCase().includes(query) ||
            item.name.toLowerCase().includes(query);
        return matchCategory && matchQuery;
    });
}

function renderDropdown() {
    const filtered = getFilteredItems();
    dropdownList.innerHTML = '';

    if (filtered.length === 0) {
        noItemsMsg.style.display = 'block';
        dropdownList.style.display = 'none';
        return;
    }

    noItemsMsg.style.display = 'none';

    filtered.slice(0, 50).forEach(item => {
        const row = document.createElement('div');
        row.style.cssText = 'padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; font-size:13px; display:flex; align-items:center; justify-content:space-between;';
        row.onmouseenter = () => row.style.background = '#F0FFF4';
        row.onmouseleave = () => row.style.background = '#fff';

        const typeBadge = item.type === 'returnable'
            ? '<span style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:999px; background:#FAF5FF; color:#6B46C1;">Returnable</span>'
            : '<span style="font-size:10px; font-weight:600; padding:2px 8px; border-radius:999px; background:#EBF8FF; color:#2B6CB0;">Consumable</span>';

        row.innerHTML = `<span><span style="font-family:monospace; font-weight:600; color:#10B981;">${item.sku}</span>
                          <span style="color:#374151;"> — ${item.name}</span></span>${typeBadge}`;
        row.onclick = () => selectItem(item);
        dropdownList.appendChild(row);
    });

    if (filtered.length > 50) {
        const moreNote = document.createElement('div');
        moreNote.style.cssText = 'padding:8px 14px; font-size:11px; color:#94a3b8; text-align:center;';
        moreNote.innerText = `+${filtered.length - 50} more — keep typing to narrow down`;
        dropdownList.appendChild(moreNote);
    }

    dropdownList.style.display = 'block';
}

function selectItem(item) {
    searchInput.value = item.sku + ' — ' + item.name;
    itemIdField.value = item.id;
    dropdownList.style.display = 'none';

    // Fill summary card
    itemEmptyState.style.display = 'none';
    itemSummaryCard.style.display = 'block';

    summarySku.innerText = item.sku;
    summaryName.innerText = item.name;
    summaryCategory.innerText = item.category.charAt(0) + item.category.slice(1).toLowerCase();
    summaryStock.innerText = item.stock + ' units in stock';

    const banner = document.getElementById('summaryTypeBanner');
    const icon = document.getElementById('summaryTypeIcon');
    const label = document.getElementById('summaryTypeLabel');
    const note = document.getElementById('summaryTypeNote');

    if (item.type === 'returnable') {
        banner.style.background = '#FAF5FF';
        banner.style.border = '2px solid #C4B5FD';
        icon.style.background = '#E9D5FF';
        icon.style.color = '#6B46C1';
        icon.innerHTML = '<i class="fa-solid fa-arrow-rotate-left"></i>';
        label.innerText = 'Returnable Item';
        label.style.color = '#6B46C1';
        note.innerText = 'Must be tracked and returned';
        note.style.color = '#8B5CF6';

        expirationWrapper.style.maxHeight = '0px';
        expirationWrapper.style.opacity = '0';
        expirationInput.value = '';
    } else {
        banner.style.background = '#EBF8FF';
        banner.style.border = '2px solid #93C5FD';
        icon.style.background = '#DBEAFE';
        icon.style.color = '#2B6CB0';
        icon.innerHTML = '<i class="fa-solid fa-box-open"></i>';
        label.innerText = 'Consumable Item';
        label.style.color = '#2B6CB0';
        note.innerText = 'Used up, tracked by expiration';
        note.style.color = '#3B82F6';

        expirationWrapper.style.maxHeight = '100px';
        expirationWrapper.style.opacity = '1';
    }
}

function resetSummary() {
    itemEmptyState.style.display = 'block';
    itemSummaryCard.style.display = 'none';
}

function onSearchInput() {
    itemIdField.value = '';
    resetSummary();
    renderDropdown();
}

function onSearchFocus() {
    renderDropdown();
}

function onCategoryChange() {
    searchInput.value = '';
    itemIdField.value = '';
    resetSummary();
    renderDropdown();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#itemSearchInput') && !e.target.closest('#itemDropdownList')) {
        dropdownList.style.display = 'none';
    }
});

document.getElementById('stockInForm').addEventListener('submit', function(e) {
    if (!itemIdField.value) {
        e.preventDefault();
        alert('Please select an item from the list.');
        searchInput.focus();
    }
});
</script>

@endsection