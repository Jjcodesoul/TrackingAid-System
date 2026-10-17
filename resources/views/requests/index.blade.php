@extends('layouts.app')

@section('content')
    <div style="margin-bottom:24px;">
        <h2 class="page-title">Requests</h2>
        <p class="page-subtitle">Review, approve, or reject incoming requests</p>
    </div>

    @if(session('success'))
        <div class="alert-box alert-success">{{ session('success') }}</div>
    @endif

    <div class="stat-grid stat-grid--four">
        <div class="stat-card"><p class="stat-label">Pending</p><p class="stat-value">{{ $stats['pending'] ?? 0 }}</p></div>
        <div class="stat-card"><p class="stat-label">Approved</p><p class="stat-value">{{ $stats['approved'] ?? 0 }}</p></div>
        <div class="stat-card"><p class="stat-label">Released</p><p class="stat-value">{{ $stats['released'] ?? 0 }}</p></div>
        <div class="stat-card"><p class="stat-label">Rejected</p><p class="stat-value">{{ $stats['rejected'] ?? 0 }}</p></div>
    </div>

    <section class="panel" style="padding:0; overflow:hidden;">
        <div class="requests-toolbar">
            <div class="request-filter-tabs">
                <button onclick="filterStatus('all')" id="tab-all" class="request-filter-tab is-active" type="button" aria-pressed="true">All</button>
                <button onclick="filterStatus('Pending')" id="tab-pending" class="request-filter-tab" type="button" aria-pressed="false">
                    Pending
                    <span class="request-count">
                        {{ $stats['pending'] ?? 0 }}
                    </span>
                </button>
                <button onclick="filterStatus('Approved')" id="tab-approved" class="request-filter-tab" type="button" aria-pressed="false">Approved</button>
                <button onclick="filterStatus('Partially Released')" id="tab-partially-released" class="request-filter-tab" type="button" aria-pressed="false">Partially Released</button>
                <button onclick="filterStatus('Released')" id="tab-released" class="request-filter-tab" type="button" aria-pressed="false">Released</button>
                <button onclick="filterStatus('Rejected')" id="tab-rejected" class="request-filter-tab" type="button" aria-pressed="false">Rejected</button>
            </div>

            <label class="sr-only" for="searchInput">Search requests</label>
            <input type="search" id="searchInput" class="request-search" aria-label="Search requests" placeholder="Search requests..." oninput="searchTable()">
        </div>

        <div style="overflow-x:auto;">
            <table class="table-ui requests-table" style="width:100%; border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Request ID</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Item</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">QTY</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Priority</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Requested By</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Date</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Status</th>
                        <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Actions</th>
                    </tr>
                </thead>
                <tbody id="requestsTable">
                    @php
                    @endphp

                    {{-- 1. LOCAL REQUESTS LOOP --}}
                    @if(isset($requests) && count($requests) > 0)
                        @foreach($requests as $req)
                            @php
                                $priorityKey = ucfirst(strtolower($req->priority ?? 'Low'));
                                $statusKey   = ucfirst(strtolower($req->status ?? 'Pending'));
                                $priorityClass = 'priority-'.strtolower($priorityKey);
                                $statusClass = match ($statusKey) {
                                    'Pending' => 'status-pending',
                                    'Approved' => 'status-approved',
                                    'Partially Released' => 'status-pending',
                                    'Released' => 'status-released',
                                    'Rejected' => 'status-rejected',
                                    default => 'status-neutral',
                                };
                                $stock = $stockAvailability[$req->inventory_id] ?? ['on_hand' => 0, 'reserved' => 0, 'available' => 0];
                                $item = $req->inventory?->item;
                                $variantLabel = collect([$item?->size_weight, $item?->variant && strtoupper($item->variant) !== 'NONE' ? $item->variant : null])->filter()->join(' / ');
                            @endphp
                            <tr class="req-row" data-status="{{ $statusKey }}" style="border-bottom:1px solid #f1f5f9;">
                                <td style="padding:14px 16px;">
                                    <span style="font-size:12px; font-weight:600; color:#64748b; font-family:monospace;">
                                        {{ $req->request_code ?? ('REQ-' . $req->id) }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:600; color:#1a202c; font-size:14px; display:block;">
                                        {{ $req->inventory?->name ?? $req->inventory?->item_name ?? 'Unknown Item' }}
                                    </span>
                                    <span style="font-size:11px; color:#94a3b8; font-family:monospace;">
                                        {{ $req->inventory?->sku ?? '—' }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-weight:700; color:#1a202c;">
                                    {{ number_format($req->quantity ?? 0) }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span class="priority-badge {{ $priorityClass }}">
                                        {{ $priorityKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                    {{ $req->source ?? $req->responder_email ?? 'Local User' }}
                                    @if(!empty($req->purpose))
                                        <span style="display:block; font-size:11px; color:#94a3b8;">
                                            {{ \Illuminate\Support\Str::limit($req->purpose, 30) }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                    {{ optional($req->created_at)->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <button type="button" class="users-action" title="Review request" aria-label="Review {{ $req->request_code }}" onclick="showLocalRequest(this)"
                                            data-request-code="{{ $req->request_code }}"
                                            data-source="{{ $req->source }}"
                                            data-requester="{{ $req->responder_email }}"
                                            data-purpose="{{ $req->purpose }}"
                                            data-item="{{ $req->inventory?->name }}"
                                            data-sku="{{ $req->inventory?->sku }}"
                                            data-variant="{{ $variantLabel }}"
                                            data-requested="{{ $req->quantity }}"
                                            data-approved="{{ $req->approved_quantity ?? $req->quantity }}"
                                            data-available="{{ $stock['available'] }}"
                                            data-on-hand="{{ $stock['on_hand'] }}"
                                            data-reserved="{{ $stock['reserved'] }}"
                                            data-status="{{ $statusKey }}"
                                            data-approval-notes="{{ $req->approval_notes }}"
                                            data-approve-url="{{ route('requests.approve', $req) }}"
                                            data-reject-url="{{ route('requests.reject', $req) }}">
                                            <i class="fa-regular fa-eye" aria-hidden="true"></i> Review
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 2. RESQOPERATION FORWARDED REQUESTS LOOP --}}
                    @if(isset($forwardedRequests) && count($forwardedRequests) > 0)
                        @foreach($forwardedRequests as $freq)
                            @php
                                $urgencyKey = ucfirst(strtolower($freq->urgency ?? 'Medium'));
                                $statusKey = match (strtolower($freq->resqperation_status ?? 'forwarded')) {
                                    'approved' => 'Approved',
                                    'rejected' => 'Rejected',
                                    'released' => 'Released',
                                    default => 'Pending',
                                };
                                $priorityClass = 'priority-'.strtolower($urgencyKey);
                                $statusClass = match ($statusKey) {
                                    'Pending' => 'status-pending',
                                    'Approved' => 'status-approved',
                                    'Released' => 'status-released',
                                    'Rejected' => 'status-rejected',
                                    default => 'status-neutral',
                                };
                            @endphp
                            <tr class="req-row" data-status="{{ $statusKey }}" style="border-bottom:1px solid #f1f5f9; background-color:#f0f9ff;">
                                <td style="padding:14px 16px;">
                                    <span style="font-size:12px; font-weight:600; color:#0369a1; font-family:monospace;">
                                        {{ $freq->tracking_reference }}
                                    </span>
                                    <span class="request-source-tag">
                                        ResQOperation
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:600; color:#1a202c; font-size:14px; display:block;">
                                        {{ $freq->item_name ?? $freq->resource_type ?? $freq->request_category ?? 'Unspecified item' }}
                                    </span>
                                    <span style="font-size:11px; color:#94a3b8; font-family:monospace;">
                                        {{ $freq->resqperation_request_id ?? 'Forwarded' }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-weight:700; color:#1a202c;">
                                    {{ number_format($freq->quantity ?? 0) }} {{ $freq->unit ?? '' }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span class="priority-badge {{ $priorityClass }}">
                                        {{ $urgencyKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                    {{ $freq->requested_by ?? $freq->request_source ?? 'External request' }}
                                    @if(!empty($freq->area_label))
                                        <span style="display:block; font-size:11px; color:#94a3b8;">
                                            {{ $freq->area_label }}
                                        </span>
                                    @endif
                                </td>
                                <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                    {{ optional($freq->created_at)->format('M d, Y') ?? 'N/A' }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span class="status-badge {{ $statusClass }}">
                                        {{ $statusKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <button
                                            type="button"
                                            title="View request details"
                                            aria-label="View {{ $freq->tracking_reference }} details"
                                            onclick="showForwardedRequest(this)"
                                            data-tracking-reference="{{ $freq->tracking_reference }}"
                                            data-request-id="{{ $freq->resqperation_request_id }}"
                                            data-source-reference="{{ $freq->source_reference }}"
                                            data-item="{{ $freq->item_name ?? $freq->resource_type ?? $freq->request_category ?? 'Unspecified item' }}"
                                            data-category="{{ $freq->request_category }}"
                                            data-resource-type="{{ $freq->resource_type }}"
                                            data-quantity="{{ $freq->quantity }}"
                                            data-unit="{{ $freq->unit }}"
                                            data-urgency="{{ $freq->urgency }}"
                                            data-requested-by="{{ $freq->requested_by }}"
                                            data-request-source="{{ $freq->request_source }}"
                                            data-source-system="{{ $freq->source_system }}"
                                            data-area-label="{{ $freq->area_label }}"
                                            data-area-note="{{ $freq->area_note }}"
                                            data-description="{{ $freq->description }}"
                                            data-validation-notes="{{ $freq->validation_notes }}"
                                            data-forwarded-at="{{ optional($freq->forwarded_at)->format('M d, Y H:i') }}"
                                            data-status="{{ $statusKey }}"
                                            style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px;"
                                        >
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @if($statusKey === 'Pending')
                                            <form action="{{ route('requests.forwarded.approve', $freq) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" title="Mark approved in TrackingAid" aria-label="Mark {{ $freq->tracking_reference }} approved in TrackingAid" onclick="return confirm('Mark this forwarded request approved in TrackingAid? No external status sync is configured.')" style="background:none; border:none; cursor:pointer; color:#059669; padding:4px; font-size:16px;">
                                                    <i class="fa-regular fa-circle-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('requests.forwarded.reject', $freq) }}" method="POST" onsubmit="return confirm('Reject this ResQperation request?')" style="margin:0;">
                                                @csrf
                                                <button type="submit" title="Reject" aria-label="Reject {{ $freq->tracking_reference }}" style="background:none; border:none; cursor:pointer; color:#DC2626; padding:4px; font-size:16px;">
                                                    <i class="fa-regular fa-circle-xmark"></i>
                                                </button>
                                            </form>
                                        @endif
                                    </div>
                                </td>
                            </tr>
                        @endforeach
                    @endif

                    {{-- 3. EMPTY STATE --}}
                    @if((!isset($requests) || count($requests) == 0) && (!isset($forwardedRequests) || count($forwardedRequests) == 0))
                        <tr>
                            <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                                No requests found.
                            </td>
                        </tr>
                    @endif
                </tbody>
            </table>
            <p id="requestNoResults" role="status" style="display:none; text-align:center; padding:24px; color:#64748b; font-size:13px;">No requests match these filters.</p>
        </div>
    </section>

    <dialog id="localRequestDialog" class="request-review-dialog" aria-labelledby="localRequestDialogTitle">
        <div class="request-review-header">
            <div>
                <h2 id="localRequestDialogTitle">Request review</h2>
                <p id="localRequestDialogCode" class="request-review-code"></p>
            </div>
            <button type="button" class="users-action" onclick="document.getElementById('localRequestDialog').close()" aria-label="Close request review">Close</button>
        </div>
        <dl class="request-review-grid">
            <div><dt>Requesting team / source</dt><dd data-local-detail="source"></dd></div>
            <div><dt>Requested by</dt><dd data-local-detail="requester"></dd></div>
            <div><dt>Item</dt><dd data-local-detail="item"></dd></div>
            <div><dt>SKU and variant</dt><dd data-local-detail="skuVariant"></dd></div>
            <div><dt>Original requested quantity</dt><dd data-local-detail="requested"></dd></div>
            <div><dt>Current status</dt><dd data-local-detail="status"></dd></div>
            <div><dt>Physical stock on hand</dt><dd data-local-detail="onHand"></dd></div>
            <div><dt>Reserved for approved requests</dt><dd data-local-detail="reserved"></dd></div>
            <div><dt>Available for new approvals</dt><dd data-local-detail="available"></dd></div>
            <div><dt>Purpose</dt><dd data-local-detail="purpose"></dd></div>
            <div class="request-review-full"><dt>Existing approval note</dt><dd data-local-detail="approvalNotes"></dd></div>
        </dl>
        <p id="requestShortageWarning" class="alert-box alert-warning" role="status" hidden></p>
        @if(auth()->user()->role === 'admin')
            <div id="localPendingActions" class="request-review-actions" hidden>
                <form id="localApproveForm" method="POST" onsubmit="return confirmLocalApproval()">
                    @csrf
                    <label class="form-label" for="approvedQuantity">Quantity to approve</label>
                    <input id="approvedQuantity" name="approved_quantity" class="form-input" type="number" min="1" required>
                    <p class="form-help">The original request stays unchanged. Reduce quantity only when stock is limited and give a reason.</p>
                    <label class="form-label" for="approvalNotes">Decision note</label>
                    <textarea id="approvalNotes" name="approval_notes" class="form-textarea" maxlength="1000"></textarea>
                    <div class="request-review-buttons">
                        <button id="approveRequestButton" class="btn-main" type="submit">Approve quantity</button>
                    </div>
                </form>
                <form id="localRejectForm" method="POST" onsubmit="return confirm('Reject this request?')">
                    @csrf
                    <button type="submit" class="btn-danger">Reject request</button>
                </form>
            </div>
        @endif
    </dialog>

    <dialog id="forwardedRequestDialog" aria-labelledby="forwardedRequestDialogTitle" style="width:min(640px, calc(100% - 32px)); max-height:85vh; overflow:auto; border:1px solid #e2e8f0; border-radius:8px; padding:0; box-shadow:0 24px 64px rgb(15 23 42 / 24%);">
        <div style="display:flex; align-items:center; justify-content:space-between; gap:16px; padding:18px 20px; border-bottom:1px solid #e2e8f0;">
            <h2 id="forwardedRequestDialogTitle" style="margin:0; font-size:18px; color:#1a202c;">ResQperation request details</h2>
            <button type="button" onclick="document.getElementById('forwardedRequestDialog').close()" aria-label="Close request details" title="Close" style="background:none; border:0; color:#64748b; cursor:pointer; font-size:20px;">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <dl style="display:grid; grid-template-columns:repeat(auto-fit, minmax(220px, 1fr)); gap:16px 20px; margin:0; padding:20px;">
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Tracking reference</dt><dd data-request-detail="trackingReference" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">ResQperation request ID</dt><dd data-request-detail="requestId" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Source reference</dt><dd data-request-detail="sourceReference" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Item</dt><dd data-request-detail="item" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Category</dt><dd data-request-detail="category" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Resource type</dt><dd data-request-detail="resourceType" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Quantity</dt><dd data-request-detail="quantity" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Urgency</dt><dd data-request-detail="urgency" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Requested by</dt><dd data-request-detail="requestedBy" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Request source</dt><dd data-request-detail="requestSource" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Area</dt><dd data-request-detail="areaLabel" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Status</dt><dd data-request-detail="status" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div style="grid-column:1/-1;"><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Area notes</dt><dd data-request-detail="areaNote" style="margin:4px 0 0; white-space:pre-wrap; overflow-wrap:anywhere;"></dd></div>
            <div style="grid-column:1/-1;"><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Description</dt><dd data-request-detail="description" style="margin:4px 0 0; white-space:pre-wrap; overflow-wrap:anywhere;"></dd></div>
            <div style="grid-column:1/-1;"><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Validation notes</dt><dd data-request-detail="validationNotes" style="margin:4px 0 0; white-space:pre-wrap; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Forwarded at</dt><dd data-request-detail="forwardedAt" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
            <div><dt style="font-size:11px; color:#64748b; text-transform:uppercase;">Source system</dt><dd data-request-detail="sourceSystem" style="margin:4px 0 0; overflow-wrap:anywhere;"></dd></div>
        </dl>
    </dialog>

    <script>
        let activeRequestStatus = 'all';

        function showLocalRequest(button) {
            const details = {
                source: button.dataset.source,
                requester: button.dataset.requester,
                item: button.dataset.item,
                skuVariant: [button.dataset.sku, button.dataset.variant].filter(Boolean).join(' / '),
                requested: button.dataset.requested,
                status: button.dataset.status,
                onHand: button.dataset.onHand,
                reserved: button.dataset.reserved,
                available: button.dataset.available,
                purpose: button.dataset.purpose,
                approvalNotes: button.dataset.approvalNotes,
            };
            Object.entries(details).forEach(([key, value]) => {
                const field = document.querySelector(`[data-local-detail="${key}"]`);
                field.textContent = value || 'Not provided';
            });

            document.getElementById('localRequestDialogCode').textContent = button.dataset.requestCode;

            const pending = button.dataset.status === 'Pending';
            const actions = document.getElementById('localPendingActions');
            if (actions) actions.hidden = !pending;

            const quantity = document.getElementById('approvedQuantity');
            const available = Number(button.dataset.available || 0);
            const requested = Number(button.dataset.requested || 0);
            const approvable = Math.min(available, requested);
            if (quantity) {
                quantity.max = String(requested);
                quantity.value = String(approvable || requested);
                quantity.disabled = available < 1;
                document.getElementById('approvalNotes').value = '';
                document.getElementById('localApproveForm').action = button.dataset.approveUrl;
                document.getElementById('localRejectForm').action = button.dataset.rejectUrl;
            }

            const warning = document.getElementById('requestShortageWarning');
            const shortage = Math.max(requested - available, 0);
            warning.hidden = !pending || shortage === 0;
            warning.textContent = shortage > 0
                ? `Requested ${requested}; ${available} available for approval; shortage ${shortage}. Reduce the approved quantity with a reason, or restock before approving the full request.`
                : '';
            const approveButton = document.getElementById('approveRequestButton');
            if (approveButton) approveButton.disabled = available < 1;
            document.getElementById('localRequestDialog').showModal();
        }

        function confirmLocalApproval() {
            const requested = Number(document.getElementById('approvedQuantity').max);
            const approved = Number(document.getElementById('approvedQuantity').value);
            const notes = document.getElementById('approvalNotes').value.trim();

            if (approved < requested && notes === '') {
                document.getElementById('approvalNotes').setCustomValidity('Add a reason for approving less than requested.');
                document.getElementById('approvalNotes').reportValidity();
                return false;
            }

            document.getElementById('approvalNotes').setCustomValidity('');
            return confirm(`Approve ${approved} of ${requested} requested units?`);
        }

        function showForwardedRequest(button) {
            const details = {
                trackingReference: button.dataset.trackingReference,
                requestId: button.dataset.requestId,
                sourceReference: button.dataset.sourceReference,
                item: button.dataset.item,
                category: button.dataset.category,
                resourceType: button.dataset.resourceType,
                quantity: `${button.dataset.quantity ?? ''} ${button.dataset.unit ?? ''}`.trim(),
                urgency: button.dataset.urgency,
                requestedBy: button.dataset.requestedBy,
                requestSource: button.dataset.requestSource,
                sourceSystem: button.dataset.sourceSystem,
                areaLabel: button.dataset.areaLabel,
                areaNote: button.dataset.areaNote,
                description: button.dataset.description,
                validationNotes: button.dataset.validationNotes,
                forwardedAt: button.dataset.forwardedAt,
                status: button.dataset.status,
            };

            Object.entries(details).forEach(([key, value]) => {
                const field = document.querySelector(`[data-request-detail="${key}"]`);
                field.textContent = value || 'Not provided';
            });

            document.getElementById('forwardedRequestDialog').showModal();
        }

        function filterStatus(status) {
            activeRequestStatus = status;
            const tabs = ['all', 'Pending', 'Approved', 'Partially Released', 'Released', 'Rejected'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t.toLowerCase().replaceAll(' ', '-'));
                if (!btn) return;

                const active = (t === 'all' && status === 'all') || t === status;
                btn.setAttribute('aria-pressed', String(active));
                btn.classList.toggle('is-active', active);
            });
            applyRequestFilters();
        }

        function searchTable() {
            applyRequestFilters();
        }

        function applyRequestFilters() {
            const search = document.getElementById('searchInput').value.toLowerCase();
            let visibleCount = 0;
            document.querySelectorAll('.req-row').forEach(row => {
                const matchesStatus = activeRequestStatus === 'all' || row.dataset.status === activeRequestStatus;
                const matchesSearch = row.innerText.toLowerCase().includes(search);
                const visible = matchesStatus && matchesSearch;
                row.style.display = visible ? '' : 'none';
                if (visible) visibleCount++;
            });
            document.getElementById('requestNoResults').style.display = visibleCount === 0 ? '' : 'none';
        }
    </script>
@endsection
