
@extends('layouts.app')

@section('content')
<div class="page-header">
    <div>
        <h1 class="page-title">Delivery Monitoring</h1>
        <p class="page-subtitle">
            Monitor released supplies and track delivery progress from the mobile app.
        </p>
    </div>

    <button type="button" id="refreshDeliveries" class="dm-refresh-btn">
        <i class="fa-solid fa-rotate"></i> Refresh
    </button>
</div>

<div class="dm-card">
    <div class="dm-toolbar">
        <div class="dm-search">
            <i class="fa-solid fa-magnifying-glass"></i>
            <input
                type="text"
                id="deliverySearch"
                placeholder="Search request, destination, item, or personnel..."
                autocomplete="off"
            >
        </div>

        <select id="statusFilter" class="dm-filter">
            <option value="">All statuses</option>
            <option value="Loading">Loading</option>
            <option value="Dispatched">Dispatched</option>
            <option value="In Transit">In Transit</option>
            <option value="Arrived">Arrived</option>
        </select>
    </div>

    <div class="dm-info">
        <span id="deliveryCount">0 deliveries</span>
        <span id="lastRefreshed">Loading deliveries...</span>
    </div>

    <div class="dm-table-wrap">
        <table class="dm-table">
            <thead>
                <tr>
                    <th>Request ID</th>
                    <th>Destination</th>
                    <th>Item / Quantity</th>
                    <th>Delivery Personnel</th>
                    <th>Released At</th>
                    <th>Status</th>
                    <th>Last Status Update</th>
                </tr>
            </thead>

            <tbody id="deliveryTableBody">
                @forelse ($deliveries as $delivery)
                    @php
                        $requestId = $delivery->request
                            ? ($delivery->request->request_id
                                ?? $delivery->request->id)
                            : 'N/A';

                        $destination = $delivery->location ?: 'N/A';

                        $itemName = $delivery->inventory
                            ? $delivery->inventory->name
                            : 'Unknown item';

                        $personnelName = $delivery->deliveryPersonnel
                            ? $delivery->deliveryPersonnel->name
                            : 'Unassigned';

                        $status = $delivery->delivery_status ?: 'Loading';

                        $statusTimestamp = match ($status) {
                            'Dispatched' => $delivery->dispatched_at,
                            'In Transit' => $delivery->in_transit_at,
                            'Arrived' => $delivery->arrived_at,
                            default => $delivery->released_at,
                        };

                        $searchText = strtolower(
                            $requestId . ' ' .
                            $destination . ' ' .
                            $itemName . ' ' .
                            $personnelName
                        );
                    @endphp

                    <tr
                        data-search="{{ $searchText }}"
                        data-status="{{ $status }}"
                    >
                        <td>
                            <strong>
                                {{ is_numeric($requestId) ? 'REQ-' . $requestId : $requestId }}
                            </strong>
                        </td>

                        <td>{{ $destination }}</td>

                        <td>
                            <strong>{{ $itemName }}</strong>
                            <div class="dm-muted">
                                {{ $delivery->quantity }} {{ $delivery->unit }}
                            </div>
                        </td>

                        <td>{{ $personnelName }}</td>

                        <td>
                            {{ $delivery->released_at
                                ? $delivery->released_at->format('M d, Y h:i A')
                                : 'N/A' }}
                        </td>

                        <td>
                            <span class="dm-status
                                {{ $status === 'Loading' ? 'status-loading' : '' }}
                                {{ $status === 'Dispatched' ? 'status-dispatched' : '' }}
                                {{ $status === 'In Transit' ? 'status-transit' : '' }}
                                {{ $status === 'Arrived' ? 'status-arrived' : '' }}">
                                {{ $status }}
                            </span>
                        </td>

                        <td>
                            <span class="dm-update-time">
                                {{ $statusTimestamp
                                    ? $statusTimestamp->format('M d, Y h:i A')
                                    : 'Awaiting update' }}
                            </span>
                        </td>
                    </tr>
                @empty
                    <tr id="emptyDeliveries">
                        <td colspan="7" class="dm-empty">
                            <i class="fa-solid fa-truck-fast"></i>
                            <p>No delivery records found.</p>
                            <span>Released supplies will appear here.</span>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div id="noSearchResults" class="dm-empty" style="display: none;">
        <i class="fa-solid fa-magnifying-glass"></i>
        <p>No matching deliveries</p>
        <span>Try another search term or status.</span>
    </div>

    <div class="dm-footer">
        <span>Updates automatically every 10 seconds.</span>
        <span id="refreshIndicator">
            <i class="fa-solid fa-circle-check"></i> Ready
        </span>
    </div>
</div>

<style>
    .dm-card {
        background: #fff;
        border: 1px solid #e5e7eb;
        border-radius: 14px;
        overflow: hidden;
        box-shadow: 0 2px 8px rgba(15, 23, 42, .04);
    }

    .dm-refresh-btn {
        border: none;
        border-radius: 8px;
        padding: 11px 16px;
        background: #0f172a;
        color: #fff;
        cursor: pointer;
        font-weight: 600;
        font-size: 13px;
    }

    .dm-refresh-btn:hover {
        background: #1e293b;
    }

    .dm-toolbar {
        display: flex;
        justify-content: space-between;
        align-items: center;
        gap: 12px;
        padding: 20px;
        border-bottom: 1px solid #e5e7eb;
    }

    .dm-search {
        display: flex;
        align-items: center;
        gap: 10px;
        flex: 1;
        max-width: 600px;
        padding: 0 13px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        color: #64748b;
    }

    .dm-search input {
        width: 100%;
        min-width: 0;
        padding: 11px 0;
        border: none;
        outline: none;
        background: transparent;
        color: #0f172a;
        font-size: 13px;
    }

    .dm-filter {
        min-width: 155px;
        padding: 11px 12px;
        border: 1px solid #d1d5db;
        border-radius: 8px;
        background: #fff;
        color: #0f172a;
        font-size: 13px;
    }

    .dm-info {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 14px 20px;
        color: #64748b;
        font-size: 12px;
    }

    .dm-table-wrap {
        width: 100%;
        overflow-x: auto;
    }

    .dm-table {
        width: 100%;
        border-collapse: collapse;
        text-align: left;
        white-space: nowrap;
    }

    .dm-table th {
        padding: 14px 16px;
        background: #f8fafc;
        color: #64748b;
        font-size: 11px;
        text-transform: uppercase;
        letter-spacing: .04em;
        font-weight: 700;
        border-top: 1px solid #e5e7eb;
        border-bottom: 1px solid #e5e7eb;
    }

    .dm-table td {
        padding: 16px;
        color: #334155;
        font-size: 13px;
        border-bottom: 1px solid #f1f5f9;
    }

    .dm-table tbody tr:hover {
        background: #f8fafc;
    }

    .dm-table td strong {
        color: #0f172a;
    }

    .dm-muted {
        margin-top: 4px;
        color: #64748b;
        font-size: 12px;
    }

    .dm-status {
        display: inline-block;
        padding: 6px 10px;
        border-radius: 20px;
        font-size: 11px;
        font-weight: 700;
    }

    .status-loading {
        background: #fef3c7;
        color: #92400e;
    }

    .status-dispatched {
        background: #dbeafe;
        color: #1d4ed8;
    }

    .status-transit {
        background: #ede9fe;
        color: #6d28d9;
    }

    .status-arrived {
        background: #d1fae5;
        color: #065f46;
    }

    .dm-empty {
        padding: 45px 20px;
        text-align: center;
        color: #64748b;
    }

    .dm-empty > i {
        font-size: 28px;
        color: #94a3b8;
        margin-bottom: 10px;
    }

    .dm-empty p {
        margin: 8px 0 4px;
        color: #0f172a;
        font-weight: 700;
    }

    .dm-empty span {
        font-size: 12px;
    }

    .dm-footer {
        display: flex;
        justify-content: space-between;
        gap: 12px;
        padding: 15px 20px;
        color: #64748b;
        font-size: 12px;
        background: #fff;
    }

    #refreshIndicator i {
        color: #10b981;
        margin-right: 4px;
    }

    @media (max-width: 700px) {
        .dm-toolbar {
            flex-direction: column;
            align-items: stretch;
        }

        .dm-search {
            max-width: none;
        }

        .dm-filter {
            width: 100%;
        }

        .dm-info,
        .dm-footer {
            flex-direction: column;
        }
    }
</style>

<script>
document.addEventListener('DOMContentLoaded', function () {
    const searchInput = document.getElementById('deliverySearch');
    const statusFilter = document.getElementById('statusFilter');
    const tableBody = document.getElementById('deliveryTableBody');
    const countLabel = document.getElementById('deliveryCount');
    const refreshedLabel = document.getElementById('lastRefreshed');
    const refreshButton = document.getElementById('refreshDeliveries');
    const indicator = document.getElementById('refreshIndicator');
    const noResults = document.getElementById('noSearchResults');

    function filterDeliveries() {
        const search = searchInput.value.toLowerCase().trim();
        const status = statusFilter.value;
        const rows = Array.from(
            tableBody.querySelectorAll('tr[data-search]')
        );

        let visibleCount = 0;

        rows.forEach(function (row) {
            const matchesSearch = row.dataset.search.includes(search);
            const matchesStatus = !status || row.dataset.status === status;
            const visible = matchesSearch && matchesStatus;

            row.style.display = visible ? '' : 'none';

            if (visible) visibleCount++;
        });

        countLabel.textContent =
            visibleCount + (visibleCount === 1 ? ' delivery' : ' deliveries');

        noResults.style.display =
            rows.length > 0 && visibleCount === 0 ? 'block' : 'none';
    }

    async function refreshDeliveries() {
        indicator.innerHTML =
            '<i class="fa-solid fa-spinner fa-spin"></i> Refreshing...';

        try {
            const response = await fetch(window.location.href, {
                headers: {
                    'X-Requested-With': 'XMLHttpRequest',
                    'Accept': 'text/html'
                },
                cache: 'no-store'
            });

            if (!response.ok) {
                throw new Error('Could not refresh deliveries.');
            }

            const html = await response.text();
            const parser = new DOMParser();
            const newDocument = parser.parseFromString(html, 'text/html');
            const newTableBody = newDocument.getElementById('deliveryTableBody');

            if (newTableBody) {
                tableBody.innerHTML = newTableBody.innerHTML;
                filterDeliveries();
            }

            const now = new Date();

            refreshedLabel.textContent =
                'Last refreshed: ' + now.toLocaleTimeString();

            indicator.innerHTML =
                '<i class="fa-solid fa-circle-check"></i> Up to date';
        } catch (error) {
            indicator.innerHTML =
                '<i class="fa-solid fa-circle-exclamation" style="color:#dc2626"></i> Refresh failed';
        }
    }

    searchInput.addEventListener('input', filterDeliveries);
    statusFilter.addEventListener('change', filterDeliveries);
    refreshButton.addEventListener('click', refreshDeliveries);

    filterDeliveries();
    refreshedLabel.textContent = 'Page loaded: ' + new Date().toLocaleTimeString();

    window.setInterval(refreshDeliveries, 10000);
});
</script>
@endsection