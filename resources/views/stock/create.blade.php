@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 style="font-weight:700;">Stock In</h2>
        <p style="color:#64748B;">
            Receive and record incoming inventory batches
        </p>
    </div>

</div>

<div class="card-ui">

    <form method="POST" action="/stock-in/store">
        @csrf

        <div style="
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        ">

            {{-- LEFT --}}
            <div>

                <label>Select Item (SKU)</label>

                <select name="item_id">

                    @foreach($items as $item)

                        <option value="{{ $item->id }}">

                            {{ $item->sku }}
                            —
                            {{ $item->name }}

                        </option>

                    @endforeach

                </select>

                <label>Quantity Added</label>
                <input
                    type="number"
                    name="quantity"
                    required
                >

                <label>Supplier / Donor</label>
                <input
                    name="supplier"
                    placeholder="Supplier or donor name"
                >

            </div>

            {{-- RIGHT --}}
            <div>

                <label>Date Received</label>

                <input
                    type="date"
                    name="date_received"
                    required
                >

                <label>Expiration Date</label>

                <input
                    type="date"
                    name="expiration_date"
                >

                <div class="card-ui"
                    style="
                        margin-top:20px;
                        background:#F0FDF4;
                    ">

                    <small style="color:#166534;">
                        Inventory Logic
                    </small>

                    <p style="
                        margin-top:10px;
                        color:#166534;
                    ">
                        Stock entries are batch-based.
                        Each stock intake is tracked separately
                        for expiration monitoring and audit logs.
                    </p>

                </div>

            </div>

        </div>

        <br>

        <button class="btn-main">
            Add Stock
        </button>

    </form>

</div>

@endsection