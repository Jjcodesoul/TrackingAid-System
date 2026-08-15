@extends('layouts.app')

@section('content')

{{-- BREADCRUMB --}}
<div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:#94a3b8;">
    <a href="/admin/dashboard" style="color:#94a3b8; text-decoration:none;">TrackingAid</a>
    <span>›</span>
    <span style="color:#1a202c; font-weight:600;">Stock In</span>
</div>

{{-- HEADER --}}
<div style="margin-bottom:24px;">
    <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Stock In</h2>
    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Record incoming inventory from suppliers or donors</p>
</div>

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

<div style="max-width:800px;">
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">

        <form method="POST" action="/stock-in/store" id="stockInForm">
            @csrf

            {{-- CATEGORY FILTER --}}
            <div style="margin-bottom:20px;">
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

                {{-- DROPDOWN RESULTS LIST --}}
                <div id="itemDropdownList" style="display:none; position:absolute; z-index:20; top:calc(100% + 4px); left:0; right:0; max-height:260px; overflow-y:auto; background:#fff; border:1px solid #e2e8f0; border-radius:10px; box-shadow:0 8px 20px rgba(0,0,0,0.08);">
                    {{-- populated by JS --}}
                </div>

                <p id="noItemsMsg" style="display:none; font-size:12px; color:#DC2626; margin:6px 0 0;">
                    No matching items found.
                </p>
            </div>

            {{-- QUANTITY + SUPPLIER --}}
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:20px;">
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

            {{-- DATE RECEIVED --}}
            <div style="margin-bottom:20px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Date Received</label>
                <input type="date" name="date_received" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            {{-- EXPIRATION DATE --}}
            <div style="margin-bottom:28px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
                </label>
                <input type="date" name="expiration_date"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            {{-- SUBMIT --}}
            <button type="submit"
                style="width:100%; padding:14px; background:#10B981; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.02em;">
                Submit Stock In
            </button>

        </form>

    </div>
</div>

<script>
// Full item list passed from backend
const allItems = [
    @foreach($items as $item)
    {
        id: {{ $item->id }},
        sku: @json($item->sku),
        name: @json($item->name),
        category: @json(strtoupper($item->category))
    },
    @endforeach
];

const searchInput = document.getElementById('itemSearchInput');
const dropdownList = document.getElementById('itemDropdownList');
const itemIdField = document.getElementById('itemIdField');
const noItemsMsg = document.getElementById('noItemsMsg');
const categoryFilter = document.getElementById('categoryFilter');

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

    filtered.slice(0, 50).forEach(item => { // cap at 50 rendered for performance
        const row = document.createElement('div');
        row.style.cssText = 'padding:10px 14px; cursor:pointer; border-bottom:1px solid #f1f5f9; font-size:13px;';
        row.onmouseenter = () => row.style.background = '#F0FFF4';
        row.onmouseleave = () => row.style.background = '#fff';
        row.innerHTML = `<span style="font-family:monospace; font-weight:600; color:#10B981;">${item.sku}</span>
                          <span style="color:#374151;"> — ${item.name}</span>`;
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
}

function onSearchInput() {
    itemIdField.value = ''; // reset selection since user is typing a new search
    renderDropdown();
}

function onSearchFocus() {
    renderDropdown();
}

function onCategoryChange() {
    searchInput.value = '';
    itemIdField.value = '';
    renderDropdown();
}

// Close dropdown when clicking outside
document.addEventListener('click', function(e) {
    if (!e.target.closest('#itemSearchInput') && !e.target.closest('#itemDropdownList')) {
        dropdownList.style.display = 'none';
    }
});

// Prevent submit if no item actually selected
document.getElementById('stockInForm').addEventListener('submit', function(e) {
    if (!itemIdField.value) {
        e.preventDefault();
        alert('Please select an item from the list.');
        searchInput.focus();
    }
});
</script>

@endsection