<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Item;
use App\Models\StorageLocation;
use App\Services\InventorySyncService;
use Illuminate\Support\Str;

class StockController extends Controller
{
    /**
     * Ensure only Admin and Staff can perform stock-in operations.
     */
    private function authorizeLogisticsAccess(Request $request): void
    {
        abort_unless(
            in_array($request->user()?->role, ['admin', 'staff'], true),
            403,
            'You do not have permission to perform stock-in operations.'
        );
    }

    public function create(Request $request)
    {
        $this->authorizeLogisticsAccess($request);

        $storageLocation = StorageLocation::designatedMambaling()
            ->where('is_active', true)
            ->firstOrFail();

        $items = Item::with('stockBatches')
            ->get()
            ->sortBy('name', SORT_NATURAL | SORT_FLAG_CASE)
            ->values();

        $categories = $items->pluck('category')->unique()->sort()->values();

        $submissionKey = (string) Str::uuid();

        return view('stock.create', compact(
            'items',
            'categories',
            'storageLocation',
            'submissionKey'
        ));
    }

    public function store(Request $request, InventorySyncService $inventorySync)
    {
        $this->authorizeLogisticsAccess($request);

        $validated = $request->validate([
            'item_id' => 'required|exists:items,id',
            'submission_key' => 'required|uuid',
            'quantity' => 'required|integer|min:1',
            'supplier' => 'nullable|string|max:255',
            'date_received' => 'required|date',
            'expiration_date' => 'nullable|date',
        ]);

        $inventorySync->receiveStock(
            (int) $validated['item_id'],
            (int) $validated['quantity'],
            (int) $request->user()->id,
            $validated['date_received'],
            $validated['expiration_date'] ?? null,
            $validated['supplier'] ?? null,
            $validated['submission_key'],
        );

        return redirect('/inventory')->with('success', 'Stock added');
    }
}
