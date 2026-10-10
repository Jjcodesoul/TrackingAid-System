<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_reports_can_be_requested_as_json(): void
    {
        $response = $this->getJson('/api/reports?report=inventory');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('report', 'inventory');
    }

    public function test_api_reports_can_store_a_request_and_return_payload(): void
    {
        $response = $this->postJson('/api/reports', [
            'report' => 'inventory',
            'requested_by' => 'group-a',
            'system_name' => 'trackingaid',
            'notes' => 'Monthly inventory export',
        ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('report', 'inventory')
            ->assertJsonPath('request_id', 1)
            ->assertJsonPath('data.status', 'success');
    }
}
