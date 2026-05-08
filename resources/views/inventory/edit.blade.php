@extends('layouts.app')

@section('content')

<div class="d-flex justify-content-between align-items-center mb-4">

    <div>
        <h2 style="font-weight:700;">Edit Inventory Item</h2>
        <p style="color:#64748B;">
            Update item information and SKU details
        </p>
    </div>

    <a href="/inventory" class="btn btn-secondary">
        Back
    </a>

</div>

<div class="card-ui">

    <form method="POST" action="/inventory/update/{{ $item->id }}">
        @csrf

        <div style="
            display:grid;
            grid-template-columns:1fr 1fr;
            gap:20px;
        ">

            {{-- LEFT --}}
            <div>

                <label>Item Name</label>
                <input
                    name="name"
                    value="{{ $item->name }}"
                    required
                >

                <label>Category</label>
                <select name="category">

                    <option {{ $item->category == 'Food' ? 'selected' : '' }}>
                        Food
                    </option>

                    <option {{ $item->category == 'Medical' ? 'selected' : '' }}>
                        Medical
                    </option>

                    <option {{ $item->category == 'Rescue' ? 'selected' : '' }}>
                        Rescue
                    </option>

                    <option {{ $item->category == 'Relief' ? 'selected' : '' }}>
                        Relief
                    </option>

                </select>

                <label>Unit Type</label>
                <input
                    name="unit_type"
                    value="{{ $item->unit_type }}"
                >

                <label>Size / Weight</label>
                <input
                    name="size_weight"
                    value="{{ $item->size_weight }}"
                >

            </div>

            {{-- RIGHT --}}
            <div>

                <label>Target Beneficiary</label>
                <input
                    name="target_beneficiary"
                    value="{{ $item->target_beneficiary }}"
                >

                <label>Variant</label>
                <input
                    name="variant"
                    value="{{ $item->variant }}"
                >

                <label>Item Type</label>
                <select name="type">

    <option
        value="consumable"
        {{ $item->type == 'consumable' ? 'selected' : '' }}>

        Consumable

    </option>

    <option
        value="returnable"
        {{ $item->type == 'returnable' ? 'selected' : '' }}>

        Returnable

    </option>

</select>
                <label>Storage Location</label>
                <input
                    name="storage_location"
                    value="{{ $item->storage_location }}"
                >

            </div>

        </div>

        <hr style="margin:25px 0;">

        {{-- SKU PREVIEW --}}
        <div class="card-ui" style="background:#ECFDF5;">

            <small style="color:#065F46;">Generated SKU</small>

            <h4 style="
                color:#10B981;
                margin-top:8px;
                font-weight:700;
            ">
                {{ $item->sku }}
            </h4>

        </div>

        <br>

        <button class="btn-main">
            Update Item
        </button>

    </form>

</div>

@endsection