@extends('layouts.app')

@section('title', 'Return Management')

@section('content')
    <h1 class="page-title">Return Management</h1>
    <div class="page-subtitle">Process returns of borrowed items</div>

    <section class="panel">
        <form action="{{ route('returns.store') }}" method="POST">
            @csrf

            <div class="row g-4">
                <div class="col-lg-6">
                    <label class="form-label" for="inventory_id">Item (SKU) *</label>
                    <select id="inventory_id" name="inventory_id" class="form-select" required>
                        <option value="">Choose an item...</option>
                        @foreach($items as $item)
                            <option value="{{ $item->id }}" {{ old('inventory_id') == $item->id ? 'selected' : '' }}>
                                {{ $item->sku }} - {{ $item->name }} (Current: {{ $item->quantity }})
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="quantity">Quantity *</label>
                    <input id="quantity" name="quantity" type="number" min="1" class="form-control" value="{{ old('quantity') }}" placeholder="e.g., 30" required>
                </div>

                <div class="col-lg-6">
                    <label class="form-label" for="condition">Condition *</label>
                    <select id="condition" name="condition" class="form-select" required>
                        @foreach(['Good', 'Damaged', 'Missing'] as $condition)
                            <option value="{{ $condition }}" {{ old('condition', 'Good') === $condition ? 'selected' : '' }}>
                                {{ $condition }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-12">
                    <label class="form-label" for="notes">Notes</label>
                    <textarea id="notes" name="notes" class="form-control" placeholder="Add any notes about the return...">{{ old('notes') }}</textarea>
                </div>
            </div>

            <div class="note mt-4">
                <strong>Note:</strong> Items marked as "Damaged" will be quarantined for inspection. Items marked as "Missing" will be logged but not added back to inventory.
            </div>

            <div class="d-flex flex-wrap gap-3 mt-4">
                <button type="submit" class="btn btn-main">Process Return</button>
                <button type="reset" class="btn btn-soft">Clear Form</button>
            </div>
        </form>
    </section>
@endsection
