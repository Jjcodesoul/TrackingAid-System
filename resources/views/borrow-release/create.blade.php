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

        <section class="bg-white border border-[#e2e8f0] p-6 rounded-xl shadow-sm mb-6">
            <div class="flex flex-wrap justify-between items-end gap-4 mb-4">
                <div>
                    <h2 class="text-[16px] font-semibold text-[#0f172a] m-0">Approved requests awaiting release</h2>
                    <p class="text-[12px] text-[#64748b] mt-1 mb-0">Items and quantities are loaded from the approved request.</p>
                </div>
                <form method="GET" action="{{ route('borrow-release.create') }}" class="flex gap-2 m-0">
                    <label class="sr-only" for="release-search">Search requests</label>
                    <input id="release-search" name="search" type="search" value="{{ $search }}" class="form-input" placeholder="Search request or SKU">
                    <button type="submit" class="btn-soft">Search</button>
                </form>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="w-full border-collapse text-left m-0">
                    <thead>
                        <tr class="bg-[#f8fafc] border-b border-[#e2e8f0]">
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Request</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Approved item</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Quantity progress</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Stock</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Status</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Action</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-[#f1f5f9]">
                        @forelse($approvedRequests as $approvedRequest)
                            @php
                                $inventory = $approvedRequest->inventory;
                                $item = $inventory?->item;
                                $releasedQuantity = (int) ($approvedRequest->released_quantity ?? 0);
                                $approvedQuantity = (int) ($approvedRequest->approved_quantity ?? $approvedRequest->quantity);
                                $remainingQuantity = max($approvedQuantity - $releasedQuantity, 0);
                                $availableQuantity = max((int) ($inventory?->quantity ?? 0), 0);
                                $releasableQuantity = min($remainingQuantity, $availableQuantity);
                                $isPartial = $releasedQuantity > 0;
                                $isOldInput = old('request_id') == $approvedRequest->id;
                            @endphp
                            <tr class="align-top">
                                <td class="p-3 text-[13px] text-[#1e293b]">
                                    <strong>{{ $approvedRequest->request_code }}</strong>
                                    <div class="text-[12px] text-[#64748b]">{{ $approvedRequest->source }}</div>
                                    <div class="text-[12px] text-[#64748b]">{{ $approvedRequest->responder_email ?: 'No requester email' }}</div>
                                    <div class="text-[11px] text-[#94a3b8]">{{ $approvedRequest->created_at?->format('M d, Y') }}</div>
                                </td>
                                <td class="p-3 text-[13px] text-[#1e293b]">
                                    <strong>{{ $inventory?->sku ?? 'Inventory unavailable' }}</strong>
                                    <div>{{ $inventory?->name ?? 'Item no longer available' }}</div>
                                    <div class="text-[12px] text-[#64748b]">
                                        {{ collect([$item?->size_weight, $item?->variant !== 'NONE' ? $item?->variant : null])->filter()->join(' / ') ?: ($item?->unit_type ?? '') }}
                                    </div>
                                </td>
                                <td class="p-3 text-[13px] text-[#1e293b]">
                                    <div>Requested: {{ number_format($approvedRequest->quantity) }}</div>
                                    <div>Approved: {{ number_format($approvedQuantity) }}</div>
                                    <div>Released: {{ number_format($releasedQuantity) }}</div>
                                    <div class="font-semibold">Remaining: {{ number_format($remainingQuantity) }}</div>
                                </td>
                                <td class="p-3 text-[13px] text-[#1e293b]">
                                    <div>Available: {{ number_format($availableQuantity) }}</div>
                                    @if($availableQuantity < $remainingQuantity)
                                        <div class="text-[12px] text-amber-700">Short by {{ number_format($remainingQuantity - $availableQuantity) }}; partial releases are limited to current stock.</div>
                                    @endif
                                </td>
                                <td class="p-3">
                                    <span class="inline-flex px-2 py-1 rounded text-[11px] font-semibold {{ $isPartial ? 'bg-amber-100 text-amber-800' : 'bg-emerald-100 text-emerald-800' }}">
                                        {{ $isPartial ? 'Partially Released' : 'Approved / Awaiting Release' }}
                                    </span>
                                </td>
                                <td class="p-3 min-w-[260px]">
                                    @if($inventory && $releasableQuantity > 0)
                                        <details>
                                            <summary class="btn-main cursor-pointer text-center">Process Release</summary>
                                            <form action="{{ route('borrow-release.store') }}" method="POST" class="release-form mt-3 space-y-3"
                                                data-request="{{ $approvedRequest->request_code }}"
                                                data-item="{{ $inventory->name }}"
                                                data-sku="{{ $inventory->sku }}"
                                                data-variant="{{ collect([$item?->size_weight, $item?->variant && strtoupper($item->variant) !== 'NONE' ? $item->variant : null])->filter()->join(' / ') }}"
                                                data-requested="{{ $approvedRequest->quantity }}"
                                                data-approved="{{ $approvedQuantity }}"
                                                data-released="{{ $releasedQuantity }}"
                                                data-remaining="{{ $remainingQuantity }}"
                                                data-available="{{ $availableQuantity }}">
                                                @csrf
                                                <input type="hidden" name="request_id" value="{{ $approvedRequest->id }}">
                                                <input type="hidden" name="idempotency_key" value="{{ $isOldInput ? old('idempotency_key') : (string) \Illuminate\Support\Str::uuid() }}">

                                                <div>
                                                    <label class="form-label" for="quantity-{{ $approvedRequest->id }}">Release quantity</label>
                                                    <input id="quantity-{{ $approvedRequest->id }}" name="quantity" type="number" min="1" max="{{ $releasableQuantity }}" value="{{ $isOldInput ? old('quantity', $releasableQuantity) : $releasableQuantity }}" class="form-input" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="delivery-{{ $approvedRequest->id }}">Delivery personnel</label>
                                                    <select id="delivery-{{ $approvedRequest->id }}" name="delivery_personnel_id" class="form-select" required>
                                                        <option value="">Choose delivery personnel...</option>
                                                        @foreach($deliveryPersonnel as $person)
                                                            <option value="{{ $person->id }}" {{ $isOldInput && old('delivery_personnel_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                                                        @endforeach
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="location-{{ $approvedRequest->id }}">Destination</label>
                                                    <input id="location-{{ $approvedRequest->id }}" name="location" type="text" value="{{ $isOldInput ? old('location') : '' }}" class="form-input" maxlength="255" required>
                                                </div>
                                                <button type="button" class="btn-main w-full review-release-button">Review release</button>
                                            </form>
                                        </details>
                                    @else
                                        <span class="text-[12px] text-rose-700">No stock available to release.</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-[#64748b] text-[13px]">No approved requests are awaiting release.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <div class="mt-4">{{ $approvedRequests->links() }}</div>
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
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase tracking-wider">Processed By</th>
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
                                <td class="p-3 text-[13px] text-[#64748b]">{{ $release->processedBy?->name ?? '—' }}</td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="p-8 text-center text-[#94a3b8] text-[13px]">No recent releases recorded yet.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </section>
    </div>

    <dialog id="releaseReviewDialog" class="request-review-dialog" aria-labelledby="releaseReviewTitle">
        <div class="request-review-header">
            <div>
                <h2 id="releaseReviewTitle">Review release</h2>
                <p class="request-review-code">Confirm these values before inventory is deducted.</p>
            </div>
            <button type="button" class="users-action" id="closeReleaseReview" aria-label="Close release review">Close</button>
        </div>
        <dl class="request-review-grid">
            <div><dt>Approved request</dt><dd id="reviewReleaseRequest"></dd></div>
            <div><dt>Item / variant</dt><dd id="reviewReleaseItem"></dd></div>
            <div><dt>Original requested</dt><dd id="reviewReleaseRequested"></dd></div>
            <div><dt>Approved quantity</dt><dd id="reviewReleaseApproved"></dd></div>
            <div><dt>Previously released</dt><dd id="reviewReleasePrevious"></dd></div>
            <div><dt>Remaining approved</dt><dd id="reviewReleaseRemaining"></dd></div>
            <div><dt>Available stock</dt><dd id="reviewReleaseAvailable"></dd></div>
            <div><dt>Quantity in this release</dt><dd id="reviewReleaseQuantity"></dd></div>
            <div><dt>Delivery personnel</dt><dd id="reviewReleaseDelivery"></dd></div>
            <div><dt>Destination</dt><dd id="reviewReleaseLocation"></dd></div>
        </dl>
        <p class="alert-box alert-warning mx-5" id="releaseReviewShortage" hidden></p>
        <div class="request-review-buttons px-5 pb-5">
            <button type="button" class="btn-main" id="confirmReleaseButton">Confirm release</button>
        </div>
    </dialog>
@endsection

@push('scripts')
    <script>
        let releaseFormToSubmit = null;
        const releaseDialog = document.getElementById('releaseReviewDialog');

        document.querySelectorAll('.release-form').forEach((form) => {
            form.querySelector('.review-release-button')?.addEventListener('click', () => {
                if (!form.reportValidity()) return;

                const delivery = form.querySelector('[name="delivery_personnel_id"]');
                const deliveryName = delivery.options[delivery.selectedIndex]?.text || 'Not selected';
                const quantity = Number(form.querySelector('[name="quantity"]').value);
                const available = Number(form.dataset.available);

                document.getElementById('reviewReleaseRequest').textContent = form.dataset.request;
                document.getElementById('reviewReleaseItem').textContent = `${form.dataset.sku} - ${form.dataset.item}${form.dataset.variant ? ` (${form.dataset.variant})` : ''}`;
                document.getElementById('reviewReleaseRequested').textContent = form.dataset.requested;
                document.getElementById('reviewReleaseApproved').textContent = form.dataset.approved;
                document.getElementById('reviewReleasePrevious').textContent = form.dataset.released;
                document.getElementById('reviewReleaseRemaining').textContent = form.dataset.remaining;
                document.getElementById('reviewReleaseAvailable').textContent = form.dataset.available;
                document.getElementById('reviewReleaseQuantity').textContent = String(quantity);
                document.getElementById('reviewReleaseDelivery').textContent = deliveryName;
                document.getElementById('reviewReleaseLocation').textContent = form.querySelector('[name="location"]').value;

                const shortage = document.getElementById('releaseReviewShortage');
                shortage.hidden = quantity <= available;
                shortage.textContent = quantity > available ? `Available stock is ${available}; reduce the release quantity before confirming.` : '';
                releaseFormToSubmit = form;
                releaseDialog.showModal();
            });
        });

        document.getElementById('closeReleaseReview').addEventListener('click', () => releaseDialog.close());
        document.getElementById('confirmReleaseButton').addEventListener('click', () => {
            if (!releaseFormToSubmit || !releaseFormToSubmit.reportValidity()) return;

            const form = releaseFormToSubmit;
            releaseFormToSubmit = null;
            releaseDialog.close();
            const button = form.querySelector('.review-release-button');
            button.disabled = true;
            button.innerHTML = '<i class="fa-solid fa-spinner fa-spin" aria-hidden="true"></i> Processing...';
            form.requestSubmit();
        });
    </script>
@endpush