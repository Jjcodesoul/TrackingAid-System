<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\StockBatch;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ReportsTest extends TestCase
{
    use RefreshDatabase;

    public function test_reports_page_shows_inventory_levels(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $item = Item::create([
            'name' => 'Water Bottles',
            'category' => 'FOOD',
            'unit_type' => 'Box',
            'size_weight' => '500ML',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Warehouse A',
            'expiration_date' => now()->addMonths(3)->toDateString(),
            'sku' => 'FOOD-WATER-BOX-500ML-ALL',
        ]);

        StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 120,
            'supplier' => 'LGU',
            'date_received' => now()->toDateString(),
            'expiration_date' => now()->addMonths(3)->toDateString(),
        ]);

        $response = $this->get(route('reports.index'));

        $response->assertOk();
        $response->assertSee('Reports');
        $response->assertSee('Inventory Levels');
        $response->assertSee('FOOD-WATER-BOX-500ML-ALL');
        $response->assertSee('120');
    }

    public function test_request_report_aggregates_monthly_approval_history(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $inventory = Inventory::create([
            'name' => 'Medical Kits',
            'category' => 'Medical',
            'sku' => 'MED-KIT-001',
            'type' => 'Consumable',
            'quantity' => 50,
            'expiration' => null,
            'storage_location' => 'Warehouse B',
        ]);

        SupplyRequest::create([
            'request_code' => 'REQ-APPROVED',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 10,
            'priority' => 'High',
            'status' => 'Approved',
            'purpose' => 'Clinic support',
            'created_at' => now(),
        ]);

        SupplyRequest::create([
            'request_code' => 'REQ-REJECTED',
            'inventory_id' => $inventory->id,
            'source' => 'ResQOperation',
            'quantity' => 4,
            'priority' => 'Low',
            'status' => 'Rejected',
            'purpose' => 'Duplicate request',
            'created_at' => now(),
        ]);

        $response = $this->get(route('reports.index', ['report' => 'request']));

        $response->assertOk();
        $response->assertSee('Request History');
        $response->assertViewHas('report', function (array $report): bool {
            $currentMonth = now()->format('M Y');
            $row = collect($report['rows'])->first(fn (array $row): bool => $row['csv'][0] === $currentMonth);

            return $row
                && $row['csv'][1] === '2'
                && $row['csv'][2] === '1'
                && $row['csv'][3] === '1'
                && $row['csv'][4] === '50%';
        });
    }

    public function test_reports_can_be_exported_as_csv(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $response = $this->get(route('reports.export', ['report' => 'inventory']));

        $response->assertOk();
        $this->assertStringContainsString('text/csv', $response->headers->get('content-type'));
        $this->assertStringContainsString('inventory-report-', $response->headers->get('content-disposition'));
    }
}
