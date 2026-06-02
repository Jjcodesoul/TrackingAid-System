@extends('layouts.app')

@section('content')

<h2>Add Item (SKU Generator)</h2>

<div class="card">

<form method="POST" action="/inventory/store">
    @csrf

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">

        <div>
            <label>Item Name</label>
            <input name="name">

            <label>Category</label>
            <input name="category">

            <label>Unit Type</label>
            <input name="unit_type">

            <label>Size / Weight</label>
            <input name="size_weight">
        </div>

        <div>
            <label>Target Beneficiary</label>
            <input name="target_beneficiary">

            <label>Variant</label>
            <input name="variant">

            <label>Type</label>
            <select name="type">
                <option>consumable</option>
                <option>returnable</option>
            </select>

            <label>Storage Location</label>
            <input name="storage_location">
        </div>

    </div>

    <br>

    <button class="btn">Save Item</button>

</form>

</div>

@endsection