@extends('layouts.app')

@section('content')

{{-- BREADCRUMB --}}
<div class="breadcrumb">
    <a href="/inventory">Inventory</a>
    <span>›</span>
    <span class="current">Add Item</span>
</div>

{{-- HEADER --}}
<div style="margin-bottom:24px;">
    <h2 class="page-title">Add Item</h2>
    <p class="page-subtitle">SKU auto-generates from your selections below.</p>
</div>

@if(session('error'))
    <div class="alert-box alert-error">{{ session('error') }}</div>
@endif

<form method="POST" action="/inventory/store">
@csrf

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    {{-- LEFT: FORM --}}
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">

        {{-- ITEM TYPE SELECTOR (moved to top, drives the rest of the form) --}}
        <div style="margin-bottom:24px; padding-bottom:24px; border-bottom:1px solid #f1f5f9;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:10px;">What kind of item is this?</label>
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">

                <label id="typeCard_consumable" onclick="selectItemType('consumable')"
                    style="cursor:pointer; border:2px solid #10B981; background:#ECFDF5; border-radius:12px; padding:16px; display:flex; align-items:center; gap:12px; transition:all .15s;">
                    <input type="radio" name="type" value="consumable" checked style="display:none;">
                    <div style="width:36px; height:36px; border-radius:8px; background:#D1FAE5; color:#10B981; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-box-open"></i>
                    </div>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:700; color:#065F46;">Consumable</p>
                        <p style="margin:0; font-size:11px; color:#059669;">Food, medical supplies, etc.</p>
                    </div>
                </label>

                <label id="typeCard_returnable" onclick="selectItemType('returnable')"
                    style="cursor:pointer; border:2px solid #e2e8f0; background:#fff; border-radius:12px; padding:16px; display:flex; align-items:center; gap:12px; transition:all .15s;">
                    <input type="radio" name="type" value="returnable" style="display:none;">
                    <div style="width:36px; height:36px; border-radius:8px; background:#F1F5F9; color:#64748b; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:700; color:#374151;">Returnable</p>
                        <p style="margin:0; font-size:11px; color:#94a3b8;">Equipment, gear, vehicles, etc.</p>
                    </div>
                </label>

            </div>
        </div>

        <h4 style="font-size:15px; font-weight:700; color:#1a202c; margin:0 0 20px;">Item Details</h4>

        {{-- ITEM NAME --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Item Name</label>
            <input type="text" id="name" name="name" placeholder="e.g. Rice, Medical Kit, Life Vest"
                onkeyup="generateSKU()" required
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- CATEGORY + UNIT TYPE --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">

            {{-- CATEGORY --}}
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Category</label>
                <select id="category" onchange="handleDynamicChange('category')" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="FOOD">Food</option>
                    <option value="MEDICAL">Medical Supplies</option>
                    <option value="RESCUE">Rescue Equipment</option>
                    <option value="RELIEF">Relief</option>
                    @foreach($customCategories ?? [] as $val)
                        <option value="{{ $val }}">{{ ucfirst(strtolower($val)) }}</option>
                    @endforeach
                    <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Category</option>
                </select>
                <div id="category_newWrapper" style="display:none; margin-top:8px;">
                    <input type="text" id="category_newInput" placeholder="Type new category name"
                        oninput="handleDynamicNewInput('category')"
                        style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new category.</p>
                </div>
                <input type="hidden" name="category" id="category_final">
            </div>

            {{-- UNIT TYPE --}}
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Unit Type</label>
                <select id="unit_type" onchange="handleDynamicChange('unit_type')" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="BOX">Box</option>
                    <option value="PACK">Pack</option>
                    <option value="PCS">PCS</option>
                    <option value="SACK">Sack</option>
                    <option value="BOTTLE">Bottle</option>
                    @foreach($customUnits ?? [] as $val)
                        <option value="{{ $val }}">{{ $val }}</option>
                    @endforeach
                    <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Unit Type</option>
                </select>
                <div id="unit_type_newWrapper" style="display:none; margin-top:8px;">
                    <input type="text" id="unit_type_newInput" placeholder="Type new unit type (e.g. Drum)"
                        oninput="handleDynamicNewInput('unit_type')"
                        style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new unit type.</p>
                </div>
                <input type="hidden" name="unit_type" id="unit_type_final">
            </div>
        </div>

        {{-- SIZE + TARGET --}}
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">

            {{-- SIZE / WEIGHT (label changes based on type) --}}
            <div>
                <label id="sizeLabel" style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Size / Weight</label>
                <select id="size_weight" onchange="handleDynamicChange('size_weight')"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="SM">Small</option>
                    <option value="MD">Medium</option>
                    <option value="LG">Large</option>
                    <option value="50KG">50KG</option>
                    <option value="25KG">25KG</option>
                    <option value="500ML">500ML</option>
                    <option value="1L">1L</option>
                    <option value="REG">Regular</option>
                    @foreach($customSizes ?? [] as $val)
                        <option value="{{ $val }}">{{ $val }}</option>
                    @endforeach
                    <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Size</option>
                </select>
                <div id="size_weight_newWrapper" style="display:none; margin-top:8px;">
                    <input type="text" id="size_weight_newInput" placeholder="Type new size (e.g. 10KG)"
                        oninput="handleDynamicNewInput('size_weight')"
                        style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new size option.</p>
                </div>
                <input type="hidden" name="size_weight" id="size_weight_final">
            </div>

            {{-- TARGET BENEFICIARY --}}
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Target Beneficiary</label>
                <select id="target_beneficiary" onchange="handleDynamicChange('target_beneficiary')" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="ADULT">Adult</option>
                    <option value="CHILD">Child</option>
                    <option value="ALL">All</option>
                    @foreach($customTargets ?? [] as $val)
                        <option value="{{ $val }}">{{ ucfirst(strtolower($val)) }}</option>
                    @endforeach
                    <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Target</option>
                </select>
                <div id="target_beneficiary_newWrapper" style="display:none; margin-top:8px;">
                    <input type="text" id="target_beneficiary_newInput" placeholder="Type new target (e.g. Elderly)"
                        oninput="handleDynamicNewInput('target_beneficiary')"
                        style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                    <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new target beneficiary.</p>
                </div>
                <input type="hidden" name="target_beneficiary" id="target_beneficiary_final">
            </div>
        </div>

        {{-- VARIANT (Consumable only) --}}
        <div id="variantWrapper" style="margin-bottom:18px; overflow:hidden; transition:max-height .25s ease, opacity .25s ease; max-height:100px; opacity:1;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Variant</label>
            <select id="variant" onchange="handleDynamicChange('variant')"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                <option value="NONE">None</option>
                <option value="REG">Regular</option>
                <option value="SPICY">Spicy</option>
                <option value="SWEET">Sweet</option>
                @foreach($customVariants ?? [] as $val)
                    <option value="{{ $val }}">{{ ucfirst(strtolower($val)) }}</option>
                @endforeach
                <option value="__new__" style="font-weight:600; color:#10B981;">+ Add New Variant</option>
            </select>
            <div id="variant_newWrapper" style="display:none; margin-top:8px;">
                <input type="text" id="variant_newInput" placeholder="Type new variant (e.g. Unscented)"
                    oninput="handleDynamicNewInput('variant')"
                    style="width:100%; padding:10px 14px; border:1px solid #10B981; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
                <p style="font-size:11px; color:#94a3b8; margin:4px 0 0;">This will be added as a new variant.</p>
            </div>
            <input type="hidden" name="variant" id="variant_final" value="NONE">
        </div>

        {{-- STORAGE LOCATION --}}
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Storage Location</label>
            <input type="text" name="storage_location" placeholder="e.g. Warehouse A, Equipment Bay" required
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- EXPIRATION DATE (Consumable only) --}}
        <div id="expirationWrapper" style="overflow:hidden; transition:max-height .25s ease, opacity .25s ease; max-height:100px; opacity:1;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
            </label>
            <input type="date" name="expiration_date"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        {{-- RETURNABLE-ONLY NOTE --}}
        <div id="returnableNote" style="display:none; background:#F8FAFC; border:1px solid #e2e8f0; border-radius:10px; padding:14px 16px;">
            <p style="margin:0; font-size:12px; color:#64748b; display:flex; align-items:center; gap:8px;">
                <i class="fa-solid fa-circle-info" style="color:#94a3b8;"></i>
                This item will be tracked as returnable equipment — no expiration or flavor variant needed.
            </p>
        </div>

    </div>

    {{-- RIGHT: SKU PANEL --}}
    <div style="display:flex; flex-direction:column; gap:16px; position:sticky; top:24px;">

        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
            <h4 style="font-size:13px; font-weight:700; color:#1a202c; margin:0 0 16px;">Generated SKU</h4>

            <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:16px 20px; margin-bottom:16px;">
                <p id="skuPreview" style="color:#10B981; font-weight:700; font-family:monospace; font-size:15px; margin:0; word-break:break-all; letter-spacing:.03em;">
                    FOOD-ITEM-PACK-SM-ALL
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
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-category">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-item">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-unit">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-size">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-target">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-variant">—</td>
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
                    <p style="font-size:11px; color:#6B7280; margin:0;">Variant segment is omitted when set to None.</p>
                </div>
            </div>
        </div>

        <button type="submit" class="btn-main btn-block" style="padding:14px; font-size:14px;">
            Save Item
        </button>

        <button type="button" onclick="clearForm()" class="btn-soft btn-block" style="padding:12px; margin-top:10px;">
            Clear Form
        </button>

        <input type="hidden" name="sku" id="sku">
    </div>

</div>
</form>

<script>
function selectItemType(type) {
    const consumableCard = document.getElementById('typeCard_consumable');
    const returnableCard = document.getElementById('typeCard_returnable');
    const variantWrapper = document.getElementById('variantWrapper');
    const expirationWrapper = document.getElementById('expirationWrapper');
    const returnableNote = document.getElementById('returnableNote');
    const sizeLabel = document.getElementById('sizeLabel');

    document.querySelector(`input[name="type"][value="${type}"]`).checked = true;

    if (type === 'consumable') {
        consumableCard.style.borderColor = '#10B981';
        consumableCard.style.background = '#ECFDF5';
        returnableCard.style.borderColor = '#e2e8f0';
        returnableCard.style.background = '#fff';

        variantWrapper.style.maxHeight = '100px';
        variantWrapper.style.opacity = '1';
        expirationWrapper.style.maxHeight = '100px';
        expirationWrapper.style.opacity = '1';
        returnableNote.style.display = 'none';
        sizeLabel.innerText = 'Size / Weight';
    } else {
        returnableCard.style.borderColor = '#10B981';
        returnableCard.style.background = '#ECFDF5';
        consumableCard.style.borderColor = '#e2e8f0';
        consumableCard.style.background = '#fff';

        variantWrapper.style.maxHeight = '0px';
        variantWrapper.style.opacity = '0';
        expirationWrapper.style.maxHeight = '0px';
        expirationWrapper.style.opacity = '0';
        returnableNote.style.display = 'block';
        sizeLabel.innerText = 'Size';

        // Reset variant to NONE since it's hidden for returnable items
        document.getElementById('variant').value = 'NONE';
        document.getElementById('variant_final').value = 'NONE';
    }

    generateSKU();
}

function handleDynamicChange(field) {
    const select = document.getElementById(field);
    const wrapper = document.getElementById(field + '_newWrapper');
    const newInput = document.getElementById(field + '_newInput');
    const finalInput = document.getElementById(field + '_final');

    if (select.value === '__new__') {
        wrapper.style.display = 'block';
        newInput.focus();
        finalInput.value = '';
    } else {
        wrapper.style.display = 'none';
        newInput.value = '';
        finalInput.value = select.value;
    }
    generateSKU();
}

function handleDynamicNewInput(field) {
    const newInput = document.getElementById(field + '_newInput');
    const finalInput = document.getElementById(field + '_final');
    const cleaned = newInput.value.trim().toUpperCase().replace(/\s+/g, '_');
    finalInput.value = cleaned;
    generateSKU();
}

function generateSKU() {
    const category = document.getElementById('category_final').value;
    const name = document.getElementById('name').value.replace(/\s+/g, '-').toUpperCase();
    const unit = document.getElementById('unit_type_final').value;
    const size = document.getElementById('size_weight_final').value;
    const target = document.getElementById('target_beneficiary_final').value;
    const variant = document.getElementById('variant_final').value;

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
    document.getElementById('sku').value = sku;
}

function copySKU() {
    const sku = document.getElementById('sku').value;
    if (!sku) return;
    navigator.clipboard.writeText(sku).then(() => {
        const label = document.getElementById('copyLabel');
        label.innerText = 'Copied!';
        setTimeout(() => label.innerText = 'Copy SKU', 2000);
    });
}

function clearForm() {
    document.querySelector('form').reset();
    document.getElementById('skuPreview').innerText = 'FOOD-ITEM-PACK-SM-ALL';
    document.getElementById('sku').value = '';

    ['category', 'unit_type', 'size_weight', 'target_beneficiary', 'variant'].forEach(field => {
        document.getElementById(field + '_final').value = field === 'variant' ? 'NONE' : '';
        document.getElementById(field + '_newWrapper').style.display = 'none';
        document.getElementById(field + '_newInput').value = '';
    });

    ['bd-category','bd-item','bd-unit','bd-size','bd-target','bd-variant'].forEach(id => {
        document.getElementById(id).innerText = '—';
    });

    selectItemType('consumable');
}
</script>

@endsection