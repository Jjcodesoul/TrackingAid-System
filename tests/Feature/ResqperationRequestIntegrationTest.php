<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ResqoperationForwardedRequest;
use App\Models\BorrowRelease;
use App\Models\StorageLocation;
use App\Models\Item;
use App\Models\StockBatch;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResqperationRequestIntegrationTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.resqoperation.api_key' => 'test-resqoperation-key']);
        $this->withHeader('X-API-KEY', 'test-resqoperation-key');
    }

    public function test_incoming_request_intake_rejects_missing_or_invalid_api_keys(): void
    {
        config(['services.resqoperation.api_key' => '']);
        $this->postJson('/api/incoming-requests', [])->assertServiceUnavailable();

        config(['services.resqoperation.api_key' => 'test-resqoperation-key']);
        $this->withHeader('X-API-KEY', 'incorrect-key')
            ->postJson('/api/incoming-requests', [])
            ->assertUnauthorized();
    }

    public function test_new_forwarded_requests_notify_logistics_admin_once(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        User::factory()->create(['role' => 'super_admin']);
        $payload = [
            'tracking_reference' => 'TA-NOTIFY-ONCE',
            'resqperation_request_id' => 'RR-NOTIFY-ONCE',
            'request_source' => 'hq_desk',
            'item_name' => 'Water',
            'quantity' => 3,
        ];

        $this->postJson('/api/incoming-requests', $payload)->assertCreated();
        $this->postJson('/api/incoming-requests', $payload)->assertOk();

        $this->assertDatabaseCount('notifications', 1);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'notifiable_type' => User::class,
            'type' => \App\Notifications\LogisticsActivityNotification::class,
        ]);
        $this->assertDatabaseMissing('notifications', ['notifiable_id' => $staff->id]);
    }

    public function test_incoming_resqperation_request_is_saved_and_shown_as_pending(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->postJson('/api/incoming-requests', [
            'tracking_reference' => 'TA-TEST-001',
            'resqperation_request_id' => 'RR-TEST-001',
            'request_source' => 'hq_desk',
            'item_name' => null,
            'resource_type' => 'Medical kit',
            'quantity' => 12,
            'urgency' => 'High',
            'requested_by' => 'Test Response Team',
        ]);

        $response->assertCreated()
            ->assertJsonPath('data.resqperation_request_id', 'RR-TEST-001');

        $this->assertDatabaseHas('resqperation_forwarded_requests', [
            'tracking_reference' => 'TA-TEST-001',
            'resqperation_request_id' => 'RR-TEST-001',
            'item_name' => null,
            'resource_type' => 'Medical kit',
        ]);

        $this->get(route('requests.index'))
            ->assertOk()
            ->assertSee('TA-TEST-001')
            ->assertSee('RR-TEST-001')
            ->assertSee('Medical kit')
            ->assertSee('Test Response Team')
            ->assertSee('data-status="Pending"', false)
            ->assertSee('title="Review request"', false)
            ->assertDontSee('Priority')
            ->assertDontSee('Urgency')
            ->assertSee('onclick="showForwardedRequest(this)"', false)
            ->assertSee('id="forwardedRequestDialog"', false)
            ->assertSee('data-item="Medical kit"', false)
            ->assertViewHas('stats', fn (array $stats): bool => $stats['pending'] === 1);
    }

    public function test_forwarded_request_can_be_approved_and_moves_to_approved_count(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $item = Item::create([
            'name' => 'Medical kit',
            'category' => 'MEDICAL',
            'unit_type' => 'BOX',
            'size_weight' => 'STD',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Warehouse A',
            'sku' => 'MED-KIT-APPROVE',
        ]);
        StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 10,
            'supplier' => 'Fixture stock',
            'date_received' => now()->toDateString(),
        ]);
        $forwardedRequest = $this->createForwardedRequest('TA-TEST-APPROVE');

        $this->post(route('requests.forwarded.approve', $forwardedRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('resqperation_forwarded_requests', [
            'id' => $forwardedRequest->id,
            'resqperation_status' => 'approved',
        ]);

        $this->get(route('requests.index'))
            ->assertOk()
            ->assertSee('data-status="Approved"', false)
            ->assertViewHas('stats', fn (array $stats): bool => $stats['pending'] === 0 && $stats['approved'] === 1);
    }

    public function test_forwarded_request_can_be_released_and_delivery_updates_are_persisted(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $staff = User::factory()->create(['role' => 'staff']);
        $item = Item::create([
            'name' => 'Medical kit',
            'category' => 'MEDICAL',
            'unit_type' => 'BOX',
            'size_weight' => 'STD',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Warehouse A',
            'sku' => 'MED-KIT-FORWARD-RELEASE',
        ]);
        $batch = StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 10,
            'supplier' => 'Fixture stock',
            'date_received' => now()->toDateString(),
            'storage_location' => 'Warehouse A',
        ]);
        $forwardedRequest = $this->createForwardedRequest('TA-TEST-FORWARD-RELEASE');
        $forwardedRequest->item_name = $item->name;
        $forwardedRequest->save();
        $deliveryPerson = User::factory()->create(['role' => 'delivery_personnel']);

        $this->actingAs($admin)
            ->post(route('requests.forwarded.approve', $forwardedRequest))
            ->assertRedirect();
        $forwardedRequest->refresh();
        $storageLocationId = StorageLocation::where('name', 'Warehouse A')->value('id');

        $this->post(route('borrow-release.store'), [
            'forwarded_request_id' => $forwardedRequest->id,
            'idempotency_key' => (string) \Illuminate\Support\Str::uuid(),
            'delivery_personnel_id' => $deliveryPerson->id,
            'quantity' => 4,
            'location' => 'Zone 1',
            'source_storage_location' => $storageLocationId,
        ])->assertRedirect(route('borrow-release.create'));

        $release = BorrowRelease::where('forwarded_request_id', $forwardedRequest->id)->firstOrFail();
        $this->assertNull($release->request_id);
        $this->assertSame(6, (int) $batch->fresh()->quantity);
        $this->assertDatabaseHas('resqperation_forwarded_requests', [
            'id' => $forwardedRequest->id,
            'resqperation_status' => 'released',
        ]);

        $this->actingAs($deliveryPerson, 'sanctum')
            ->patchJson("/api/deliveries/{$release->id}/status", ['status' => 'Dispatched'])
            ->assertOk()
            ->assertJsonPath('data.forwarded_request.tracking_reference', 'TA-TEST-FORWARD-RELEASE');

        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $admin->id,
            'notifiable_type' => User::class,
            'type' => \App\Notifications\LogisticsActivityNotification::class,
        ]);
        $this->assertDatabaseHas('notifications', [
            'notifiable_id' => $staff->id,
            'notifiable_type' => User::class,
            'type' => \App\Notifications\LogisticsActivityNotification::class,
        ]);
    }

    public function test_forwarded_request_can_be_rejected_and_moves_to_rejected_count(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $forwardedRequest = $this->createForwardedRequest('TA-TEST-REJECT');

        $this->post(route('requests.forwarded.reject', $forwardedRequest))
            ->assertRedirect();

        $this->assertDatabaseHas('resqperation_forwarded_requests', [
            'id' => $forwardedRequest->id,
            'resqperation_status' => 'rejected',
        ]);

        $this->get(route('requests.index'))
            ->assertOk()
            ->assertSee('data-status="Rejected"', false)
            ->assertViewHas('stats', fn (array $stats): bool => $stats['pending'] === 0 && $stats['rejected'] === 1);
    }

    public function test_repeated_delivery_updates_existing_tracking_reference(): void
    {
        $payload = [
            'tracking_reference' => 'TA-TEST-DUPLICATE',
            'resqperation_request_id' => 'RR-TEST-DUPLICATE',
            'item_name' => 'Water',
            'quantity' => 5,
        ];

        $this->postJson('/api/incoming-requests', $payload)->assertCreated();
        $this->postJson('/api/incoming-requests', array_merge($payload, [
            'quantity' => 8,
        ]))->assertOk();

        $this->assertDatabaseCount('resqperation_forwarded_requests', 1);
        $this->assertDatabaseHas('resqperation_forwarded_requests', [
            'tracking_reference' => 'TA-TEST-DUPLICATE',
            'quantity' => 8,
        ]);
    }

    public function test_incoming_request_response_reports_a_matched_item_shortage_and_staff_can_review_it(): void
    {
        $item = Item::create([
            'name' => 'Medical Kit',
            'category' => 'MEDICAL',
            'unit_type' => 'BOX',
            'size_weight' => 'STD',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Warehouse A',
            'sku' => 'MED-KIT-STD-CON',
        ]);
        StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 65,
            'supplier' => 'Fixture stock',
            'date_received' => now()->toDateString(),
        ]);

        $response = $this->postJson('/api/incoming-requests', [
            'tracking_reference' => 'TA-STOCK-CHECK-001',
            'resqperation_request_id' => 'RR-STOCK-CHECK-001',
            'item_name' => 'MED-KIT-STD-CON',
            'quantity' => 100,
            'requested_by' => 'Medical Response Team',
        ]);

        $response->assertCreated()
            ->assertJsonPath('stock_availability.status', 'partially_available')
            ->assertJsonPath('stock_availability.requested_quantity', 100)
            ->assertJsonPath('stock_availability.available_quantity', 65)
            ->assertJsonPath('stock_availability.shortage', 35);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->get(route('requests.index'))
            ->assertOk()
            ->assertSee('TA-STOCK-CHECK-001')
            ->assertSee('Available: 65')
            ->assertSee('Short by 35');
    }

    private function createForwardedRequest(string $trackingReference): ResqoperationForwardedRequest
    {
        return ResqoperationForwardedRequest::create([
            'tracking_reference' => $trackingReference,
            'resqperation_request_id' => 'RR-' . $trackingReference,
            'item_name' => 'Medical kit',
            'quantity' => 4,
            'resqperation_status' => 'forwarded',
        ]);
    }
}
