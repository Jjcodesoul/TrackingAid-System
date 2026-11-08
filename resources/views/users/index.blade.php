<x-app-layout>
    @section('title', 'Users & Roles')
    <div class="users-page">
        
        {{-- Header Bar --}}
        <div class="page-header">
            <div>
                <h1 class="page-title">Users &amp; Roles</h1>
                <p class="page-subtitle">Manage system access and staff records</p>
            </div>
            
            <div>
                <button type="button" onclick="openUserModal()" class="btn-main">
                    <i class="fa-solid fa-user-plus"></i>
                    <span>Add New User</span>
                </button>
            </div>
        </div>

        @if(session('success'))
            <div class="alert-box alert-success" role="status">{{ session('success') }}</div>
        @endif
        @if(session('error'))
            <div class="alert-box alert-error" role="alert">{{ session('error') }}</div>
        @endif
        @if($errors->any())
            <div class="alert-box alert-error" role="alert">
                <ul class="m-0 pl-5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="GET" action="{{ route('users.index') }}" class="card-ui p-4 mb-4" style="display:flex; gap:12px; align-items:end; flex-wrap:wrap;">
            <div style="flex:1; min-width:180px;">
                <label for="user-search" class="form-label">Search users</label>
                <input id="user-search" name="search" type="search" value="{{ $filters['search'] ?? '' }}" class="form-input" placeholder="Name or email">
            </div>
            <div style="min-width:160px;">
                <label for="user-role-filter" class="form-label">Role</label>
                <select id="user-role-filter" name="role" class="form-select">
                    <option value="">All roles</option>
                    @foreach(['admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel'] as $value => $label)
                        <option value="{{ $value }}" @selected(($filters['role'] ?? '') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
            <div style="min-width:150px;">
                <label for="user-status-filter" class="form-label">Account status</label>
                <select id="user-status-filter" name="is_active" class="form-select">
                    <option value="">All statuses</option>
                    <option value="1" @selected(($filters['is_active'] ?? '') === '1')>Active</option>
                    <option value="0" @selected(($filters['is_active'] ?? '') === '0')>Inactive</option>
                </select>
            </div>
            <button type="submit" class="btn-main">Filter</button>
            <a href="{{ route('users.index') }}" class="btn-soft">Clear</a>
        </form>

        <div class="card-ui" style="padding:0; overflow:hidden;">
            <div class="overflow-x-auto">
                <table class="table-ui">
                    <thead class="text-xs text-slate-700 uppercase bg-slate-50 border-b border-slate-200">
                        <tr>
                            <th scope="col" class="px-4 py-3 font-bold">Name</th>
                            <th scope="col" class="px-4 py-3 font-bold">Email</th>
                            <th scope="col" class="px-4 py-3 font-bold">Role</th>
                            <th scope="col" class="px-4 py-3 font-bold">Status</th>
                            <th scope="col" class="px-4 py-3 font-bold">Last active</th>
                            <th scope="col" class="px-4 py-3 font-bold text-right">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-slate-100">
                        @forelse($users as $userItem)
                                <tr class="hover:bg-slate-50/70 transition-colors">
                                    <td class="px-4 py-3.5 font-medium text-slate-900">{{ $userItem->name }}</td>
                                    <td class="px-4 py-3.5">{{ $userItem->email }}</td>
                                    <td class="px-4 py-3.5">
                                        <span class="status-badge role-badge role-{{ $userItem->role }}">
                                            {{ match($userItem->role) { 'admin' => 'Administrator', 'staff' => 'Staff', 'user' => 'User / Borrower', 'delivery_personnel' => 'Delivery Personnel', default => ucfirst(str_replace('_', ' ', $userItem->role)) } }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">
                                        <span class="status-badge {{ $userItem->is_active ? 'status-approved' : 'status-rejected' }}">
                                            {{ $userItem->is_active ? 'Active' : 'Inactive' }}
                                        </span>
                                    </td>
                                    <td class="px-4 py-3.5">{{ $userItem->last_login_at?->format('M d, Y H:i') ?? 'Never signed in' }}</td>
                                    <td class="px-4 py-3.5 text-right">
                                        <div class="flex items-center justify-end gap-3">
                                            @if($isSuperAdmin)
                                                <a href="{{ route('super-admin.activity-logs.users.show', $userItem) }}" class="users-action">Activity</a>
                                            @endif
                                            <a href="{{ route('users.edit', $userItem->id) }}" class="users-action">Edit</a>
                                            
                                            <form action="{{ route('users.destroy', $userItem->id) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this user access?');" class="m-0 p-0 inline">
                                                @csrf
                                                <button type="submit" class="users-action is-danger">Remove</button>
                                            </form>
                                            @if($isSuperAdmin)
                                                <form action="{{ route('super-admin.users.status', $userItem) }}" method="POST" class="m-0 p-0 inline">
                                                    @csrf
                                                    <input type="hidden" name="is_active" value="{{ $userItem->is_active ? 0 : 1 }}">
                                                    <button type="submit" class="users-action">{{ $userItem->is_active ? 'Deactivate' : 'Activate' }}</button>
                                                </form>
                                                <form action="{{ route('super-admin.users.force-logout', $userItem) }}" method="POST" class="m-0 p-0 inline" onsubmit="return confirm('Revoke all current sessions and API tokens for this account?');">
                                                    @csrf
                                                    <button type="submit" class="users-action">Force logout</button>
                                                </form>
                                                <form action="{{ route('super-admin.users.password-reset', $userItem) }}" method="POST" class="m-0 p-0 inline" onsubmit="return confirm('Send an expiring password reset link to this user?');">
                                                    @csrf
                                                    <button type="submit" class="users-action">Reset password</button>
                                                </form>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                        @empty
                            <tr><td colspan="6" class="p-8 text-center text-slate-500">No users match these filters.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div style="padding:16px;">{{ $users->links() }}</div>
        </div>

        {{-- Fixed Layout: Only 'hidden' is applied initially --}}
        <div id="user-form-modal" class="users-modal-overlay hidden fixed inset-0 z-50 overflow-y-auto bg-slate-900/50 items-center justify-center p-4" role="dialog" aria-modal="true" aria-labelledby="user-modal-title">
            
            <div class="bg-white border border-slate-200 rounded-xl shadow-xl w-full max-w-2xl transform transition-all">
                
                <div class="border-b border-slate-100 px-6 py-4 flex items-center justify-between bg-slate-50">
                    <h3 id="user-modal-title" class="text-base font-semibold text-slate-900 m-0 flex items-center gap-2">
                        <i class="fa-solid fa-id-card text-emerald-500"></i>
                        Add New User Record
                    </h3>
                    <button type="button" onclick="closeUserModal()" class="text-slate-400 hover:text-slate-600 border-none bg-transparent cursor-pointer text-base p-1">
                        <i class="fa-solid fa-xmark"></i>
                    </button>
                </div>

                <form method="POST" action="{{ route('users.store') }}" class="m-0 p-0">
                    @csrf
                    
                    <div class="p-6 space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="first_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">First Name *</label>
                                <input type="text" id="first_name" name="first_name" required placeholder="e.g., Joanna" value="{{ old('first_name') }}"
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                            </div>

                            <div>
                                <label for="last_name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Last Name *</label>
                                <input type="text" id="last_name" name="last_name" required placeholder="e.g., Doe" value="{{ old('last_name') }}"
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                            </div>
                        </div>

                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" id="email" name="email" required placeholder="admin@trackingaid.org" value="{{ old('email') }}"
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">System Role *</label>
                                <div class="relative w-full">
                                    <select id="role" name="role" required
                                        class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 appearance-none focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all cursor-pointer">
                                        <option value="" disabled selected>Select option...</option>
                                        <option value="user">User</option>
                                        <option value="staff">Staff</option>
                                        <option value="admin">Admin</option>
                                        <option value="delivery_personnel">Delivery Personnel</option>
                                    </select>
                                    <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">
                                        <i class="fa-solid fa-chevron-down"></i>
                                    </div>
                                </div>
                            </div>

                            <p class="text-sm text-slate-600 self-center">A secure password setup link will be emailed to this address.</p>
                        </div>
                    </div>

                    <div class="border-t border-slate-100 bg-slate-50 px-6 py-4 flex justify-end gap-3">
                        <button type="button" onclick="closeUserModal()" class="btn-soft">
                             Close
                        </button>
                        <button type="submit" class="btn-main">
                             Save User
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
            modal.querySelector('input')?.focus();
        }

        function closeUserModal() {
            const modal = document.getElementById('user-form-modal');
            modal.classList.remove('flex');
            modal.classList.add('hidden');
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('keydown', function (event) {
            if (event.key === 'Escape' && !document.getElementById('user-form-modal').classList.contains('hidden')) {
                closeUserModal();
            }
        });

        @if($errors->any())
            openUserModal();
        @endif
    </script>
</x-app-layout>
