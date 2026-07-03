<?php $__env->startSection('content'); ?>
    <div style="margin-bottom:24px;">
        <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Requests</h2>
        <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Incoming requests from ResqOperation — review, approve, or reject</p>
    </div>

    <?php if(session('success')): ?>
        <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
            <?php echo e(session('success')); ?>

        </div>
    <?php endif; ?>

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Pending</div>
                <div class="display-6 fw-bold"><?php echo e($stats['pending'] ?? 0); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Approved</div>
                <div class="display-6 fw-bold"><?php echo e($stats['approved'] ?? 0); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Released</div>
                <div class="display-6 fw-bold"><?php echo e($stats['released'] ?? 0); ?></div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Rejected</div>
                <div class="display-6 fw-bold"><?php echo e($stats['rejected'] ?? 0); ?></div>
            </div>
        </div>
    </div>

    <section class="panel" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button onclick="filterStatus('all')" id="tab-all" type="button" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#1a202c; color:#fff;">All</button>
                <button onclick="filterStatus('Pending')" id="tab-pending" type="button" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151; display:flex; align-items:center; gap:6px;">
                    Pending
                    <span style="background:#EF4444; color:#fff; border-radius:999px; padding:1px 7px; font-size:11px;"><?php echo e($requests->where('status', 'Pending')->count()); ?></span>
                </button>
                <button onclick="filterStatus('Approved')" id="tab-approved" type="button" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">Approved</button>
                <button onclick="filterStatus('Rejected')" id="tab-rejected" type="button" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">Rejected</button>
            </div>

            <div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
                <input type="text" id="searchInput" placeholder="Search requests..." onkeyup="searchTable()" style="padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; width:220px;">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Request ID</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Item</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">QTY</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Priority</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Requested By</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Date</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Status</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTable">
                    <?php $__empty_1 = true; $__currentLoopData = $requests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $req): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                        <?php
                            $priorityColors = [
                                'Critical' => ['bg' => '#FEF2F2', 'color' => '#DC2626'],
                                'High' => ['bg' => '#FFF7ED', 'color' => '#EA580C'],
                                'Medium' => ['bg' => '#FFFBEB', 'color' => '#D97706'],
                                'Low' => ['bg' => '#F0FDF4', 'color' => '#16A34A'],
                            ];
                            $pc = $priorityColors[$req->priority] ?? ['bg' => '#F7FAFC', 'color' => '#4A5568'];

                            $statusColors = [
                                'Pending' => ['bg' => '#FFFBEB', 'color' => '#D97706'],
                                'Approved' => ['bg' => '#ECFDF5', 'color' => '#059669'],
                                'Rejected' => ['bg' => '#FEF2F2', 'color' => '#DC2626'],
                                'Released' => ['bg' => '#EEF2FF', 'color' => '#4338CA'],
                            ];
                            $sc = $statusColors[$req->status] ?? ['bg' => '#F7FAFC', 'color' => '#4A5568'];
                        ?>
                        <tr class="req-row" data-status="<?php echo e($req->status); ?>" style="border-bottom:1px solid #f1f5f9;">
                            <td style="padding:14px 16px;"><span style="font-size:12px; font-weight:600; color:#64748b; font-family:monospace;"><?php echo e($req->request_code); ?></span></td>
                            <td style="padding:14px 16px;">
                                <span style="font-weight:600; color:#1a202c; font-size:14px; display:block;"><?php echo e($req->inventory?->name ?? 'Unknown Item'); ?></span>
                                <span style="font-size:11px; color:#94a3b8; font-family:monospace;"><?php echo e($req->inventory?->sku ?? '—'); ?></span>
                            </td>
                            <td style="padding:14px 16px; font-weight:700; color:#1a202c;"><?php echo e(number_format($req->quantity)); ?></td>
                            <td style="padding:14px 16px;"><span style="background:<?php echo e($pc['bg']); ?>; color:<?php echo e($pc['color']); ?>; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700;"><?php echo e($req->priority); ?></span></td>
                            <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                <?php echo e($req->source ?? $req->responder_email ?? '—'); ?>

                                <?php if($req->purpose): ?>
                                    <span style="display:block; font-size:11px; color:#94a3b8;"><?php echo e(\Illuminate\Support\Str::limit($req->purpose, 30)); ?></span>
                                <?php endif; ?>
                            </td>
                            <td style="padding:14px 16px; font-size:13px; color:#4a5568;"><?php echo e(optional($req->created_at)->format('M d, Y')); ?></td>
                            <td style="padding:14px 16px;"><span style="background:<?php echo e($sc['bg']); ?>; color:<?php echo e($sc['color']); ?>; padding:3px 12px; border-radius:6px; font-size:12px; font-weight:700;"><?php echo e($req->status); ?></span></td>
                            <td style="padding:14px 16px;">
                                <div style="display:flex; align-items:center; gap:8px;">
                                    <button type="button" title="<?php echo e($req->notification_status ?? 'No notification yet'); ?>" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px;"><i class="fa-regular fa-eye"></i></button>
                                    <?php if($req->status === 'Pending'): ?>
                                        <form action="<?php echo e(route('requests.approve', $req)); ?>" method="POST" style="margin:0;"><?php echo csrf_field(); ?><button type="submit" title="Approve" style="background:none; border:none; cursor:pointer; color:#059669; padding:4px; font-size:16px;"><i class="fa-regular fa-circle-check"></i></button></form>
                                        <form action="<?php echo e(route('requests.reject', $req)); ?>" method="POST" onsubmit="return confirm('Reject this request?')" style="margin:0;"><?php echo csrf_field(); ?><button type="submit" title="Reject" style="background:none; border:none; cursor:pointer; color:#DC2626; padding:4px; font-size:16px;"><i class="fa-regular fa-circle-xmark"></i></button></form>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">No requests found.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </section>

    <script>
        function filterStatus(status) {
            const rows = document.querySelectorAll('.req-row');
            rows.forEach(row => {
                const rowStatus = row.getAttribute('data-status');
                row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
            });

            const tabs = ['all', 'Pending', 'Approved', 'Rejected'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t.toLowerCase());
                if (!btn) return;

                if ((t === 'all' && status === 'all') || t === status) {
                    btn.style.background = '#1a202c';
                    btn.style.color = '#fff';
                    btn.style.border = 'none';
                } else {
                    btn.style.background = '#fff';
                    btn.style.color = '#374151';
                    btn.style.border = '1px solid #e2e8f0';
                }
            });
        }

        function searchTable() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            document.querySelectorAll('.req-row').forEach(row => {
                row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
            });
        }
    </script>
<?php $__env->stopSection(); ?>

<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/requests/index.blade.php ENDPATH**/ ?>