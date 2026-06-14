<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\User;
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
    }
}
