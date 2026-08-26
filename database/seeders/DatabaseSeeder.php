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
            [
                'sku' => 'HYGIENE-KIT-BAG-FAM-ALL', 'name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'unit_type' => 'Bag', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGIENE-SOAP-PCS-REG-ALL', 'name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-02-28',
            ],
            [
                'sku' => 'HYGIENE-PADS-PACK-REG-WOMEN', 'name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'WOMEN', 'variant' => 'REGULAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-06-30',
            ],
            [
                'sku' => 'HYGIENE-SANITIZER-BTL-500ML-ALL', 'name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '500ML', 'target_beneficiary' => 'ALL', 'variant' => '70PCT', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2027-11-30',
            ],
            [
                'sku' => 'HYGIENE-TOOTHBRUSH-KIT-PCS-ALL', 'name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGIENE-MASK-PACK-REG-ALL', 'name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'WASHABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGIENE-PURIFICATION-TAB-BOX-ALL', 'name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'unit_type' => 'Box', 'size_weight' => '50TAB', 'target_beneficiary' => 'ALL', 'variant' => 'CHLORINE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-09-30',
            ],
            [
                'sku' => 'SHELTER-TENT-UNIT-FAM-ALL', 'name' => 'Family Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => '4PERSON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-TARP-PCS-3X4M-ALL', 'name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '3X4M', 'target_beneficiary' => 'ALL', 'variant' => 'BLUE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-MAT-ROLL-REG-ALL', 'name' => 'Sleeping Mats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-MOSQUITO-NET-PCS-REG-ALL', 'name' => 'Mosquito Nets', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'HANGING', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-LANTERN-PCS-LED-ALL', 'name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-ROPE-COIL-10M-ALL', 'name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'unit_type' => 'Coil', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'NYLON', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHELTER-TOOLKIT-BOX-REG-ADULT', 'name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'STANDARD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMM-RADIO-PCS-VHF-ADULT', 'name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'TWO-WAY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMM-POWERBANK-PCS-10000MAH-ALL', 'name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10000MAH', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMM-MEGAPHONE-PCS-REG-ADULT', 'name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'BATTERY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMM-WHISTLE-PCS-REG-ALL', 'name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PEALESS', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMM-BATTERY-PACK-AA-ALL', 'name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-01-31',
            ],
            [
                'sku' => 'COMM-SOLAR-CHARGER-PCS-20W-ALL', 'name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '20W', 'target_beneficiary' => 'ALL', 'variant' => 'FOLDABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            ['sku' => 'HYGIENE-SHAMPOO-BTL-250ML-ALL', 'name' => 'Shampoo (250ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '250ML', 'target_beneficiary' => 'ALL', 'variant' => 'MILD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-04-30'],
            ['sku' => 'HYGIENE-TOOTHPASTE-TUBE-100G-ALL', 'name' => 'Toothpaste (100g)', 'category' => 'HYGIENE', 'unit_type' => 'Tube', 'size_weight' => '100G', 'target_beneficiary' => 'ALL', 'variant' => 'FLUORIDE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-05-31'],
            ['sku' => 'HYGIENE-COMB-PCS-REG-ALL', 'name' => 'Wide-Tooth Combs', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-NAILCLIPPER-PCS-REG-ALL', 'name' => 'Nail Clippers', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-BUCKET-PCS-20L-ALL', 'name' => 'Wash Buckets (20L)', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-WASHCLOTH-PACK-REG-ALL', 'name' => 'Washcloths', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-LAUNDRY-SOAP-PCS-REG-ALL', 'name' => 'Laundry Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'BAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2029-01-31'],
            ['sku' => 'HYGIENE-DIAPER-PACK-M-CHILD', 'name' => 'Disposable Diapers (Medium)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'M', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-DIAPER-PACK-L-CHILD', 'name' => 'Disposable Diapers (Large)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'L', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-BABYWIPES-PACK-ALL', 'name' => 'Baby Wipes', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => '80CT', 'target_beneficiary' => 'CHILD', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-07-31'],
            ['sku' => 'HYGIENE-REPELLENT-BTL-100ML-ALL', 'name' => 'Insect Repellent (100ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '100ML', 'target_beneficiary' => 'ALL', 'variant' => 'SPRAY', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-10-31'],
            ['sku' => 'HYGIENE-HAIRBRUSH-PCS-REG-ALL', 'name' => 'Hair Brushes', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGIENE-DISINFECTANT-BTL-1L-ALL', 'name' => 'Surface Disinfectant (1L)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '1L', 'target_beneficiary' => 'ALL', 'variant' => 'LIQUID', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-03-31'],
            ['sku' => 'SHELTER-TENT-UNIT-6P-ALL', 'name' => 'Large Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => '6P', 'target_beneficiary' => 'ALL', 'variant' => 'FAMILY', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-PLASTIC-SHEET-ROLL-ALL', 'name' => 'Plastic Sheeting Roll', 'category' => 'SHELTER', 'unit_type' => 'Roll', 'size_weight' => '4X25M', 'target_beneficiary' => 'ALL', 'variant' => 'CLEAR', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-PILLOW-PCS-REG-ALL', 'name' => 'Sleeping Pillows', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-BEDROLL-PCS-REG-ALL', 'name' => 'Bed Rolls', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-GROUNDSHEET-PCS-2X3M-ALL', 'name' => 'Ground Sheets (2x3m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '2X3M', 'target_beneficiary' => 'ALL', 'variant' => 'WATERPROOF', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-TENTSTAKE-SET-ALL', 'name' => 'Tent Stake Sets', 'category' => 'SHELTER', 'unit_type' => 'Set', 'size_weight' => '12PCS', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHELTER-HAMMER-PCS-REG-ADULT', 'name' => 'Tent Hammers', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CLAW', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHELTER-AXE-PCS-REG-ADULT', 'name' => 'Hand Axes', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CAMP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHELTER-BROOM-PCS-REG-ALL', 'name' => 'Utility Brooms', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-WATERCONTAINER-PCS-20L-ALL', 'name' => 'Water Containers (20L)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'STACKABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHELTER-CANVAS-REPAIR-KIT-BOX-ADULT', 'name' => 'Canvas Repair Kits', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'PATCH', 'type' => 'consumable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHELTER-RAINCOAT-PCS-REG-ALL', 'name' => 'Raincoats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'REUSABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMM-BASE-RADIO-PCS-VHF-ADULT', 'name' => 'Base Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'DESKTOP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-HEADLAMP-PCS-LED-ADULT', 'name' => 'Headlamps', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ADULT', 'variant' => 'USB', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-TORCH-PCS-LED-ALL', 'name' => 'LED Torches', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'HANDHELD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-BATTERY-PACK-AAA-ALL', 'name' => 'AAA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AAA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-02-28'],
            ['sku' => 'COMM-BATTERY-PCS-9V-ALL', 'name' => '9V Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '9V', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-03-31'],
            ['sku' => 'COMM-CHARGING-CABLE-PACK-ALL', 'name' => 'Charging Cable Sets', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '3IN1', 'target_beneficiary' => 'ALL', 'variant' => 'USB', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMM-POWERSTRIP-PCS-6PORT-ALL', 'name' => 'Power Strips (6-port)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '6PORT', 'target_beneficiary' => 'ALL', 'variant' => 'SURGE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-SIMCARD-PACK-4G-ALL', 'name' => 'Emergency SIM Cards', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '4G', 'target_beneficiary' => 'ALL', 'variant' => 'PREPAID', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMM-ANTENNA-PCS-VHF-ADULT', 'name' => 'Radio Antennas', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'FLEX', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-SIREN-PCS-12V-ADULT', 'name' => 'Portable Sirens', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '12V', 'target_beneficiary' => 'ADULT', 'variant' => 'ALARM', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-MEMORYCARD-PCS-64GB-ALL', 'name' => 'Memory Cards (64GB)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '64GB', 'target_beneficiary' => 'ALL', 'variant' => 'MICROSD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-SOLAR-LANTERN-PCS-LED-ALL', 'name' => 'Solar Lanterns', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'SOLAR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-USB-HUB-PCS-4PORT-ALL', 'name' => 'USB Charging Hubs', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '4PORT', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMM-EXTENSION-CABLE-PCS-10M-ALL', 'name' => 'Extension Cables (10m)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHELTER-BULLHORN-PCS-REG-ADULT', 'name' => 'Rechargeable Bullhorns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
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
            ['name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-KIT-BAG-FAM-ALL', 'type' => 'Consumable', 'quantity' => 85, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-SOAP-PCS-REG-ALL', 'type' => 'Consumable', 'quantity' => 620, 'expiration' => '2028-02-28', 'storage_location' => 'Warehouse B'],
            ['name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-PADS-PACK-REG-WOMEN', 'type' => 'Consumable', 'quantity' => 310, 'expiration' => '2028-06-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-SANITIZER-BTL-500ML-ALL', 'type' => 'Consumable', 'quantity' => 180, 'expiration' => '2027-11-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-TOOTHBRUSH-KIT-PCS-ALL', 'type' => 'Consumable', 'quantity' => 260, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-MASK-PACK-REG-ALL', 'type' => 'Returnable', 'quantity' => 75, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'sku' => 'HYGIENE-PURIFICATION-TAB-BOX-ALL', 'type' => 'Consumable', 'quantity' => 140, 'expiration' => '2028-09-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Family Relief Tent', 'category' => 'SHELTER', 'sku' => 'SHELTER-TENT-UNIT-FAM-ALL', 'type' => 'Returnable', 'quantity' => 18, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'sku' => 'SHELTER-TARP-PCS-3X4M-ALL', 'type' => 'Returnable', 'quantity' => 42, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Sleeping Mats', 'category' => 'SHELTER', 'sku' => 'SHELTER-MAT-ROLL-REG-ALL', 'type' => 'Returnable', 'quantity' => 65, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Mosquito Nets', 'category' => 'SHELTER', 'sku' => 'SHELTER-MOSQUITO-NET-PCS-REG-ALL', 'type' => 'Returnable', 'quantity' => 50, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'sku' => 'SHELTER-LANTERN-PCS-LED-ALL', 'type' => 'Returnable', 'quantity' => 32, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'sku' => 'SHELTER-ROPE-COIL-10M-ALL', 'type' => 'Returnable', 'quantity' => 28, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'sku' => 'SHELTER-TOOLKIT-BOX-REG-ADULT', 'type' => 'Returnable', 'quantity' => 14, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-RADIO-PCS-VHF-ADULT', 'type' => 'Returnable', 'quantity' => 16, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-POWERBANK-PCS-10000MAH-ALL', 'type' => 'Returnable', 'quantity' => 24, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-MEGAPHONE-PCS-REG-ADULT', 'type' => 'Returnable', 'quantity' => 10, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-WHISTLE-PCS-REG-ALL', 'type' => 'Returnable', 'quantity' => 90, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-BATTERY-PACK-AA-ALL', 'type' => 'Consumable', 'quantity' => 120, 'expiration' => '2031-01-31', 'storage_location' => 'Warehouse C'],
            ['name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'sku' => 'COMM-SOLAR-CHARGER-PCS-20W-ALL', 'type' => 'Returnable', 'quantity' => 12, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
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
