<?php

namespace Database\Seeders;

use App\Models\Inventory;
use App\Models\Item;
use App\Models\User;
use App\Services\InventorySyncService;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        // ─── USERS ───────────────────────────────────────────
        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name'     => 'Test User',
                'password' => bcrypt('password'),
                'role'     => 'staff',
            ]
        );

        User::firstOrCreate(
            ['email' => 'admin@trackingaid.org'],
            [
                'name'     => 'Admin User',
                'password' => bcrypt('password'),
                'role'     => 'admin',
            ]
        );

        // ─── INVENTORY ITEMS ─────────────────────────────────
        $items = [
            [
                'sku'                => 'FOOD-RICE-SACK-50KG-ALL',
                'name'               => 'Rice (50kg)',
                'category'           => 'FOOD',
                'unit_type'          => 'Sack',
                'size_weight'        => '50KG',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'consumable',
                'storage_location'   => 'Warehouse A',
                'expiration_date'    => '2026-12-31',
            ],
            [
                'sku'                => 'MEDICAL-MEDKIT-BOX-SM-ALL',
                'name'               => 'Medical Kit (Small)',
                'category'           => 'MEDICAL',
                'unit_type'          => 'Box',
                'size_weight'        => 'SM',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'consumable',
                'storage_location'   => 'Warehouse B',
                'expiration_date'    => '2026-08-01',
            ],
            [
                'sku'                => 'MEDICAL-N95MASK-PACK-REG-ALL',
                'name'               => 'N95 Masks',
                'category'           => 'MEDICAL',
                'unit_type'          => 'Pack',
                'size_weight'        => 'REG',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'consumable',
                'storage_location'   => 'Warehouse B',
                'expiration_date'    => '2026-07-15',
            ],
            [
                'sku'                => 'RESCUE-LIFEVEST-PCS-MD-ADULT',
                'name'               => 'Life Vest (Medium)',
                'category'           => 'RESCUE',
                'unit_type'          => 'PCS',
                'size_weight'        => 'MD',
                'target_beneficiary' => 'ADULT',
                'variant'            => 'NONE',
                'type'               => 'returnable',
                'storage_location'   => 'Equipment Bay',
                'expiration_date'    => null,
            ],
            [
                'sku'                => 'FOOD-NOODLES-PACK-REG-ALL',
                'name'               => 'Instant Noodles',
                'category'           => 'FOOD',
                'unit_type'          => 'Pack',
                'size_weight'        => 'REG',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'consumable',
                'storage_location'   => 'Warehouse A',
                'expiration_date'    => '2026-07-20',
            ],
            [
                'sku'                => 'RELIEF-BLANKET-PCS-REG-ALL',
                'name'               => 'Blankets',
                'category'           => 'RELIEF',
                'unit_type'          => 'PCS',
                'size_weight'        => 'REG',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'returnable',
                'storage_location'   => 'Warehouse C',
                'expiration_date'    => null,
            ],
            [
                'sku'                => 'FOOD-WATER-BTL-500ML-ALL',
                'name'               => 'Water (500ml)',
                'category'           => 'FOOD',
                'unit_type'          => 'Bottle',
                'size_weight'        => '500ML',
                'target_beneficiary' => 'ALL',
                'variant'            => 'NONE',
                'type'               => 'consumable',
                'storage_location'   => 'Warehouse A',
                'expiration_date'    => '2026-07-10',
            ],
            [
                'sku'                => 'RESCUE-ROPE-PCS-LG-ADULT',
                'name'               => 'Rescue Rope (Large)',
                'category'           => 'RESCUE',
                'unit_type'          => 'PCS',
                'size_weight'        => 'LG',
                'target_beneficiary' => 'ADULT',
                'variant'            => 'NONE',
                'type'               => 'returnable',
                'storage_location'   => 'Equipment Bay',
                'expiration_date'    => null,
            ],
        ];

        foreach ($items as $itemData) {
            Item::firstOrCreate(
                ['sku' => $itemData['sku']],
                $itemData
            );
        }

        // ─── INVENTORY TABLE (for FK relationships) ──────────
        $inventoryRecords = [
            ['name' => 'Rice (50kg)',            'category' => 'FOOD',    'sku' => 'FOOD-RICE-SACK-50KG-ALL',      'type' => 'Consumable',  'quantity' => 450, 'expiration' => '2026-12-31', 'storage_location' => 'Warehouse A'],
            ['name' => 'Medical Kit (Small)',     'category' => 'MEDICAL', 'sku' => 'MEDICAL-MEDKIT-BOX-SM-ALL',    'type' => 'Consumable',  'quantity' => 8,   'expiration' => '2026-08-01', 'storage_location' => 'Warehouse B'],
            ['name' => 'N95 Masks',              'category' => 'MEDICAL', 'sku' => 'MEDICAL-N95MASK-PACK-REG-ALL', 'type' => 'Consumable',  'quantity' => 1240,'expiration' => '2026-07-15', 'storage_location' => 'Warehouse B'],
            ['name' => 'Life Vest (Medium)',     'category' => 'RESCUE',  'sku' => 'RESCUE-LIFEVEST-PCS-MD-ADULT', 'type' => 'Returnable',  'quantity' => 12,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
            ['name' => 'Instant Noodles',        'category' => 'FOOD',    'sku' => 'FOOD-NOODLES-PACK-REG-ALL',    'type' => 'Consumable',  'quantity' => 2340,'expiration' => '2026-07-20', 'storage_location' => 'Warehouse A'],
            ['name' => 'Blankets',               'category' => 'RELIEF',  'sku' => 'RELIEF-BLANKET-PCS-REG-ALL',    'type' => 'Returnable',  'quantity' => 6,   'expiration' => null,          'storage_location' => 'Warehouse C'],
            ['name' => 'Water (500ml)',          'category' => 'FOOD',    'sku' => 'FOOD-WATER-BTL-500ML-ALL',     'type' => 'Consumable',  'quantity' => 3600,'expiration' => '2026-07-10', 'storage_location' => 'Warehouse A'],
            ['name' => 'Rescue Rope (Large)',    'category' => 'RESCUE',  'sku' => 'RESCUE-ROPE-PCS-LG-ADULT',    'type' => 'Returnable',  'quantity' => 24,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
        ];

        foreach ($inventoryRecords as $invData) {
            Inventory::firstOrCreate(
                ['sku' => $invData['sku']],
                $invData
            );
        }

        // ─── SAMPLE REQUESTS & STOCK ────────────────────────
        $this->call([
            RequestSeeder::class,
            StockBatchSeeder::class,
        ]);

        app(InventorySyncService::class)->syncAllItems();
    }
}
