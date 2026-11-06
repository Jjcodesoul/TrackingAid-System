@extends('layouts.app')

@section('title', 'Item History')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Item history</h1>
        <p class="page-subtitle">{{ $item->name }} · SKU {{ $item->sku }}</p>
    </div>
    <a href="{{ route('inventory.index') }}" class="btn-soft">Back to inventory</a>
</div>

<section class="card-ui history-summary" aria-label="Item creation details">
    <div><span>Added to inventory</span><strong>{{ $item->created_at?->format('M d, Y H:i') ?? 'Date unavailable' }}</strong></div>
    <div><span>Added by</span><strong>{{ $item->creator?->name ?? 'Not recorded' }}</strong></div>
    <div><span>Current stock</span><strong>{{ number_format($item->total_stock) }} {{ $item->unit_type }}</strong></div>
    <p>Older receipts may not retain their original quantities, users, or locations. In that case, the transaction balance is marked incomplete.</p>
</section>

<section class="card-ui history-table-wrap" aria-label="Inventory transactions">
    <div class="table-scroll">
        <table class="table-ui history-table">
            <thead><tr>
                <th scope="col">Date and time</th>
                <th scope="col">Activity</th>
                <th scope="col">Quantity change</th>
                <th scope="col">Remaining</th>
                <th scope="col">Location</th>
                <th scope="col">User / source</th>
                <th scope="col">Details</th>
            </tr></thead>
            <tbody>
            @forelse($events as $event)
                <tr>
                    <td>{{ $event['date']?->format('M d, Y H:i') ?? 'Date unavailable' }}</td>
                    <td>{{ $event['kind'] }}</td>
                    <td>{{ $event['quantity'] }}</td>
                    <td>{{ $event['remaining'] === null ? 'Incomplete' : number_format($event['remaining']) }}</td>
                    <td>{{ $event['location'] }}</td>
                    <td>{{ $event['user'] }}</td>
                    <td>{{ $event['details'] ?: '—' }}</td>
                </tr>
            @empty
                <tr><td colspan="7" class="history-empty">No recorded inventory transactions are available.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</section>
@endsection
