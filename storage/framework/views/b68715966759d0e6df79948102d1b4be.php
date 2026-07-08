<?php if (isset($component)) { $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54 = $component; } ?>
<?php if (isset($attributes)) { $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54 = $attributes; } ?>
<?php $component = App\View\Components\AppLayout::resolve([] + (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag ? $attributes->all() : [])); ?>
<?php $component->withName('app-layout'); ?>
<?php if ($component->shouldRender()): ?>
<?php $__env->startComponent($component->resolveView(), $component->data()); ?>
<?php if (isset($attributes) && $attributes instanceof Illuminate\View\ComponentAttributeBag): ?>
<?php $attributes = $attributes->except(\App\View\Components\AppLayout::ignoredParameterNames()); ?>
<?php endif; ?>
<?php $component->withAttributes([]); ?>
    <div class="p-8 bg-slate-50 min-h-screen text-left relative">
        
        
        <div class="border-b border-slate-200 pb-6 mb-6">
            <h1 class="text-xl font-bold text-slate-900 tracking-wide m-0 p-0">Edit User Account</h1>
            <p class="text-xs text-slate-400 mt-1 m-0">Modify system credentials and administrative access controls for this record.</p>
        </div>

        
        <div class="bg-white border border-slate-200 rounded-none p-6 shadow-sm max-w-2xl">
            <form method="POST" action="<?php echo e(route('users.update', $user->id)); ?>" class="m-0 p-0">
                <?php echo csrf_field(); ?>

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" id="name" name="name" required value="<?php echo e(old('name', $user->name)); ?>"
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>

                        
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" id="email" name="email" required value="<?php echo e(old('email', $user->email)); ?>"
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        
                        <div>
                            <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">System Role *</label>
                            <div class="relative w-full">
                                <select id="role" name="role" required 
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 appearance-none focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all cursor-pointer">
                                    <option value="user" <?php echo e(old('role', $user->role) === 'user' ? 'selected' : ''); ?>>User</option>
                                    <option value="staff" <?php echo e(old('role', $user->role) === 'staff' ? 'selected' : ''); ?>>Staff</option>
                                    <option value="admin" <?php echo e(old('role', $user->role) === 'admin' ? 'selected' : ''); ?>>Admin</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Password (Leave blank to keep current)</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" 
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>
                    </div>
                </div>

                
                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="<?php echo e(route('users.index')); ?>" class="inline-flex items-center px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 bg-transparent border border-slate-200 rounded-none cursor-pointer transition-colors uppercase tracking-wide no-underline">
                        Cancel
                    </a>
                    <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 border-none rounded-none cursor-pointer transition-colors shadow-sm uppercase tracking-wide">
                        Save Configurations
                    </button>
                </div>
            </form>
        </div>

    </div>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/users/edit.blade.php ENDPATH**/ ?>