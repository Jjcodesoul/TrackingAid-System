<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StockBatch;

class StockController extends Controller
{
    public function create()
    {
        $items = Item::all();
        return view('stock.create', compact('items'));
    }

    public function store(Request $request)
    {
        StockBatch::create([
            'item_id' => $request->item_id,
            'quantity' => $request->quantity,
            'supplier' => $request->supplier,
            'date_received' => $request->date_received,
            'expiration_date' => $request->expiration_date,
        ]);

        return redirect('/inventory')->with('success', 'Stock added');
    }
}