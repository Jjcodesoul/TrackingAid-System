@extends('layouts.app')

@section('title', 'Return Management')

@section('content')
    <h1 class="page-title">Return Management</h1>
    <div class="page-subtitle">Process returns of borrowed items</div>

    <div class="row g-3 mb-4">
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Returnable Items</div>
                <div class="display-6 fw-bold">{{ $items->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Good Returns</div>
                <div class="display-6 fw-bold">{{ $recentReturns->where('condition', 'Good')->count() }}</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="panel p-3">
                <div class="text-muted small">Recent Returns</div>
                <div class="display-6 fw-bold">{{ $recentReturns->count() }}</div>
            </div>
        </div>
    </div>

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

    <section class="panel mt-4">
        <div class="d-flex justify-content-between align-items-center mb-3">
            <h5 class="mb-0">Recent Return Activity</h5>
            <span class="text-muted small">Latest 5 records</span>
        </div>

        <div class="table-responsive">
            <table class="table align-middle">
                <thead>
                    <tr>
                        <th>Item</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReturns as $return)
                        <tr>
                            <td>{{ $return->inventory->sku ?? 'N/A' }}</td>
                            <td>{{ number_format($return->quantity) }}</td>
                            <td>{{ $return->condition }}</td>
                            <td>{{ $return->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="text-center text-muted py-4">No recent returns recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection
