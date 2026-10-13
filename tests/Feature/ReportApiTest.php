<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class ReportApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_reports_can_be_requested_as_json(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);

        $response = $this->withHeader('X-API-KEY', 'test-report-key')
            ->getJson('/api/reports?report=inventory');

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('report', 'inventory');
    }

    public function test_api_reports_can_store_a_request_and_return_payload(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);

        $response = $this->withHeader('X-API-KEY', 'test-report-key')
            ->postJson('/api/reports', [
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

    public function test_external_report_endpoint_returns_a_signed_csv_download(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);
        config(['app.url' => 'https://reports.example.test']);
        Storage::fake('local');

        $response = $this->withHeader('X-API-KEY', 'test-report-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'inventory',
                'format' => 'csv',
                'member_id' => 'member-42',
            ]);

        $response->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('report_type', 'inventory')
            ->assertJsonPath('format', 'csv')
            ->assertJsonPath('member_id', 'member-42')
            ->assertJsonStructure(['request_id', 'file_name', 'download_url', 'expires_at']);

        $this->assertSame('https', parse_url($response->json('download_url'), PHP_URL_SCHEME));
        $this->assertSame('reports.example.test', parse_url($response->json('download_url'), PHP_URL_HOST));

        $downloadResponse = $this->get($response->json('download_url'));

        $downloadResponse->assertOk()
            ->assertHeader('content-disposition');

        $this->assertStringContainsString('SKU', $downloadResponse->streamedContent());

        $this->assertDatabaseHas('report_requests', [
            'id' => $response->json('request_id'),
            'member_id' => 'member-42',
            'status' => 'completed',
            'format' => 'csv',
        ]);
    }

    public function test_external_report_endpoint_can_generate_a_pdf(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);
        Storage::fake('local');

        $response = $this->withHeader('X-API-KEY', 'test-report-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'inventory',
                'format' => 'pdf',
            ]);

        $response->assertOk();

        $downloadResponse = $this->get($response->json('download_url'));

        $downloadResponse->assertOk();
        $this->assertStringStartsWith('%PDF-', $downloadResponse->streamedContent());
    }

    public function test_external_report_endpoint_can_generate_a_json_file(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);
        Storage::fake('local');

        $response = $this->withHeader('X-API-KEY', 'test-report-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'inventory',
                'format' => 'json',
            ]);

        $response->assertOk();

        $downloadResponse = $this->get($response->json('download_url'));

        $downloadResponse->assertOk();
        $this->assertStringContainsString('"report_type": "inventory"', $downloadResponse->streamedContent());
    }

    public function test_external_report_endpoint_rejects_missing_or_invalid_api_keys(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);

        $this->postJson('/api/v1/external/reports/generate', [
            'report_type' => 'inventory',
            'format' => 'csv',
        ])->assertUnauthorized();

        $this->withHeader('X-API-KEY', 'incorrect-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'inventory',
                'format' => 'csv',
            ])->assertUnauthorized();
    }

    public function test_external_report_endpoint_fails_closed_when_no_api_key_is_configured(): void
    {
        config(['services.trackingaid_reports.api_key' => '']);

        $this->withHeader('X-API-KEY', 'some-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'inventory',
                'format' => 'csv',
            ])->assertServiceUnavailable();
    }

    public function test_external_report_endpoint_requires_an_explicitly_supported_report_type(): void
    {
        config(['services.trackingaid_reports.api_key' => 'test-report-key']);

        $this->withHeader('X-API-KEY', 'test-report-key')
            ->postJson('/api/v1/external/reports/generate', [
                'report_type' => 'demographics',
                'format' => 'pdf',
            ])->assertUnprocessable()
            ->assertJsonValidationErrors('report_type');
    }
}
