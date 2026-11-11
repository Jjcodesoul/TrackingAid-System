<?php

namespace Tests\Feature;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\StorageLocation;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Tests\TestCase;

class SkuGenerationTest extends TestCase
{
    use RefreshDatabase;

    public function test_sku_format_accepts_uppercase_alphanumeric_examples_with_numeric_final_characters(): void
    {
        $generator = app(\App\Services\SkuGenerator::class);
        foreach (['ITEM001', 'RICE2026', 'MED0001', 'SUPPLY123'] as $sku) {
            $this->assertTrue($generator->isValidFormat($sku), $sku);
        }
        foreach (['item001', 'ABC123', 'TOO-LONG-SKU-123', 'SKU 12345'] as $sku) {
            $this->assertFalse($generator->isValidFormat($sku), $sku);
        }
    }

    protected function setUp(): void
    {
        parent::setUp();

        StorageLocation::firstOrCreate(
            ['name' => 'Main Warehouse'],
            ['warehouse_name' => 'Mambaling — temporary labels; physical locations pending confirmation', 'is_active' => true],
        );
    }

    public function test_sku_uses_item_attributes_and_is_generated_on_the_server(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('inventory.store'), $this->itemPayload([
                'sku' => 'CLIENT-SUPPLIED-VALUE',
            ]))
            ->assertRedirect('/inventory');

        $item = Item::firstOrFail();
        $this->assertSame('FORICESK01', $item->sku);
        $this->assertSame(10, strlen($item->sku));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{7,12}$/', $item->sku);
        $this->assertDatabaseHas('inventory', ['sku' => $item->sku]);
    }

    public function test_similar_items_get_sequential_skus_and_unit_type_is_part_of_the_sku(): void
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
        $this->assertSame(['FORICESK01', 'FORICESK02'], Item::orderBy('id')->pluck('sku')->all());

        $this->post(route('inventory.store'), $this->itemPayload())
            ->assertRedirect('/inventory');

        $this->post(route('inventory.store'), $this->itemPayload(['unit_type' => 'BOX']))
            ->assertRedirect('/inventory');

        $this->assertSame(4, Item::count());
        $this->assertSame(4, Inventory::count());
        $this->assertSame('FORICESK03', Item::orderBy('id')->skip(2)->value('sku'));
        $this->assertSame('FORICEBX01', Item::orderBy('id')->skip(3)->value('sku'));
    }

    public function test_reusable_label_is_accepted_while_legacy_storage_value_is_preserved(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->post(route('inventory.store'), $this->itemPayload(['type' => 'reusable']))
            ->assertRedirect('/inventory');

        $this->assertDatabaseHas('items', ['name' => 'Rice', 'type' => 'returnable']);
        $this->assertDatabaseHas('inventory', ['sku' => Item::firstOrFail()->sku, 'type' => 'Returnable']);
    }

    public function test_csv_import_assigns_the_same_sequential_skus_as_item_creation(): void
    {
        $csv = implode("\n", [
            'name,category,unit_type,size_weight,target_beneficiary,variant,type,storage_location,quantity,min_stock,expiration_date',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,Mambaling Storage,10,10,',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,Mambaling Storage,5,10,',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 2);

        $this->assertSame(['FORICESK01', 'FORICESK02'], Item::orderBy('id')->pluck('sku')->all());
        $this->assertSame([10, 5], Item::orderBy('id')->with('stockBatches')->get()->map(fn (Item $item) => (int) $item->stockBatches->sum('quantity'))->all());
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
            'storage_location_id' => StorageLocation::where('name', 'Main Warehouse')->value('id'),
        ], $overrides);
    }
}
