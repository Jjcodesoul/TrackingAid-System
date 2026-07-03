

<?php $__env->startSection('content'); ?>
<style>
    .notif-container {
        display: flex;
        flex-direction: column;
        gap: 24px;
        width: 100%;
    }

    .notif-header {
        display: flex;
        justify-content: space-between;
        align-items: flex-end;
        border-bottom: 1px solid #e2e8f0;
        padding-bottom: 16px;
    }

    .notif-title h2 {
        font-size: 24px;
        font-weight: 700;
        color: var(--text-dark);
    }

    .notif-title span {
        font-size: 14px;
        color: #718096;
    }

    .notif-list {
        display: flex;
        flex-direction: column;
        gap: 16px;
    }

    /* Beautiful empty-state design matching your application wrapper */
    .empty-notifications {
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
        padding: 60px 20px;
        background: #ffffff;
        border-radius: 12px;
        border: 1px dashed #cbd5e1;
        color: #94a3b8;
        text-align: center;
        gap: 12px;
    }

    .empty-notifications i {
        font-size: 48px;
        color: #cbd5e1;
    }

    .empty-notifications p {
        font-size: 14px;
        font-weight: 500;
        margin: 0;
    }
</style>

<div class="notif-container">
    <?php
        // Empty array because no real requests or stock drops have occurred yet
        $systemNotifications = []; 
        $unreadCount = count($systemNotifications);
    ?>

    <div class="notif-header">
        <div class="notif-title">
            <h2>Notifications</h2>
            <span><?php echo e($unreadCount); ?> unread</span>
        </div>
    </div>

    <div class="notif-list">
        <?php $__empty_1 = true; $__currentLoopData = $systemNotifications; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $notif): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <div class="empty-notifications">
                <i class="fa-regular fa-bell-slash"></i>
                <div>
                    <p>No new notifications</p>
                    <span style="font-size: 12px; color: #a0aec0;">You haven't received any logistics requests or stock alerts yet.</span>
                </div>
            </div>
        <?php endif; ?>
    </div>
</div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/notifications.blade.php ENDPATH**/ ?>