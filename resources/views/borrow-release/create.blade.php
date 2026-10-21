@extends('layouts.app')

@section('title', 'Borrow / Release')

@section('content')
    <style>
        .borrow-release-table { min-width: 980px; }
        .borrow-release-table th,
        .borrow-release-table td { vertical-align: middle; }
        .borrow-release-table tbody tr:hover { background: #f8fbfa; }
        .borrow-release-actions { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; }
        .borrow-review-button,
        .borrow-release-trigger {
            min-height: 40px;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 8px 12px;
            border-radius: 8px;
            font: inherit;
            font-size: 13px;
            font-weight: 600;
            line-height: 1.2;
            white-space: nowrap;
            cursor: pointer;
            list-style: none;
            transition: background-color .15s ease, border-color .15s ease, color .15s ease;
        }
        .borrow-review-button {
            color: #176b57;
            background: #fff;
            border: 1px solid #9bbdb3;
        }
        .borrow-review-button:hover { background: #eff8f5; border-color: #176b57; }
        .borrow-release-trigger {
            color: #fff;
            background: #087f5b;
            border: 1px solid #087f5b;
        }
        .borrow-release-trigger:hover { background: #066b4d; border-color: #066b4d; }
        .borrow-release-details { margin: 0; }
        .borrow-release-trigger::-webkit-details-marker { display: none; }

        @media (max-width: 720px) {
            .borrow-release-actions { align-items: stretch; }
            .borrow-review-button,
            .borrow-release-trigger { flex: 1 1 auto; }
        }
    </style>
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
        <div class="mb-5">
            <h1 class="page-title">Borrow / Release</h1>
        </div>

        <section class="bg-white border border-[#e2e8f0] p-5 rounded-xl shadow-sm">
            <div class="flex flex-wrap justify-between items-end gap-4 mb-4">
                <h2 class="text-[16px] font-semibold text-[#0f172a] m-0">Approved requests awaiting release</h2>
                <form method="GET" action="{{ route('borrow-release.create') }}" class="flex gap-2 m-0">
                    <label class="sr-only" for="release-search">Search requests</label>
                    <input id="release-search" name="search" type="search" value="{{ $search }}" class="form-input" placeholder="Search request, item, SKU, or requester">
                    <button type="submit" class="btn-soft">Search</button>
                </form>
            </div>

            <div class="w-full overflow-x-auto">
                <table class="borrow-release-table w-full border-collapse text-left m-0">
                    <thead>
                        <tr class="bg-[#f8fafc] border-b border-[#e2e8f0]">
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Request ID</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Item</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">SKU</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Requester</th>
                            <th class="p-3 text-[11px] font-semibold text-[#64748b] uppercase">Approved quantity</th>
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
                                $canReleaseApprovedQuantity = $remainingQuantity > 0 && $availableQuantity >= $remainingQuantity;
                                $isOldInput = old('request_id') == $approvedRequest->id;
                            @endphp
                            <tr class="align-top">
                                <td class="p-3 text-[13px] font-medium text-[#1e293b]">{{ $approvedRequest->request_code }}</td>
                                <td class="p-3 text-[13px] text-[#1e293b]">{{ $inventory?->name ?? 'Item unavailable' }}</td>
                                <td class="p-3 text-[13px] text-[#475569]">{{ $inventory?->sku ?? '—' }}</td>
                                <td class="p-3 text-[13px] text-[#475569]">{{ $approvedRequest->source ?: ($approvedRequest->responder_email ?: '—') }}</td>
                                <td class="p-3 text-[13px] font-semibold text-[#1e293b]">{{ number_format($approvedQuantity) }}</td>
                                <td class="p-3 min-w-[240px]">
                                    @if($inventory)
                                        <div class="borrow-release-actions">
                                            <button type="button" class="borrow-review-button review-request-button"
                                                data-request="{{ $approvedRequest->request_code }}"
                                                data-requester="{{ $approvedRequest->source ?: ($approvedRequest->responder_email ?: '—') }}"
                                                data-item="{{ $inventory->name }}"
                                                data-sku="{{ $inventory->sku }}"
                                                data-approved="{{ $approvedQuantity }}"
                                                data-remaining="{{ $remainingQuantity }}"
                                                data-available="{{ $availableQuantity }}">
                                                <i class="fa-regular fa-eye" aria-hidden="true"></i>
                                                Review
                                            </button>
                                        @if($canReleaseApprovedQuantity)
                                        <details class="borrow-release-details">
                                            <summary class="borrow-release-trigger">
                                                <i class="fa-solid fa-box-open" aria-hidden="true"></i>
                                                Release Item
                                            </summary>
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
                                                    <label class="form-label" for="quantity-{{ $approvedRequest->id }}">Approved quantity to release</label>
                                                    <input id="quantity-{{ $approvedRequest->id }}" name="quantity" type="number" min="{{ $remainingQuantity }}" max="{{ $remainingQuantity }}" value="{{ $remainingQuantity }}" class="form-input" readonly aria-readonly="true" required>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="delivery-{{ $approvedRequest->id }}">Delivery personnel</label>
                                                    <select id="delivery-{{ $approvedRequest->id }}" name="delivery_personnel_id" class="form-select" required>
                                                        <option value="">Choose delivery personnel...</option>
                                                        @forelse($deliveryPersonnel as $person)
                                                            <option value="{{ $person->id }}" {{ $isOldInput && old('delivery_personnel_id') == $person->id ? 'selected' : '' }}>{{ $person->name }}</option>
                                                        @empty
                                                            <option value="" disabled>No delivery personnel accounts found. Ask an admin to add one in Users &amp; Roles.</option>
                                                        @endforelse
                                                    </select>
                                                </div>
                                                <div>
                                                    <label class="form-label" for="location-{{ $approvedRequest->id }}">Destination</label>
                                                    <input id="location-{{ $approvedRequest->id }}" name="location" type="text" value="{{ $isOldInput ? old('location') : '' }}" class="form-input" maxlength="255" required>
                                                </div>
                                                <button type="button" class="btn-main w-full review-release-button">Confirm release details</button>
                                            </form>
                                        </details>
                                        @elseif($remainingQuantity > 0)
                                            <span class="text-[12px] text-rose-700">Insufficient stock ({{ number_format($availableQuantity) }} available).</span>
                                        @else
                                            <span class="text-[12px] text-rose-700">No quantity remains to release.</span>
                                        @endif
                                        </div>
                                    @else
                                        <span class="text-[12px] text-rose-700">Item unavailable.</span>
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
    </div>

    <dialog id="requestDetailsDialog" class="request-review-dialog" aria-labelledby="requestDetailsTitle">
        <div class="request-review-header">
            <div>
                <h2 id="requestDetailsTitle">Request details</h2>
                <p class="request-review-code">Approved quantities and current stock.</p>
            </div>
            <button type="button" class="users-action" id="closeRequestDetails" aria-label="Close request details">Close</button>
        </div>
        <dl class="request-review-grid">
            <div><dt>Request ID</dt><dd id="detailsRequestId"></dd></div>
            <div><dt>Requester</dt><dd id="detailsRequester"></dd></div>
            <div><dt>Item</dt><dd id="detailsItem"></dd></div>
            <div><dt>SKU</dt><dd id="detailsSku"></dd></div>
            <div><dt>Approved quantity</dt><dd id="detailsApproved"></dd></div>
            <div><dt>Remaining to release</dt><dd id="detailsRemaining"></dd></div>
            <div><dt>Available stock</dt><dd id="detailsAvailable"></dd></div>
        </dl>
        <div class="request-review-buttons px-5 pb-5">
            <button type="button" class="btn-soft" id="doneRequestDetails">Close</button>
        </div>
    </dialog>

    <dialog id="releaseReviewDialog" class="request-review-dialog" aria-labelledby="releaseReviewTitle">
        <div class="request-review-header">
            <div>
                <h2 id="releaseReviewTitle">Confirm release</h2>
                <p class="request-review-code">Confirm the approved item and quantity.</p>
            </div>
            <button type="button" class="users-action" id="closeReleaseReview" aria-label="Close release review">Close</button>
        </div>
        <dl class="request-review-grid">
            <div><dt>Approved request</dt><dd id="reviewReleaseRequest"></dd></div>
            <div><dt>Item / variant</dt><dd id="reviewReleaseItem"></dd></div>
            <div><dt>Approved quantity</dt><dd id="reviewReleaseApproved"></dd></div>
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
        const requestDetailsDialog = document.getElementById('requestDetailsDialog');

        document.querySelectorAll('.review-request-button').forEach((button) => {
            button.addEventListener('click', () => {
                document.getElementById('detailsRequestId').textContent = button.dataset.request;
                document.getElementById('detailsRequester').textContent = button.dataset.requester;
                document.getElementById('detailsItem').textContent = button.dataset.item;
                document.getElementById('detailsSku').textContent = button.dataset.sku;
                document.getElementById('detailsApproved').textContent = button.dataset.approved;
                document.getElementById('detailsRemaining').textContent = button.dataset.remaining;
                document.getElementById('detailsAvailable').textContent = button.dataset.available;
                requestDetailsDialog.showModal();
            });
        });

        document.getElementById('closeRequestDetails').addEventListener('click', () => requestDetailsDialog.close());
        document.getElementById('doneRequestDetails').addEventListener('click', () => requestDetailsDialog.close());

        document.querySelectorAll('.release-form').forEach((form) => {
            form.querySelector('.review-release-button')?.addEventListener('click', () => {
                if (!form.reportValidity()) return;

                const delivery = form.querySelector('[name="delivery_personnel_id"]');
                const deliveryName = delivery.options[delivery.selectedIndex]?.text || 'Not selected';
                const quantity = Number(form.querySelector('[name="quantity"]').value);
                const available = Number(form.dataset.available);

                document.getElementById('reviewReleaseRequest').textContent = form.dataset.request;
                document.getElementById('reviewReleaseItem').textContent = `${form.dataset.sku} - ${form.dataset.item}${form.dataset.variant ? ` (${form.dataset.variant})` : ''}`;
                document.getElementById('reviewReleaseApproved').textContent = form.dataset.approved;
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
