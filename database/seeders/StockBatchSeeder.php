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
