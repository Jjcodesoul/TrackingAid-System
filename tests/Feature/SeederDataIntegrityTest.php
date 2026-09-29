<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_request_seeder_links_requests_to_the_declared_inventory_skus(): void
    {
        foreach ([
            'MEDICAL-MEDKIT-BOX-SM-ALL' => 'Medical Kit (Small)',
            'FOOD-RICE-SACK-50KG-ALL' => 'Rice (50kg)',
            'RELIEF-BLANKET-PCS-REG-ALL' => 'Blankets',
            'FOOD-NOODLES-PACK-REG-ALL' => 'Instant Noodles',
        ] as $sku => $name) {
            Inventory::create([
                'name' => $name, 'category' => 'Test', 'sku' => $sku,
                'type' => 'Consumable', 'quantity' => 10,
            ]);
        }

        $this->seed(\Database\Seeders\RequestSeeder::class);

        $requests = SupplyRequest::with('inventory')->orderBy('request_code')->get();

        $this->assertSame([
            'REQ-2024-086' => 'RELIEF-BLANKET-PCS-REG-ALL',
            'REQ-2024-087' => 'MEDICAL-MEDKIT-BOX-SM-ALL',
            'REQ-2024-088' => 'FOOD-NOODLES-PACK-REG-ALL',
            'REQ-2024-089' => 'RELIEF-BLANKET-PCS-REG-ALL',
            'REQ-2024-090' => 'FOOD-RICE-SACK-50KG-ALL',
            'REQ-2024-091' => 'MEDICAL-MEDKIT-BOX-SM-ALL',
        ], $requests->mapWithKeys(fn (SupplyRequest $request): array => [$request->request_code => $request->inventory->sku])->all());

        $this->assertSame(['High', 'Low', 'Medium'], $requests->pluck('priority')->unique()->sort()->values()->all());
    }
}
