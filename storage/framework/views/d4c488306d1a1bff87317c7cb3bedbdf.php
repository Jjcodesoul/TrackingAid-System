<?php $__env->startSection('title', 'Borrow / Release'); ?>

<?php $__env->startSection('content'); ?>
    <div class="p-6 bg-[#f8fafc] min-h-screen text-left">
        
        
        <div class="mb-6">
            <h1 class="text-[24px] font-semibold text-[#0f172a] tracking-tight m-0">Borrow / Release</h1>
            <div class="text-[14px] text-[#64748b] mt-0.5">Release inventory items for field operations</div>
        </div>

        
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Approved Requests</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1"><?php echo e($approvedRequests->count()); ?></div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Available Items</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1"><?php echo e($items->count()); ?></div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Recent Releases</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1"><?php echo e($recentReleases->count()); ?></div>
            </div>
        </div>

        
        <section class="bg-white border border-[#e2e8f0] p-6 rounded-none shadow-sm mb-6">
            <form action="<?php echo e(route('borrow-release.store')); ?>" method="POST" class="m-0">
                <?php echo csrf_field(); ?>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="request_id">Approved Request *</label>
                        <select id="request_id" name="request_id" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] bg-white focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" required>
                            <option value="">Choose an approved request...</option>
                            <?php $__currentLoopData = $approvedRequests; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $approvedRequest): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option
                                    value="<?php echo e($approvedRequest->id); ?>"
                                    data-inventory-id="<?php echo e($approvedRequest->inventory_id); ?>"
                                    data-quantity="<?php echo e($approvedRequest->quantity); ?>"
                                    data-purpose="<?php echo e($approvedRequest->purpose); ?>"
                                    <?php echo e(old('request_id') == $approvedRequest->id ? 'selected' : ''); ?>

                                >
                                    <?php echo e($approvedRequest->request_code); ?> - <?php echo e($approvedRequest->inventory?->sku); ?> - <?php echo e($approvedRequest->quantity); ?>

                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="inventory_id">Item (SKU) *</label>
                        <select id="inventory_id" name="inventory_id" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] bg-white focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" required>
                            <option value="">Choose an item...</option>
                            <?php $__currentLoopData = $items; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $item): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <option value="<?php echo e($item->id); ?>" <?php echo e(old('inventory_id') == $item->id ? 'selected' : ''); ?>>
                                    <?php echo e($item->sku); ?> - <?php echo e($item->name); ?> (Stock: <?php echo e($item->quantity); ?>)
                                </option>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="unit">Unit *</label>
                        <input id="unit" name="unit" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="<?php echo e(old('unit')); ?>" placeholder="e.g., PCS, BOX, SACK" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="quantity">Quantity *</label>
                        <input id="quantity" name="quantity" type="number" min="1" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="<?php echo e(old('quantity')); ?>" placeholder="e.g., 50" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="purpose">Purpose / Incident Reference *</label>
                        <input id="purpose" name="purpose" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="<?php echo e(old('purpose')); ?>" placeholder="e.g., Emergency Response - Flood 2024" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="location">Location</label>
                        <input id="location" name="location" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="<?php echo e(old('location')); ?>" placeholder="e.g., Disaster Zone A, Relief Center">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="released_at">Date & Time *</label>
                        <input id="released_at" name="released_at" type="datetime-local" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="<?php echo e(old('released_at', now()->format('Y-m-d\TH:i'))); ?>" required>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-5">
                    <button type="submit" class="bg-[#22c55e] hover:bg-[#16a34a] text-white font-medium text-[13px] px-4 py-2 rounded-none transition-colors border-none cursor-pointer">Release Items</button>
                    <button type="reset" class="bg-[#f1f5f9] hover:bg-[#e2e8f0] text-[#475569] border border-[#cbd5e1] font-medium text-[13px] px-4 py-2 rounded-none transition-colors cursor-pointer">Clear Form</button>
                </div>
            </form>
        </section>

        
        <section class="bg-white border border-[#e2e8f0] p-6 rounded-none shadow-sm">
            <div class="flex justify-content-between align-items-center mb-4">
                <h5 class="text-[16px] font-semibold text-[#0f172a] m-0">Recent Release Activity</h5>
                <span class="text-[#64748b] text-[12px]">Latest 5 transactions</span>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse text-left m-0">
                    <thead>
                        <tr class="bg-[#f8fafc] border-b border-[#e2e8f0]">
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Request</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Item</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Quantity</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Location</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Released At</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1f5f9]">
                        <?php $__empty_1 = true; $__currentLoopData = $recentReleases; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $release): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); $__empty_1 = false; ?>
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-[13px] font-medium text-[#1e293b]"><?php echo e($release->request->request_code ?? 'N/A'); ?></td>
                                <td class="p-3 text-[13px] text-[#475569]"><?php echo e($release->inventory->sku ?? 'N/A'); ?></td>
                                <td class="p-3 text-[13px] text-[#475569]"><?php echo e(number_format($release->quantity)); ?></td>
                                <td class="p-3 text-[13px] text-[#475569]"><?php echo e($release->location ?? '—'); ?></td>
                                <td class="p-3 text-[13px] text-[#64748b]"><?php echo e($release->released_at?->format('M d, Y H:i') ?? '—'); ?></td>
                            </tr>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); if ($__empty_1): ?>
                            <tr>
                                <td colspan="5" class="p-8 text-center text-[#94a3b8] text-[13px]">No recent releases recorded yet.</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </section>
    </div>
<?php $__env->stopSection(); ?>

<?php $__env->startPush('scripts'); ?>
    <script>
        const requestSelect = document.getElementById('request_id');
        const itemSelect = document.getElementById('inventory_id');
        const quantityInput = document.getElementById('quantity');
        const purposeInput = document.getElementById('purpose');

        requestSelect?.addEventListener('change', function () {
            const selected = this.options[this.selectedIndex];
            if (!selected?.value) {
                return;
            }

            itemSelect.value = selected.dataset.inventoryId || '';
            quantityInput.value = selected.dataset.quantity || '';
            purposeInput.value = selected.dataset.purpose || '';
        });
    </script>
<?php $__env->stopPush(); ?>
<?php echo $__env->make('layouts.app', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/borrow-release/create.blade.php ENDPATH**/ ?>