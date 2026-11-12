@extends('layouts.app')

@section('title', 'Return Management')

@section('content')
    <div class="page-header">
        <div>
            <h1 class="page-title">Return Management</h1>
            <p class="page-subtitle">Record returns against released reusable items. Consumables are not returnable.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="alert-box alert-success" role="status">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="alert-box alert-error" role="alert">
            <ul class="m-0 pl-5">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <section class="panel mb-4">
        <div class="flex flex-wrap items-end justify-between gap-3 mb-4">
            <div>
                <h2 class="section-title">Released reusable items with quantity outstanding</h2>
                <p class="page-subtitle">Damaged and missing items close the return balance but are not added to available stock.</p>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table-ui">
                <thead>
                    <tr>
                        <th>Request / Borrower</th>
                        <th>Item / SKU / Variant</th>
                        <th>Released</th>
                        <th>Returned</th>
                        <th>Outstanding</th>
                        <th>Return status</th>
                        <th>Release date</th>
                        <th>Record return</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($returnableReleases as $release)
                        @php
                            $returnedQuantity = (int) ($release->returned_quantity ?? 0);
                            $outstandingQuantity = max((int) $release->quantity - $returnedQuantity, 0);
                            $returnStatus = $returnedQuantity > 0 ? 'Partially returned' : 'Awaiting return';
                            $item = $release->inventory?->item;
                            $variant = collect([$item?->size_weight, $item?->variant && strtoupper($item->variant) !== 'NONE' ? $item->variant : null])->filter()->join(' / ');
                            $returnLocationVerified = $release->source_storage_location && $verifiedLocationNames->contains($release->source_storage_location);
                        @endphp
                        <tr>
                            <td>
                                <strong>{{ $release->request?->request_code ?? $release->forwardedRequest?->tracking_reference ?? 'Request unavailable' }}</strong>
                                <div class="text-sm text-slate-600">{{ $release->request?->source ?? $release->request?->responder_email ?? $release->forwardedRequest?->requested_by ?? 'Borrower not recorded' }}</div>
                                <div class="text-sm text-slate-600">{{ $release->location ?: 'Destination not recorded' }}</div>
                                <div class="text-sm {{ $returnLocationVerified ? 'text-slate-600' : 'text-rose-700' }}">Source: {{ $release->source_storage_location ?: 'not recorded' }}{{ $returnLocationVerified ? '' : ' — location verification required' }}</div>
                            </td>
                            <td>
                                <strong>{{ $release->inventory?->name ?? 'Item unavailable' }}</strong>
                                <div class="sku-code">{{ $release->inventory?->sku ?? '—' }}</div>
                                @if($variant !== '')
                                    <div class="text-sm text-slate-600">{{ $variant }}</div>
                                @endif
                            </td>
                            <td>{{ number_format($release->quantity) }} {{ $release->unit }}</td>
                            <td>{{ number_format($returnedQuantity) }}</td>
                            <td><span class="status-badge status-pending">{{ number_format($outstandingQuantity) }}</span></td>
                            <td><span class="status-badge {{ $returnedQuantity > 0 ? 'status-info' : 'status-pending' }}">{{ $returnStatus }}</span></td>
                            <td>{{ $release->released_at?->format('M d, Y') ?? '—' }}</td>
                            <td class="min-w-[280px]">
                                @if($returnLocationVerified)
                                <details>
                                    <summary class="btn-main cursor-pointer">Record return</summary>
                                    <form action="{{ route('returns.store') }}" method="POST" class="return-form mt-3 space-y-3" onsubmit="return confirmReturn(this)">
                                        @csrf
                                        <input type="hidden" name="borrow_release_id" value="{{ $release->id }}">
                                        <input type="hidden" name="idempotency_key" value="{{ (string) \Illuminate\Support\Str::uuid() }}">
                                        <div>
                                            <label class="form-label" for="return-quantity-{{ $release->id }}">Quantity to process</label>
                                            <input id="return-quantity-{{ $release->id }}" name="quantity" type="number" class="form-input" min="1" max="{{ $outstandingQuantity }}" value="{{ $outstandingQuantity }}" required>
                                        </div>
                                        <div>
                                            <label class="form-label" for="return-condition-{{ $release->id }}">Condition</label>
                                            <select id="return-condition-{{ $release->id }}" name="condition" class="form-select" required>
                                                <option value="Good">Good, restock</option>
                                                <option value="Damaged">Damaged, do not restock</option>
                                                <option value="Missing">Missing / lost, do not restock</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="form-label" for="returned-by-{{ $release->id }}">Returned by</label>
                                            <input id="returned-by-{{ $release->id }}" name="returned_by_name" type="text" class="form-input" maxlength="255" required autocomplete="name">
                                            <p class="form-help">The signed-in user will be recorded as receiving and inspecting the return.</p>
                                        </div>
                                        <div>
                                            <label class="form-label" for="return-notes-{{ $release->id }}">Notes</label>
                                            <textarea id="return-notes-{{ $release->id }}" name="notes" class="form-textarea" maxlength="2000"></textarea>
                                        </div>
                                        <button type="submit" class="btn-main">Confirm return</button>
                                    </form>
                                </details>
                                @else
                                    <span class="text-sm text-rose-700">An administrator must verify this release location before a return can be recorded.</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="text-center text-slate-600 py-8">No reusable releases currently have a return balance outstanding.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="mt-4">{{ $returnableReleases->links() }}</div>
    </section>

    <section class="panel">
        <div class="flex flex-wrap items-center justify-between gap-3 mb-3">
            <h2 class="section-title">Recent return activity</h2>
            <span class="text-sm text-slate-600">Latest five records</span>
        </div>
        <div class="table-wrap">
            <table class="table-ui">
                <thead>
                    <tr>
                        <th>Request</th>
                        <th>Item / SKU</th>
                        <th>Quantity</th>
                        <th>Condition</th>
                        <th>Returned by</th>
                        <th>Received / inspected by</th>
                        <th>Date and time</th>
                        <th>Notes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($recentReturns as $return)
                        <tr>
                            <td>{{ $return->borrowRelease?->request?->request_code ?? $return->borrowRelease?->forwardedRequest?->tracking_reference ?? 'Legacy / unlinked' }}</td>
                            <td>{{ $return->inventory?->name ?? 'Item unavailable' }} <span class="sku-code">{{ $return->inventory?->sku }}</span></td>
                            <td>{{ number_format($return->quantity) }}</td>
                            <td><span class="status-badge {{ $return->condition === 'Good' ? 'status-approved' : ($return->condition === 'Damaged' ? 'status-pending' : 'status-rejected') }}">{{ $return->condition === 'Missing' ? 'Missing / lost' : $return->condition }}</span></td>
                            <td>{{ $return->returned_by_name ?: 'Not recorded' }}</td>
                            <td>{{ $return->receivedBy?->name ?? $return->processedBy?->name ?? 'Not recorded' }}</td>
                            <td>{{ $return->created_at?->format('M d, Y H:i') ?? '—' }}</td>
                            <td>{{ $return->notes ?: '—' }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="text-center text-slate-600 py-8">No returns recorded yet.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </section>

    <script>
        function confirmReturn(form) {
            const quantity = form.querySelector('[name="quantity"]').value;
            const condition = form.querySelector('[name="condition"]');
            return confirm(`Record ${quantity} item(s) as ${condition.options[condition.selectedIndex].text}?`);
        }
    </script>
@endsection
