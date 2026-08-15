@extends('layouts.app')

@section('content')

{{-- BREADCRUMB --}}
<div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:#94a3b8;">
    <a href="/inventory" style="color:#94a3b8; text-decoration:none;">TrackingAid</a>
    <span>›</span>
    <a href="/inventory" style="color:#94a3b8; text-decoration:none;">Inventory</a>
    <span>›</span>
    <span style="color:#1a202c; font-weight:600;">Edit Item</span>
</div>

{{-- HEADER --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
    <div>
        <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Edit Inventory Item</h2>
        <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Update item information and SKU details</p>
    </div>
    <a href="/inventory" class="btn-secondary">Back</a>
</div>

@if(session('error'))
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

<form method="POST" action="/inventory/update/{{ $item->id }}">
@csrf

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    {{-- LEFT: FORM --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <h4 style="font-size:15px; font-weight:700; color:#1a202c; margin:0 0 20px;">Item Details</h4>

        {{-- ITEM NAME --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Item Name</label>
            <input type="text" id="name" name="name" value="{{ $item->name }}"
                onkeyup="generateSKU()" required
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- CATEGORY + UNIT TYPE --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Category</label>
                <select id="category" name="category_select" onchange="handleCategoryChange()" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="FOOD" {{ $item->category == 'FOOD' ? 'selected' : '' }}>Food</option>
                    <option value="MEDICAL" {{ $item->category == 'MEDICAL' ? 'selected' : '' }}>Medical Supplies</option>
                    <option value="RESCUE" {{ $item->category == 'RESCUE' ? 'selected' : '' }}>Rescue Equipment</option>
                    <option value="RELIEF" {{ $item->category == 'RELIEF' ? 'selected' : '' }}>Relief</option>
                    @foreach($customCategories ?? [] as $customCat)
                        <option value="{{ $customCat }}" {{ $item->category == $customCat ? 'selected' : '' }}>
                            {{ ucfirst(strtolower($customCat)) }}
                        </option>
                    @endforeach
                    <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Category</option>
                </select>

                <div id="newCategoryWrapper" style="display:none; margin-top:8px;">
                    <input type="text" id="newCategoryInput" placeholder="Type new category name"
                        oninput="handleNewCategoryInput()"
                        style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new category.</p>
                </div>

                <input type="hidden" name="category" id="categoryFinal" value="{{ $item->category }}">
            </div>

            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Unit Type</label>
                <input type="text" id="unit_type" name="unit_type" value="{{ $item->unit_type }}"
                    onkeyup="generateSKU()"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>

        {{-- SIZE + TARGET --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Size / Weight</label>
                <input type="text" id="size_weight" name="size_weight" value="{{ $item->size_weight }}"
                    onkeyup="generateSKU()"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Target Beneficiary</label>
                <input type="text" id="target_beneficiary" name="target_beneficiary" value="{{ $item->target_beneficiary }}"
                    onkeyup="generateSKU()"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
        </div>

        {{-- VARIANT + ITEM TYPE --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Variant</label>
                <input type="text" id="variant" name="variant" value="{{ $item->variant }}"
                    onkeyup="generateSKU()"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Item Type</label>
                <select name="type" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="consumable" {{ $item->type == 'consumable' ? 'selected' : '' }}>Consumable</option>
                    <option value="returnable" {{ $item->type == 'returnable' ? 'selected' : '' }}>Returnable</option>
                </select>
            </div>
        </div>

        {{-- STORAGE LOCATION --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Storage Location</label>
            <input type="text" name="storage_location" value="{{ $item->storage_location }}"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- EXPIRATION DATE --}}
        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
            </label>
            <input type="date" name="expiration_date" value="{{ $item->expiration_date ? \Carbon\Carbon::parse($item->expiration_date)->format('Y-m-d') : '' }}"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
    </div>

    {{-- RIGHT: SKU PANEL --}}
    <div style="display:flex; flex-direction:column; gap:16px; position:sticky; top:24px;">

        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
            <h4 style="font-size:13px; font-weight:700; color:#1a202c; margin:0 0 16px;">Generated SKU</h4>

            <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:16px 20px; margin-bottom:16px;">
                <p id="skuPreview" style="color:#10B981; font-weight:700; font-family:monospace; font-size:15px; margin:0; word-break:break-all; letter-spacing:.03em;">
                    {{ $item->sku }}
                </p>
            </div>

            <div style="margin-bottom:16px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Category</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Item</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Unit</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Size</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Target</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Variant</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-category">{{ $item->category ?: '—' }}</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-item">{{ $item->name ? strtoupper(str_replace(' ', '-', $item->name)) : '—' }}</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-unit">{{ $item->unit_type ?: '—' }}</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-size">{{ $item->size_weight ?: '—' }}</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-target">{{ $item->target_beneficiary ?: '—' }}</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-variant">{{ ($item->variant && strtoupper($item->variant) !== 'NONE') ? $item->variant : '—' }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <button type="button" onclick="copySKU()"
                style="width:100%; padding:10px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; font-weight:600; color:#374151; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                <i class="fa-regular fa-copy"></i>
                <span id="copyLabel">Copy SKU</span>
            </button>
        </div>

        <div style="background:#EFF6FF; border:1px solid #BFDBFE; border-radius:12px; padding:16px;">
            <div style="display:flex; align-items:flex-start; gap:10px;">
                <i class="fa-solid fa-circle-info" style="color:#3B82F6; margin-top:2px; font-size:13px;"></i>
                <div>
                    <p style="font-size:12px; font-weight:700; color:#1e40af; margin:0 0 4px;">SKU Format</p>
                    <p style="font-size:11px; color:#3B82F6; margin:0 0 4px; font-family:monospace;">CATEGORY-ITEM-UNIT-SIZE-TARGET[-VARIANT]</p>
                    <p style="font-size:11px; color:#6B7280; margin:0;">Variant segment is omitted when set to None. SKU updates automatically as you edit fields above.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-main" style="width:100%; padding:14px; font-size:14px; text-align:center;">
            Update Item
        </button>

        <a href="/inventory" class="btn-secondary" style="width:100%; padding:12px; text-align:center; box-sizing:border-box; display:block;">
            Cancel
        </a>
    </div>

</div>
</form>

<script>
function handleCategoryChange() {
    const select = document.getElementById('category');
    const wrapper = document.getElementById('newCategoryWrapper');
    const newInput = document.getElementById('newCategoryInput');

    if (select.value === '__new__') {
        wrapper.style.display = 'block';
        newInput.focus();
        document.getElementById('categoryFinal').value = '';
    } else {
        wrapper.style.display = 'none';
        newInput.value = '';
        document.getElementById('categoryFinal').value = select.value;
    }
    generateSKU();
}

function handleNewCategoryInput() {
    const newInput = document.getElementById('newCategoryInput');
    const cleaned = newInput.value.trim().toUpperCase().replace(/\s+/g, '_');
    document.getElementById('categoryFinal').value = cleaned;
    generateSKU();
}

function generateSKU() {
    const category = document.getElementById('categoryFinal').value;
    const name = document.getElementById('name').value.replace(/\s+/g, '-').toUpperCase();
    const unit = document.getElementById('unit_type').value.toUpperCase();
    const size = document.getElementById('size_weight').value.toUpperCase();
    const target = document.getElementById('target_beneficiary').value.toUpperCase();
    const variant = document.getElementById('variant').value.toUpperCase();

    document.getElementById('bd-category').innerText = category || '—';
    document.getElementById('bd-item').innerText = name || '—';
    document.getElementById('bd-unit').innerText = unit || '—';
    document.getElementById('bd-size').innerText = size || '—';
    document.getElementById('bd-target').innerText = target || '—';
    document.getElementById('bd-variant').innerText = (variant && variant !== 'NONE') ? variant : '—';

    let parts = [category, name, unit, size, target];
    if (variant && variant !== 'NONE') parts.push(variant);

    let sku = parts.filter(p => p).join('-').replace(/--+/g, '-').replace(/-$/, '');

    document.getElementById('skuPreview').innerText = sku || 'CATEGORY-ITEM-UNIT-SIZE-TARGET';
}

function copySKU() {
    const sku = document.getElementById('skuPreview').innerText;
    if (!sku) return;
    navigator.clipboard.writeText(sku).then(() => {
        const label = document.getElementById('copyLabel');
        label.innerText = 'Copied!';
        setTimeout(() => label.innerText = 'Copy SKU', 2000);
    });
}
</script>

@endsection