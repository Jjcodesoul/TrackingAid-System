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
                'sku'                => 'FOORICE50KGCST01',
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
                'sku'                => 'MEDMEDKISMCST01',
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
                'sku'                => 'MEDN95MAREGCST01',
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
                'sku'                => 'RESLIFVEMDRST01',
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
                'sku'                => 'FOOINSNOREGCST01',
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
                'sku'                => 'RELBLANREGRST01',
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
                'sku'                => 'FOOWATE500MLCST01',
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
                'sku'                => 'RESRESROLGRST01',
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
                'sku' => 'HYGFAMHYFAMCST01', 'name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'unit_type' => 'Bag', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGBATSOREGCUN01', 'name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-02-28',
            ],
            [
                'sku' => 'HYGSANPAREGCRE01', 'name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'WOMEN', 'variant' => 'REGULAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-06-30',
            ],
            [
                'sku' => 'HYGHANSA500MLC7001', 'name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '500ML', 'target_beneficiary' => 'ALL', 'variant' => '70PCT', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2027-11-30',
            ],
            [
                'sku' => 'HYGTOOKIREGCST01', 'name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STANDARD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGREUFAREGRWA01', 'name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'WASHABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null,
            ],
            [
                'sku' => 'HYGWATPU50TABCCH01', 'name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'unit_type' => 'Box', 'size_weight' => '50TAB', 'target_beneficiary' => 'ALL', 'variant' => 'CHLORINE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-09-30',
            ],
            [
                'sku' => 'SHEFAMREFAMR4P01', 'name' => 'Family Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => 'FAM', 'target_beneficiary' => 'ALL', 'variant' => '4PERSON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHETARSH3X4MRBL01', 'name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '3X4M', 'target_beneficiary' => 'ALL', 'variant' => 'BLUE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHESLEMAREGRFO01', 'name' => 'Sleeping Mats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHEMOSNEREGRHA01', 'name' => 'Mosquito Nets', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'HANGING', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHEEMELALEDRRE01', 'name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHEUTIRO10MRNY01', 'name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'unit_type' => 'Coil', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'NYLON', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'SHESHEREREGRST01', 'name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'STANDARD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMHANRAVHFRTW01', 'name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'TWO-WAY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMEMEPO10000RUS01', 'name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10000MAH', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMMEGAREGRBA01', 'name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'BATTERY', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMEMEWHREGRPE01', 'name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PEALESS', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            [
                'sku' => 'COMAABAAACAL01', 'name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-01-31',
            ],
            [
                'sku' => 'COMPORSO20WRFO01', 'name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '20W', 'target_beneficiary' => 'ALL', 'variant' => 'FOLDABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null,
            ],
            ['sku' => 'HYGSHAM250MLCMI01', 'name' => 'Shampoo (250ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '250ML', 'target_beneficiary' => 'ALL', 'variant' => 'MILD', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-04-30'],
            ['sku' => 'HYGTOOT100GCFL01', 'name' => 'Toothpaste (100g)', 'category' => 'HYGIENE', 'unit_type' => 'Tube', 'size_weight' => '100G', 'target_beneficiary' => 'ALL', 'variant' => 'FLUORIDE', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-05-31'],
            ['sku' => 'HYGWIDTOREGCPL01', 'name' => 'Wide-Tooth Combs', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGNAICLREGRST01', 'name' => 'Nail Clippers', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGWASBU20LRPL01', 'name' => 'Wash Buckets (20L)', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'returnable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGWASHREGCCO01', 'name' => 'Washcloths', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGLAUSOREGCBA01', 'name' => 'Laundry Soap Bars', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'BAR', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2029-01-31'],
            ['sku' => 'HYGDISDIMCDA01', 'name' => 'Disposable Diapers (Medium)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'M', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGDISDILCDA01', 'name' => 'Disposable Diapers (Large)', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => 'L', 'target_beneficiary' => 'CHILD', 'variant' => 'DAYTIME', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGBABWI80CTCUN01', 'name' => 'Baby Wipes', 'category' => 'HYGIENE', 'unit_type' => 'Pack', 'size_weight' => '80CT', 'target_beneficiary' => 'CHILD', 'variant' => 'UNSCENTED', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-07-31'],
            ['sku' => 'HYGINSRE100MLCSP01', 'name' => 'Insect Repellent (100ml)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '100ML', 'target_beneficiary' => 'ALL', 'variant' => 'SPRAY', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-10-31'],
            ['sku' => 'HYGHAIBRREGCPL01', 'name' => 'Hair Brushes', 'category' => 'HYGIENE', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'PLASTIC', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => null],
            ['sku' => 'HYGSURDI1LCLI01', 'name' => 'Surface Disinfectant (1L)', 'category' => 'HYGIENE', 'unit_type' => 'Bottle', 'size_weight' => '1L', 'target_beneficiary' => 'ALL', 'variant' => 'LIQUID', 'type' => 'consumable', 'storage_location' => 'Warehouse B', 'expiration_date' => '2028-03-31'],
            ['sku' => 'SHELARRE6PRFA01', 'name' => 'Large Relief Tent', 'category' => 'SHELTER', 'unit_type' => 'Unit', 'size_weight' => '6P', 'target_beneficiary' => 'ALL', 'variant' => 'FAMILY', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHEPLASH4X25MCCL01', 'name' => 'Plastic Sheeting Roll', 'category' => 'SHELTER', 'unit_type' => 'Roll', 'size_weight' => '4X25M', 'target_beneficiary' => 'ALL', 'variant' => 'CLEAR', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHESLEPIREGRFO01', 'name' => 'Sleeping Pillows', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'FOAM', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHEBEDROREGRCO01', 'name' => 'Bed Rolls', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'COTTON', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHEGROSH2X3MRWA01', 'name' => 'Ground Sheets (2x3m)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '2X3M', 'target_beneficiary' => 'ALL', 'variant' => 'WATERPROOF', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHETENST12PCSRST01', 'name' => 'Tent Stake Sets', 'category' => 'SHELTER', 'unit_type' => 'Set', 'size_weight' => '12PCS', 'target_beneficiary' => 'ALL', 'variant' => 'STEEL', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHETENHAREGRCL01', 'name' => 'Tent Hammers', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CLAW', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHEHANAXREGRCA01', 'name' => 'Hand Axes', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'CAMP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHEUTIBRREGROU01', 'name' => 'Utility Brooms', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHEWATCO20LRST01', 'name' => 'Water Containers (20L)', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => '20L', 'target_beneficiary' => 'ALL', 'variant' => 'STACKABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'SHECANREREGCPA01', 'name' => 'Canvas Repair Kits', 'category' => 'SHELTER', 'unit_type' => 'Box', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'PATCH', 'type' => 'consumable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHERAINREGRRE01', 'name' => 'Raincoats', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ALL', 'variant' => 'REUSABLE', 'type' => 'returnable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMBASRAVHFRDE01', 'name' => 'Base Radios', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'DESKTOP', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMHEADLEDRUS01', 'name' => 'Headlamps', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ADULT', 'variant' => 'USB', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMLEDTOLEDRHA01', 'name' => 'LED Torches', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'HANDHELD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMAAABAAAACAL01', 'name' => 'AAA Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => 'AAA', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-02-28'],
            ['sku' => 'COM9VBA9VCAL01', 'name' => '9V Batteries', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '9V', 'target_beneficiary' => 'ALL', 'variant' => 'ALKALINE', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => '2031-03-31'],
            ['sku' => 'COMCHACA3IN1CUS01', 'name' => 'Charging Cable Sets', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '3IN1', 'target_beneficiary' => 'ALL', 'variant' => 'USB', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMPOWST6PORTRSU01', 'name' => 'Power Strips (6-port)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '6PORT', 'target_beneficiary' => 'ALL', 'variant' => 'SURGE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMEMESI4GCPR01', 'name' => 'Emergency SIM Cards', 'category' => 'COMMUNICATIONS', 'unit_type' => 'Pack', 'size_weight' => '4G', 'target_beneficiary' => 'ALL', 'variant' => 'PREPAID', 'type' => 'consumable', 'storage_location' => 'Warehouse C', 'expiration_date' => null],
            ['sku' => 'COMRADANVHFRFL01', 'name' => 'Radio Antennas', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'VHF', 'target_beneficiary' => 'ADULT', 'variant' => 'FLEX', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMPORSI12VRAL01', 'name' => 'Portable Sirens', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '12V', 'target_beneficiary' => 'ADULT', 'variant' => 'ALARM', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMMEMCA64GBRMI01', 'name' => 'Memory Cards (64GB)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '64GB', 'target_beneficiary' => 'ALL', 'variant' => 'MICROSD', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMSOLLALEDRSO01', 'name' => 'Solar Lanterns', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => 'LED', 'target_beneficiary' => 'ALL', 'variant' => 'SOLAR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMUSBCH4PORTRUS01', 'name' => 'USB Charging Hubs', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '4PORT', 'target_beneficiary' => 'ALL', 'variant' => 'USB-C', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'COMEXTCA10MROU01', 'name' => 'Extension Cables (10m)', 'category' => 'COMMUNICATIONS', 'unit_type' => 'PCS', 'size_weight' => '10M', 'target_beneficiary' => 'ALL', 'variant' => 'OUTDOOR', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
            ['sku' => 'SHERECBUREGRRE01', 'name' => 'Rechargeable Bullhorns', 'category' => 'SHELTER', 'unit_type' => 'PCS', 'size_weight' => 'REG', 'target_beneficiary' => 'ADULT', 'variant' => 'RECHARGEABLE', 'type' => 'returnable', 'storage_location' => 'Equipment Bay', 'expiration_date' => null],
        ];

        foreach ($items as $itemData) {
            Item::firstOrCreate(
                ['sku' => $itemData['sku']],
                $itemData
            );
        }

        // ─── INVENTORY TABLE (for FK relationships) ──────────
        $inventoryRecords = [
            ['name' => 'Rice (50kg)',            'category' => 'FOOD',    'sku' => 'FOORICE50KGCST01',      'type' => 'Consumable',  'quantity' => 152, 'expiration' => '2026-12-31', 'storage_location' => 'Warehouse A'],
            ['name' => 'Medical Kit (Small)',     'category' => 'MEDICAL', 'sku' => 'MEDMEDKISMCST01',    'type' => 'Consumable',  'quantity' => 8,   'expiration' => '2026-08-01', 'storage_location' => 'Warehouse B'],
            ['name' => 'N95 Masks',              'category' => 'MEDICAL', 'sku' => 'MEDN95MAREGCST01', 'type' => 'Consumable',  'quantity' => 1240,'expiration' => '2026-07-15', 'storage_location' => 'Warehouse B'],
            ['name' => 'Life Vest (Medium)',     'category' => 'RESCUE',  'sku' => 'RESLIFVEMDRST01', 'type' => 'Returnable',  'quantity' => 12,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
            ['name' => 'Instant Noodles',        'category' => 'FOOD',    'sku' => 'FOOINSNOREGCST01',    'type' => 'Consumable',  'quantity' => 2340,'expiration' => '2026-07-20', 'storage_location' => 'Warehouse A'],
            ['name' => 'Blankets',               'category' => 'RELIEF',  'sku' => 'RELBLANREGRST01',    'type' => 'Returnable',  'quantity' => 6,   'expiration' => null,          'storage_location' => 'Warehouse C'],
            ['name' => 'Water (500ml)',          'category' => 'FOOD',    'sku' => 'FOOWATE500MLCST01',     'type' => 'Consumable',  'quantity' => 3600,'expiration' => '2026-07-10', 'storage_location' => 'Warehouse A'],
            ['name' => 'Rescue Rope (Large)',    'category' => 'RESCUE',  'sku' => 'RESRESROLGRST01',    'type' => 'Returnable',  'quantity' => 24,  'expiration' => null,          'storage_location' => 'Equipment Bay'],
            ['name' => 'Family Hygiene Kit', 'category' => 'HYGIENE', 'sku' => 'HYGFAMHYFAMCST01', 'type' => 'Consumable', 'quantity' => 85, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Bath Soap Bars', 'category' => 'HYGIENE', 'sku' => 'HYGBATSOREGCUN01', 'type' => 'Consumable', 'quantity' => 620, 'expiration' => '2028-02-28', 'storage_location' => 'Warehouse B'],
            ['name' => 'Sanitary Pads', 'category' => 'HYGIENE', 'sku' => 'HYGSANPAREGCRE01', 'type' => 'Consumable', 'quantity' => 310, 'expiration' => '2028-06-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Hand Sanitizer (500ml)', 'category' => 'HYGIENE', 'sku' => 'HYGHANSA500MLC7001', 'type' => 'Consumable', 'quantity' => 180, 'expiration' => '2027-11-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Toothbrush Kits', 'category' => 'HYGIENE', 'sku' => 'HYGTOOKIREGCST01', 'type' => 'Consumable', 'quantity' => 260, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Reusable Face Masks', 'category' => 'HYGIENE', 'sku' => 'HYGREUFAREGRWA01', 'type' => 'Returnable', 'quantity' => 75, 'expiration' => null, 'storage_location' => 'Warehouse B'],
            ['name' => 'Water Purification Tablets', 'category' => 'HYGIENE', 'sku' => 'HYGWATPU50TABCCH01', 'type' => 'Consumable', 'quantity' => 140, 'expiration' => '2028-09-30', 'storage_location' => 'Warehouse B'],
            ['name' => 'Family Relief Tent', 'category' => 'SHELTER', 'sku' => 'SHEFAMREFAMR4P01', 'type' => 'Returnable', 'quantity' => 18, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Tarpaulin Sheet (3x4m)', 'category' => 'SHELTER', 'sku' => 'SHETARSH3X4MRBL01', 'type' => 'Returnable', 'quantity' => 42, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Sleeping Mats', 'category' => 'SHELTER', 'sku' => 'SHESLEMAREGRFO01', 'type' => 'Returnable', 'quantity' => 65, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Mosquito Nets', 'category' => 'SHELTER', 'sku' => 'SHEMOSNEREGRHA01', 'type' => 'Returnable', 'quantity' => 50, 'expiration' => null, 'storage_location' => 'Warehouse C'],
            ['name' => 'Emergency Lanterns', 'category' => 'SHELTER', 'sku' => 'SHEEMELALEDRRE01', 'type' => 'Returnable', 'quantity' => 32, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Utility Rope (10m)', 'category' => 'SHELTER', 'sku' => 'SHEUTIRO10MRNY01', 'type' => 'Returnable', 'quantity' => 28, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Shelter Repair Toolkit', 'category' => 'SHELTER', 'sku' => 'SHESHEREREGRST01', 'type' => 'Returnable', 'quantity' => 14, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Handheld Radios', 'category' => 'COMMUNICATIONS', 'sku' => 'COMHANRAVHFRTW01', 'type' => 'Returnable', 'quantity' => 16, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Power Banks', 'category' => 'COMMUNICATIONS', 'sku' => 'COMEMEPO10000RUS01', 'type' => 'Returnable', 'quantity' => 24, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Megaphones', 'category' => 'COMMUNICATIONS', 'sku' => 'COMMEGAREGRBA01', 'type' => 'Returnable', 'quantity' => 10, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'Emergency Whistles', 'category' => 'COMMUNICATIONS', 'sku' => 'COMEMEWHREGRPE01', 'type' => 'Returnable', 'quantity' => 90, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
            ['name' => 'AA Batteries', 'category' => 'COMMUNICATIONS', 'sku' => 'COMAABAAACAL01', 'type' => 'Consumable', 'quantity' => 120, 'expiration' => '2031-01-31', 'storage_location' => 'Warehouse C'],
            ['name' => 'Portable Solar Chargers', 'category' => 'COMMUNICATIONS', 'sku' => 'COMPORSO20WRFO01', 'type' => 'Returnable', 'quantity' => 12, 'expiration' => null, 'storage_location' => 'Equipment Bay'],
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
