<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockBatch;
use Carbon\Carbon;
use Illuminate\Validation\ValidationException;

class InventorySyncService
{
    public function syncAllItems(): void
    {
        Item::with('stockBatches')->get()->each(function (Item $item): void {
            $this->syncItem($item);
        });
    }

    public function syncItem(Item $item): Inventory
    {
        $item->loadMissing('stockBatches');

        $inventory = Inventory::withTrashed()->firstOrNew(['sku' => $item->sku]);

        $inventory->fill([
            'name' => $item->name,
            'category' => $item->category,
            'type' => $this->inventoryType($item->type),
            'quantity' => $this->stockQuantity($item),
            'expiration' => $this->nextExpirationDate($item)?->toDateString(),
            'storage_location' => $item->storage_location,
        ]);

        if ($inventory->exists && $inventory->trashed()) {
            $inventory->restore();
        }

        $inventory->save();

        return $inventory;
    }

    public function syncInventory(Inventory $inventory): Inventory
    {
        $item = Item::with('stockBatches')->where('sku', $inventory->sku)->first();

        return $item ? $this->syncItem($item) : $inventory->refresh();
    }

    public function deleteInventoryForItem(Item $item): void
    {
        $inventory = Inventory::where('sku', $item->sku)->first();

        if ($inventory) {
            $inventory->delete();
        }
    }

    public function releaseFromInventory(Inventory $inventory, int $quantity): Inventory
    {
        $item = Item::where('sku', $inventory->sku)->first();

        if (! $item) {
            $inventory->decrement('quantity', $quantity);

            return $inventory->refresh();
        }

        $remaining = $quantity;

        $batches = StockBatch::where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->orderByRaw('expiration_date is null')
            ->orderBy('expiration_date')
            ->orderBy('date_received')
            ->orderBy('id')
            ->lockForUpdate()
            ->get();

        foreach ($batches as $batch) {
            if ($remaining <= 0) {
                break;
            }

            $deducted = min((int) $batch->quantity, $remaining);
            $batch->decrement('quantity', $deducted);
            $remaining -= $deducted;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'quantity' => 'Not enough stock available for this release.',
            ]);
        }

        return $this->syncItem($item->fresh('stockBatches'));
    }

    public function receiveReturn(Inventory $inventory, int $quantity): Inventory
    {
        $item = Item::where('sku', $inventory->sku)->first();

        if (! $item) {
            $inventory->increment('quantity', $quantity);

            return $inventory->refresh();
        }

        StockBatch::create([
            'item_id' => $item->id,
            'quantity' => $quantity,
            'supplier' => 'Returned stock',
            'date_received' => now()->toDateString(),
            'expiration_date' => $item->expiration_date?->toDateString(),
        ]);

        return $this->syncItem($item->fresh('stockBatches'));
    }

    public function stockQuantity(Item $item): int
    {
        $batches = $item->relationLoaded('stockBatches')
            ? $item->stockBatches
            : $item->stockBatches()->get();

        return (int) $batches->sum('quantity');
    }

    public function nextExpirationDate(Item $item): ?Carbon
    {
        $batches = $item->relationLoaded('stockBatches')
            ? $item->stockBatches
            : $item->stockBatches()->get();

        $batchExpiration = $batches
            ->filter(fn (StockBatch $batch): bool => (int) $batch->quantity > 0 && filled($batch->expiration_date))
            ->map(fn (StockBatch $batch): Carbon => Carbon::parse($batch->expiration_date))
            ->sort()
            ->first();

        if ($batchExpiration) {
            return $batchExpiration;
        }

        return $item->expiration_date ? Carbon::parse($item->expiration_date) : null;
    }

    private function inventoryType(?string $type): string
    {
        return strtolower((string) $type) === 'returnable' ? 'Returnable' : 'Consumable';
    }
}
