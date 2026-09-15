@extends('layouts.app')

@section('title', 'Return Management')

@section('content')
    <h1 class="page-title">Return Management</h1>
    <div class="page-subtitle">Process returns of borrowed items</div>

    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
            <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Returnable Items</div>
            <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $items->count() }}</div>
        </div>
        <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
            <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Good Returns</div>
            <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $recentReturns->where('condition', 'Good')->count() }}</div>
        </div>
        <div class="bg-white border border-[#e2e8f0] p-4 rounded-xl shadow-sm">
            <div class="text-[#64748b] text-[12px] font-medium uppercase tracking-wider">Recent Returns</div>
            <div class="text-[28px] font-bold text-[#0f172a] mt-1">{{ $recentReturns->count() }}</div>
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
                        <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Item</th>
                        <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Quantity</th>
                        <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Condition</th>
                        <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReturns as $return)
                        <tr class="hover:bg-gray-50">
                            <td class="p-3 text-[13px] font-medium text-[#1e293b]">{{ $return->inventory->sku ?? 'N/A' }}</td>
                            <td class="p-3 text-[13px] text-[#475569]">{{ number_format($return->quantity) }}</td>
                            <td class="p-3 text-[13px] text-[#475569]">{{ $return->condition }}</td>
                            <td class="p-3 text-[13px] text-[#64748b]">{{ $return->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="p-8 text-center text-[#94a3b8] text-[13px]">No recent returns recorded yet.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>
@endsection