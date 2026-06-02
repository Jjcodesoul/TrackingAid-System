@extends('layouts.app')

@section('title', 'Borrow / Release')

@section('content')
    <h1 class="page-title">Borrow / Release</h1>
    <div class="page-subtitle">Release inventory items for field operations</div>

    <section class="panel">
        <form action="{{ route('borrow-release.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-lg-6">
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

                <div class="col-lg-6">
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

                <div class="col-lg-6">
                    <label class="form-label" for="unit">Unit *</label>
                    <input id="unit" name="unit" type="text" class="form-control" value="{{ old('unit') }}" placeholder="e.g., PCS, BOX, SACK" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="quantity">Quantity *</label>
                    <input id="quantity" name="quantity" type="number" min="1" class="form-control" value="{{ old('quantity') }}" placeholder="e.g., 50" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="purpose">Purpose / Incident Reference *</label>
                    <input id="purpose" name="purpose" type="text" class="form-control" value="{{ old('purpose') }}" placeholder="e.g., Emergency Response - Flood 2024" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="location">Location</label>
                    <input id="location" name="location" type="text" class="form-control" value="{{ old('location') }}" placeholder="e.g., Disaster Zone A, Relief Center">
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="released_at">Date & Time *</label>
                    <input id="released_at" name="released_at" type="datetime-local" class="form-control" value="{{ old('released_at', now()->format('Y-m-d\TH:i')) }}" required>
                </div>
            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <button type="submit" class="btn btn-main">Release Items</button>
                <button type="reset" class="btn btn-soft">Clear Form</button>
            </div>
        </form>
    </section>
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
    </script>
@endpush
