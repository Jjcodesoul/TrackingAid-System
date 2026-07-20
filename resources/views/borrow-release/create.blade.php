@extends('layouts.app')

@section('title', 'Borrow / Release')

@section('content')
    <div class="p-6 bg-[#f8fafc] min-h-screen text-left">
        
        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="mb-4 px-5 py-3 bg-[#f0fdf4] border border-[#86efac] text-[#166534] text-[13px] font-medium rounded-none shadow-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-check text-[#16a34a]"></i>
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 px-5 py-3 bg-[#fef2f2] border border-[#fca5a5] text-[#991b1b] text-[13px] font-medium rounded-none shadow-sm flex items-center gap-3">
                <i class="fa-solid fa-circle-exclamation text-[#dc2626]"></i>
                {{ session('error') }}
            </div>
        @endif

        @if($errors->any())
            <div class="mb-4 px-5 py-3 bg-[#fffbeb] border border-[#fde68a] text-[#92400e] text-[13px] font-medium rounded-none shadow-sm">
                <div class="flex items-center gap-3 mb-1.5">
                    <i class="fa-solid fa-triangle-exclamation text-[#d97706]"></i>
                    <span>Please fix the following errors:</span>
                </div>
                <ul class="m-0 pl-5 text-[12px] space-y-0.5">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Header Section --}}
        <div class="mb-6">
            <h1 class="text-[24px] font-semibold text-[#0f172a] tracking-tight m-0">Borrow / Release</h1>
            <div class="text-[14px] text-[#64748b] mt-0.5">Release inventory items for field operations</div>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Approved Requests</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $approvedRequests->count() }}</div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Available Items</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $items->count() }}</div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-none shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Recent Releases</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $recentReleases->count() }}</div>
            </div>
        </div>

        {{-- Form Section --}}
        <section class="bg-white border border-[#e2e8f0] p-6 rounded-none shadow-sm mb-6">
            <form action="{{ route('borrow-release.store') }}" method="POST" class="m-0">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="request_id">Approved Request *</label>
                        <select id="request_id" name="request_id" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] bg-white focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" required>
                            <option value="">Choose an approved request...</option>
                            @foreach($approvedRequests as $approvedRequest)
                                <option
                                    value="{{ $approvedRequest->id }}"
                                    data-inventory-id="{{ $approvedRequest->inventory_id }}"
                                    data-quantity="{{ $approvedRequest->quantity }}"
                                    data-purpose="{{ $approvedRequest->purpose }}"
                                    {{ old('request_id') == $approvedRequest->id ? 'selected' : '' }}
                                >
                                    {{ $approvedRequest->request_code }} - {{ $approvedRequest->inventory?->sku }} - {{ $approvedRequest->quantity }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="inventory_id">Item (SKU) *</label>
                        <select id="inventory_id" name="inventory_id" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] bg-white focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" required>
                            <option value="">Choose an item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('inventory_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->sku }} - {{ $item->name }} (Stock: {{ $item->quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="unit">Unit *</label>
                        <input id="unit" name="unit" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="{{ old('unit') }}" placeholder="e.g., PCS, BOX, SACK" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="quantity">Quantity *</label>
                        <input id="quantity" name="quantity" type="number" min="1" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="{{ old('quantity') }}" placeholder="e.g., 50" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="purpose">Purpose / Incident Reference *</label>
                        <input id="purpose" name="purpose" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="{{ old('purpose') }}" placeholder="e.g., Emergency Response - Flood 2024" required>
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="location">Location</label>
                        <input id="location" name="location" type="text" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="{{ old('location') }}" placeholder="e.g., Disaster Zone A, Relief Center">
                    </div>

                    <div>
                        <label class="block text-[12px] font-semibold text-[#475569] mb-1" for="released_at">Date & Time *</label>
                        <input id="released_at" name="released_at" type="datetime-local" class="w-full h-9.5 px-3 border border-[#cbd5e1] rounded-none text-[14px] focus:border-[#22c55e] focus:outline-none focus:ring-1 focus:ring-[#22c55e]" value="{{ old('released_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-5">
                    <button type="submit" class="bg-[#22c55e] hover:bg-[#16a34a] text-white font-medium text-[13px] px-4 py-2 rounded-none transition-colors border-none cursor-pointer">Release Items</button>
                    <button type="reset" class="bg-[#f1f5f9] hover:bg-[#e2e8f0] text-[#475569] border border-[#cbd5e1] font-medium text-[13px] px-4 py-2 rounded-none transition-colors cursor-pointer">Clear Form</button>
                </div>
            </form>
        </section>

        {{-- Activity Table Section --}}
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
                        @forelse($recentReleases as $release)
                            <tr class="hover:bg-gray-50">
                                <td class="p-3 text-[13px] font-medium text-[#1e293b]">{{ $release->request->request_code ?? 'N/A' }}</td>
                                <td class="p-3 text-[13px] text-[#475569]">{{ $release->inventory->sku ?? 'N/A' }}</td>
                                <td class="p-3 text-[13px] text-[#475569]">{{ number_format($release->quantity) }}</td>
                                <td class="p-3 text-[13px] text-[#475569]">{{ $release->location ?? '—' }}</td>
                                <td class="p-3 text-[13px] text-[#64748b]">{{ $release->released_at?->format('M d, Y H:i') ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="p-8 text-center text-[#94a3b8] text-[13px]">No recent releases recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>
@endsection

@push('scripts')
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

        document.querySelector('form')?.addEventListener('submit', function (e) {
            const submitBtn = this.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin"></i> Processing...';
                submitBtn.classList.remove('bg-[#22c55e]', 'hover:bg-[#16a34a]');
                submitBtn.classList.add('bg-[#94a3b8]', 'cursor-not-allowed');
            }
        });
    </script>
@endpush