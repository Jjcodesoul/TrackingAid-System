@extends('layouts.app')

@section('content')
    <div style="margin-bottom:24px;">
        <h2 class="page-title">Requests</h2>
        <p class="page-subtitle">Review, approve, or reject incoming requests</p>
    </div>

    @if(session('success'))
        <div class="alert-box alert-success">{{ session('success') }}</div>
    @endif

    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Pending</div>
                <div class="display-6 fw-bold">{{ $stats['pending'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Approved</div>
                <div class="display-6 fw-bold">{{ $stats['approved'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Released</div>
                <div class="display-6 fw-bold">{{ $stats['released'] ?? 0 }}</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="panel p-3">
                <div class="text-muted small">Rejected</div>
                <div class="display-6 fw-bold">{{ $stats['rejected'] ?? 0 }}</div>
            </div>
        </div>
    </div>

    <section class="panel" style="padding:0; overflow:hidden;">
        <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">
            <div style="display:flex; gap:8px; flex-wrap:wrap;">
                <button onclick="filterStatus('all')" id="tab-all" type="button" aria-pressed="true" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#1a202c; color:#fff;">All</button>
                <button onclick="filterStatus('Pending')" id="tab-pending" type="button" aria-pressed="false" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151; display:flex; align-items:center; gap:6px;">
                    Pending
                    <span style="background:#EF4444; color:#fff; border-radius:999px; padding:1px 7px; font-size:11px;">
                        {{ ($requests ? $requests->where('status', 'Pending')->count() : 0) + (isset($forwardedRequests) ? $forwardedRequests->count() : 0) }}
                    </span>
                </button>
                <button onclick="filterStatus('Approved')" id="tab-approved" type="button" aria-pressed="false" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">Approved</button>
                <button onclick="filterStatus('Released')" id="tab-released" type="button" aria-pressed="false" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">Released</button>
                <button onclick="filterStatus('Rejected')" id="tab-rejected" type="button" aria-pressed="false" style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">Rejected</button>
            </div>

            <div style="position:relative;">
                <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
                <input type="search" id="searchInput" aria-label="Search requests" placeholder="Search requests..." oninput="searchTable()" style="padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; width:220px;">
            </div>
        </div>

        <div style="overflow-x:auto;">
            <table style="width:100%; border-collapse:collapse;">
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
                        $priorityColors = [
                            'Critical' => ['bg' => '#FEF2F2', 'color' => '#DC2626'],
                            'High'     => ['bg' => '#FFF7ED', 'color' => '#EA580C'],
                            'Medium'   => ['bg' => '#FFFBEB', 'color' => '#D97706'],
                            'Low'      => ['bg' => '#F0FDF4', 'color' => '#16A34A'],
                        ];
                        $statusColors = [
                            'Pending'  => ['bg' => '#FFFBEB', 'color' => '#D97706'],
                            'Approved' => ['bg' => '#ECFDF5', 'color' => '#059669'],
                            'Rejected' => ['bg' => '#FEF2F2', 'color' => '#DC2626'],
                            'Released' => ['bg' => '#EEF2FF', 'color' => '#4338CA'],
                        ];
                    @endphp

                    {{-- 1. LOCAL REQUESTS LOOP --}}
                    @if(isset($requests) && count($requests) > 0)
                        @foreach($requests as $req)
                            @php
                                $priorityKey = ucfirst(strtolower($req->priority ?? 'Low'));
                                $statusKey   = ucfirst(strtolower($req->status ?? 'Pending'));
                                $pc = $priorityColors[$priorityKey] ?? ['bg' => '#F7FAFC', 'color' => '#4A5568'];
                                $sc = $statusColors[$statusKey] ?? ['bg' => '#F7FAFC', 'color' => '#4A5568'];
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
                                    <span style="background:{{ $pc['bg'] }}; color:{{ $pc['color'] }}; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700;">
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
                                    <span style="background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; padding:3px 12px; border-radius:6px; font-size:12px; font-weight:700;">
                                        {{ $statusKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <button type="button" title="{{ $req->notification_status ?? 'No notification yet' }}" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px;">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
                                        @if($statusKey === 'Pending')
                                            <form action="{{ route('requests.approve', $req) }}" method="POST" style="margin:0;">
                                                @csrf
                                                <button type="submit" title="Approve" style="background:none; border:none; cursor:pointer; color:#059669; padding:4px; font-size:16px;">
                                                    <i class="fa-regular fa-circle-check"></i>
                                                </button>
                                            </form>
                                            <form action="{{ route('requests.reject', $req) }}" method="POST" onsubmit="return confirm('Reject this request?')" style="margin:0;">
                                                @csrf
                                                <button type="submit" title="Reject" style="background:none; border:none; cursor:pointer; color:#DC2626; padding:4px; font-size:16px;">
                                                    <i class="fa-regular fa-circle-xmark"></i>
                                                </button>
                                            </form>
                                        @endif
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
                                $pc = $priorityColors[$urgencyKey] ?? ['bg' => '#FFFBEB', 'color' => '#D97706'];
                                $sc = $statusColors['Pending'];
                            @endphp
                            <tr class="req-row" data-status="Pending" style="border-bottom:1px solid #f1f5f9; background-color:#f0f9ff;">
                                <td style="padding:14px 16px;">
                                    <span style="font-size:12px; font-weight:600; color:#0369a1; font-family:monospace;">
                                        {{ $freq->tracking_reference }}
                                    </span>
                                    <span style="display:inline-block; background:#e0f2fe; color:#0369a1; border-radius:4px; padding:1px 6px; font-size:10px; font-weight:700; margin-left:4px;">
                                        ResQOperation
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="font-weight:600; color:#1a202c; font-size:14px; display:block;">
                                        {{ $freq->item_name }}
                                    </span>
                                    <span style="font-size:11px; color:#94a3b8; font-family:monospace;">
                                        {{ $freq->resqoperation_request_id ?? 'Forwarded' }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-weight:700; color:#1a202c;">
                                    {{ number_format($freq->quantity ?? 0) }} {{ $freq->unit ?? '' }}
                                </td>
                                <td style="padding:14px 16px;">
                                    <span style="background:{{ $pc['bg'] }}; color:{{ $pc['color'] }}; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700;">
                                        {{ $urgencyKey }}
                                    </span>
                                </td>
                                <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                                    {{ $freq->request_source ?? 'HQ Desk' }}
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
                                    <span style="background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; padding:3px 12px; border-radius:6px; font-size:12px; font-weight:700;">
                                        Pending
                                    </span>
                                </td>
                                <td style="padding:14px 16px;">
                                    <div style="display:flex; align-items:center; gap:8px;">
                                        <button type="button" title="Forwarded External Request" style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px;">
                                            <i class="fa-regular fa-eye"></i>
                                        </button>
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

    <script>
        let activeRequestStatus = 'all';

        function filterStatus(status) {
            activeRequestStatus = status;
            const tabs = ['all', 'Pending', 'Approved', 'Released', 'Rejected'];
            tabs.forEach(t => {
                const btn = document.getElementById('tab-' + t.toLowerCase());
                if (!btn) return;

                const active = (t === 'all' && status === 'all') || t === status;
                btn.setAttribute('aria-pressed', String(active));
                if (active) {
                    btn.style.background = '#1a202c';
                    btn.style.color = '#fff';
                    btn.style.border = 'none';
                } else {
                    btn.style.background = '#fff';
                    btn.style.color = '#374151';
                    btn.style.border = '1px solid #e2e8f0';
                }
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
