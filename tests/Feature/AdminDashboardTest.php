<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminDashboardTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_dashboard_loads_without_expiration_column_errors(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)
            ->get('/admin/dashboard')
            ->assertOk();
    }

    public function test_admin_dashboard_shows_all_request_statuses_and_real_completed_deliveries(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::create([
            'name' => 'Rescue rope', 'category' => 'Rescue', 'sku' => 'ROPE-001',
            'type' => 'Returnable', 'quantity' => 20,
        ]);

        foreach (['Pending', 'Approved', 'Released', 'Rejected'] as $index => $status) {
            SupplyRequest::create([
                'request_code' => 'REQ-UI-' . $index,
                'inventory_id' => $inventory->id,
                'quantity' => 1,
                'priority' => 'High',
                'status' => $status,
            ]);
        }

        $releasedRequest = SupplyRequest::where('status', 'Released')->firstOrFail();
        BorrowRelease::create([
            'request_id' => $releasedRequest->id,
            'inventory_id' => $inventory->id,
            'unit' => 'PCS',
            'quantity' => 1,
            'purpose' => 'Dashboard test',
            'location' => 'Zone 1',
            'released_at' => now(),
            'delivery_status' => 'Arrived',
            'arrived_at' => now(),
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk()
            ->assertSee('Request Status Overview')
            ->assertSee('Released')
            ->assertSee('Arrived this month')
            ->assertSee('>1</h3>', false);
    }
}
