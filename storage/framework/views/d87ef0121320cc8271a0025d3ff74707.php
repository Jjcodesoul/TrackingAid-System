<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 style="font-weight:700;">Edit Inventory Item</h2>
        <p style="color:#64748B;">
            Update item information and SKU details
        </p>
    </div>

    <a href="/inventory" class="btn btn-secondary">
        Back
    </a>

</div>

<div class="card-ui">

    <form method="POST" action="/inventory/update/<?php echo e($item->id); ?>">
        <?php echo csrf_field(); ?>

        <div style="
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        ">

            
            <div>

                <label>Item Name</label>
                <input
                    name="name"
                    value="<?php echo e($item->name); ?>"
                    required
                >

                <label>Category</label>
                <select name="category">

                    <option <?php echo e($item->category == 'Food' ? 'selected' : ''); ?>>
                        Food
                    </option>

                    <option <?php echo e($item->category == 'Medical' ? 'selected' : ''); ?>>
                        Medical
                    </option>

                    <option <?php echo e($item->category == 'Rescue' ? 'selected' : ''); ?>>
                        Rescue
                    </option>

                    <option <?php echo e($item->category == 'Relief' ? 'selected' : ''); ?>>
                        Relief
                    </option>

                </select>

                <label>Unit Type</label>
                <input
                    name="unit_type"
                    value="<?php echo e($item->unit_type); ?>"
                >

                <label>Size / Weight</label>
                <input
                    name="size_weight"
                    value="<?php echo e($item->size_weight); ?>"
                >

            </div>

            
            <div>

                <label>Target Beneficiary</label>
                <input
                    name="target_beneficiary"
                    value="<?php echo e($item->target_beneficiary); ?>"
                >

                <label>Variant</label>
                <input
                    name="variant"
                    value="<?php echo e($item->variant); ?>"
                >

                <label>Item Type</label>
                <select name="type">

    <option
        value="consumable"
        <?php echo e($item->type == 'consumable' ? 'selected' : ''); ?>>

        Consumable

    </option>

    <option
        value="returnable"
        <?php echo e($item->type == 'returnable' ? 'selected' : ''); ?>>

        Returnable

    </option>

</select>
                <label>Storage Location</label>
                <input
                    name="storage_location"
                    value="<?php echo e($item->storage_location); ?>"
                >

            </div>

        </div>

        <hr style="margin:25px 0;">

        
        <div class="card-ui" style="background:#ECFDF5;">

            <small style="color:#065F46;">Generated SKU</small>

            <h4 style="
                color:#10B981;
                margin-top:8px;
                font-weight:700;
            ">
                <?php echo e($item->sku); ?>

            </h4>

        </div>

        <br>

        <button class="btn-main">
            Update Item
        </button>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/oem/TrackingAid-System/resources/views/inventory/edit.blade.php ENDPATH**/ ?>