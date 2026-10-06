<?php

namespace Tests\Feature;

use App\Models\User;
use App\Models\ResqoperationForwardedRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ResqperationRequestIntegrationTest extends TestCase
{
    use RefreshDatabase;

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
            ->assertSee('title="View request details"', false)
            ->assertSee('onclick="showForwardedRequest(this)"', false)
            ->assertSee('id="forwardedRequestDialog"', false)
            ->assertSee('data-item="Medical kit"', false)
            ->assertViewHas('stats', fn (array $stats): bool => $stats['pending'] === 1);
    }

    public function test_forwarded_request_can_be_approved_and_moves_to_approved_count(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
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
