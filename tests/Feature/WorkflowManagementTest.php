<?php

namespace Tests\Feature;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request;
use App\Models\ReturnItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkflowManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_index_provides_status_statistics(): void
    {
        $this->actingAs(User::factory()->create());

        Inventory::create([
            'name' => 'Water Bottles',
            'category' => 'Relief',
            'sku' => 'WTR-001',
            'type' => 'Consumable',
            'quantity' => 100,
            'expiration' => null,
            'storage_location' => 'Warehouse A',
        ]);

        $inventory = Inventory::first();

        Request::create([
            'request_code' => 'REQ-001',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 10,
            'priority' => 'High',
            'status' => 'Pending',
            'purpose' => 'Emergency response',
        ]);

        Request::create([
            'request_code' => 'REQ-002',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 5,
            'priority' => 'Medium',
            'status' => 'Approved',
            'purpose' => 'Field support',
        ]);

        Request::create([
            'request_code' => 'REQ-003',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 4,
            'priority' => 'Low',
            'status' => 'Released',
            'purpose' => 'Relief convoy',
        ]);

        Request::create([
            'request_code' => 'REQ-004',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 2,
            'priority' => 'High',
            'status' => 'Rejected',
            'purpose' => 'Duplicate request',
        ]);

        $response = $this->get(route('requests.index'));

        $response->assertOk();
        $response->assertViewHas('stats', function ($stats) {
            return $stats['pending'] === 1
                && $stats['approved'] === 1
                && $stats['released'] === 1
                && $stats['rejected'] === 1;
        });
    }

    public function test_borrow_release_create_view_includes_recent_releases(): void
    {
        $this->actingAs(User::factory()->create());

        $inventory = Inventory::create([
            'name' => 'Tents',
            'category' => 'Shelter',
            'sku' => 'TNT-002',
            'type' => 'Returnable',
            'quantity' => 20,
            'expiration' => null,
            'storage_location' => 'Warehouse B',
        ]);

        $request = Request::create([
            'request_code' => 'REQ-010',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 4,
            'priority' => 'High',
            'status' => 'Approved',
            'purpose' => 'Shelter deployment',
        ]);

        $release = BorrowRelease::create([
            'request_id' => $request->id,
            'inventory_id' => $inventory->id,
            'unit' => 'PCS',
            'quantity' => 2,
            'purpose' => 'Shelter deployment',
            'location' => 'Zone 3',
            'released_at' => now(),
        ]);

        $response = $this->get(route('borrow-release.create'));

        $response->assertOk();
        $response->assertViewHas('recentReleases', function ($releases) use ($release) {
            return $releases->contains($release);
        });
    }

    public function test_return_create_view_includes_recent_returns(): void
    {
        $this->actingAs(User::factory()->create());

        $inventory = Inventory::create([
            'name' => 'Blankets',
            'category' => 'Relief',
            'sku' => 'BLK-003',
            'type' => 'Returnable',
            'quantity' => 15,
            'expiration' => null,
            'storage_location' => 'Warehouse C',
        ]);

        $return = ReturnItem::create([
            'inventory_id' => $inventory->id,
            'quantity' => 3,
            'condition' => 'Good',
            'notes' => 'Returned after deployment',
        ]);

        $response = $this->get(route('returns.create'));

        $response->assertOk();
        $response->assertViewHas('recentReturns', function ($returns) use ($return) {
            return $returns->contains($return);
        });
    }

    public function test_notifications_page_lists_request_status_updates(): void
    {
        $this->actingAs(User::factory()->create());

        $inventory = Inventory::create([
            'name' => 'Water Bottles',
            'category' => 'Relief',
            'sku' => 'WTR-101',
            'type' => 'Consumable',
            'quantity' => 50,
            'expiration' => null,
            'storage_location' => 'Warehouse A',
        ]);

        Request::create([
            'request_code' => 'REQ-101',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 10,
            'priority' => 'High',
            'status' => 'Approved',
            'purpose' => 'Emergency response',
            'notification_status' => 'Responder notified: request approved',
        ]);

        $response = $this->get(route('notifications.index'));

        $response->assertOk();
        $response->assertSee('Responder notified: request approved');
        $response->assertSee(route('requests.index'));
    }
}
