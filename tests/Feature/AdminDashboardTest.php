<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use App\Models\ReturnItem;
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

    public function test_staff_cannot_access_admin_inventory_actions(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);

        $this->actingAs($staff)
            ->get(route('inventory.create'))
            ->assertRedirect(route('dashboard'))
            ->assertSessionHas('error');
    }

    public function test_user_management_uses_the_shared_shell_without_static_sample_accounts(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('appSidebarToggle')
            ->assertSee($admin->email)
            ->assertDontSee('Static Mode');
    }

    public function test_temporary_admin_creation_route_is_not_available(): void
    {
        $this->get('/force-register-admin')->assertNotFound();

        $this->assertDatabaseMissing('users', [
            'email' => 'admin@trackingaid.org',
        ]);
    }

    public function test_users_page_uses_the_shared_shell_and_database_records(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff', 'name' => 'Actual Staff Record']);

        $this->actingAs($admin)
            ->get(route('users.index'))
            ->assertOk()
            ->assertSee('appSidebar')
            ->assertSee('Actual Staff Record')
            ->assertDontSee('Static Mode');
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
        ReturnItem::create([
            'inventory_id' => $inventory->id,
            'quantity' => 2,
            'condition' => 'Good',
        ]);

        $response = $this->actingAs($admin)->get('/admin/dashboard');

        $response->assertOk()
            ->assertSee('Request Status Overview')
            ->assertSee('Released')
            ->assertSee('Items Released')
            ->assertSee('Items Returned')
            ->assertSee('Arrived this month')
            ->assertSee('>1</h3>', false);
    }
}
