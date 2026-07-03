<?php $__env->startSection('content'); ?>


<div style="margin-bottom:24px;">
    <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Dashboard</h2>
    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">System overview — TrackingAid × ResqOperation</p>
</div>


<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:20px;">

    <a href="/inventory" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Total Inventory Stock</p>
            <h3 style="font-size:32px; font-weight:700; color:#1a202c; margin:0 0 4px;"><?php echo e(number_format($totalStock)); ?></h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">Across all warehouses</p>
        </div>
        <div style="background:#EBF8FF; color:#3182ce; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid fa-boxes-stacked"></i>
        </div>
    </a>

    <a href="/requests" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Active Requests</p>
            <h3 style="font-size:32px; font-weight:700; color:#1a202c; margin:0 0 4px;"><?php echo e($activeRequests); ?></h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">From ResqOperation</p>
        </div>
        <div style="background:#F0FFF4; color:#38a169; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid fa-list-check"></i>
        </div>
    </a>

    <a href="/requests" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Approved Requests</p>
            <h3 style="font-size:32px; font-weight:700; color:#1a202c; margin:0 0 4px;"><?php echo e($approvedRequests); ?></h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">This month</p>
        </div>
        <div style="background:#F0FFF4; color:#38a169; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-regular fa-circle-check"></i>
        </div>
    </a>

</div>


<div style="display:grid; grid-template-columns:repeat(3,1fr); gap:20px; margin-bottom:24px;">

    <a href="/inventory" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #FED7AA; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Low Stock Alerts</p>
            <h3 style="font-size:32px; font-weight:700; color:#EA580C; margin:0 0 4px;"><?php echo e($lowStockAlerts); ?></h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">Below minimum threshold</p>
        </div>
        <div style="background:#FFF7ED; color:#EA580C; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid fa-triangle-exclamation"></i>
        </div>
    </a>

    <a href="/inventory" style="text-decoration:none; color:inherit; background:#fff; border:1px solid #FDE68A; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start; transition:box-shadow .15s;" onmouseover="this.style.boxShadow='0 4px 16px rgba(0,0,0,0.08)'" onmouseout="this.style.boxShadow='none'">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Expiring Items</p>
            <h3 style="font-size:32px; font-weight:700; color:#D97706; margin:0 0 4px;"><?php echo e($expiringItems); ?></h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">Within 7 days</p>
        </div>
        <div style="background:#FFFBEB; color:#D97706; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-regular fa-clock"></i>
        </div>
    </a>

    <div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; display:flex; justify-content:space-between; align-items:flex-start;">
        <div>
            <p style="font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em; margin:0 0 8px;">Completed Deliveries</p>
            <h3 style="font-size:32px; font-weight:700; color:#1a202c; margin:0 0 4px;">0</h3>
            <p style="font-size:12px; color:#94a3b8; margin:0;">This month</p>
        </div>
        <div style="background:#EBF8FF; color:#3182ce; width:40px; height:40px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0;">
            <i class="fa-solid fa-truck"></i>
        </div>
    </div>

</div>


<div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px; margin-bottom:20px;">
    <h4 style="font-size:14px; font-weight:700; color:#1a202c; margin:0 0 20px;">Inventory Trend — Stock In vs. Stock Out</h4>
    <canvas id="trendChart" style="max-height:200px;"></canvas>
</div>


<div style="display:grid; grid-template-columns:1fr 380px; gap:20px;">

<div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
    <h4 style="font-size:14px; font-weight:700; color:#1a202c; margin:0 0 20px;">Activity Feed</h4>
    <div style="display:flex; flex-direction:column; gap:14px;">
        <?php $__empty_1 = true; $__currentLoopData = $activities; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $activity): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
            <?php
                $dotColor = match($activity->status) {
                    'Approved' => '#10B981',
                    'Rejected' => '#EF4444',
                    default    => '#F59E0B',
                };
            ?>
            <div style="display:flex; align-items:flex-start; gap:12px;">
                <div style="width:8px; height:8px; border-radius:999px; background:<?php echo e($dotColor); ?>; margin-top:5px; flex-shrink:0;"></div>
                <div>
                    <p style="font-size:13px; color:#1a202c; margin:0 0 2px;">
                        <?php echo e($activity->request_code); ?> — <?php echo e($activity->status); ?> —
                        <?php echo e($activity->quantity); ?> <?php echo e($activity->inventory?->unit_type ?? 'units'); ?>

                        of <?php echo e($activity->inventory?->name ?? 'item'); ?>

                        <?php if($activity->source): ?> — <?php echo e($activity->source); ?> <?php endif; ?>
                    </p>
                    <p style="font-size:11px; color:#94a3b8; margin:0;">
                        <?php echo e(\Carbon\Carbon::parse($activity->updated_at)->diffForHumans()); ?>

                    </p>
                </div>
            </div>
        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
            <p style="font-size:13px; color:#94a3b8;">No recent activity.</p>
        <?php endif; ?>
    </div>
</div>

</div>


<div style="background:#fff; border:1px solid #e2e8f0; border-radius:16px; padding:24px;">
    <h4 style="font-size:14px; font-weight:700; color:#1a202c; margin:0 0 20px;">Request Status Overview</h4>
    <?php
        $total = max($totalRequests, 1);
        $pendingPct  = round(($activeRequests / $total) * 100);
        $approvedPct = round(($approvedRequests / $total) * 100);
        $rejectedPct = round(($rejectedRequests / $total) * 100);
    ?>
    <div style="display:flex; flex-direction:column; gap:16px;">
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="font-size:13px; color:#4a5568;">Pending</span>
                <span style="font-size:13px; font-weight:700; color:#1a202c;"><?php echo e($activeRequests); ?></span>
            </div>
            <div style="background:#f1f5f9; height:8px; border-radius:999px; overflow:hidden;">
                <div style="width:<?php echo e($pendingPct); ?>%; background:#F59E0B; height:100%; border-radius:999px;"></div>
            </div>
        </div>
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="font-size:13px; color:#4a5568;">Approved</span>
                <span style="font-size:13px; font-weight:700; color:#1a202c;"><?php echo e($approvedRequests); ?></span>
            </div>
            <div style="background:#f1f5f9; height:8px; border-radius:999px; overflow:hidden;">
                <div style="width:<?php echo e($approvedPct); ?>%; background:#10B981; height:100%; border-radius:999px;"></div>
            </div>
        </div>
        <div>
            <div style="display:flex; justify-content:space-between; margin-bottom:6px;">
                <span style="font-size:13px; color:#4a5568;">Rejected</span>
                <span style="font-size:13px; font-weight:700; color:#1a202c;"><?php echo e($rejectedRequests); ?></span>
            </div>
            <div style="background:#f1f5f9; height:8px; border-radius:999px; overflow:hidden;">
                <div style="width:<?php echo e($rejectedPct); ?>%; background:#EF4444; height:100%; border-radius:999px;"></div>
            </div>
        </div>
    </div>
    <a href="/requests" style="display:block; text-align:right; font-size:12px; color:#10B981; margin-top:20px; text-decoration:none; font-weight:600;">View all requests →</a>
</div>

</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
const ctx = document.getElementById('trendChart').getContext('2d');
new Chart(ctx, {
    type: 'line',
    data: {
        labels: ['Dec', 'Jan', 'Feb', 'Mar', 'Apr', 'May'],
        datasets: [
            {
                label: 'Stock In',
                data: [1200, 1500, 1800, 1600, 2000, 2400],
                borderColor: '#10B981',
                backgroundColor: 'rgba(16,185,129,0.08)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
            },
            {
                label: 'Stock Out',
                data: [800, 1000, 1200, 1100, 1300, 1500],
                borderColor: '#EF4444',
                backgroundColor: 'rgba(239,68,68,0.05)',
                borderWidth: 2,
                tension: 0.4,
                fill: true,
                pointRadius: 3,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: {
            legend: { position: 'bottom', labels: { font: { size: 12 }, usePointStyle: true } }
        },
        scales: {
            y: { grid: { color: '#f1f5f9' }, ticks: { font: { size: 11 } } },
            x: { grid: { display: false }, ticks: { font: { size: 11 } } }
        }
    }
});
</script>

<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/admin/dashboard.blade.php ENDPATH**/ ?>