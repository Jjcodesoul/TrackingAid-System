<x-app-layout>
    <div class="p-8 bg-slate-50 min-h-screen text-left relative">
        
        {{-- Header Bar --}}
        <div class="border-b border-slate-200 pb-6 mb-6">
            <h1 class="page-title">Edit User</h1>
            <p class="page-subtitle">Update credentials and access level for this account</p>
        </div>

        {{-- Form Card Layout --}}
        <div class="bg-white border border-slate-200 rounded-xl p-6 shadow-sm max-w-2xl">
            <form method="POST" action="{{ route('users.update', $user->id) }}" class="m-0 p-0">
                @csrf

                <div class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- Full Name Input --}}
                        <div>
                            <label for="name" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Full Name *</label>
                            <input type="text" id="name" name="name" required value="{{ old('name', $user->name) }}"
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>

                        {{-- Email Input --}}
                        <div>
                            <label for="email" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">Email Address *</label>
                            <input type="email" id="email" name="email" required value="{{ old('email', $user->email) }}"
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        {{-- System Role Selection Dropdown --}}
                        <div>
                            <label for="role" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">System Role *</label>
                            <div class="relative w-full">
                                <select id="role" name="role" required 
                                    class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 appearance-none focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all cursor-pointer">
                                    <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                                    <option value="staff" {{ old('role', $user->role) === 'staff' ? 'selected' : '' }}>Staff</option>
                                    <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                                </select>
                                <div class="absolute inset-y-0 right-0 flex items-center pr-3 pointer-events-none text-slate-400 text-xs">
                                    <i class="fa-solid fa-chevron-down"></i>
                                </div>
                            </div>
                        </div>

                        {{-- Optional Password Field --}}
                        <div>
                            <label for="password" class="block text-xs font-bold text-slate-700 uppercase tracking-wider mb-1.5">New Password (Leave blank to keep current)</label>
                            <input type="password" id="password" name="password" placeholder="••••••••" 
                                class="w-full text-sm px-3 py-2 bg-slate-50 border border-slate-200 rounded-xl text-slate-800 placeholder-slate-400 focus:outline-none focus:border-emerald-500 focus:bg-white box-border transition-all" />
                        </div>
                    </div>
                </div>

                {{-- Action Group --}}
                <div class="mt-6 pt-4 border-t border-slate-100 flex justify-end gap-3">
                    <a href="{{ route('users.index') }}" class="btn-secondary">
                        Cancel
                    </a>
                    <button type="submit" class="btn-main">
                        Save Changes
                    </button>
                </div>
            </form>
        </div>

    </div>
</x-app-layout>