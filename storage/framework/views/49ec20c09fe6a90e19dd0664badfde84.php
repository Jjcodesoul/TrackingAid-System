<?php $__env->startSection('content'); ?>

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 style="font-weight:700;">Stock In</h2>
        <p style="color:#64748B;">
            Receive and record incoming inventory batches
        </p>
    </div>

</div>

<div class="card-ui">

    <form method="POST" action="/stock-in/store">
        <?php echo csrf_field(); ?>

        <div style="
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        ">

            
            <div>

                <label>Select Item (SKU)</label>

                <select name="item_id">

                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>

                        <option value="<?php echo e($item->id); ?>">

                            <?php echo e($item->sku); ?>

                            —
                            <?php echo e($item->name); ?>


                        </option>

                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>

                </select>

                <label>Quantity Added</label>
                <input
                    type="number"
                    name="quantity"
                    required
                >

                <label>Supplier / Donor</label>
                <input
                    name="supplier"
                    placeholder="Supplier or donor name"
                >

            </div>

            
            <div>

                <label>Date Received</label>

                <input
                    type="date"
                    name="date_received"
                    required
                >

                <label>Expiration Date</label>

                <input
                    type="date"
                    name="expiration_date"
                >

                <div class="card-ui"
                    style="
                        margin-top:20px;
                        background:#F0FDF4;
                    ">

                    <small style="color:#166534;">
                        Inventory Logic
                    </small>

                    <p style="
                        margin-top:10px;
                        color:#166534;
                    ">
                        Stock entries are batch-based.
                        Each stock intake is tracked separately
                        for expiration monitoring and audit logs.
                    </p>

                </div>

            </div>

        </div>

        <br>

        <button class="btn-main">
            Add Stock
        </button>

    </form>

</div>

<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH /home/oem/TrackingAid-System/resources/views/stock/create.blade.php ENDPATH**/ ?>