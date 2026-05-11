<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 style="font-weight:700;">Inventory Dashboard</h2>

        <p style="color:#64748B;">
            Manage rescue and relief inventory
        </p>
    </div>

    <button
        class="btn-main"
        data-bs-toggle="modal"
        data-bs-target="#addModal">

        + Add Item

    </button>

</div>


<div class="grid mb-4">

    <div class="card-ui">
        <h5>Total Items</h5>
        <h2><?php echo e($items->count()); ?></h2>
    </div>

    <div class="card-ui">
        <h5>Low Stock</h5>

        <h2>
            <?php echo e($items->filter(fn($i) => $i->total_stock < 10)->count()); ?>

        </h2>
    </div>

    <div class="card-ui">
        <h5>Out of Stock</h5>

        <h2>
            <?php echo e($items->filter(fn($i) => $i->total_stock <= 0)->count()); ?>

        </h2>
    </div>

</div>


<div class="card-ui">

    <div style="overflow-x:auto;">

        <table>

            <thead>

                <tr>
                    <th>Item</th>
                    <th>SKU</th>
                    <th>Category</th>
                    <th>Stock</th>
                    <th>Status</th>
                    <th>Location</th>
                    <th width="180">Actions</th>
                </tr>

            </thead>

            <tbody>

            <?php $__empty_1 = true; $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>

                <tr>

                    <td><?php echo e($item->name); ?></td>

                    <td>

                        <small style="font-weight:600;">
                            <?php echo e($item->sku); ?>

                        </small>

                    </td>

                    <td><?php echo e($item->category); ?></td>

                    <td><?php echo e($item->total_stock); ?></td>

                    <td>

                        <?php if($item->total_stock <= 0): ?>

                            <span class="badge-danger">
                                Out of Stock
                            </span>

                        <?php elseif($item->total_stock < 10): ?>

                            <span class="badge-warning">
                                Low Stock
                            </span>

                        <?php else: ?>

                            <span class="badge-success">
                                In Stock
                            </span>

                        <?php endif; ?>

                    </td>

                    <td><?php echo e($item->storage_location); ?></td>

                    <td>

                        <div style="display:flex;gap:10px;">

                            <a
                                href="/inventory/edit/<?php echo e($item->id); ?>"
                                class="btn-edit">

                                Edit

                            </a>

                            <form
                                method="POST"
                                action="/inventory/delete/<?php echo e($item->id); ?>"
                                onsubmit="return confirm('Delete this item?')">

                                <?php echo csrf_field(); ?>

                                <button
                                    type="submit"
                                    class="btn-danger-custom">

                                    Delete

                                </button>

                            </form>

                        </div>

                    </td>

                </tr>

            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>

                <tr>

                    <td colspan="7"
                        style="
                            text-align:center;
                            padding:30px;
                            color:#64748B;
                        ">

                        No inventory items found.

                    </td>

                </tr>

            <?php endif; ?>

            </tbody>

        </table>

    </div>

</div>


<div class="modal fade" id="addModal" tabindex="-1">

    <div class="modal-dialog modal-lg">

        <div class="modal-content">

            <form method="POST" action="/inventory/store">

                <?php echo csrf_field(); ?>

                <div class="modal-header">

                    <h4 style="font-weight:700;">
                        Add Item (SKU Generator)
                    </h4>

                    <button
                        type="button"
                        class="btn-close"
                        data-bs-dismiss="modal">
                    </button>

                </div>

                <div class="modal-body">

                    <div style="
                        display:grid;
                        grid-template-columns:1fr 1fr;
                        gap:20px;
                    ">

                        
                        <div>

                            <label>Item Name</label>

                            <input
                                type="text"
                                id="name"
                                name="name"
                                onkeyup="generateSKU()"
                                required
                            >

                            <label>Category</label>

                            <select
                                id="category"
                                name="category"
                                onchange="generateSKU()"
                                required>

                                <option value="">
                                    Select
                                </option>

                                <option value="FOOD">
                                    FOOD
                                </option>

                                <option value="MEDICAL">
                                    MEDICAL
                                </option>

                                <option value="RESCUE">
                                    RESCUE
                                </option>

                                <option value="RELIEF">
                                    RELIEF
                                </option>

                            </select>

                            <label>Unit Type</label>

                            <select
                                id="unit_type"
                                name="unit_type"
                                onchange="generateSKU()"
                                required>

                                <option value="">
                                    Select
                                </option>

                                <option value="BOX">
                                    BOX
                                </option>

                                <option value="PACK">
                                    PACK
                                </option>

                                <option value="PCS">
                                    PCS
                                </option>

                                <option value="SACK">
                                    SACK
                                </option>

                                <option value="BOTTLE">
                                    BOTTLE
                                </option>

                            </select>

                            <label>Size / Weight</label>

                            <input
                                type="text"
                                id="size_weight"
                                name="size_weight"
                                placeholder="50KG / 500ML / SMALL"
                                onkeyup="generateSKU()"
                            >

                        </div>

                        
                        <div>

                            <label>Target Beneficiary</label>

                            <select
                                id="target_beneficiary"
                                name="target_beneficiary"
                                onchange="generateSKU()"
                                required>

                                <option value="">
                                    Select
                                </option>

                                <option value="ADULT">
                                    ADULT
                                </option>

                                <option value="CHILD">
                                    CHILD
                                </option>

                                <option value="ALL">
                                    ALL
                                </option>

                            </select>

                            <label>Variant</label>

                            <input
                                type="text"
                                id="variant"
                                name="variant"
                                placeholder="REGULAR / SPICY / NONE"
                                onkeyup="generateSKU()"
                            >

                            <label>Item Type</label>

                            <select name="type" required>

                                <option value="consumable">
                                    Consumable
                                </option>

                                <option value="returnable">
                                    Returnable
                                </option>

                            </select>

                            <label>Storage Location</label>

                            <input
                                type="text"
                                name="storage_location"
                                placeholder="Warehouse A"
                                required
                            >

                        </div>

                    </div>

                    <hr>

                    
                    <div class="card-ui"
                        style="
                            background:#ECFDF5;
                            border:1px solid #A7F3D0;
                        ">

                        <small style="color:#166534;">
                            Auto Generated SKU
                        </small>

                        <h3
                            id="skuPreview"
                            style="
                                color:#10B981;
                                margin-top:10px;
                                font-weight:700;
                                word-break:break-word;
                            ">

                            CATEGORY-ITEM-UNIT-SIZE-TARGET-VARIANT

                        </h3>

                    </div>

                    
                    <input
                        type="hidden"
                        name="sku"
                        id="sku"
                    >

                </div>

                <div class="modal-footer">

                    <button
                        type="submit"
                        class="btn-main">

                        Save Item

                    </button>

                </div>

            </form>

        </div>

    </div>

</div>


<script>

function generateSKU() {

    let category =
        document.getElementById('category').value;

    let name =
        document.getElementById('name').value;

    let unit =
        document.getElementById('unit_type').value;

    let size =
        document.getElementById('size_weight').value;

    let target =
        document.getElementById('target_beneficiary').value;

    let variant =
        document.getElementById('variant').value;

    // FORMAT VALUES
    name = name.replace(/\s+/g, '-').toUpperCase();

    size = size.replace(/\s+/g, '').toUpperCase();

    variant = variant.replace(/\s+/g, '-').toUpperCase();

    // GENERATE SKU
    let sku =
        `${category}-${name}-${unit}-${size}-${target}-${variant}`;

    // REMOVE DOUBLE DASH
    sku = sku.replace(/--+/g, '-');

    // REMOVE ENDING DASH
    sku = sku.replace(/-$/, '');

    // SHOW SKU
    document.getElementById('skuPreview').innerText = sku;

    // SAVE TO HIDDEN INPUT
    document.getElementById('sku').value = sku;
}

</script>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/oem/TrackingAid-System/resources/views/inventory/index.blade.php ENDPATH**/ ?>