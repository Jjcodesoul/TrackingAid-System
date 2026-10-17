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

        User::firstOrCreate(
            ['email' => 'delivery@trackingaid.org'],
            [
                'name'                 => 'Demo Delivery Personnel',
                'password'             => bcrypt('password'),
                'role'                 => 'delivery_personnel',
                'must_change_password' => true,
            ]
        );

        // ─── INVENTORY ITEMS ─────────────────────────────────
        $items = [
            [
                'sku'                => 'FOO-669FB372',
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
                'sku'                => 'MED-381AB4AC',
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
                'sku'                => 'MED-C09F0F76',
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
                'sku'                => 'RES-EEB70A7B',
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
                'sku'                => 'FOO-EF61C25B',
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
                'sku'                => 'REL-02B4AA7D',
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
                'sku'                => 'FOO-0D7FC9D8',
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
                'sku'                => 'RES-70BB1FA5',
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
            [
                'sku' => 'HYG-708C3C3B', 'name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'unit_type' => 'Bag', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYG-21CA9504', 'name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-02-28',
            ],
            [
                'sku' => 'HYG-4D9C9AEA', 'name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'WOMEN', 'variant' => 'REGULAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-06-30',
            ],
            [
                'sku' => 'HYG-17EE16D7', 'name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '500ML', 'target_beneficiary' => 'ALL', 'variant' => '70PCT', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2027-11-30',
            ],
            [
                'sku' => 'HYG-58202971', 'name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYG-B6204A15', 'name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'WASHABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYG-0743FBD4', 'name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'unit_type' => 'Box', 'size_weight' => '50TAB', 'target_beneficiary' => 'ALL', 'variant' => 'CHLORINE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-09-30',
            ],
            [
                'sku' => 'SHE-936D1922', 'name' => 'Family Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => '4PERSON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-62656045', 'name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '3X4M', 'target_beneficiary' => 'ALL', 'variant' => 'BLUE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-6F6A9D13', 'name' => 'Sleeping Mats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-58D97A4F', 'name' => 'Mosquito Nets', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'HANGING', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-A2AEEF7D', 'name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-F201A6BC', 'name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'unit_type' => 'Coil', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'NYLON', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHE-6C0B55C4', 'name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'STANDARD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COM-A97C8E4A', 'name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'TWO-WAY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COM-5D8FA03D', 'name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10000MAH', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COM-F660ABC6', 'name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'BATTERY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COM-A6F3EBAA', 'name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PEALESS', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COM-0D9B3764', 'name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-01-31',
            ],
            [
                'sku' => 'COM-B29104B8', 'name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '20W', 'target_beneficiary' => 'ALL', 'variant' => 'FOLDABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            ['sku' => 'HYG-0D45B561', 'name' => 'Shampoo (250ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '250ML', 'target_beneficiary' => 'ALL', 'variant' => 'MILD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-04-30'],
            ['sku' => 'HYG-636C3F05', 'name' => 'Toothpaste (100g)', 'category' => 'HYGIENE', 'unit_type' => 'Tube', 'size_weight' => '100G', 'target_beneficiary' => 'ALL', 'variant' => 'FLUORIDE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-05-31'],
            ['sku' => 'HYG-0D1A38B0', 'name' => 'Wide-Tooth Combs', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-922A53EF', 'name' => 'Nail Clippers', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-7BD0CA22', 'name' => 'Wash Buckets (20L)', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-37B992E3', 'name' => 'Washcloths', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-62298FEC', 'name' => 'Laundry Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'BAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2029-01-31'],
            ['sku' => 'HYG-15EB17E8', 'name' => 'Disposable Diapers (Medium)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'M', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-6561C010', 'name' => 'Disposable Diapers (Large)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'L', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-F428DE0F', 'name' => 'Baby Wipes', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => '80CT', 'target_beneficiary' => 'CHILD', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-07-31'],
            ['sku' => 'HYG-1F751F13', 'name' => 'Insect Repellent (100ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '100ML', 'target_beneficiary' => 'ALL', 'variant' => 'SPRAY', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-10-31'],
            ['sku' => 'HYG-FF3A566A', 'name' => 'Hair Brushes', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYG-77C72A27', 'name' => 'Surface Disinfectant (1L)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '1L', 'target_beneficiary' => 'ALL', 'variant' => 'LIQUID', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-03-31'],
            ['sku' => 'SHE-2ECE006A', 'name' => 'Large Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => '6P', 'target_beneficiary' => 'ALL', 'variant' => 'FAMILY', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-9A39CD36', 'name' => 'Plastic Sheeting Roll', 'category' => 'SHELTER', 'unit_type' => 'Roll', 'size_weight' => '4X25M', 'target_beneficiary' => 'ALL', 'variant' => 'CLEAR', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-5127FFEC', 'name' => 'Sleeping Pillows', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-C3342C91', 'name' => 'Bed Rolls', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-EB212A65', 'name' => 'Ground Sheets (2x3m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '2X3M', 'target_beneficiary' => 'ALL', 'variant' => 'WATERPROOF', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-B286E96C', 'name' => 'Tent Stake Sets', 'category' => 'SHELTER', 'unit_type' => 'Set', 'size_weight' => '12PCS', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHE-6DA41146', 'name' => 'Tent Hammers', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CLAW', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHE-1E83F529', 'name' => 'Hand Axes', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CAMP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHE-2E9E7EAA', 'name' => 'Utility Brooms', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-72965DD1', 'name' => 'Water Containers (20L)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'STACKABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHE-E4027356', 'name' => 'Canvas Repair Kits', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'PATCH', 'type' => 'consumable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHE-398FB17B', 'name' => 'Raincoats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'REUSABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COM-50B37DBE', 'name' => 'Base Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'DESKTOP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-14A2E3C4', 'name' => 'Headlamps', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ADULT', 'variant' => 'USB', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-E768E7E8', 'name' => 'LED Torches', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'HANDHELD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-F17FAB5D', 'name' => 'AAA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AAA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-02-28'],
            ['sku' => 'COM-1EEE4EB1', 'name' => '9V Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '9V', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-03-31'],
            ['sku' => 'COM-BBAA1259', 'name' => 'Charging Cable Sets', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '3IN1', 'target_beneficiary' => 'ALL', 'variant' => 'USB', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COM-B6ED0B30', 'name' => 'Power Strips (6-port)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '6PORT', 'target_beneficiary' => 'ALL', 'variant' => 'SURGE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-07405809', 'name' => 'Emergency SIM Cards', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '4G', 'target_beneficiary' => 'ALL', 'variant' => 'PREPAID', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COM-B5F4FA79', 'name' => 'Radio Antennas', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'FLEX', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-336D0C01', 'name' => 'Portable Sirens', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '12V', 'target_beneficiary' => 'ADULT', 'variant' => 'ALARM', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-4F3B2566', 'name' => 'Memory Cards (64GB)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '64GB', 'target_beneficiary' => 'ALL', 'variant' => 'MICROSD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-66B48D11', 'name' => 'Solar Lanterns', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'SOLAR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-E6DF404D', 'name' => 'USB Charging Hubs', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '4PORT', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COM-5CCE88DE', 'name' => 'Extension Cables (10m)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHE-E42909D0', 'name' => 'Rechargeable Bullhorns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
        ];

        foreach ($items as $itemData) {
            Item::firstOrCreate(
                ['sku' => $itemData['sku']],
                $itemData
            );
        }

        // ─── INVENTORY TABLE (for FK relationships) ──────────
        $inventoryRecords = [
            ['name' => 'Rice (50kg)',            'category' => 'FOOD',    'sku' => 'FOO-669FB372',      'type' => 'Consumable',  'quantity' => 152, 'expiration' => '2026-12-31', 'storage_location' => 'Warehouse A'],
            ['name' => 'Medical Kit (Small)',     'category' => 'MEDICAL', 'sku' => 'MED-381AB4AC',    'type' => 'Consumable',  'quantity' => 8,   'expiration' => '2026-08-01', 'storage_location' => 'Warehouse B'],
            ['name' => 'N95 Masks',              'category' => 'MEDICAL', 'sku' => 'MED-C09F0F76', 'type' => 'Consumable',  'quantity' => 1240,'expiration' => '2026-07-15', 'storage_location' => 'Warehouse B'],
            ['name' => 'Life Vest (Medium)',     'category' => 'RESCUE',  'sku' => 'RES-EEB70A7B', 'type' => 'Returnable',  'quantity' => 12,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
            ['name' => 'Instant Noodles',        'category' => 'FOOD',    'sku' => 'FOO-EF61C25B',    'type' => 'Consumable',  'quantity' => 2340,'expiration' => '2026-07-20', 'storage_location' => 'Warehouse A'],
            ['name' => 'Blankets',               'category' => 'RELIEF',  'sku' => 'REL-02B4AA7D',    'type' => 'Returnable',  'quantity' => 6,   'expiration' => null,          'storage_location' => 'Warehouse C'],
            ['name' => 'Water (500ml)',          'category' => 'FOOD',    'sku' => 'FOO-0D7FC9D8',     'type' => 'Consumable',  'quantity' => 3600,'expiration' => '2026-07-10', 'storage_location' => 'Warehouse A'],
            ['name' => 'Rescue Rope (Large)',    'category' => 'RESCUE',  'sku' => 'RES-70BB1FA5',    'type' => 'Returnable',  'quantity' => 24,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
            ['name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'sku' => 'HYG-708C3C3B', 'type' => 'Consumable', 'quantity' => 85, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'sku' => 'HYG-21CA9504', 'type' => 'Consumable', 'quantity' => 620, 'expiration' => '2028-02-28', 'storage_location' => 'Warehouse B'],
            ['name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'sku' => 'HYG-4D9C9AEA', 'type' => 'Consumable', 'quantity' => 310, 'expiration' => '2028-06-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'sku' => 'HYG-17EE16D7', 'type' => 'Consumable', 'quantity' => 180, 'expiration' => '2027-11-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'sku' => 'HYG-58202971', 'type' => 'Consumable', 'quantity' => 260, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'sku' => 'HYG-B6204A15', 'type' => 'Returnable', 'quantity' => 75, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'sku' => 'HYG-0743FBD4', 'type' => 'Consumable', 'quantity' => 140, 'expiration' => '2028-09-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Family Relief Tent', 'category' => 'SHELTER', 'sku' => 'SHE-936D1922', 'type' => 'Returnable', 'quantity' => 18, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'sku' => 'SHE-62656045', 'type' => 'Returnable', 'quantity' => 42, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Sleeping Mats', 'category' => 'SHELTER', 'sku' => 'SHE-6F6A9D13', 'type' => 'Returnable', 'quantity' => 65, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Mosquito Nets', 'category' => 'SHELTER', 'sku' => 'SHE-58D97A4F', 'type' => 'Returnable', 'quantity' => 50, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'sku' => 'SHE-A2AEEF7D', 'type' => 'Returnable', 'quantity' => 32, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'sku' => 'SHE-F201A6BC', 'type' => 'Returnable', 'quantity' => 28, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'sku' => 'SHE-6C0B55C4', 'type' => 'Returnable', 'quantity' => 14, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-A97C8E4A', 'type' => 'Returnable', 'quantity' => 16, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-5D8FA03D', 'type' => 'Returnable', 'quantity' => 24, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-F660ABC6', 'type' => 'Returnable', 'quantity' => 10, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-A6F3EBAA', 'type' => 'Returnable', 'quantity' => 90, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-0D9B3764', 'type' => 'Consumable', 'quantity' => 120, 'expiration' => '2031-01-31', 'storage_location' => 'Warehouse C'],
            ['name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'sku' => 'COM-B29104B8', 'type' => 'Returnable', 'quantity' => 12, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
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
