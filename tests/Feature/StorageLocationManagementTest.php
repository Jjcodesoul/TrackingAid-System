<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StorageLocationManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_separate_storage_location_management_page_is_not_exposed(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get('/storage-locations')
            ->assertNotFound();
    }

    public function test_only_the_designated_location_is_offered_for_inventory_and_stock_in(): void
    {
        $location = StorageLocation::designatedMambaling()->where('is_active', true)->firstOrFail();
        $this->assertSame('Mambaling, Cebu City, Philippines', $location->warehouse_name);

        $admin = User::factory()->create(['role' => 'admin']);
        foreach ([route('inventory.create'), route('stock.create'), route('inventory.index')] as $url) {
            $this->actingAs($admin)->get($url)->assertOk()->assertSeeText('Mambaling Storage');
        }

        $this->actingAs($admin)->get(route('inventory.create'))
            ->assertDontSeeText('Main Warehouse')->assertDontSeeText('Equipment and Tools Storage');
        $this->actingAs($admin)->get(route('stock.create'))
            ->assertDontSeeText('Main Warehouse')->assertDontSeeText('Equipment and Tools Storage');
    }

    public function test_new_inventory_uses_designated_location_even_if_client_posts_another_id(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->post(route('inventory.store'), [
            'name' => 'Emergency Water',
            'category' => 'FOOD',
            'unit_type' => 'BOTTLE',
            'target_beneficiary' => 'ALL',
            'type' => 'consumable',
            'storage_location_id' => StorageLocation::where('name', 'Warehouse A')->value('id'),
        ])->assertRedirect('/inventory')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('items', ['name' => 'Emergency Water', 'storage_location' => 'Mambaling Storage']);
    }

    public function test_stock_in_uses_designated_location_and_preserves_existing_item_assignment(): void
    {
        $submissionKey = (string) \Illuminate\Support\Str::uuid();
        $item = Item::create([
            'name' => 'Water kit', 'category' => 'RELIEF', 'unit_type' => 'BOX',
            'type' => 'consumable', 'storage_location' => 'Warehouse A', 'sku' => 'REL-WATER-STOCK-IN',
        ]);
        $operator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($operator)->post(route('stock.store'), [
            'item_id' => $item->id,
            'submission_key' => $submissionKey,
            'storage_location_id' => StorageLocation::where('name', 'Warehouse B')->value('id'),
            'quantity' => 5,
            'supplier' => 'Test supplier',
            'date_received' => now()->toDateString(),
        ])->assertRedirect('/inventory')->assertSessionHasNoErrors();

        $this->actingAs($operator)->post(route('stock.store'), [
            'item_id' => $item->id,
            'submission_key' => $submissionKey,
            'storage_location_id' => StorageLocation::where('name', 'Warehouse B')->value('id'),
            'quantity' => 5,
            'supplier' => 'Test supplier',
            'date_received' => now()->toDateString(),
        ])->assertRedirect('/inventory')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_batches', [
            'item_id' => $item->id, 'quantity' => 5, 'storage_location' => 'Mambaling Storage', 'movement_type' => 'stock-in',
        ]);
        $this->assertSame(1, \App\Models\StockBatch::where('submission_key', $submissionKey)->count());
        $this->assertSame('Warehouse A', $item->fresh()->storage_location);
    }
}
