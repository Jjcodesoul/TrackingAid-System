@extends('layouts.app')

@section('content')

{{-- HEADER --}}
<div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:8px;">
    <div>
        <h2 style="font-weight:700; font-size:22px; color:#1a202c; margin:0;">Inventory</h2>
        <p style="color:#64748B; font-size:13px; margin:4px 0 0;">All registered items and current stock levels</p>
    </div>
    <a href="{{ route('inventory.create') }}" class="btn-main">+ Add Item (SKU)</a>
</div>

{{-- ALERTS --}}
@if(session('success'))
    <div style="background:#ECFDF5; border:1px solid #A7F3D0; color:#166534; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('success') }}
    </div>
@endif
@if(session('error'))
    <div style="background:#FEF2F2; border:1px solid #FECACA; color:#991B1B; padding:12px 16px; border-radius:8px; margin-bottom:16px;">
        {{ session('error') }}
    </div>
@endif

{{-- TABLE CARD --}}
<div class="card-ui" style="padding:0; overflow:hidden;">

    {{-- SEARCH / FILTER --}}
    <div style="padding:16px 20px; border-bottom:1px solid #f1f5f9; display:flex; align-items:center; gap:12px;">
        <div style="position:relative; flex:1; max-width:320px;">
            <i class="fa-solid fa-magnifying-glass" style="position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; font-size:13px;"></i>
            <input type="text" id="searchInput" placeholder="Search items..." onkeyup="filterTable()"
                style="width:100%; padding:8px 12px 8px 36px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; outline:none; box-sizing:border-box;">
        </div>
        <select id="categoryFilter" onchange="filterTable()"
            style="padding:8px 12px; border:1px solid #e2e8f0; border-radius:8px; font-size:13px; color:#374151; outline:none; background:#fff;">
            <option value="">All</option>
            <option value="FOOD">Food</option>
            <option value="MEDICAL">Medical</option>
            <option value="RESCUE">Rescue</option>
            <option value="RELIEF">Relief</option>
        </select>
    </div>

    {{-- TABLE --}}
    <div style="overflow-x:auto;">
        <table style="width:100%; border-collapse:collapse;">
            <thead>
                <tr style="background:#f8fafc; border-bottom:1px solid #e2e8f0;">
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">SKU</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Name</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Category</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Stock</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Unit</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Type</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Flags</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Location</th>
                    <th style="padding:12px 16px; text-align:left; font-size:11px; font-weight:600; text-transform:uppercase; color:#94a3b8; letter-spacing:.05em;">Actions</th>
                </tr>
            </thead>
            <tbody id="inventoryTable">
            @forelse($items as $item)
                <tr style="border-bottom:1px solid #f1f5f9;" class="table-row" data-category="{{ strtoupper($item->category) }}">

                    <td style="padding:14px 16px;">
                        <span style="font-size:11px; font-weight:600; color:#64748b; font-family:monospace;">{{ $item->sku }}</span>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="font-weight:600; color:#1a202c; font-size:14px;">{{ $item->name }}</span>
                    </td>

                    <td style="padding:14px 16px;">
                        @php
                            $catColors = [
                                'FOOD'    => ['bg'=>'#F0FFF4','color'=>'#276749'],
                                'MEDICAL' => ['bg'=>'#EBF8FF','color'=>'#2B6CB0'],
                                'RESCUE'  => ['bg'=>'#FFF5F5','color'=>'#C53030'],
                                'RELIEF'  => ['bg'=>'#FFFAF0','color'=>'#C05621'],
                            ];
                            $cat = strtoupper($item->category);
                            $cc = $catColors[$cat] ?? ['bg'=>'#F7FAFC','color'=>'#4A5568'];
                        @endphp
                        <span style="background:{{ $cc['bg'] }}; color:{{ $cc['color'] }}; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">
                            {{ ucfirst(strtolower($item->category)) }}
                        </span>
                    </td>

                    <td style="padding:14px 16px;">
                        <span style="font-weight:700; font-size:15px; color:{{ $item->total_stock <= 0 ? '#dc2626' : ($item->total_stock < 10 ? '#d97706' : '#1a202c') }};">
                            {{ number_format($item->total_stock) }}
                        </span>
                    </td>

                    <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                        {{ $item->unit_type ?? '—' }}
                    </td>

                    <td style="padding:14px 16px;">
                        @if(strtolower($item->type) === 'consumable')
                            <span style="background:#EBF8FF; color:#2B6CB0; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">Consumable</span>
                        @else
                            <span style="background:#FAF5FF; color:#6B46C1; padding:3px 10px; border-radius:999px; font-size:12px; font-weight:600;">Returnable</span>
                        @endif
                    </td>

                    <td style="padding:14px 16px;">
                        @if($item->total_stock <= 0)
                            <span style="background:#FEF2F2; color:#DC2626; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Out of Stock</span>
                        @elseif($item->total_stock < 10)
                            <span style="background:#FEF2F2; color:#DC2626; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Low Stock</span>
                        @elseif($item->expiration_date && \Carbon\Carbon::parse($item->expiration_date)->diffInDays(now()) <= 30)
                            <span style="background:#FFFBEB; color:#D97706; padding:3px 10px; border-radius:6px; font-size:12px; font-weight:600;">Expiring</span>
                        @else
                            <span style="color:#94a3b8; font-size:13px;">—</span>
                        @endif
                    </td>

                    <td style="padding:14px 16px; font-size:13px; color:#4a5568;">
                        {{ $item->storage_location ?? '—' }}
                    </td>

                    <td style="padding:14px 16px;">
                        <div style="display:flex; gap:8px;">
                            <a href="/inventory/edit/{{ $item->id }}"
                                style="padding:5px 12px; background:#EBF8FF; color:#2B6CB0; border-radius:6px; font-size:12px; font-weight:600; text-decoration:none;">
                                Edit
                            </a>
                            <form method="POST" action="/inventory/delete/{{ $item->id }}"
                                onsubmit="return confirm('Delete this item?')" style="margin:0;">
                                @csrf
                                <button type="submit"
                                    style="padding:5px 12px; background:#FEF2F2; color:#DC2626; border:none; border-radius:6px; font-size:12px; font-weight:600; cursor:pointer;">
                                    Delete
                                </button>
                            </form>
                        </div>
                    </td>

                </tr>
            @empty
                <tr>
                    <td colspan="9" style="text-align:center; padding:40px; color:#94a3b8; font-size:14px;">
                        No inventory items found.
                    </td>
                </tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>

<script>
function filterTable() {
    const search = document.getElementById('searchInput').value.toLowerCase();
    const category = document.getElementById('categoryFilter').value.toUpperCase();
    const rows = document.querySelectorAll('.table-row');
    rows.forEach(row => {
        const text = row.innerText.toLowerCase();
        const rowCat = row.getAttribute('data-category');
        const matchSearch = text.includes(search);
        const matchCat = category === '' || rowCat === category;
        row.style.display = matchSearch && matchCat ? '' : 'none';
    });
}
</script>

@endsection
