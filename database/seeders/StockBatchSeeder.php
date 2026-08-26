<?php

namespace Database\Seeders;

use App\Models\Item;
use App\Models\StockBatch;
use Illuminate\Database\Seeder;

class StockBatchSeeder extends Seeder
{
    public function run(): void
    {
        $stocks = [
            'FOOD-RICE-SACK-50KG-ALL'      => ['quantity' => 152, 'supplier' => 'DSWD',      'date_received' => '2026-01-10', 'expiration_date' => '2026-12-31'],
            'MEDICAL-MEDKIT-BOX-SM-ALL'    => ['quantity' => 8,   'supplier' => 'Red Cross',  'date_received' => '2026-02-15', 'expiration_date' => '2026-08-01'],
            'MEDICAL-N95MASK-PACK-REG-ALL' => ['quantity' => 1240,'supplier' => 'DOH',        'date_received' => '2026-01-20', 'expiration_date' => '2026-07-15'],
            'RESCUE-LIFEVEST-PCS-MD-ADULT' => ['quantity' => 12,  'supplier' => 'NDRRMC',     'date_received' => '2026-03-01', 'expiration_date' => null],
            'FOOD-NOODLES-PACK-REG-ALL'    => ['quantity' => 2340,'supplier' => 'DSWD',       'date_received' => '2026-02-01', 'expiration_date' => '2026-07-20'],
            'RELIEF-BLANKET-PCS-REG-ALL'   => ['quantity' => 6,   'supplier' => 'Red Cross',  'date_received' => '2026-01-05', 'expiration_date' => null],
            'FOOD-WATER-BTL-500ML-ALL'     => ['quantity' => 3600,'supplier' => 'LGU Cebu',   'date_received' => '2026-03-10', 'expiration_date' => '2026-07-10'],
            'RESCUE-ROPE-PCS-LG-ADULT'     => ['quantity' => 24,  'supplier' => 'NDRRMC',     'date_received' => '2026-02-20', 'expiration_date' => null],
            'HYGIENE-KIT-BAG-FAM-ALL' => ['quantity' => 85, 'supplier' => 'DSWD', 'date_received' => '2026-04-05', 'expiration_date' => null],
            'HYGIENE-SOAP-PCS-REG-ALL' => ['quantity' => 620, 'supplier' => 'UNICEF', 'date_received' => '2026-04-10', 'expiration_date' => '2028-02-28'],
            'HYGIENE-PADS-PACK-REG-WOMEN' => ['quantity' => 310, 'supplier' => 'UNFPA', 'date_received' => '2026-04-12', 'expiration_date' => '2028-06-30'],
            'HYGIENE-SANITIZER-BTL-500ML-ALL' => ['quantity' => 180, 'supplier' => 'DOH', 'date_received' => '2026-04-15', 'expiration_date' => '2027-11-30'],
            'HYGIENE-TOOTHBRUSH-KIT-PCS-ALL' => ['quantity' => 260, 'supplier' => 'UNICEF', 'date_received' => '2026-04-18', 'expiration_date' => null],
            'HYGIENE-MASK-PACK-REG-ALL' => ['quantity' => 75, 'supplier' => 'DOH', 'date_received' => '2026-04-20', 'expiration_date' => null],
            'HYGIENE-PURIFICATION-TAB-BOX-ALL' => ['quantity' => 140, 'supplier' => 'Red Cross', 'date_received' => '2026-04-22', 'expiration_date' => '2028-09-30'],
            'SHELTER-TENT-UNIT-FAM-ALL' => ['quantity' => 18, 'supplier' => 'NDRRMC', 'date_received' => '2026-04-25', 'expiration_date' => null],
            'SHELTER-TARP-PCS-3X4M-ALL' => ['quantity' => 42, 'supplier' => 'LGU Cebu', 'date_received' => '2026-04-27', 'expiration_date' => null],
            'SHELTER-MAT-ROLL-REG-ALL' => ['quantity' => 65, 'supplier' => 'Red Cross', 'date_received' => '2026-04-29', 'expiration_date' => null],
            'SHELTER-MOSQUITO-NET-PCS-REG-ALL' => ['quantity' => 50, 'supplier' => 'DSWD', 'date_received' => '2026-05-01', 'expiration_date' => null],
            'SHELTER-LANTERN-PCS-LED-ALL' => ['quantity' => 32, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-03', 'expiration_date' => null],
            'SHELTER-ROPE-COIL-10M-ALL' => ['quantity' => 28, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-05', 'expiration_date' => null],
            'SHELTER-TOOLKIT-BOX-REG-ADULT' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-07', 'expiration_date' => null],
            'COMM-RADIO-PCS-VHF-ADULT' => ['quantity' => 16, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-10', 'expiration_date' => null],
            'COMM-POWERBANK-PCS-10000MAH-ALL' => ['quantity' => 24, 'supplier' => 'Red Cross', 'date_received' => '2026-05-12', 'expiration_date' => null],
            'COMM-MEGAPHONE-PCS-REG-ADULT' => ['quantity' => 10, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-14', 'expiration_date' => null],
            'COMM-WHISTLE-PCS-REG-ALL' => ['quantity' => 90, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-16', 'expiration_date' => null],
            'COMM-BATTERY-PACK-AA-ALL' => ['quantity' => 120, 'supplier' => 'DOH', 'date_received' => '2026-05-18', 'expiration_date' => '2031-01-31'],
            'COMM-SOLAR-CHARGER-PCS-20W-ALL' => ['quantity' => 12, 'supplier' => 'Red Cross', 'date_received' => '2026-05-20', 'expiration_date' => null],
            'HYGIENE-SHAMPOO-BTL-250ML-ALL' => ['quantity' => 150, 'supplier' => 'UNICEF', 'date_received' => '2026-05-22', 'expiration_date' => '2028-04-30'],
            'HYGIENE-TOOTHPASTE-TUBE-100G-ALL' => ['quantity' => 175, 'supplier' => 'UNICEF', 'date_received' => '2026-05-24', 'expiration_date' => '2028-05-31'],
            'HYGIENE-COMB-PCS-REG-ALL' => ['quantity' => 120, 'supplier' => 'DSWD', 'date_received' => '2026-05-26', 'expiration_date' => null],
            'HYGIENE-NAILCLIPPER-PCS-REG-ALL' => ['quantity' => 95, 'supplier' => 'Red Cross', 'date_received' => '2026-05-28', 'expiration_date' => null],
            'HYGIENE-BUCKET-PCS-20L-ALL' => ['quantity' => 70, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-30', 'expiration_date' => null],
            'HYGIENE-WASHCLOTH-PACK-REG-ALL' => ['quantity' => 110, 'supplier' => 'DSWD', 'date_received' => '2026-06-01', 'expiration_date' => null],
            'HYGIENE-LAUNDRY-SOAP-PCS-REG-ALL' => ['quantity' => 240, 'supplier' => 'DSWD', 'date_received' => '2026-06-03', 'expiration_date' => '2029-01-31'],
            'HYGIENE-DIAPER-PACK-M-CHILD' => ['quantity' => 80, 'supplier' => 'UNICEF', 'date_received' => '2026-06-05', 'expiration_date' => null],
            'HYGIENE-DIAPER-PACK-L-CHILD' => ['quantity' => 75, 'supplier' => 'UNICEF', 'date_received' => '2026-06-07', 'expiration_date' => null],
            'HYGIENE-BABYWIPES-PACK-ALL' => ['quantity' => 130, 'supplier' => 'UNICEF', 'date_received' => '2026-06-09', 'expiration_date' => '2028-07-31'],
            'HYGIENE-REPELLENT-BTL-100ML-ALL' => ['quantity' => 90, 'supplier' => 'DOH', 'date_received' => '2026-06-11', 'expiration_date' => '2028-10-31'],
            'HYGIENE-HAIRBRUSH-PCS-REG-ALL' => ['quantity' => 100, 'supplier' => 'DSWD', 'date_received' => '2026-06-13', 'expiration_date' => null],
            'HYGIENE-DISINFECTANT-BTL-1L-ALL' => ['quantity' => 65, 'supplier' => 'DOH', 'date_received' => '2026-06-15', 'expiration_date' => '2028-03-31'],
            'SHELTER-TENT-UNIT-6P-ALL' => ['quantity' => 10, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-17', 'expiration_date' => null],
            'SHELTER-PLASTIC-SHEET-ROLL-ALL' => ['quantity' => 30, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-19', 'expiration_date' => null],
            'SHELTER-PILLOW-PCS-REG-ALL' => ['quantity' => 80, 'supplier' => 'Red Cross', 'date_received' => '2026-06-21', 'expiration_date' => null],
            'SHELTER-BEDROLL-PCS-REG-ALL' => ['quantity' => 55, 'supplier' => 'DSWD', 'date_received' => '2026-06-23', 'expiration_date' => null],
            'SHELTER-GROUNDSHEET-PCS-2X3M-ALL' => ['quantity' => 45, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-25', 'expiration_date' => null],
            'SHELTER-TENTSTAKE-SET-ALL' => ['quantity' => 36, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-27', 'expiration_date' => null],
            'SHELTER-HAMMER-PCS-REG-ADULT' => ['quantity' => 22, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-29', 'expiration_date' => null],
            'SHELTER-AXE-PCS-REG-ADULT' => ['quantity' => 12, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-01', 'expiration_date' => null],
            'SHELTER-BROOM-PCS-REG-ALL' => ['quantity' => 28, 'supplier' => 'DSWD', 'date_received' => '2026-07-03', 'expiration_date' => null],
            'SHELTER-WATERCONTAINER-PCS-20L-ALL' => ['quantity' => 60, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-05', 'expiration_date' => null],
            'SHELTER-CANVAS-REPAIR-KIT-BOX-ADULT' => ['quantity' => 18, 'supplier' => 'Red Cross', 'date_received' => '2026-07-07', 'expiration_date' => null],
            'SHELTER-RAINCOAT-PCS-REG-ALL' => ['quantity' => 48, 'supplier' => 'DSWD', 'date_received' => '2026-07-09', 'expiration_date' => null],
            'COMM-BASE-RADIO-PCS-VHF-ADULT' => ['quantity' => 6, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-11', 'expiration_date' => null],
            'COMM-HEADLAMP-PCS-LED-ADULT' => ['quantity' => 35, 'supplier' => 'Red Cross', 'date_received' => '2026-07-13', 'expiration_date' => null],
            'COMM-TORCH-PCS-LED-ALL' => ['quantity' => 42, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-15', 'expiration_date' => null],
            'COMM-BATTERY-PACK-AAA-ALL' => ['quantity' => 85, 'supplier' => 'DOH', 'date_received' => '2026-07-17', 'expiration_date' => '2031-02-28'],
            'COMM-BATTERY-PCS-9V-ALL' => ['quantity' => 40, 'supplier' => 'DOH', 'date_received' => '2026-07-19', 'expiration_date' => '2031-03-31'],
            'COMM-CHARGING-CABLE-PACK-ALL' => ['quantity' => 30, 'supplier' => 'Red Cross', 'date_received' => '2026-07-21', 'expiration_date' => null],
            'COMM-POWERSTRIP-PCS-6PORT-ALL' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-23', 'expiration_date' => null],
            'COMM-SIMCARD-PACK-4G-ALL' => ['quantity' => 25, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-25', 'expiration_date' => null],
            'COMM-ANTENNA-PCS-VHF-ADULT' => ['quantity' => 15, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-27', 'expiration_date' => null],
            'COMM-SIREN-PCS-12V-ADULT' => ['quantity' => 8, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-29', 'expiration_date' => null],
            'COMM-MEMORYCARD-PCS-64GB-ALL' => ['quantity' => 20, 'supplier' => 'Red Cross', 'date_received' => '2026-07-31', 'expiration_date' => null],
            'COMM-SOLAR-LANTERN-PCS-LED-ALL' => ['quantity' => 26, 'supplier' => 'Red Cross', 'date_received' => '2026-08-02', 'expiration_date' => null],
            'COMM-USB-HUB-PCS-4PORT-ALL' => ['quantity' => 12, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-04', 'expiration_date' => null],
            'COMM-EXTENSION-CABLE-PCS-10M-ALL' => ['quantity' => 18, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-06', 'expiration_date' => null],
            'SHELTER-BULLHORN-PCS-REG-ADULT' => ['quantity' => 9, 'supplier' => 'NDRRMC', 'date_received' => '2026-08-08', 'expiration_date' => null],
        ];

        foreach ($stocks as $sku => $data) {
            $item = Item::where('sku', $sku)->first();
            if ($item) {
                StockBatch::firstOrCreate(
                    ['item_id' => $item->id, 'date_received' => $data['date_received']],
                    [
                        'quantity'        => $data['quantity'],
                        'supplier'        => $data['supplier'],
                        'expiration_date' => $data['expiration_date'],
                    ]
                );
            }
        }
    }
}
