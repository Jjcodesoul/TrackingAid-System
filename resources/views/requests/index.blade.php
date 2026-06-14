@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div style="margin-bottom:24px;">
    <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Requests</h2>
    <p style="color:#64748B; font-size:13px; margin:4px 0 0;">Incoming requests from ResqOperation — review, approve, or reject</p>
</div>

@if(session('success'))
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif

{{-- TABLE CARD --}}
<div class="card-ui" style="padding:0; overflow:hidden;">

    {{-- FILTER TABS + SEARCH --}}
    <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; justify-content:space-between; gap:12px; flex-wrap:wrap;">

        {{-- TABS --}}
        <div style="display:flex; gap:8px;">
            <button onclick="filterStatus('all')" id="tab-all"
                style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:none; background:#1a202c; color:#fff;">
                All
            </button>
            <button onclick="filterStatus('Pending')" id="tab-pending"
                style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151; display:flex; align-items:center; gap:6px;">
                Pending
                <span style="background:#EF4444; color:#fff; border-radius:999px; padding:1px 7px; font-size:11px;">
                    {{ $requests->where('status','Pending')->count() }}
                </span>
            </button>
            <button onclick="filterStatus('Approved')" id="tab-approved"
                style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">
                Approved
            </button>
            <button onclick="filterStatus('Rejected')" id="tab-rejected"
                style="padding:7px 16px; border-radius:8px; font-size:13px; font-weight:600; cursor:pointer; border:1px solid #e2e8f0; background:#fff; color:#374151;">
                Rejected
            </button>
        </div>

        {{-- SEARCH --}}
        <div style="position:relative;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="text" id="searchInput" placeholder="Search requests..." onkeyup="searchTable()"
                style="padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; width:220px;">
        </div>
    </div>

    {{-- TABLE --}}
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
            @forelse($requests as $req)
                @php
                    $priorityColors = [
                        'Critical' => ['bg'=>'#FEF2F2','color'=>'#DC2626'],
                        'High'     => ['bg'=>'#FFF7ED','color'=>'#EA580C'],
                        'Medium'   => ['bg'=>'#FFFBEB','color'=>'#D97706'],
                        'Low'      => ['bg'=>'#F0FDF4','color'=>'#16A34A'],
                    ];
                    $pc = $priorityColors[$req->priority] ?? ['bg'=>'#F7FAFC','color'=>'#4A5568'];

                    $statusColors = [
                        'Pending'  => ['bg'=>'#FFFBEB','color'=>'#D97706'],
                        'Approved' => ['bg'=>'#ECFDF5','color'=>'#059669'],
                        'Rejected' => ['bg'=>'#FEF2F2','color'=>'#DC2626'],
                    ];
                    $sc = $statusColors[$req->status] ?? ['bg'=>'#F7FAFC','color'=>'#4A5568'];
                @endphp
                <tr style="border-bottom:1px solid #f1f5f9;" class="req-row" data-status="{{ $req->status }}">

                    {{-- REQUEST ID --}}
                    <td style="padding:14px 16px;">
                        <span style="font-size:12px; font-weight:600; color:#64748b; font-family:monospace;">{{ $req->request_code }}</span>
                    </td>

                    {{-- ITEM --}}
                    <td style="padding:14px 16px;">
                        <span style="font-weight:600; color:#1a202c; font-size:14px; display:block;">
                            {{ $req->inventory?->name ?? 'Unknown Item' }}
                        </span>
                        <span style="font-size:11px; color:#94a3b8; font-family:monospace;">
                            {{ $req->inventory?->sku ?? '—' }}
                        </span>
                    </td>

                    {{-- QTY --}}
                    <td style="padding:14px 16px; font-weight:700; color:#1a202c;">
                        {{ number_format($req->quantity) }}
                    </td>

                    {{-- PRIORITY --}}
                    <td style="padding:14px 16px;">
                        <span style="background:{{ $pc['bg'] }}; color:{{ $pc['color'] }}; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:700;">
                            {{ $req->priority }}
                        </span>
                    </td>

                    {{-- REQUESTED BY --}}
                    <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                        {{ $req->source ?? $req->responder_email ?? '—' }}
                        @if($req->purpose)
                            <span style="display:block; font-size:11px; color:#94a3b8;">{{ Str::limit($req->purpose, 30) }}</span>
                        @endif
                    </td>

                    {{-- DATE --}}
                    <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                        {{ \Carbon\Carbon::parse($req->created_at)->format('M d, Y') }}
                    </td>

                    {{-- STATUS --}}
                    <td style="padding:14px 16px;">
                        <span style="background:{{ $sc['bg'] }}; color:{{ $sc['color'] }}; padding:3px 12px; border-radius:6px; font-size:12px; font-weight:700;">
                            {{ $req->status }}
                        </span>
                    </td>

                    {{-- ACTIONS --}}
                    <td style="padding:14px 16px;">
                        <div style="display:flex; align-items:center; gap:8px;">

                            {{-- VIEW --}}
                            <button title="{{ $req->purpose ?? 'No purpose stated' }}"
                                style="background:none; border:none; cursor:pointer; color:#94a3b8; padding:4px;">
                                <i class="fa-regular fa-eye"></i>
                            </button>

                            @if($req->status === 'Pending')
                                {{-- APPROVE --}}
                                <form action="{{ route('requests.approve', $req) }}" method="POST" style="margin:0;">
                                    @csrf
                                    <button type="submit" title="Approve"
                                        style="background:none; border:none; cursor:pointer; color:#059669; padding:4px; font-size:16px;">
                                        <i class="fa-regular fa-circle-check"></i>
                                    </button>
                                </form>

                                {{-- REJECT --}}
                                <form action="{{ route('requests.reject', $req) }}" method="POST"
                                    onsubmit="return confirm('Reject this request?')" style="margin:0;">
                                    @csrf
                                    <button type="submit" title="Reject"
                                        style="background:none; border:none; cursor:pointer; color:#DC2626; padding:4px; font-size:16px;">
                                        <i class="fa-regular fa-circle-xmark"></i>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="8" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                        No requests found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterStatus(status) {
    const rows = document.querySelectorAll('.req-row');
    rows.forEach(row => {
        const rowStatus = row.getAttribute('data-status');
        row.style.display = (status === 'all' || rowStatus === status) ? '' : 'none';
    });

    // Update tab styles
    const tabs = ['all','Pending','Approved','Rejected'];
    tabs.forEach(t => {
        const btn = document.getElementById('tab-' + t.toLowerCase());
        if (btn) {
            btn.style.background = (t === status || (status === 'all' && t === 'all')) ? '#1a202c' : '#fff';
            btn.style.color = (t === status || (status === 'all' && t === 'all')) ? '#fff' : '#374151';
            btn.style.border = (t === status || (status === 'all' && t === 'all')) ? 'none' : '1px solid #e2e8f0';
        }
    });
}

function searchTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    document.querySelectorAll('.req-row').forEach(row => {
        row.style.display = row.innerText.toLowerCase().includes(search) ? '' : 'none';
    });
}
</script>

@endsection
