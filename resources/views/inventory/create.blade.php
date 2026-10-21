@extends('layouts.app')

@section('content')
<style>
    .paginated-select { position: relative; width: 100%; }
    .paginated-select-native { position: absolute !important; width: 1px !important; height: 1px !important; padding: 0 !important; margin: -1px !important; overflow: hidden !important; clip: rect(0, 0, 0, 0) !important; white-space: nowrap !important; border: 0 !important; }
    .paginated-select-trigger { width: 100%; min-height: 42px; display: flex; align-items: center; justify-content: space-between; gap: 12px; padding: 10px 14px; border: 1px solid #bdcbc5; border-radius: 8px; background: #fff; color: #1f2937; font: inherit; font-size: 13px; text-align: left; cursor: pointer; }
    .paginated-select-trigger:hover { border-color: #176b57; }
    .paginated-select-trigger:focus { outline: 0; border-color: #10b981; box-shadow: 0 0 0 3px rgb(16 185 129 / 15%); }
    .paginated-select-trigger[aria-invalid="true"] { border-color: #dc2626; }
    .paginated-select-panel { position: absolute; z-index: 40; top: calc(100% + 6px); left: 0; width: 100%; padding: 8px; border: 1px solid #dbe4ee; border-radius: 10px; background: #fff; box-shadow: 0 14px 32px rgb(15 23 42 / 16%); }
    .paginated-select-panel.is-above { top: auto; bottom: calc(100% + 6px); }
    .paginated-select-search { width: 100%; min-height: 38px; margin-bottom: 7px; padding: 8px 10px; border: 1px solid #d1d5db; border-radius: 7px; font: inherit; font-size: 13px; box-sizing: border-box; }
    .paginated-select-options { display: grid; gap: 2px; max-height: 256px; overflow-y: auto; }
    .paginated-select-option { width: 100%; min-height: 34px; padding: 7px 9px; border: 0; border-radius: 6px; background: #fff; color: #334155; font: inherit; font-size: 13px; text-align: left; cursor: pointer; }
    .paginated-select-option:hover, .paginated-select-option[aria-selected="true"] { background: #eff8f5; color: #075e49; }
    .paginated-select-option.is-add { color: #087f5b; font-weight: 700; }
    .paginated-select-empty { padding: 12px 8px; color: #64748b; font-size: 12px; }
    .paginated-select-pager { display: flex; align-items: center; justify-content: space-between; gap: 8px; padding-top: 8px; margin-top: 7px; border-top: 1px solid #edf1f4; }
    .paginated-select-page-button { min-height: 30px; padding: 5px 9px; border: 1px solid #dbe4ee; border-radius: 6px; background: #fff; color: #334155; font: inherit; font-size: 12px; cursor: pointer; }
    .paginated-select-page-button:disabled { opacity: .45; cursor: not-allowed; }
    .paginated-select-page-label { color: #64748b; font-size: 11px; }
    .paginated-select-error { display: none; margin-top: 5px; color: #b91c1c; font-size: 11px; }
    .paginated-select-error.is-visible { display: block; }
</style>

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
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif
@if($errors->any())
    <div class="alert-box alert-warning" role="alert">
        <ul class="m-0 pl-5">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form method="POST" action="/inventory/store" class="inventory-form">
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

                <label id="typeCard_returnable" onclick="selectItemType('reusable')"
                    style="cursor:pointer; border:2px solid #e2e8f0; background:#fff; border-radius:12px; padding:16px; display:flex; align-items:center; gap:12px; transition:all .15s;">
                    <input type="radio" name="type" value="reusable" style="display:none;">
                    <div style="width:36px; height:36px; border-radius:8px; background:#F1F5F9; color:#64748b; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
                        <i class="fa-solid fa-arrow-rotate-left"></i>
                    </div>
                    <div>
                        <p style="margin:0; font-size:13px; font-weight:700; color:#374151;">Reusable</p>
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
                This reusable equipment will be tracked for return after release.
            </p>
        </div>

    </div>

    {{-- RIGHT: SKU PANEL --}}
    <div style="display:flex; flex-direction:column; gap:16px; position:sticky; top:24px;">

        <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
            <h4 style="font-size:13px; font-weight:700; color:#1a202c; margin:0 0 16px;">Compact SKU preview</h4>

            <div style="background:#ECFDF5; border:1px solid #A7F3D0; border-radius:10px; padding:16px 20px; margin-bottom:16px;">
                <p id="skuPreview" style="color:#10B981; font-weight:700; font-family:monospace; font-size:20px; margin:0; letter-spacing:.06em;">
                    Generated on save
                </p>
            </div>

            <div style="margin-bottom:16px;">
                <table style="width:100%; border-collapse:collapse;">
                    <thead>
                        <tr style="border-bottom:1px solid #f1f5f9;">
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Category</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Item</th>
                            <th style="padding:6px 8px; font-size:10px; font-weight:700; color:#94a3b8; text-transform:uppercase; text-align:left;">Details</th>
                        </tr>
                    </thead>
                    <tbody>
                        <tr>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-category">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-item">—</td>
                            <td style="padding:8px; font-size:12px; font-weight:700; color:#1a202c;" id="bd-details">—</td>
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
                    <p style="font-size:11px; color:#3B82F6; margin:0 0 4px; font-family:monospace;">CATEGORY + ITEM + SIZE + TYPE + VARIANT + SEQUENCE</p>
                    <p style="font-size:11px; color:#6B7280; margin:0;">Short readable codes with no dashes. Example: Rice (50 kg) is FOORICE50KGCST01. Quantity is tracked separately.</p>
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
const paginatedSelectPageSize = 8;

function setupPaginatedSelect(select) {
    if (select.dataset.paginated === 'true') return;

    const label = select.parentElement.querySelector('label')?.textContent.trim() || 'Choose an option';
    const wrapper = document.createElement('div');
    wrapper.className = 'paginated-select';
    select.parentNode.insertBefore(wrapper, select);
    wrapper.appendChild(select);

    select.dataset.paginated = 'true';
    select.classList.add('paginated-select-native');
    select.tabIndex = -1;
    select.removeAttribute('required');
    select.setAttribute('aria-hidden', 'true');

    const trigger = document.createElement('button');
    trigger.type = 'button';
    trigger.className = 'paginated-select-trigger';
    trigger.setAttribute('aria-haspopup', 'listbox');
    trigger.setAttribute('aria-expanded', 'false');
    trigger.setAttribute('aria-label', label);

    const currentValue = document.createElement('span');
    currentValue.className = 'paginated-select-value';
    const chevron = document.createElement('i');
    chevron.className = 'fa-solid fa-chevron-down';
    chevron.setAttribute('aria-hidden', 'true');
    trigger.append(currentValue, chevron);

    const panel = document.createElement('div');
    panel.className = 'paginated-select-panel';
    panel.hidden = true;

    const search = document.createElement('input');
    search.type = 'search';
    search.className = 'paginated-select-search';
    search.placeholder = `Search ${label.toLowerCase()}...`;
    search.setAttribute('aria-label', `Search ${label.toLowerCase()}`);

    const options = document.createElement('div');
    options.className = 'paginated-select-options';
    options.setAttribute('role', 'listbox');

    const pager = document.createElement('div');
    pager.className = 'paginated-select-pager';
    const previous = document.createElement('button');
    previous.type = 'button';
    previous.className = 'paginated-select-page-button';
    previous.textContent = 'Previous';
    const pageLabel = document.createElement('span');
    pageLabel.className = 'paginated-select-page-label';
    pageLabel.setAttribute('aria-live', 'polite');
    const next = document.createElement('button');
    next.type = 'button';
    next.className = 'paginated-select-page-button';
    next.textContent = 'Next';
    pager.append(previous, pageLabel, next);
    panel.append(search, options, pager);

    const error = document.createElement('div');
    error.className = 'paginated-select-error';
    error.textContent = `Choose ${label.toLowerCase()} to continue.`;
    error.setAttribute('role', 'alert');
    wrapper.append(trigger, panel, error);

    let page = 0;

    const filteredOptions = () => Array.from(select.options).filter((option) => {
        const query = search.value.trim().toLowerCase();
        return !option.disabled && `${option.textContent} ${option.value}`.toLowerCase().includes(query);
    });

    const updateValue = () => {
        const selected = select.options[select.selectedIndex];
        currentValue.textContent = selected?.textContent.trim() || 'Select';
        trigger.setAttribute('aria-invalid', error.classList.contains('is-visible') ? 'true' : 'false');
        if (select.value && select.value !== '__new__') error.classList.remove('is-visible');
    };

    const renderOptions = () => {
        const matches = filteredOptions();
        const pageCount = Math.max(1, Math.ceil(matches.length / paginatedSelectPageSize));
        page = Math.min(page, pageCount - 1);
        options.replaceChildren();

        matches.slice(page * paginatedSelectPageSize, (page + 1) * paginatedSelectPageSize).forEach((option) => {
            const optionButton = document.createElement('button');
            optionButton.type = 'button';
            optionButton.className = `paginated-select-option${option.value === '__new__' ? ' is-add' : ''}`;
            optionButton.textContent = option.textContent.trim();
            optionButton.setAttribute('role', 'option');
            optionButton.setAttribute('aria-selected', String(option.value === select.value));
            optionButton.addEventListener('click', () => {
                select.value = option.value;
                select.dispatchEvent(new Event('change', { bubbles: true }));
                updateValue();
                panel.hidden = true;
                trigger.setAttribute('aria-expanded', 'false');
                trigger.focus();
            });
            options.appendChild(optionButton);
        });

        if (matches.length === 0) {
            const empty = document.createElement('div');
            empty.className = 'paginated-select-empty';
            empty.textContent = 'No matching options.';
            options.appendChild(empty);
        }

        pageLabel.textContent = `Page ${page + 1} of ${pageCount}`;
        previous.disabled = page === 0;
        next.disabled = page >= pageCount - 1;
    };

    const close = () => {
        panel.hidden = true;
        trigger.setAttribute('aria-expanded', 'false');
        panel.classList.remove('is-above');
    };

    const open = () => {
        if (!panel.hidden) return;
        panel.hidden = false;
        trigger.setAttribute('aria-expanded', 'true');
        panel.classList.toggle('is-above', window.innerHeight - wrapper.getBoundingClientRect().bottom < 330);
        search.value = '';
        page = 0;
        renderOptions();
        search.focus();
    };

    trigger.addEventListener('click', () => {
        if (panel.hidden) open();
        else close();
    });
    search.addEventListener('input', () => { page = 0; renderOptions(); });
    previous.addEventListener('click', () => { page--; renderOptions(); });
    next.addEventListener('click', () => { page++; renderOptions(); });
    select.addEventListener('change', () => { updateValue(); renderOptions(); });
    panel.addEventListener('keydown', (event) => {
        if (event.key === 'Escape') {
            close();
            trigger.focus();
        }
    });
    document.addEventListener('click', (event) => {
        if (!wrapper.contains(event.target)) close();
    });

    select.dataset.pagerWrapper = 'true';
    select._paginatedSelect = { trigger, error, updateValue, renderOptions, open, close };
    updateValue();
}

function syncPaginatedSelects() {
    document.querySelectorAll('.inventory-form select').forEach((select) => {
        if (!select._paginatedSelect) setupPaginatedSelect(select);
        select._paginatedSelect?.updateValue();
        select._paginatedSelect?.renderOptions();
    });
}

syncPaginatedSelects();

document.querySelector('.inventory-form').addEventListener('submit', (event) => {
    const requiredFields = ['category', 'unit_type', 'target_beneficiary'];
    for (const field of requiredFields) {
        const finalInput = document.getElementById(`${field}_final`);
        const select = document.getElementById(field);
        if (finalInput.value.trim()) continue;

        const control = select._paginatedSelect;
        control.error.classList.add('is-visible');
        control.trigger.setAttribute('aria-invalid', 'true');
        control.open();
        event.preventDefault();
        return;
    }
});

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
        document.getElementById('variant')._paginatedSelect?.updateValue();
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
    if (cleaned) {
        const control = document.getElementById(field)._paginatedSelect;
        control?.error.classList.remove('is-visible');
        control?.trigger.setAttribute('aria-invalid', 'false');
    }
    generateSKU();
}

function generateSKU() {
    const category = document.getElementById('category_final').value;
    const rawName = document.getElementById('name').value.trim();
    const categoryCodes = { FOOD: 'FOO', MEDICAL: 'MED', RESCUE: 'RES', RELIEF: 'REL', HYGIENE: 'HYG', SHELTER: 'SHE', COMMUNICATIONS: 'COM' };
    const categoryKey = category.trim().toUpperCase();
    const categoryCode = categoryCodes[categoryKey] || (categoryKey.replace(/[^A-Z0-9]/g, '').slice(0, 3) || 'GEN');
    const variantValue = document.getElementById('variant_final').value;
    const targetValue = document.getElementById('target_beneficiary_final').value;
    const selectedType = document.querySelector('input[name="type"]:checked')?.value;
    const preview = document.getElementById('skuPreview');
    const sizeValue = document.getElementById('size_weight_final').value;

    document.getElementById('bd-category').innerText = categoryCode || '—';
    document.getElementById('bd-item').innerText = rawName || '—';
    document.getElementById('bd-details').innerText = [document.getElementById('size_weight_final').value, variantValue, targetValue]
        .filter(value => value && value.toUpperCase() !== 'NONE').join(' · ') || '—';
    if (!category || !rawName) {
        preview.innerText = 'Generated on save';
        document.getElementById('sku').value = '';
        return;
    }

    const words = rawName.toUpperCase().match(/[A-Z0-9]+/g) || [];
    const itemWords = words.filter(word => !/^\d+(ML|L|KG|G|CM|M|PCS|CT)$/.test(word));
    const itemCode = itemWords.length > 1
        ? `${itemWords[0].slice(0, 3)}${itemWords[1].slice(0, 2)}`
        : (itemWords[0] || 'ITEM').slice(0, 4);
    const compact = (value, length, fallback) => {
        const cleaned = (value || '').trim().toUpperCase().replace(/[^A-Z0-9]/g, '');
        return (cleaned && cleaned !== 'NONE' ? cleaned : fallback).slice(0, length);
    };
    const typeCode = selectedType === 'reusable' ? 'R' : 'C';
    const sku = `${categoryCode}${itemCode}${compact(sizeValue, 5, 'STD')}${typeCode}${compact(variantValue, 2, 'ST')}01`;
    preview.innerText = sku;
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
    document.getElementById('skuPreview').innerText = 'Generated on save';
    document.getElementById('sku').value = '';

    ['category', 'unit_type', 'size_weight', 'target_beneficiary', 'variant'].forEach(field => {
        document.getElementById(field + '_final').value = field === 'variant' ? 'NONE' : '';
        document.getElementById(field + '_newWrapper').style.display = 'none';
        document.getElementById(field + '_newInput').value = '';
    });

    ['bd-category','bd-item','bd-details'].forEach(id => {
        document.getElementById(id).innerText = '—';
    });

    selectItemType('consumable');
    syncPaginatedSelects();
}
</script>

@endsection
