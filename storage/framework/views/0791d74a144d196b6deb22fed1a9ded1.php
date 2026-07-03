<?php $__env->startSection('title', 'Return Management'); ?>

<?php $__env->startSection('content'); ?>
    <h1 class="page-title">Return Management</h1>
    <div class="page-subtitle">Process returns of borrowed items</div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Returnable Items</div>
                <div class="display-6 fw-bold"><?php echo e($items->count()); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Good Returns</div>
                <div class="display-6 fw-bold"><?php echo e($recentReturns->where('condition', 'Good')->count()); ?></div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Recent Returns</div>
                <div class="display-6 fw-bold"><?php echo e($recentReturns->count()); ?></div>
            </div>
        </div>
    </div>

    <section class="panel">
        <form action="<?php echo e(route('returns.store')); ?>" method="POST">
            <?php echo csrf_field(); ?>

            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="form-label" for="inventory_id">Item (SKU) *</label>
                    <select id="inventory_id" name="inventory_id" class="form-select" required>
                        <option value="">Choose an item...</option>
                        <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($item->id); ?>" <?php echo e(old('inventory_id') == $item->id ? 'selected' : ''); ?>>
                                <?php echo e($item->sku); ?> - <?php echo e($item->name); ?> (Current: <?php echo e($item->quantity); ?>)
                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="quantity">Quantity *</label>
                    <input id="quantity" name="quantity" type="number" min="1" class="form-control" value="<?php echo e(old('quantity')); ?>" placeholder="e.g., 30" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="condition">Condition *</label>
                    <select id="condition" name="condition" class="form-select" required>
                        <?php $__currentLoopData = ['Good', 'Damaged', 'Missing']; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $condition): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <option value="<?php echo e($condition); ?>" <?php echo e(old('condition', 'Good') === $condition ? 'selected' : ''); ?>>
                                <?php echo e($condition); ?>

                            </option>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Add any notes about the return..."><?php echo e(old('notes')); ?></textarea>
                </div>
            </div>

            <div class="note mt-4">
                <strong>Note:</strong> Items marked as "Damaged" will be quarantined for inspection. Items marked as "Missing" will be logged but not added back to inventory.
            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <button type="submit" class="btn btn-main">Process Return</button>
                <button type="reset" class="btn btn-soft">Clear Form</button>
            </div>
        </form>
    </section>

    <section class="panel mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Return Activity</h5>
            <span class="text-muted small">Latest 5 records</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $__empty_1 = true; $__currentLoopData = $recentReturns; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $return): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <tr>
                            <td><?php echo e($return->inventory->sku ?? 'N/A'); ?></td>
                            <td><?php echo e(number_format($return->quantity)); ?></td>
                            <td><?php echo e($return->condition); ?></td>
                            <td><?php echo e($return->notes ?: '—'); ?></td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No recent returns recorded yet.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/returns/create.blade.php ENDPATH**/ ?>