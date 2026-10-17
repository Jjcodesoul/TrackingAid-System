<?php

namespace Tests\Feature;

use App\Models\BorrowRelease;
use App\Models\Inventory;
use App\Models\Request;
use App\Models\ReturnItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Str;
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

    public function test_request_approval_rejects_quantity_above_available_stock(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::create([
            'name' => 'Water', 'category' => 'Relief', 'sku' => 'WATER-APPROVAL-1',
            'type' => 'Consumable', 'quantity' => 2,
        ]);
        $request = Request::create([
            'request_code' => 'REQ-SHORT-1', 'inventory_id' => $inventory->id,
            'quantity' => 5, 'priority' => 'High', 'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->post(route('requests.approve', $request), ['approved_quantity' => 5])
            ->assertSessionHasErrors('approved_quantity');

        $this->assertSame('Pending', $request->fresh()->status);
        $this->assertNull($request->fresh()->approved_quantity);
    }

    public function test_admin_can_record_an_adjusted_approval_without_changing_the_original_request(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::create([
            'name' => 'Medical kits', 'category' => 'Medical', 'sku' => 'MED-APPROVAL-1',
            'type' => 'Consumable', 'quantity' => 3,
        ]);
        $request = Request::create([
            'request_code' => 'REQ-ADJUSTED-1', 'inventory_id' => $inventory->id,
            'quantity' => 4, 'priority' => 'High', 'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->post(route('requests.approve', $request), [
                'approved_quantity' => 3,
                'approval_notes' => 'Approve available quantity; restock remaining need.',
            ])
            ->assertRedirect();

        $request->refresh();
        $this->assertSame(4, (int) $request->quantity);
        $this->assertSame(3, (int) $request->approved_quantity);
        $this->assertSame($admin->id, (int) $request->approved_by_user_id);
        $this->assertSame('Approved', $request->status);
    }

    public function test_approved_request_reservations_reduce_stock_available_to_later_approvals(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $inventory = Inventory::create([
            'name' => 'Blankets', 'category' => 'Relief', 'sku' => 'BLANKET-APPROVAL-1',
            'type' => 'Returnable', 'quantity' => 5,
        ]);
        Request::create([
            'request_code' => 'REQ-RESERVED-1', 'inventory_id' => $inventory->id,
            'quantity' => 4, 'approved_quantity' => 4, 'priority' => 'High', 'status' => 'Approved',
        ]);
        $pendingRequest = Request::create([
            'request_code' => 'REQ-RESERVED-2', 'inventory_id' => $inventory->id,
            'quantity' => 4, 'priority' => 'High', 'status' => 'Pending',
        ]);

        $this->actingAs($admin)
            ->post(route('requests.approve', $pendingRequest), [
                'approved_quantity' => 4,
                'approval_notes' => 'Full requested quantity.',
            ])
            ->assertSessionHasErrors('approved_quantity');

        $this->assertSame('Pending', $pendingRequest->fresh()->status);
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

        $response->assertOk()
            ->assertSee('REQ-010')
            ->assertDontSee('name="inventory_id"', false);
        $response->assertViewHas('recentReleases', function ($releases) use ($release) {
            return $releases->contains($release);
        });
    }

    public function test_release_uses_the_approved_request_item_and_records_processor(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        [$inventory, $request, $deliveryPerson] = $this->createReleaseFixture();

        $this->actingAs($staff)
            ->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 3))
            ->assertRedirect(route('borrow-release.create'));

        $this->assertDatabaseHas('borrow_releases', [
            'request_id' => $request->id,
            'inventory_id' => $inventory->id,
            'quantity' => 3,
            'processed_by_user_id' => $staff->id,
        ]);
        $this->assertSame(7, (int) $inventory->fresh()->quantity);
        $this->assertSame('Released', $request->fresh()->status);
    }

    public function test_partial_releases_cannot_cumulatively_exceed_the_approved_quantity(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        [$inventory, $request, $deliveryPerson] = $this->createReleaseFixture();

        $this->actingAs($staff)
            ->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 2))
            ->assertRedirect(route('borrow-release.create'));

        $this->assertSame('Partially Released', $request->fresh()->status);

        $this->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 2))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('borrow_releases', 1);
        $this->assertSame(8, (int) $inventory->fresh()->quantity);

        $this->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 1))
            ->assertRedirect(route('borrow-release.create'));

        $this->assertSame('Released', $request->fresh()->status);
        $this->assertSame(7, (int) $inventory->fresh()->quantity);
    }

    public function test_duplicate_release_submission_does_not_deduct_stock_twice(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        [$inventory, $request, $deliveryPerson] = $this->createReleaseFixture();
        $payload = $this->releasePayload($request, $deliveryPerson, 3);

        $this->actingAs($staff)
            ->post(route('borrow-release.store'), $payload)
            ->assertRedirect(route('borrow-release.create'));

        $this->post(route('borrow-release.store'), $payload)
            ->assertRedirect(route('borrow-release.create'))
            ->assertSessionHas('success', 'This release was already processed.');

        $this->assertDatabaseCount('borrow_releases', 1);
        $this->assertSame(7, (int) $inventory->fresh()->quantity);
    }

    public function test_rejected_request_cannot_be_released(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        [, $request, $deliveryPerson] = $this->createReleaseFixture(status: 'Rejected');

        $this->actingAs($staff)
            ->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 1))
            ->assertSessionHasErrors('request_id');

        $this->assertDatabaseCount('borrow_releases', 0);
    }

    public function test_insufficient_stock_prevents_release_without_mutating_inventory(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        [$inventory, $request, $deliveryPerson] = $this->createReleaseFixture(stock: 2);

        $this->actingAs($staff)
            ->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 3))
            ->assertSessionHasErrors('quantity');

        $this->assertDatabaseCount('borrow_releases', 0);
        $this->assertSame(2, (int) $inventory->fresh()->quantity);
    }

    public function test_non_staff_user_cannot_process_a_release(): void
    {
        $user = User::factory()->create(['role' => 'user']);
        [, $request, $deliveryPerson] = $this->createReleaseFixture();

        $this->actingAs($user)
            ->post(route('borrow-release.store'), $this->releasePayload($request, $deliveryPerson, 1))
            ->assertForbidden();

        $this->assertDatabaseCount('borrow_releases', 0);
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

    public function test_returns_only_show_reusable_releases_with_outstanding_quantity(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $reusable = Inventory::create([
            'name' => 'Response blanket', 'category' => 'Relief', 'sku' => 'RETURN-BLANKET',
            'type' => 'Returnable', 'quantity' => 8,
        ]);
        $consumable = Inventory::create([
            'name' => 'Water pack', 'category' => 'Relief', 'sku' => 'RETURN-WATER',
            'type' => 'Consumable', 'quantity' => 8,
        ]);
        $reusableRequest = Request::create([
            'request_code' => 'REQ-RETURNABLE', 'inventory_id' => $reusable->id,
            'quantity' => 3, 'priority' => 'Medium', 'status' => 'Released',
        ]);
        $consumableRequest = Request::create([
            'request_code' => 'REQ-CONSUMABLE', 'inventory_id' => $consumable->id,
            'quantity' => 3, 'priority' => 'Medium', 'status' => 'Released',
        ]);
        BorrowRelease::create([
            'request_id' => $reusableRequest->id, 'inventory_id' => $reusable->id,
            'unit' => 'PCS', 'quantity' => 3, 'purpose' => 'Response', 'released_at' => now(),
        ]);
        BorrowRelease::create([
            'request_id' => $consumableRequest->id, 'inventory_id' => $consumable->id,
            'unit' => 'PACK', 'quantity' => 3, 'purpose' => 'Response', 'released_at' => now(),
        ]);

        $this->actingAs($staff)
            ->get(route('returns.create'))
            ->assertOk()
            ->assertSee('RETURN-BLANKET')
            ->assertDontSee('RETURN-WATER');
    }

    public function test_returns_are_capped_and_only_good_condition_adds_stock_once(): void
    {
        $staff = User::factory()->create(['role' => 'staff']);
        $inventory = Inventory::create([
            'name' => 'Response blanket', 'category' => 'Relief', 'sku' => 'RETURN-TEST-1',
            'type' => 'Returnable', 'quantity' => 10,
        ]);
        $request = Request::create([
            'request_code' => 'REQ-RETURN-TEST-1', 'inventory_id' => $inventory->id,
            'quantity' => 6, 'priority' => 'Medium', 'status' => 'Released',
        ]);
        $release = BorrowRelease::create([
            'request_id' => $request->id, 'inventory_id' => $inventory->id,
            'unit' => 'PCS', 'quantity' => 6, 'purpose' => 'Response', 'released_at' => now(),
        ]);

        $this->actingAs($staff);
        $damagedPayload = [
            'borrow_release_id' => $release->id,
            'idempotency_key' => (string) Str::uuid(),
            'quantity' => 2,
            'condition' => 'Damaged',
        ];
        $this->post(route('returns.store'), $damagedPayload)->assertRedirect();
        $this->assertSame(10, (int) $inventory->fresh()->quantity);

        $this->post(route('returns.store'), [
            ...$damagedPayload,
            'idempotency_key' => (string) Str::uuid(),
            'quantity' => 5,
        ])->assertSessionHasErrors('quantity');
        $this->assertSame(10, (int) $inventory->fresh()->quantity);

        $goodPayload = [
            'borrow_release_id' => $release->id,
            'idempotency_key' => (string) Str::uuid(),
            'quantity' => 1,
            'condition' => 'Good',
        ];
        $this->post(route('returns.store'), $goodPayload)->assertRedirect();
        $this->post(route('returns.store'), $goodPayload)->assertRedirect();

        $this->assertSame(11, (int) $inventory->fresh()->quantity);
        $this->assertDatabaseCount('returns', 2);
        $this->assertDatabaseHas('returns', [
            'borrow_release_id' => $release->id,
            'processed_by_user_id' => $staff->id,
            'condition' => 'Good',
        ]);
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

    private function createReleaseFixture(int $approvedQuantity = 3, int $stock = 10, string $status = 'Approved'): array
    {
        $inventory = Inventory::create([
            'name' => 'Bottled Water 500ml',
            'category' => 'Relief',
            'sku' => 'WAT-500',
            'type' => 'Consumable',
            'quantity' => $stock,
            'storage_location' => 'Warehouse A',
        ]);

        $request = Request::create([
            'request_code' => 'REQ-REL-' . Str::upper(Str::random(6)),
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => $approvedQuantity,
            'priority' => 'High',
            'status' => $status,
            'purpose' => 'Field support',
        ]);

        $deliveryPerson = User::factory()->create(['role' => 'delivery_personnel']);

        return [$inventory, $request, $deliveryPerson];
    }

    private function releasePayload(Request $request, User $deliveryPerson, int $quantity): array
    {
        return [
            'request_id' => $request->id,
            'idempotency_key' => (string) Str::uuid(),
            'delivery_personnel_id' => $deliveryPerson->id,
            'quantity' => $quantity,
            'location' => 'Zone 1',
        ];
    }
}
