<?php

namespace Tests\Feature;

use App\Models\Item;
use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class UiUxSmokeTest extends TestCase
{
    use RefreshDatabase;

    public function test_staff_dashboard_shows_live_values_without_demo_or_loading_placeholders(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $response = $this->actingAs($staff)->get('/dashboard');

        $response->assertOk()
            ->assertSee('Total Inventory Stock')
            ->assertSee('Awaiting review')
            ->assertSee('appSidebarToggle')
            ->assertSee('dashboard-kpi-grid')
            ->assertDontSee('+12.5% from last month')
            ->assertDontSee('Chart data loading from system model');
    }

    public function test_inventory_filter_includes_categories_that_are_present_in_data(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']));

        Item::create([
            'name' => 'Hygiene Kit',
            'category' => 'HYGIENE',
            'unit_type' => 'BAG',
            'target_beneficiary' => 'ALL',
            'type' => 'consumable',
            'storage_location' => 'Warehouse B',
            'sku' => 'HYGIENE-KIT-BAG-ALL',
        ]);

        $this->get(route('inventory.index'))
            ->assertOk()
            ->assertSee('Filter by category')
            ->assertSee('value="HYGIENE"', false)
            ->assertSee('aria-label="Search inventory items"', false)
            ->assertSee('aria-label="Filter by stock and expiration status"', false)
            ->assertSee('<th scope="col"', false)
            ->assertSee('Expiration');
    }

    public function test_inventory_lists_expiry_date_and_distinguishes_expired_from_expiring_stock(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']));

        foreach ([
            ['EXPIRING-UI', 'Fresh stock', 5, now()->addDays(10)],
            ['EXPIRED-UI', 'Past expiry', 20, now()->subDay()],
        ] as [$sku, $name, $quantity, $expiration]) {
            $item = Item::create([
                'name' => $name,
                'category' => 'FOOD',
                'unit_type' => 'BOX',
                'target_beneficiary' => 'ALL',
                'type' => 'consumable',
                'storage_location' => 'Warehouse A',
                'sku' => $sku,
            ]);

            StockBatch::create([
                'item_id' => $item->id,
                'quantity' => $quantity,
                'supplier' => 'Demo fixture',
                'date_received' => now()->subDays(60)->toDateString(),
                'expiration_date' => $expiration->toDateString(),
            ]);
        }

        $response = $this->get(route('inventory.index'));
        $response->assertOk()
            ->assertSee('value="expired"', false)
            ->assertSee('value="expiring"', false)
            ->assertSee('Expired')
            ->assertSee('Expiring')
            ->assertSee('data-status="low-stock expiring"', false)
            ->assertSee('data-status="in-stock expired"', false)
            ->assertSee(now()->addDays(10)->format('M d, Y'))
            ->assertSee(now()->subDay()->format('M d, Y'))
            ->assertViewHas('inventoryStats', fn (array $stats): bool => $stats === [
                'totalItems' => 2,
                'totalStock' => 25,
                'lowStock' => 1,
                'outOfStock' => 0,
                'expired' => 1,
                'expiring' => 1,
            ]);
    }

    public function test_request_list_can_filter_released_status_and_announces_empty_results(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'staff']));
        $inventory = Inventory::create([
            'name' => 'Water', 'category' => 'Relief', 'sku' => 'WATER-UX-1',
            'type' => 'Consumable', 'quantity' => 10,
        ]);
        SupplyRequest::create([
            'request_code' => 'REQ-RELEASED-UX', 'inventory_id' => $inventory->id,
            'quantity' => 2, 'priority' => 'High', 'status' => 'Released',
        ]);

        $this->get(route('requests.index'))
            ->assertOk()
            ->assertSee("filterStatus('Released')", false)
            ->assertSee('aria-label="Search requests"', false)
            ->assertSee('No requests match these filters.')
            ->assertSee('REQ-RELEASED-UX');
    }
}
