<?php $__env->startSection('content'); ?>


<div style="display:flex; align-items:center; gap:8px; margin-bottom:20px; font-size:13px; color:#94a3b8;">
    <a href="/admin/dashboard" style="color:#94a3b8; text-decoration:none;">TrackingAid</a>
    <span>›</span>
    <span style="color:#1a202c; font-weight:600;">Stock In</span>
</div>


<div style="margin-bottom:24px;">
    <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Stock In</h2>
    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Record incoming inventory from suppliers or donors</p>
</div>

<?php if(session('success')): ?>
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        <?php echo e(session('success')); ?>

    </div>
<?php endif; ?>
<?php if(session('error')): ?>
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        <?php echo e(session('error')); ?>

    </div>
<?php endif; ?>

<div style="max-width:800px;">
    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:28px;">

        <form method="POST" action="/stock-in/store">
            <?php echo csrf_field(); ?>

            
            <div style="margin-bottom:20px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Select Item (SKU)</label>
                <select name="item_id" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; background:#fff; color:#374151; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">— Select SKU —</option>
                    <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                        <option value="<?php echo e($item->id); ?>">
                            <?php echo e($item->sku); ?> — <?php echo e($item->name); ?>

                        </option>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                </select>
            </div>

            
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

            
            <div style="margin-bottom:20px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">Date Received</label>
                <input type="date" name="date_received" required
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            
            <div style="margin-bottom:28px;">
                <label style="font-size:12px; font-weight:600; color:#374151; display:block; margin-bottom:6px;">
                    Expiration Date <span style="color:#94a3b8; font-weight:400;">(optional)</span>
                </label>
                <input type="date" name="expiration_date"
                    style="width:100%; padding:10px 14px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;"
                    onfocus="this.style.borderColor='#10B981'" onblur="this.style.borderColor='#e2e8f0'">
            </div>

            
            <button type="submit"
                style="width:100%; padding:14px; background:#10B981; color:#fff; border:none; border-radius:10px; font-size:14px; font-weight:700; cursor:pointer; letter-spacing:.02em;">
                Submit Stock In
            </button>

        </form>

    </div>
</div>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/stock/create.blade.php ENDPATH**/ ?>