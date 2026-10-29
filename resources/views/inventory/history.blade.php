@extends('layouts.app')

@section('content')
<div class="page-header" style="margin-bottom:16px;">
    <div><h2 class="page-title">Item History</h2><p class="page-subtitle">{{ $item->name }} · SKU {{ $item->sku }}</p></div>
    <a href="{{ route('inventory.index') }}" class="btn-soft">Back to Inventory</a>
</div>
<div class="card-ui" style="padding:20px; margin-bottom:16px;">
    <strong>Item created:</strong> {{ $item->created_at?->format('M d, Y H:i') ?? 'Date unavailable' }}
    · <strong>Added by:</strong> {{ $item->creator?->name ?? 'Not recorded' }}
    <p style="margin:8px 0 0; color:#64748b;">Historical records shown below are only those available in the database. Creator tracking applies to records created after the tracking migration.</p>
</div>
<div class="card-ui" style="padding:0; overflow:auto;">
    <table style="width:100%; border-collapse:collapse;">
        <thead><tr style="text-align:left; color:#64748b; font-size:11px; text-transform:uppercase;">
            <th style="padding:12px 16px;">Date / time</th><th style="padding:12px 16px;">Record</th><th style="padding:12px 16px;">Quantity change</th><th style="padding:12px 16px;">Location</th><th style="padding:12px 16px;">User</th><th style="padding:12px 16px;">Details</th>
        </tr></thead><tbody>
        @php($events = collect())
        @foreach($item->stockBatches as $batch)
            @php($events->push(['date'=>$batch->created_at,'kind'=>$batch->movement_type === 'initial-import' ? 'Initial inventory balance' : 'Stock-in','quantity'=>'+'.number_format($batch->quantity),'location'=>$batch->storage_location ?: 'Not recorded','user'=>$batch->creator?->name ?? 'Not recorded','details'=>trim(($batch->supplier ?? '').($batch->date_received ? ' · Received '.$batch->date_received->format('M d, Y') : ''))]))
        @endforeach
        @foreach($releases as $release)
            @php($events->push(['date'=>$release->released_at,'kind'=>'Release','quantity'=>'−'.number_format($release->quantity),'location'=>$release->location ?: 'Not recorded','user'=>$release->processedBy?->name ?? 'Not recorded','details'=>$release->purpose]))
        @endforeach
        @foreach($returns as $return)
            @php($events->push(['date'=>$return->created_at,'kind'=>'Return ('.$return->condition.')','quantity'=>$return->condition === 'Good' ? '+'.number_format($return->quantity) : '0 (no stock increase)','location'=>'Not recorded','user'=>$return->processedBy?->name ?? 'Not recorded','details'=>$return->notes ?? '']))
        @endforeach
        @forelse($events->sortByDesc('date') as $event)
            <tr style="border-top:1px solid #f1f5f9;"><td style="padding:12px 16px;">{{ $event['date']?->format('M d, Y H:i') ?? 'Date unavailable' }}</td><td style="padding:12px 16px;">{{ $event['kind'] }}</td><td style="padding:12px 16px;">{{ $event['quantity'] }}</td><td style="padding:12px 16px;">{{ $event['location'] }}</td><td style="padding:12px 16px;">{{ $event['user'] }}</td><td style="padding:12px 16px;">{{ $event['details'] }}</td></tr>
        @empty
            <tr><td colspan="6" style="padding:24px; text-align:center; color:#64748b;">No recorded inventory movements are available.</td></tr>
        @endforelse
        </tbody>
    </table>
</div>
@endsection
