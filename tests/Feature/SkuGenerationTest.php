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

    public function test_csv_import_combines_repeated_products_and_adds_each_stock_quantity(): void
    {
        $csv = implode("\n", [
            'name,category,unit_type,size_weight,target_beneficiary,variant,type,quantity,min_stock,expiration_date',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,10,10,',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,5,10,',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 2);

        $this->assertSame(['FORICESK01'], Item::orderBy('id')->pluck('sku')->all());
        $this->assertSame([15], Item::orderBy('id')->with('stockBatches')->get()->map(fn (Item $item) => (int) $item->stockBatches->sum('quantity'))->all());
        $this->assertSame(2, \App\Models\StockBatch::where('item_id', Item::firstOrFail()->id)->count());
    }

    public function test_csv_import_automatically_assigns_fixed_storage_location_without_csv_field(): void
    {
        $csv = implode("\n", [
            'name,category,unit_type,size_weight,target_beneficiary,variant,type,quantity,min_stock,expiration_date',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,10,10,',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 1);

        $this->assertDatabaseHas('items', [
            'name' => 'Rice',
            'storage_location' => 'Mambaling Storage',
        ]);
        $this->assertDatabaseHas('stock_batches', [
            'storage_location' => 'Mambaling Storage',
            'movement_type' => 'initial-import',
            'quantity' => 10,
        ]);
    }

    public function test_csv_import_accepts_reordered_alias_headers_and_adds_stock_to_matching_existing_item(): void
    {
        $item = Item::create([
            'name' => 'Rice',
            'category' => 'FOOD',
            'unit_type' => 'SACK',
            'size_weight' => '5KG',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Mambaling Storage',
            'sku' => 'FORICESK01',
        ]);
        \App\Models\StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 6,
            'supplier' => 'Existing stock',
            'date_received' => now()->toDateString(),
            'storage_location' => 'Mambaling Storage',
        ]);
        $csv = "\xEF\xBB\xBFQuantity;Item Name;Category;Unit;Type\n"
            ."9; rice ;food;sack;Consumable\n";

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 1)
            ->assertSessionHas('import_summary.created', 0)
            ->assertSessionHas('import_summary.updated', 1);

        $this->assertSame(1, Item::count());
        $this->assertSame(15, (int) $item->fresh()->total_stock);
        $this->assertDatabaseHas('stock_batches', [
            'item_id' => $item->id,
            'quantity' => 9,
            'movement_type' => 'stock-in',
            'storage_location' => 'Mambaling Storage',
        ]);
        $this->assertDatabaseHas('inventory', ['sku' => $item->sku, 'quantity' => 15]);
    }

    public function test_csv_import_matches_existing_item_by_sku_even_when_name_format_differs(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $item = Item::create([
            'name' => 'Emergency Blanket',
            'category' => 'MEDICAL',
            'unit_type' => 'EACH',
            'size_weight' => 'STANDARD',
            'target_beneficiary' => 'ALL',
            'variant' => 'THERMAL',
            'type' => 'consumable',
            'storage_location' => 'Mambaling Storage',
            'sku' => 'MEDBLANK01',
            'min_stock' => 7,
        ]);
        \App\Models\StockBatch::create([
            'item_id' => $item->id,
            'quantity' => 20,
            'supplier' => 'Opening stock',
            'date_received' => now()->toDateString(),
            'storage_location' => 'Mambaling Storage',
        ]);
        $csv = "SKU,Item Name,Category,Unit,Quantity,Supplier,Date Received\n"
            ."medblank01, emergency   blanket ,medical,each,5,Relief Supplier,2026-04-15\n";

        $this->actingAs($admin)
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.rows_processed', 1)
            ->assertSessionHas('import_summary.created', 0)
            ->assertSessionHas('import_summary.updated', 1)
            ->assertSessionHas('import_summary.stock_added', 5);

        $this->assertSame(1, Item::count());
        $this->assertSame('Emergency Blanket', $item->fresh()->name);
        $this->assertSame(7, (int) $item->fresh()->min_stock);
        $this->assertSame(25, (int) Inventory::where('sku', $item->sku)->value('quantity'));
        $this->assertDatabaseHas('stock_batches', [
            'item_id' => $item->id,
            'quantity' => 5,
            'supplier' => 'Relief Supplier',
            'date_received' => '2026-04-15',
            'created_by_user_id' => $admin->id,
            'movement_type' => 'stock-in',
            'storage_location' => 'Mambaling Storage',
        ]);
    }

    public function test_csv_import_handles_existing_and_new_items_and_repeated_rows_without_duplicates(): void
    {
        $existing = Item::create([
            'name' => 'Flashlight',
            'category' => 'RESCUE',
            'unit_type' => 'EACH',
            'target_beneficiary' => 'ALL',
            'variant' => 'NONE',
            'type' => 'consumable',
            'storage_location' => 'Mambaling Storage',
            'sku' => 'RESFLASH01',
        ]);
        \App\Models\StockBatch::create([
            'item_id' => $existing->id,
            'quantity' => 10,
            'supplier' => 'Opening stock',
            'date_received' => now()->toDateString(),
            'movement_type' => 'initial-import',
            'storage_location' => 'Mambaling Storage',
        ]);
        $csv = "SKU,Item Name,Category,Unit,Quantity\n"
            ."RESFLASH01,Flashlight,RESCUE,EACH,3\n"
            ."RESFLASH01,Flashlight,RESCUE,EACH,2\n"
            ."NEWKIT001,First Aid Kit,MEDICAL,BOX,4\n"
            ."NEWKIT001,First Aid Kit,MEDICAL,BOX,6\n";

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.rows_processed', 4)
            ->assertSessionHas('import_summary.imported', 4)
            ->assertSessionHas('import_summary.created', 1)
            ->assertSessionHas('import_summary.updated', 3)
            ->assertSessionHas('import_summary.stock_added', 15);

        $this->assertSame(2, Item::count());
        $this->assertSame(15, (int) Inventory::where('sku', 'RESFLASH01')->value('quantity'));
        $this->assertSame(10, (int) Inventory::where('sku', 'NEWKIT001')->value('quantity'));
        $this->assertSame(2, \App\Models\StockBatch::where('item_id', $existing->id)->where('movement_type', 'stock-in')->count());
        $this->assertSame(2, \App\Models\StockBatch::where('item_id', Item::where('sku', 'NEWKIT001')->value('id'))->count());
        $this->assertDatabaseHas('activity_logs', [
            'actor_id' => User::where('role', 'admin')->firstOrFail()->id,
            'subject_type' => \App\Models\StockBatch::class,
            'subject_id' => (string) \App\Models\StockBatch::where('item_id', $existing->id)->where('quantity', 3)->value('id'),
            'event_type' => 'created',
        ]);
    }

    public function test_csv_import_reports_matching_candidates_instead_of_choosing_ambiguous_item(): void
    {
        $first = Item::create([
            'name' => 'Blanket',
            'category' => 'MEDICAL',
            'unit_type' => 'EACH',
            'size_weight' => 'SINGLE',
            'target_beneficiary' => 'ALL',
            'variant' => 'THERMAL',
            'type' => 'consumable',
            'storage_location' => 'Mambaling Storage',
            'sku' => 'MEDBLANK11',
        ]);
        $second = Item::create([
            'name' => 'Blanket',
            'category' => 'MEDICAL',
            'unit_type' => 'EACH',
            'size_weight' => 'DOUBLE',
            'target_beneficiary' => 'ALL',
            'variant' => 'THERMAL',
            'type' => 'consumable',
            'storage_location' => 'Mambaling Storage',
            'sku' => 'MEDBLANK12',
        ]);
        $csv = "Item Name,Category,Unit,Quantity\nBlanket,MEDICAL,EACH,8\n";

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 0)
            ->assertSessionHas('import_summary.skipped', 1)
            ->assertSessionHas('import_summary.errors', function (array $errors) use ($first, $second): bool {
                $message = implode(' ', $errors);
                return str_contains($message, "Candidates: #{$first->id} SKU {$first->sku}")
                    && str_contains($message, "#{$second->id} SKU {$second->sku}");
            });

        $this->assertSame(2, Item::count());
        $this->assertSame(0, \App\Models\StockBatch::whereIn('item_id', [$first->id, $second->id])->count());
    }

    public function test_csv_import_ignores_legacy_storage_column_and_uses_fixed_location(): void
    {
        $csv = implode("\n", [
            'name,category,unit_type,size_weight,target_beneficiary,variant,type,storage_location,quantity,min_stock,expiration_date',
            'Rice,FOOD,SACK,5KG,ALL,NONE,consumable,Unknown storage,10,10,',
        ]);

        $this->actingAs(User::factory()->create(['role' => 'admin']))
            ->from(route('inventory.index'))
            ->post(route('inventory.import'), ['csv_file' => UploadedFile::fake()->createWithContent('items.csv', $csv)])
            ->assertRedirect(route('inventory.index'))
            ->assertSessionHas('import_summary.imported', 1);

        $this->assertDatabaseHas('items', [
            'name' => 'Rice',
            'storage_location' => 'Mambaling Storage',
        ]);
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
