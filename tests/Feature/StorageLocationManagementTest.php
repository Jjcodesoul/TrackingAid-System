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

    public function test_temporary_locations_are_consistent_in_item_stock_in_and_inventory_pages(): void
    {
        $names = [
            'Main Warehouse',
            'Relief Goods Storage',
            'Food and Water Storage',
            'Medical Supplies Storage',
            'Clothing and Essentials Storage',
            'Equipment and Tools Storage',
        ];
        foreach ($names as $name) {
            $location = StorageLocation::where('name', $name)->firstOrFail();
            $this->assertTrue($location->is_active);
            $this->assertStringContainsString('Mambaling', $location->warehouse_name);
            $this->assertStringContainsString('temporary', strtolower($location->warehouse_name));
        }

        $admin = User::factory()->create(['role' => 'admin']);
        foreach ([route('inventory.create'), route('stock.create'), route('inventory.index')] as $url) {
            $response = $this->actingAs($admin)->get($url)->assertOk();
            foreach ($names as $name) {
                $response->assertSeeText($name);
            }
        }
    }

    public function test_stock_in_can_move_an_item_from_an_inactive_default_location_to_an_active_location(): void
    {
        $location = StorageLocation::create([
            'name' => 'Mambaling Closed Bay',
            'warehouse_name' => 'Mambaling Operations',
            'is_active' => false,
        ]);
        $item = Item::create([
            'name' => 'Water kit', 'category' => 'RELIEF', 'unit_type' => 'BOX',
            'type' => 'consumable', 'storage_location' => $location->name, 'sku' => 'REL-WATER-KIT-01',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)->get(route('stock.create'))->assertOk()->assertSee('REL-WATER-KIT-01');
        $this->post(route('stock.store'), [
            'item_id' => $item->id,
            'storage_location_id' => StorageLocation::where('name', 'Warehouse A')->value('id'),
            'quantity' => 5,
            'date_received' => now()->toDateString(),
        ])->assertRedirect('/inventory')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_batches', [
            'item_id' => $item->id, 'storage_location' => 'Warehouse A', 'quantity' => 5,
        ]);
        $this->post(route('stock.store'), [
            'item_id' => $item->id,
            'storage_location_id' => $location->id,
            'quantity' => 1,
            'date_received' => now()->toDateString(),
        ])->assertSessionHasErrors('storage_location_id');
    }

    public function test_stock_in_records_the_selected_active_mambaling_location(): void
    {
        $item = Item::create([
            'name' => 'Water kit', 'category' => 'RELIEF', 'unit_type' => 'BOX',
            'type' => 'consumable', 'storage_location' => 'Warehouse A', 'sku' => 'REL-WATER-STOCK-IN',
        ]);
        $operator = User::factory()->create(['role' => 'admin']);

        $this->actingAs($operator)->post(route('stock.store'), [
            'item_id' => $item->id,
            'storage_location_id' => StorageLocation::where('name', 'Warehouse B')->value('id'),
            'quantity' => 5,
            'supplier' => 'Test supplier',
            'date_received' => now()->toDateString(),
        ])->assertRedirect('/inventory')->assertSessionHasNoErrors();

        $this->assertDatabaseHas('stock_batches', [
            'item_id' => $item->id, 'quantity' => 5, 'storage_location' => 'Warehouse B', 'movement_type' => 'stock-in',
        ]);
        $this->assertSame('Warehouse A', $item->fresh()->storage_location);
    }
}
