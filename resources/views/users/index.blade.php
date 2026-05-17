<x-app-layout>
    {{-- CSS Fix to prevent Modal flickering on load --}}
    <style>[x-cloak] { display: none !important; }</style>

    {{-- 1. Notifications (Success & Errors) --}}
    <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 mt-4">
        @if (session('success'))
            <div x-data="{ show: true }" x-init="setTimeout(() => show = false, 4000)" x-show="show" 
                 class="p-4 mb-4 text-sm text-green-800 rounded-lg bg-green-50 border border-green-200 flex justify-between items-center">
                <span class="font-bold">{{ session('success') }}</span>
                <button @click="show = false" class="text-green-500 hover:text-green-700">&times;</button>
            </div>
        @endif

        @if ($errors->any())
            <div class="p-4 mb-4 text-sm text-red-800 rounded-lg bg-red-50 border border-red-200">
                <ul class="list-disc pl-5">
                    @foreach ($errors->all() as $error)
                        <li class="font-bold">{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>

    {{-- 2. Main Access Control Interface --}}
    <div x-data="{ showModal: false }" class="py-12 bg-gray-50 min-h-screen">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="flex justify-between items-center mb-6">
                <div>
                    <h2 class="text-3xl font-bold text-gray-800">Users & Roles</h2>
                    <p class="text-gray-500">Manage user accounts and permissions</p>
                </div>
                
                <button @click="showModal = true" type="button" 
                        class="bg-[#2ecc71] hover:bg-[#27ae60] text-white px-6 py-2 rounded-lg font-bold shadow-md transition-all active:scale-95">
                    + Add New User
                </button>
            </div>

            <div class="bg-white overflow-hidden shadow-sm rounded-xl border border-gray-100">
                <table class="min-w-full divide-y divide-gray-200">
                    <thead class="bg-gray-50">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Name</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-gray-400 uppercase">Email</th>
                            <th class="px-6 py-4 text-center text-xs font-bold text-gray-400 uppercase">Role</th>
                            <th class="px-6 py-4 text-right text-xs font-bold text-gray-400 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-200 bg-white">
                        @forelse($users as $user)
                            <tr class="hover:bg-gray-50/50 transition">
                                <td class="px-6 py-4 font-bold text-gray-700">{{ $user->name }}</td>
                                <td class="px-6 py-4 text-gray-500">{{ $user->email }}</td>
                                <td class="px-6 py-4 text-center">
                                    <span class="px-3 py-1 text-[10px] font-black rounded-full {{ $user->role === 'admin' ? 'bg-purple-100 text-purple-700' : 'bg-blue-100 text-blue-700' }} uppercase">
                                        {{ $user->role }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 text-right flex justify-end space-x-3">
                                    <button type="button" class="text-gray-400 hover:text-[#2ecc71] font-bold transition">Edit</button>
                                    
                                    {{-- FIXED LINE BELOW: Added Route check to prevent Internal Server Error --}}
                                    @if(Route::has('users.destroy'))
                                        <form action="{{ route('users.destroy', $user->id) }}" method="POST" onsubmit="return confirm('Are you sure?');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="text-rose-500 hover:text-rose-700 font-bold transition">Disable</button>
                                        </form>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="4" class="px-6 py-10 text-center text-gray-400">No personnel records found.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- 3. The Modal Backdrop and Form --}}
        <div x-show="showModal"
             class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100"
             x-cloak>
            
            <div @click.away="showModal = false" class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-8 border border-gray-100">
                <div class="flex justify-between items-center mb-6">
                    <h3 class="text-2xl font-black text-gray-800">New User</h3>
                    <button @click="showModal = false" type="button" class="text-gray-400 hover:text-gray-600 text-2xl font-bold">&times;</button>
                </div>

                <form action="{{ route('users.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Full Name</label>
                            <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-200 rounded-lg focus:ring-[#2ecc71] focus:border-[#2ecc71]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Email Address</label>
                            <input type="email" name="email" value="{{ old('email') }}" required class="w-full border-gray-200 rounded-lg focus:ring-[#2ecc71] focus:border-[#2ecc71]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">Temporary Password</label>
                            <input type="password" name="password" required class="w-full border-gray-200 rounded-lg focus:ring-[#2ecc71] focus:border-[#2ecc71]">
                        </div>
                        <div>
                            <label class="block text-xs font-black text-gray-400 uppercase mb-1">System Role</label>
                            <select name="role" class="w-full border-gray-200 rounded-lg focus:ring-[#2ecc71] font-bold">
                                <option value="user">Standard Staff</option>
                                <option value="admin">Administrator</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-8 flex justify-end space-x-3">
                        <button type="button" @click="showModal = false" class="px-5 py-2 text-gray-400 font-bold hover:text-gray-600 transition">Cancel</button>
                        <button type="submit" class="bg-[#2ecc71] hover:bg-[#27ae60] text-white px-8 py-2 rounded-lg font-black shadow-lg transition-transform active:scale-95">
                            Save User
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-app-layout>