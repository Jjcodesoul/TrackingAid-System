<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\Request as SupplyRequest;
use App\Models\StockBatch;
use App\Models\User;
use App\Services\SkuGenerator;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SeederDataIntegrityTest extends TestCase
{
    use RefreshDatabase;

    public function test_full_seed_uses_compact_generated_skus_consistently(): void
    {
        $this->seed(\Database\Seeders\DatabaseSeeder::class);
        $skuGenerator = app(SkuGenerator::class);

        $deliveryUser = User::where('email', 'delivery@trackingaid.org')->firstOrFail();
        $this->assertSame('delivery_personnel', $deliveryUser->role);
        $this->assertTrue($deliveryUser->must_change_password);

        $this->actingAs(User::where('email', 'admin@trackingaid.org')->firstOrFail())
            ->get(route('borrow-release.create'))
            ->assertOk()
            ->assertSeeText('Demo Delivery Personnel');

        $this->assertGreaterThan(0, Item::count());
        foreach (Item::all() as $item) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{3}-[A-F0-9]{8}$/', $item->sku);
            $this->assertSame($skuGenerator->generate(
                $item->category,
                $item->name,
                $item->size_weight,
                $item->variant,
                $item->target_beneficiary,
                $item->type,
            ), $item->sku);
            $this->assertDatabaseHas('inventory', ['sku' => $item->sku]);
        }

        foreach (Inventory::all() as $inventory) {
            $this->assertMatchesRegularExpression('/^[A-Z0-9]{3}-[A-F0-9]{8}$/', $inventory->sku);
            $this->assertDatabaseHas('items', ['sku' => $inventory->sku]);
        }

        $this->assertSame(Item::count(), StockBatch::query()->distinct('item_id')->count('item_id'));
    }

    public function test_request_seeder_links_requests_to_the_declared_inventory_skus(): void
    {
        foreach ([
            'MED-381AB4AC' => 'Medical Kit (Small)',
            'FOO-669FB372' => 'Rice (50kg)',
            'REL-02B4AA7D' => 'Blankets',
            'FOO-EF61C25B' => 'Instant Noodles',
        ] as $sku => $name) {
            Inventory::create([
                'name' => $name, 'category' => 'Test', 'sku' => $sku,
                'type' => 'Consumable', 'quantity' => 10,
            ]);
        }

        $this->seed(\Database\Seeders\RequestSeeder::class);

        $requests = SupplyRequest::with('inventory')->orderBy('request_code')->get();

        $this->assertSame([
            'REQ-2024-086' => 'REL-02B4AA7D',
            'REQ-2024-087' => 'MED-381AB4AC',
            'REQ-2024-088' => 'FOO-EF61C25B',
            'REQ-2024-089' => 'REL-02B4AA7D',
            'REQ-2024-090' => 'FOO-669FB372',
            'REQ-2024-091' => 'MED-381AB4AC',
        ], $requests->mapWithKeys(fn (SupplyRequest $request): array => [$request->request_code => $request->inventory->sku])->all());

        $this->assertSame(['High', 'Low', 'Medium'], $requests->pluck('priority')->unique()->sort()->values()->all());
    }
}
