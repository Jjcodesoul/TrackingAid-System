@extends('layouts.app')

@section('title', 'Borrow / Release')

@section('content')
    <div class="p-6 bg-[#f8fafc] min-h-screen text-left">

        {{-- Flash Messages --}}
        @if(session('success'))
            <div class="alert-box alert-success mb-4">{{ session('success') }}</div>
        @endif

        @if(session('error'))
            <div class="alert-box alert-error mb-4">{{ session('error') }}</div>
        @endif

        @if($errors->any())
            <div class="alert-box alert-warning mb-4" style="display:block;">
                <div class="flex items-center gap-3 mb-1.5">
                    <i class="fa-solid fa-triangle-exclamation"></i>
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
            <h1 class="page-title">Borrow / Release</h1>
            <p class="page-subtitle">Release inventory items for field operations</p>
        </div>

        {{-- Stat Cards --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Approved Requests</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $approvedRequests->count() }}</div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Available Items</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $items->count() }}</div>
            </div>
            <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
                <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Recent Releases</div>
                <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $recentReleases->count() }}</div>
            </div>
        </div>

        {{-- Form Section --}}
        <section class="bg-white border border-[#e2e8f0] p-6 rounded-xl shadow-sm mb-6">
            <form action="{{ route('borrow-release.store') }}" method="POST" class="m-0">
                @csrf

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                    <div>
                        <label class="form-label" for="request_id">Approved Request *</label>
                        <select id="request_id" name="request_id" class="form-select" required>
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
                        <label class="form-label" for="inventory_id">Item (SKU) *</label>
                        <select id="inventory_id" name="inventory_id" class="form-select" required>
                            <option value="">Choose an item...</option>
                            @foreach($items as $item)
                                <option value="{{ $item->id }}" {{ old('inventory_id') == $item->id ? 'selected' : '' }}>
                                    {{ $item->sku }} - {{ $item->name }} (Stock: {{ $item->quantity }})
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="form-label" for="unit">Unit *</label>
                        <input id="unit" name="unit" type="text" class="form-input" value="{{ old('unit') }}" placeholder="e.g., PCS, BOX, SACK" required>
                    </div>

                    <div>
                        <label class="form-label" for="quantity">Quantity *</label>
                        <input id="quantity" name="quantity" type="number" min="1" class="form-input" value="{{ old('quantity') }}" placeholder="e.g., 50" required>
                    </div>

                    <div>
                        <label class="form-label" for="purpose">Purpose / Incident Reference *</label>
                        <input id="purpose" name="purpose" type="text" class="form-input" value="{{ old('purpose') }}" placeholder="e.g., Emergency Response - Flood 2024" required>
                    </div>

                    <div>
                        <label class="form-label" for="location">Location</label>
                        <input id="location" name="location" type="text" class="form-input" value="{{ old('location') }}" placeholder="e.g., Disaster Zone A, Relief Center">
                    </div>

                    <div>
                        <label class="form-label" for="released_at">Date & Time *</label>
                        <input id="released_at" name="released_at" type="datetime-local" class="form-input" value="{{ old('released_at', now()->format('Y-m-d\TH:i')) }}" required>
                    </div>
                </div>

                <div class="flex flex-wrap gap-3 mt-5">
                    <button type="submit" class="btn-main">Release Items</button>
                    <button type="reset" class="btn-soft">Clear Form</button>
                </div>
            </form>
        </section>

        {{-- Activity Table Section --}}
        <section class="bg-white border border-[#e2e8f0] p-6 rounded-xl shadow-sm">
            <div class="flex justify-between items-center mb-4">
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
                submitBtn.classList.add('opacity-60', 'cursor-not-allowed');
            }
        });
    </script>
@endpush