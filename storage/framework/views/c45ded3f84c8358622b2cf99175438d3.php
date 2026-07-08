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
        
        
        <div class="flex flex-col md:flex-row md:items-center md:justify-between border-b border-slate-200 pb-6 mb-6 gap-4">
            <div>
                <h1 class="text-xl font-bold text-slate-900 tracking-wide m-0 p-0">Users & Roles</h1>
                <p class="text-xs text-slate-400 mt-1 m-0">Manage system access and register new administrative or field staff records.</p>
            </div>
            
            <div>
                <button type="button" onclick="openUserModal()" class="inline-flex items-center gap-2 px-4 py-2.5 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 transition-colors border-none rounded-none cursor-pointer shadow-sm uppercase tracking-wide">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Add New User</span>
                </button>
            </div>
        </div>

        
        <div class="bg-white border border-slate-200 rounded-none p-6 shadow-sm">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left text-slate-600 border-collapse">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-bold">Name</th>
                            <th scope="col" class="px-4 py-3 font-bold">Email</th>
                            <th scope="col" class="px-4 py-3 font-bold">Role</th>
                            <th scope="col" class="px-4 py-3 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        <?php if(isset($users)): ?>
                            <?php $__currentLoopData = $users; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $userItem): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-4 py-3.5 font-medium text-slate-900"><?php echo e($userItem->name); ?></td>
                                    <td class="px-4 py-3.5"><?php echo e($userItem->email); ?></td>
                                    <td class="px-4 py-3.5">
                                        <span class="inline-flex px-2 py-0.5 text-xs font-semibold rounded-none uppercase
                                            <?php echo e($userItem->role === 'admin' ? 'bg-amber-50 text-amber-800 border border-amber-200' : ($userItem->role === 'staff' ? 'bg-blue-50 text-blue-700' : 'bg-slate-100 text-slate-700')); ?>">
                                            <?php echo e($userItem->role); ?>

                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            <a href="<?php echo e(route('users.edit', $userItem->id)); ?>" class="text-slate-400 hover:text-slate-600 font-medium text-xs no-underline">Edit</a>
                                            
                                            <form action="<?php echo e(route('users.destroy', $userItem->id)); ?>" method="POST" onsubmit="return confirm('Are you sure you want to remove this user access?');" class="m-0 p-0 inline">
                                                <?php echo csrf_field(); ?>
                                                <button type="submit" class="text-slate-400 hover:text-rose-600 font-medium bg-transparent border-none p-0 cursor-pointer text-xs">Remove</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                        <?php else: ?>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-3.5 font-medium text-slate-900">Test User</td>
                                <td class="px-4 py-3.5">test@example.com</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold bg-blue-50 text-blue-700 rounded-none uppercase">Staff</span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="text-slate-300 text-xs italic">Static Mode</span>
                                </td>
                            </tr>
                            <tr class="hover:bg-slate-50/70 transition-colors">
                                <td class="px-4 py-3.5 font-medium text-slate-900">Admin User</td>
                                <td class="px-4 py-3.5">admin@trackingaid.org</td>
                                <td class="px-4 py-3.5">
                                    <span class="inline-flex px-2 py-0.5 text-xs font-semibold bg-amber-50 text-amber-800 border border-amber-200 rounded-none uppercase">Admin</span>
                                </td>
                                <td class="px-4 py-3.5 text-right">
                                    <span class="text-slate-300 text-xs italic">Static Mode</span>
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        
        <div id="user-form-modal" class="hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 backdrop-blur-sm items-center justify-center p-4">
            
            <div class="bg-white border border-slate-200 rounded-none shadow-xl w-full max-w-2xl transform transition-all">
                
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between bg-slate-50">
                    <h3 class="text-sm font-bold text-slate-900 uppercase tracking-wider m-0 flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-emerald-500"></i>
                        Add New User Record
                    </h3>
                    <button type="button" onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600 border-none bg-transparent cursor-pointer text-base p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="/users" class="m-0 p-0">
                    <?php echo csrf_field(); ?>
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                                <input type="text" id="name" name="name" required placeholder="e.g., Joanna Doe" 
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                            </div>

                            <div>
                                <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                                <input type="email" id="email" name="email" required placeholder="admin@trackingaid.org" 
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">System Role *</label>
                                <div class="relative w-full">
                                    <select id="role" name="role" required 
                                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 appearance-none focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all cursor-pointer">
                                        <option value="" disabled selected>Select option...</option>
                                        <option value="user">User</option>
                                        <option value="staff">Staff</option>
                                        <option value="admin">Admin</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <div>
                                <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Account Password *</label>
                                <input type="password" id="password" name="password" required placeholder="••••••••" 
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-none text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                            </div>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeUserModal()" class="px-4 py-2 text-xs font-bold text-slate-500 hover:text-slate-700 bg-transparent border border-slate-200 rounded-none cursor-pointer transition-colors uppercase tracking-wide">
                             Close
                        </button>
                        <button type="submit" class="px-4 py-2 text-xs font-bold text-white bg-emerald-500 hover:bg-emerald-600 border-none rounded-none cursor-pointer transition-colors shadow-sm uppercase tracking-wide">
                             Save User Record
                        </button>
                    </div>
                </form>

            </div>
        </div>

    </div>

    <script>
        function openUserModal() {
            const modal = document.getElementById('user-form-modal');
            modal.classList.remove('hidden');
            modal.classList.add('flex');
            document.body.style.overflow = 'hidden';
        }

        function closeUserModal() {
            const modal = document.getElementById('user-form-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }
    </script>
 <?php echo $__env->renderComponent(); ?>
<?php endif; ?>
<?php if (isset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $attributes = $__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__attributesOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?>
<?php if (isset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54)): ?>
<?php $component = $__componentOriginal9ac128a9029c0e4701924bd2d73d7f54; ?>
<?php unset($__componentOriginal9ac128a9029c0e4701924bd2d73d7f54); ?>
<?php endif; ?><?php /**PATH C:\Capstone\bag o\TrackingAid-System\resources\views/users/index.blade.php ENDPATH**/ ?>