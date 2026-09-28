<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\StockBatch;
use App\Models\User;
use App\Models\BorrowRelease;
use App\Models\ReturnItem;
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
            $row = $report['rows']->getCollection()->first(fn (array $row): bool => $row['csv'][0] === $currentMonth);

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

    public function test_inventory_summary_table_paginates_without_truncating_csv_export(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        foreach (range(1, 17) as $number) {
            Item::create([
                'name' => sprintf('Pagination Item %02d', $number),
                'category' => 'FOOD',
                'unit_type' => 'BOX',
                'target_beneficiary' => 'ALL',
                'type' => 'consumable',
                'storage_location' => 'Warehouse A',
                'sku' => sprintf('PAGINATION-%02d', $number),
            ]);
        }

        $firstPage = $this->get(route('reports.index', ['report' => 'inventory']));
        $firstPage->assertOk()
            ->assertSee('aria-label="Summary table pagination"', false)
            ->assertSee('href="' . e(route('reports.index', ['report' => 'inventory', 'page' => 2])) . '"', false)
            ->assertViewHas('report', fn (array $report): bool => $report['rows']->count() === 15
                && $report['rows']->total() === 17
                && $report['rows']->currentPage() === 1);
        $screenTable = $this->reportTableMarkup($firstPage->getContent(), 'screen-report-table-wrap');
        $this->assertStringContainsString('PAGINATION-01', $screenTable);
        $this->assertStringContainsString('PAGINATION-15', $screenTable);
        $this->assertStringNotContainsString('PAGINATION-16', $screenTable);
        $firstPage->assertSee('class="report-table-wrap print-report-table-wrap"', false)
            ->assertSee('PAGINATION-17');
        $firstPage->assertViewHas('allRows', fn ($rows): bool => $rows->count() === 17);
        $this->assertMatchesRegularExpression(
            '/Showing\s*<strong>1–15<\/strong>\s*of\s*<strong>17<\/strong> rows/',
            $firstPage->getContent()
        );

        $secondPage = $this->get(route('reports.index', ['report' => 'inventory', 'page' => 2]));
        $secondPage->assertOk()
            ->assertViewHas('report', fn (array $report): bool => $report['rows']->count() === 2
                && $report['rows']->currentPage() === 2);
        $screenTable = $this->reportTableMarkup($secondPage->getContent(), 'screen-report-table-wrap');
        $this->assertStringContainsString('PAGINATION-16', $screenTable);
        $this->assertStringContainsString('PAGINATION-17', $screenTable);
        $this->assertStringNotContainsString('PAGINATION-01', $screenTable);
        $this->assertMatchesRegularExpression(
            '/Showing\s*<strong>16–17<\/strong>\s*of\s*<strong>17<\/strong> rows/',
            $secondPage->getContent()
        );

        $this->get(route('reports.index', ['report' => 'inventory', 'page' => 999]))
            ->assertOk()
            ->assertSee('PAGINATION-16')
            ->assertSee('PAGINATION-17')
            ->assertViewHas('report', fn (array $report): bool => $report['rows']->currentPage() === 2);

        $csvResponse = $this->get(route('reports.export', ['report' => 'inventory']))
            ->assertOk();
        $csv = $csvResponse->streamedContent();

        $this->assertStringContainsString('PAGINATION-17', $csv);
        $this->assertCount(18, array_filter(explode("\n", trim($csv))));
    }

    private function reportTableMarkup(string $html, string $wrapperClass): string
    {
        preg_match('/<div class="report-table-wrap ' . preg_quote($wrapperClass, '/') . '">(.*?)<\/div>/s', $html, $matches);

        return $matches[1] ?? '';
    }

    public function test_inventory_report_sums_batches_and_only_counts_future_expirations(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));

        $item = Item::create([
            'name' => 'Rice', 'category' => 'FOOD', 'unit_type' => 'SACK',
            'target_beneficiary' => 'ALL', 'type' => 'consumable',
            'storage_location' => 'Warehouse A', 'sku' => 'FOOD-RICE-SACK',
        ]);
        foreach ([12, 8] as $quantity) {
            StockBatch::create([
                'item_id' => $item->id, 'quantity' => $quantity, 'supplier' => 'Verified supplier',
                'date_received' => now()->toDateString(),
                'expiration_date' => now()->addDays(20)->toDateString(),
            ]);
        }

        $expiredItem = Item::create([
            'name' => 'Expired Water', 'category' => 'FOOD', 'unit_type' => 'BOX',
            'target_beneficiary' => 'ALL', 'type' => 'consumable',
            'storage_location' => 'Warehouse A', 'sku' => 'FOOD-WATER-EXPIRED',
        ]);
        StockBatch::create([
            'item_id' => $expiredItem->id, 'quantity' => 5, 'supplier' => 'Verified supplier',
            'date_received' => now()->subYear()->toDateString(),
            'expiration_date' => now()->subDay()->toDateString(),
        ]);

        $this->get(route('reports.index'))
            ->assertOk()
            ->assertViewHas('report', function (array $report): bool {
                $rice = $report['rows']->getCollection()->first(fn (array $row): bool => $row['csv'][0] === 'FOOD-RICE-SACK');

                return $report['stats'][0]['value'] === '2'
                    && $report['stats'][1]['value'] === '25'
                    && $report['stats'][2]['value'] === '1'
                    && $report['stats'][3]['value'] === '1'
                    && $rice['csv'][3] === '20';
            });
    }

    public function test_borrow_return_report_reconciles_good_damaged_and_missing_quantities(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $inventory = Inventory::create([
            'name' => 'Rescue tents', 'category' => 'Shelter', 'sku' => 'TENT-001',
            'type' => 'Returnable', 'quantity' => 20, 'storage_location' => 'Warehouse B',
        ]);
        $request = SupplyRequest::create([
            'request_code' => 'REQ-TENT-001', 'inventory_id' => $inventory->id,
            'source' => 'ResQOperation', 'quantity' => 10, 'priority' => 'High',
            'status' => 'Released', 'purpose' => 'Field deployment',
        ]);
        BorrowRelease::create([
            'request_id' => $request->id, 'inventory_id' => $inventory->id, 'unit' => 'PCS',
            'quantity' => 10, 'purpose' => 'Field deployment', 'location' => 'Zone 1', 'released_at' => now(),
        ]);
        foreach ([['Good', 6], ['Damaged', 2], ['Missing', 1]] as [$condition, $quantity]) {
            ReturnItem::create(['inventory_id' => $inventory->id, 'quantity' => $quantity, 'condition' => $condition]);
        }

        $this->get(route('reports.index', ['report' => 'borrow-return']))
            ->assertOk()
            ->assertViewHas('report', function (array $report): bool {
                $row = $report['rows'][0]['csv'];

                return $report['stats'][0]['value'] === '10'
                    && $report['stats'][1]['value'] === '9'
                    && $report['stats'][2]['value'] === '4'
                    && $report['stats'][3]['value'] === '3'
                    && $row[2] === '10' && $row[3] === '6' && $row[4] === '3' && $row[5] === '4';
            });
    }

    public function test_distribution_report_aggregates_by_destination_and_top_item(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']));
        $inventory = Inventory::create([
            'name' => 'Water', 'category' => 'Relief', 'sku' => 'WATER-001',
            'type' => 'Consumable', 'quantity' => 100, 'storage_location' => 'Warehouse A',
        ]);
        $request = SupplyRequest::create([
            'request_code' => 'REQ-WATER-001', 'inventory_id' => $inventory->id,
            'source' => 'ResQOperation', 'quantity' => 20, 'priority' => 'High',
            'status' => 'Released', 'purpose' => 'Relief distribution',
        ]);
        foreach ([5, 7] as $quantity) {
            BorrowRelease::create([
                'request_id' => $request->id, 'inventory_id' => $inventory->id, 'unit' => 'BOX',
                'quantity' => $quantity, 'purpose' => 'Relief distribution', 'location' => 'Barangay 1', 'released_at' => now(),
            ]);
        }

        $this->get(route('reports.index', ['report' => 'distribution']))
            ->assertOk()
            ->assertViewHas('report', function (array $report): bool {
                return $report['stats'][0]['value'] === '12'
                    && $report['stats'][1]['value'] === '1'
                    && $report['stats'][2]['value'] === '2'
                    && $report['stats'][3]['value'] === 'Barangay 1'
                    && $report['rows'][0]['csv'][1] === '2'
                    && $report['rows'][0]['csv'][2] === '1'
                    && $report['rows'][0]['csv'][3] === '12'
                    && $report['rows'][0]['csv'][4] === 'Water';
            });
    }
}
