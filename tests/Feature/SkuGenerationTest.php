<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sku_uses_item_attributes_and_is_generated_on_the_server(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('inventory.store'), $this->itemPayload([
                'sku' => 'CLIENT-SUPPLIED-VALUE',
            ]))
            ->assertRedirect('/inventory');

        $item = Item::firstOrFail();
        $this->assertSame('FOORICE5KGCST01', $item->sku);
        $this->assertDatabaseHas('inventory', ['sku' => $item->sku]);
    }

    public function test_distinct_variants_get_distinct_skus_and_exact_duplicates_are_rejected(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)
            ->post(route('inventory.store'), $this->itemPayload())
            ->assertRedirect('/inventory');

        $this->post(route('inventory.store'), $this->itemPayload(['size_weight' => '25KG']))
            ->assertRedirect('/inventory');

        $this->assertSame(2, Item::count());
        $this->assertSame(2, Inventory::count());
        $this->assertSame(2, Item::query()->distinct('sku')->count('sku'));

        $this->post(route('inventory.store'), $this->itemPayload())
            ->assertSessionHasErrors('sku');

        $this->assertSame(2, Item::count());
        $this->assertSame(2, Inventory::count());
    }

    public function test_reusable_label_is_accepted_while_legacy_storage_value_is_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('inventory.store'), $this->itemPayload(['type' => 'reusable']))
            ->assertRedirect('/inventory');

        $this->assertDatabaseHas('items', ['name' => 'Rice', 'type' => 'returnable']);
        $this->assertDatabaseHas('inventory', ['sku' => Item::firstOrFail()->sku, 'type' => 'Returnable']);
    }

    private function itemPayload(array $overrides = []): array
    {
        return array_merge([
            'name' => 'Rice',
            'category' => 'FOOD',
            'unit_type' => 'SACK',
            'size_weight' => '5KG',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Warehouse A',
        ], $overrides);
    }
}
