<?php $__env->startSection('content'); ?>


<div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:#94a3b8;">
    <a href="/inventory" style="color:#94a3b8; text-decoration:none;">TrackingAid</a>
    <span>›</span>
    <a href="/inventory" style="color:#94a3b8; text-decoration:none;">Inventory</a>
    <span>›</span>
    <span style="color:#1a202c; font-weight:600;">Add Item</span>
</div>


<div style="margin-bottom:24px;">
    <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Add Item — SKU Generator</h2>
    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Define item properties. SKU is auto-generated from your selections in real time.</p>
</div>

<?php if(session('error')): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<form method="POST" action="/inventory/store">
<?php echo csrf_field(); ?>

<div style="display:grid; grid-template-columns:1fr 380px; gap:24px; align-items:start;">

    
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">
        <h4 style="font-size:15px; font-weight:700; color:#1a202c; margin:0 0 20px;">Item Details</h4>

        
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Item Name</label>
            <input type="text" id="name" name="name" placeholder="e.g. Rice, Medical Kit, Life Vest"
                onkeyup="generateSKU()" required
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; transition:border .2s;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Category</label>
                <select id="category" name="category" onchange="generateSKU()" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="FOOD">Food</option>
                    <option value="MEDICAL">Medical Supplies</option>
                    <option value="RESCUE">Rescue Equipment</option>
                    <option value="RELIEF">Relief</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Unit Type</label>
                <select id="unit_type" name="unit_type" onchange="generateSKU()" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="BOX">Box</option>
                    <option value="PACK">Pack</option>
                    <option value="PCS">PCS</option>
                    <option value="SACK">Sack</option>
                    <option value="BOTTLE">Bottle</option>
                </select>
            </div>
        </div>

        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Size / Weight</label>
                <select id="size_weight" name="size_weight" onchange="generateSKU()"
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
                </select>
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Target Beneficiary</label>
                <select id="target_beneficiary" name="target_beneficiary" onchange="generateSKU()" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="">Select</option>
                    <option value="ADULT">Adult</option>
                    <option value="CHILD">Child</option>
                    <option value="ALL">All</option>
                </select>
            </div>
        </div>

        
        <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px; margin-bottom:18px;">
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Variant</label>
                <select id="variant" name="variant" onchange="generateSKU()"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="NONE">None</option>
                    <option value="REG">Regular</option>
                    <option value="SPICY">Spicy</option>
                    <option value="SWEET">Sweet</option>
                </select>
            </div>
            <div>
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Item Type</label>
                <select name="type" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box; background:#fff;">
                    <option value="consumable">Consumable</option>
                    <option value="returnable">Returnable</option>
                </select>
            </div>
        </div>

        
        <div style="margin-bottom:18px;">
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Storage Location</label>
            <input type="text" name="storage_location" placeholder="e.g. Warehouse A, Equipment Bay"
                required
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>

        
        <div>
            <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
            </label>
            <input type="date" name="expiration_date"
                style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
        </div>
    </div>

    
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

        
        <button type="submit"
            style="width:100%; padding:14px; background:#10B981; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.02em;">
            Save Item
        </button>

        <button type="button" onclick="clearForm()"
            style="width:100%; padding:12px; background:#fff; color:#374151; border:1px solid #e2e8f0; border-radius:10px; font-size:13px; font-weight:600; cursor:pointer;">
            Clear Form
        </button>

        <input type="hidden" name="sku" id="sku">
    </div>

</div>
</form>

<script>
function generateSKU() {
    const category = document.getElementById('category').value;
    const name = document.getElementById('name').value.replace(/\s+/g, '-').toUpperCase();
    const unit = document.getElementById('unit_type').value;
    const size = document.getElementById('size_weight').value;
    const target = document.getElementById('target_beneficiary').value;
    const variant = document.getElementById('variant').value;

    // Update breakdown
    document.getElementById('bd-category').innerText = category || '—';
    document.getElementById('bd-item').innerText = name || '—';
    document.getElementById('bd-unit').innerText = unit || '—';
    document.getElementById('bd-size').innerText = size || '—';
    document.getElementById('bd-target').innerText = target || '—';
    document.getElementById('bd-variant').innerText = (variant && variant !== 'NONE') ? variant : '—';

    // Build SKU
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
    ['bd-category','bd-item','bd-unit','bd-size','bd-target','bd-variant'].forEach(id => {
        document.getElementById(id).innerText = '—';
    });
}
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/inventory/create.blade.php ENDPATH**/ ?>