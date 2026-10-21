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
@endif

<style>
    .stock-category-picker { position:relative; }
    .stock-category-native { position:absolute; width:1px; height:1px; opacity:0; pointer-events:none; }
    .stock-category-trigger, .stock-category-search { width:100%; box-sizing:border-box; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; background:#fff; color:#374151; font-size:13px; text-align:left; }
    .stock-category-trigger { cursor:pointer; display:flex; justify-content:space-between; align-items:center; }
    .stock-picker-panel { position:absolute; z-index:30; top:calc(100% + 5px); left:0; right:0; padding:8px; border:1px solid #dbe4ee; border-radius:10px; background:#fff; box-shadow:0 12px 28px rgba(15,23,42,.14); }
    .stock-category-options { max-height:220px; overflow-y:auto; margin-top:6px; }
    .stock-category-option, .stock-item-option { width:100%; border:0; border-radius:6px; background:#fff; color:#334155; cursor:pointer; text-align:left; }
    .stock-category-option { padding:9px 10px; font-size:13px; }
    .stock-category-option:hover, .stock-item-option:hover { background:#f0fdf4; }
    .stock-picker-pager { display:flex; align-items:center; justify-content:space-between; gap:8px; border-top:1px solid #eef2f7; margin-top:6px; padding:8px 2px 0; color:#64748b; font-size:11px; }
    .stock-picker-pager button { padding:5px 9px; border:1px solid #dbe4ee; border-radius:6px; background:#fff; color:#334155; cursor:pointer; }
    .stock-picker-pager button:disabled { opacity:.45; cursor:default; }
    #itemDropdownList { padding:6px; max-height:none !important; overflow:visible !important; }
    .stock-item-options { max-height:280px; overflow-y:auto; }
    .stock-item-option { display:flex; align-items:center; justify-content:space-between; gap:12px; padding:10px 9px; border-bottom:1px solid #f1f5f9; font-size:13px; }
    .stock-item-option-main { min-width:0; }
    .stock-item-option-sku { color:#059669; font-family:monospace; font-weight:700; }
    .stock-item-option-name { color:#374151; }
    .stock-item-option-badge { flex:none; padding:3px 8px; border-radius:999px; font-size:10px; font-weight:600; }
    @media (max-width: 700px) { .stock-item-option { align-items:flex-start; flex-direction:column; gap:5px; } }
</style>

<form method="POST" action="/stock-in/store" id="stockInForm" class="inventory-form">
@csrf

<div style="display:grid; grid-template-columns:1fr 340px; gap:24px; align-items:start;">

    {{-- LEFT: FORM --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <h4 style="font-size:15px; font-weight:700; color:#1a202c; margin:0 0 20px;">Stock Details</h4>

        {{-- CATEGORY FILTER --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Filter by Category</label>
            <div class="stock-category-picker" id="categoryPicker">
            <select id="categoryFilter" class="stock-category-native" onchange="onCategoryChange()"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; background:#fff; color:#374151; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
                <option value="">All Categories</option>
                @foreach($categories as $cat)
                    <option value="{{ strtoupper($cat) }}">{{ ucfirst(strtolower($cat)) }}</option>
                @endforeach
            </select>
            <button type="button" id="categoryPickerTrigger" class="stock-category-trigger" aria-expanded="false">
                <span id="categoryPickerValue">All Categories</span><span aria-hidden="true">▾</span>
            </button>
            <div id="categoryPickerPanel" class="stock-picker-panel" style="display:none;">
                <input type="search" id="categoryPickerSearch" class="stock-category-search" placeholder="Search categories..." autocomplete="off">
                <div id="categoryPickerOptions" class="stock-category-options"></div>
                <div class="stock-picker-pager"><button type="button" id="categoryPickerPrev">Previous</button><span id="categoryPickerPage"></span><button type="button" id="categoryPickerNext">Next</button></div>
            </div>
            </div>
        </div>

        {{-- SEARCHABLE ITEM SKU --}}
        <div style="margin-bottom:20px; position:relative;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Select Item (SKU)</label>

            <input type="text" id="itemSearchInput" placeholder="Type SKU or item name to search..."
                autocomplete="off"
                oninput="onSearchInput()" onfocus="onSearchFocus()"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">

            <input type="hidden" name="item_id" id="itemIdField" required>

            <div id="itemDropdownList" style="display:none; position:absolute; z-index:20; top:calc(100% + 4px); left:0; right:0; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.08);">
                <div id="itemDropdownOptions" class="stock-item-options"></div>
                <div class="stock-picker-pager"><button type="button" id="itemPickerPrev">Previous</button><span id="itemPickerPage"></span><button type="button" id="itemPickerNext">Next</button></div>
            </div>

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

        <button type="submit"
            style="width:100%; padding:14px; background:#10B981; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.02em;">
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
const categoryPicker = document.getElementById('categoryPicker');
const categoryPickerTrigger = document.getElementById('categoryPickerTrigger');
const categoryPickerValue = document.getElementById('categoryPickerValue');
const categoryPickerPanel = document.getElementById('categoryPickerPanel');
const categoryPickerSearch = document.getElementById('categoryPickerSearch');
const categoryPickerOptions = document.getElementById('categoryPickerOptions');
const categoryPickerPage = document.getElementById('categoryPickerPage');
const categoryPickerPrev = document.getElementById('categoryPickerPrev');
const categoryPickerNext = document.getElementById('categoryPickerNext');
const itemDropdownOptions = document.getElementById('itemDropdownOptions');
const itemPickerPage = document.getElementById('itemPickerPage');
const itemPickerPrev = document.getElementById('itemPickerPrev');
const itemPickerNext = document.getElementById('itemPickerNext');
const expirationWrapper = document.getElementById('expirationWrapper');
const expirationInput = document.getElementById('expirationInput');
const pickerPageSize = 8;
let categoryPage = 0;
let itemPage = 0;

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
    const pageCount = Math.max(1, Math.ceil(filtered.length / pickerPageSize));
    itemPage = Math.min(itemPage, pageCount - 1);
    itemDropdownOptions.innerHTML = '';

    if (filtered.length === 0) {
        noItemsMsg.style.display = 'block';
        dropdownList.style.display = 'none';
        return;
    }

    noItemsMsg.style.display = 'none';

    filtered.slice(itemPage * pickerPageSize, (itemPage + 1) * pickerPageSize).forEach(item => {
        const row = document.createElement('button');
        row.type = 'button';
        row.className = 'stock-item-option';

        const main = document.createElement('span');
        main.className = 'stock-item-option-main';
        const sku = document.createElement('span');
        sku.className = 'stock-item-option-sku';
        sku.textContent = item.sku;
        const name = document.createElement('span');
        name.className = 'stock-item-option-name';
        name.textContent = ' — ' + item.name;
        main.append(sku, name);

        const badge = document.createElement('span');
        badge.className = 'stock-item-option-badge';
        badge.textContent = item.type === 'returnable' ? 'Reusable' : 'Consumable';
        badge.style.background = item.type === 'returnable' ? '#FAF5FF' : '#EBF8FF';
        badge.style.color = item.type === 'returnable' ? '#6B46C1' : '#2B6CB0';

        row.append(main, badge);
        row.onclick = () => selectItem(item);
        itemDropdownOptions.appendChild(row);
    });

    itemPickerPage.textContent = `Page ${itemPage + 1} of ${pageCount} · ${filtered.length} items`;
    itemPickerPrev.disabled = itemPage === 0;
    itemPickerNext.disabled = itemPage >= pageCount - 1;
    dropdownList.style.display = 'block';
}

function renderCategoryOptions() {
    const query = categoryPickerSearch.value.trim().toLowerCase();
    const options = Array.from(categoryFilter.options).filter(option =>
        option.textContent.toLowerCase().includes(query)
    );
    const pageCount = Math.max(1, Math.ceil(options.length / pickerPageSize));
    categoryPage = Math.min(categoryPage, pageCount - 1);
    categoryPickerOptions.innerHTML = '';

    options.slice(categoryPage * pickerPageSize, (categoryPage + 1) * pickerPageSize).forEach(option => {
        const button = document.createElement('button');
        button.type = 'button';
        button.className = 'stock-category-option';
        button.textContent = option.textContent;
        button.setAttribute('aria-selected', String(option.value === categoryFilter.value));
        if (option.value === categoryFilter.value) button.style.fontWeight = '700';
        button.addEventListener('click', () => {
            categoryFilter.value = option.value;
            categoryPickerValue.textContent = option.textContent;
            categoryPickerPanel.style.display = 'none';
            categoryPickerTrigger.setAttribute('aria-expanded', 'false');
            categoryFilter.dispatchEvent(new Event('change', { bubbles: true }));
        });
        categoryPickerOptions.appendChild(button);
    });

    categoryPickerPage.textContent = `${options.length ? categoryPage + 1 : 0} of ${pageCount} · ${options.length} categories`;
    categoryPickerPrev.disabled = categoryPage === 0;
    categoryPickerNext.disabled = categoryPage >= pageCount - 1;
}

categoryPickerTrigger.addEventListener('click', () => {
    const opening = categoryPickerPanel.style.display === 'none';
    categoryPickerPanel.style.display = opening ? 'block' : 'none';
    categoryPickerTrigger.setAttribute('aria-expanded', String(opening));
    if (opening) {
        categoryPickerSearch.value = '';
        categoryPage = 0;
        renderCategoryOptions();
        categoryPickerSearch.focus();
    }
});
categoryPickerSearch.addEventListener('input', () => { categoryPage = 0; renderCategoryOptions(); });
categoryPickerPrev.addEventListener('click', () => { categoryPage--; renderCategoryOptions(); });
categoryPickerNext.addEventListener('click', () => { categoryPage++; renderCategoryOptions(); });
itemPickerPrev.addEventListener('click', () => { itemPage--; renderDropdown(); });
itemPickerNext.addEventListener('click', () => { itemPage++; renderDropdown(); });

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
        label.innerText = 'Reusable Item';
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
    itemPage = 0;
    itemIdField.value = '';
    resetSummary();
    renderDropdown();
}

function onSearchFocus() {
    renderDropdown();
}

function onCategoryChange() {
    categoryPickerValue.textContent = categoryFilter.options[categoryFilter.selectedIndex].textContent;
    searchInput.value = '';
    itemPage = 0;
    itemIdField.value = '';
    resetSummary();
    renderDropdown();
}

document.addEventListener('click', function(e) {
    if (!e.target.closest('#itemSearchInput') && !e.target.closest('#itemDropdownList')) {
        dropdownList.style.display = 'none';
    }
    if (!e.target.closest('#categoryPicker')) {
        categoryPickerPanel.style.display = 'none';
        categoryPickerTrigger.setAttribute('aria-expanded', 'false');
    }
});

renderCategoryOptions();

document.getElementById('stockInForm').addEventListener('submit', function(e) {
    if (!itemIdField.value) {
        e.preventDefault();
        alert('Please select an item from the list.');
        searchInput.focus();
    }
});
</script>

@endsection
