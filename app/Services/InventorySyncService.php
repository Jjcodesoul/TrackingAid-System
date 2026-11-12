<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\StockBatch;
use App\Models\StorageLocation;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
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

    public function receiveStock(
        int $itemId,
        int $quantity,
        int $actorId,
        string $dateReceived,
        ?string $expirationDate = null,
        ?string $supplier = null,
        ?string $submissionKey = null,
        string $movementType = 'stock-in',
    ): StockBatch {
        return DB::transaction(function () use ($itemId, $quantity, $actorId, $dateReceived, $expirationDate, $supplier, $submissionKey, $movementType): StockBatch {
            $item = Item::whereKey($itemId)->lockForUpdate()->firstOrFail();

            if ($submissionKey !== null) {
                $priorBatch = StockBatch::where('submission_key', $submissionKey)->first();
                if ($priorBatch) {
                    if ((int) $priorBatch->item_id !== $itemId || (int) $priorBatch->quantity !== $quantity) {
                        throw ValidationException::withMessages([
                            'submission_key' => 'This receipt has already been submitted with different details.',
                        ]);
                    }

                    return $priorBatch;
                }
            }

            $location = StorageLocation::designatedMambaling()
                ->where('is_active', true)
                ->lockForUpdate()
                ->first();
            if (! $location) {
                throw ValidationException::withMessages([
                    'storage_location_id' => 'Mambaling Storage is not configured as the active storage location.',
                ]);
            }

            $batch = StockBatch::create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'supplier' => $supplier,
                'date_received' => $dateReceived,
                'expiration_date' => $expirationDate,
                'created_by_user_id' => $actorId,
                'movement_type' => $movementType,
                'submission_key' => $submissionKey,
                'storage_location' => $location->name,
            ]);

            $this->syncItem($item->fresh('stockBatches'));

            return $batch;
        });
    }

    public function deleteInventoryForItem(Item $item): void
    {
        $inventory = Inventory::where('sku', $item->sku)->first();

        if ($inventory) {
            $inventory->delete();
        }
    }

    public function releaseFromInventory(Inventory $inventory, int $quantity, ?string $location = null): Inventory
    {
        $item = Item::where('sku', $inventory->sku)->first();

        if (! $item) {
            if ($location !== null && $location !== $inventory->storage_location) {
                throw ValidationException::withMessages(['source_storage_location' => 'This inventory record is assigned to a different storage location.']);
            }
            if ((int) $inventory->quantity < $quantity) {
                throw ValidationException::withMessages(['quantity' => 'Not enough stock available for this release.']);
            }
            $inventory->decrement('quantity', $quantity);

            return $inventory->refresh();
        }

        $remaining = $quantity;

        $batches = StockBatch::where('item_id', $item->id)
            ->where('quantity', '>', 0)
            ->when($location !== null, fn ($query) => $query->where('storage_location', $location))
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

    public function receiveReturn(Inventory $inventory, int $quantity, ?int $actorId = null, ?string $location = null): Inventory
    {
        $item = Item::where('sku', $inventory->sku)->first();

        if (! $item) {
            if ($location !== null && $location !== $inventory->storage_location) {
                throw ValidationException::withMessages(['borrow_release_id' => 'The return location does not match the release location.']);
            }
            $inventory->increment('quantity', $quantity);

            return $inventory->refresh();
        }

        StockBatch::create([
            'item_id' => $item->id,
            'quantity' => $quantity,
            'supplier' => 'Returned stock',
            'date_received' => now()->toDateString(),
            'expiration_date' => $item->expiration_date?->toDateString(),
            'created_by_user_id' => $actorId,
            'movement_type' => 'return-good',
            'storage_location' => $location ?: $item->storage_location,
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
