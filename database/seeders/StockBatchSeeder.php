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
            'FOO-669FB372'      => ['quantity' => 152, 'supplier' => 'DSWD',      'date_received' => '2026-01-10', 'expiration_date' => '2026-12-31'],
            'MED-381AB4AC'    => ['quantity' => 8,   'supplier' => 'Red Cross',  'date_received' => '2026-02-15', 'expiration_date' => '2026-08-01'],
            'MED-C09F0F76' => ['quantity' => 1240,'supplier' => 'DOH',        'date_received' => '2026-01-20', 'expiration_date' => '2026-07-15'],
            'RES-EEB70A7B' => ['quantity' => 12,  'supplier' => 'NDRRMC',     'date_received' => '2026-03-01', 'expiration_date' => null],
            'FOO-EF61C25B'    => ['quantity' => 2340,'supplier' => 'DSWD',       'date_received' => '2026-02-01', 'expiration_date' => '2026-07-20'],
            'REL-02B4AA7D'   => ['quantity' => 6,   'supplier' => 'Red Cross',  'date_received' => '2026-01-05', 'expiration_date' => null],
            'FOO-0D7FC9D8'     => ['quantity' => 3600,'supplier' => 'LGU Cebu',   'date_received' => '2026-03-10', 'expiration_date' => '2026-07-10'],
            'RES-70BB1FA5'     => ['quantity' => 24,  'supplier' => 'NDRRMC',     'date_received' => '2026-02-20', 'expiration_date' => null],
            'HYG-708C3C3B' => ['quantity' => 85, 'supplier' => 'DSWD', 'date_received' => '2026-04-05', 'expiration_date' => null],
            'HYG-21CA9504' => ['quantity' => 620, 'supplier' => 'UNICEF', 'date_received' => '2026-04-10', 'expiration_date' => '2028-02-28'],
            'HYG-4D9C9AEA' => ['quantity' => 310, 'supplier' => 'UNFPA', 'date_received' => '2026-04-12', 'expiration_date' => '2028-06-30'],
            'HYG-17EE16D7' => ['quantity' => 180, 'supplier' => 'DOH', 'date_received' => '2026-04-15', 'expiration_date' => '2027-11-30'],
            'HYG-58202971' => ['quantity' => 260, 'supplier' => 'UNICEF', 'date_received' => '2026-04-18', 'expiration_date' => null],
            'HYG-B6204A15' => ['quantity' => 75, 'supplier' => 'DOH', 'date_received' => '2026-04-20', 'expiration_date' => null],
            'HYG-0743FBD4' => ['quantity' => 140, 'supplier' => 'Red Cross', 'date_received' => '2026-04-22', 'expiration_date' => '2028-09-30'],
            'SHE-936D1922' => ['quantity' => 18, 'supplier' => 'NDRRMC', 'date_received' => '2026-04-25', 'expiration_date' => null],
            'SHE-62656045' => ['quantity' => 42, 'supplier' => 'LGU Cebu', 'date_received' => '2026-04-27', 'expiration_date' => null],
            'SHE-6F6A9D13' => ['quantity' => 65, 'supplier' => 'Red Cross', 'date_received' => '2026-04-29', 'expiration_date' => null],
            'SHE-58D97A4F' => ['quantity' => 50, 'supplier' => 'DSWD', 'date_received' => '2026-05-01', 'expiration_date' => null],
            'SHE-A2AEEF7D' => ['quantity' => 32, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-03', 'expiration_date' => null],
            'SHE-F201A6BC' => ['quantity' => 28, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-05', 'expiration_date' => null],
            'SHE-6C0B55C4' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-07', 'expiration_date' => null],
            'COM-A97C8E4A' => ['quantity' => 16, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-10', 'expiration_date' => null],
            'COM-5D8FA03D' => ['quantity' => 24, 'supplier' => 'Red Cross', 'date_received' => '2026-05-12', 'expiration_date' => null],
            'COM-F660ABC6' => ['quantity' => 10, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-14', 'expiration_date' => null],
            'COM-A6F3EBAA' => ['quantity' => 90, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-16', 'expiration_date' => null],
            'COM-0D9B3764' => ['quantity' => 120, 'supplier' => 'DOH', 'date_received' => '2026-05-18', 'expiration_date' => '2031-01-31'],
            'COM-B29104B8' => ['quantity' => 12, 'supplier' => 'Red Cross', 'date_received' => '2026-05-20', 'expiration_date' => null],
            'HYG-0D45B561' => ['quantity' => 150, 'supplier' => 'UNICEF', 'date_received' => '2026-05-22', 'expiration_date' => '2028-04-30'],
            'HYG-636C3F05' => ['quantity' => 175, 'supplier' => 'UNICEF', 'date_received' => '2026-05-24', 'expiration_date' => '2028-05-31'],
            'HYG-0D1A38B0' => ['quantity' => 120, 'supplier' => 'DSWD', 'date_received' => '2026-05-26', 'expiration_date' => null],
            'HYG-922A53EF' => ['quantity' => 95, 'supplier' => 'Red Cross', 'date_received' => '2026-05-28', 'expiration_date' => null],
            'HYG-7BD0CA22' => ['quantity' => 70, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-30', 'expiration_date' => null],
            'HYG-37B992E3' => ['quantity' => 110, 'supplier' => 'DSWD', 'date_received' => '2026-06-01', 'expiration_date' => null],
            'HYG-62298FEC' => ['quantity' => 240, 'supplier' => 'DSWD', 'date_received' => '2026-06-03', 'expiration_date' => '2029-01-31'],
            'HYG-15EB17E8' => ['quantity' => 80, 'supplier' => 'UNICEF', 'date_received' => '2026-06-05', 'expiration_date' => null],
            'HYG-6561C010' => ['quantity' => 75, 'supplier' => 'UNICEF', 'date_received' => '2026-06-07', 'expiration_date' => null],
            'HYG-F428DE0F' => ['quantity' => 130, 'supplier' => 'UNICEF', 'date_received' => '2026-06-09', 'expiration_date' => '2028-07-31'],
            'HYG-1F751F13' => ['quantity' => 90, 'supplier' => 'DOH', 'date_received' => '2026-06-11', 'expiration_date' => '2028-10-31'],
            'HYG-FF3A566A' => ['quantity' => 100, 'supplier' => 'DSWD', 'date_received' => '2026-06-13', 'expiration_date' => null],
            'HYG-77C72A27' => ['quantity' => 65, 'supplier' => 'DOH', 'date_received' => '2026-06-15', 'expiration_date' => '2028-03-31'],
            'SHE-2ECE006A' => ['quantity' => 10, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-17', 'expiration_date' => null],
            'SHE-9A39CD36' => ['quantity' => 30, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-19', 'expiration_date' => null],
            'SHE-5127FFEC' => ['quantity' => 80, 'supplier' => 'Red Cross', 'date_received' => '2026-06-21', 'expiration_date' => null],
            'SHE-C3342C91' => ['quantity' => 55, 'supplier' => 'DSWD', 'date_received' => '2026-06-23', 'expiration_date' => null],
            'SHE-EB212A65' => ['quantity' => 45, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-25', 'expiration_date' => null],
            'SHE-B286E96C' => ['quantity' => 36, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-27', 'expiration_date' => null],
            'SHE-6DA41146' => ['quantity' => 22, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-29', 'expiration_date' => null],
            'SHE-1E83F529' => ['quantity' => 12, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-01', 'expiration_date' => null],
            'SHE-2E9E7EAA' => ['quantity' => 28, 'supplier' => 'DSWD', 'date_received' => '2026-07-03', 'expiration_date' => null],
            'SHE-72965DD1' => ['quantity' => 60, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-05', 'expiration_date' => null],
            'SHE-E4027356' => ['quantity' => 18, 'supplier' => 'Red Cross', 'date_received' => '2026-07-07', 'expiration_date' => null],
            'SHE-398FB17B' => ['quantity' => 48, 'supplier' => 'DSWD', 'date_received' => '2026-07-09', 'expiration_date' => null],
            'COM-50B37DBE' => ['quantity' => 6, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-11', 'expiration_date' => null],
            'COM-14A2E3C4' => ['quantity' => 35, 'supplier' => 'Red Cross', 'date_received' => '2026-07-13', 'expiration_date' => null],
            'COM-E768E7E8' => ['quantity' => 42, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-15', 'expiration_date' => null],
            'COM-F17FAB5D' => ['quantity' => 85, 'supplier' => 'DOH', 'date_received' => '2026-07-17', 'expiration_date' => '2031-02-28'],
            'COM-1EEE4EB1' => ['quantity' => 40, 'supplier' => 'DOH', 'date_received' => '2026-07-19', 'expiration_date' => '2031-03-31'],
            'COM-BBAA1259' => ['quantity' => 30, 'supplier' => 'Red Cross', 'date_received' => '2026-07-21', 'expiration_date' => null],
            'COM-B6ED0B30' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-23', 'expiration_date' => null],
            'COM-07405809' => ['quantity' => 25, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-25', 'expiration_date' => null],
            'COM-B5F4FA79' => ['quantity' => 15, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-27', 'expiration_date' => null],
            'COM-336D0C01' => ['quantity' => 8, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-29', 'expiration_date' => null],
            'COM-4F3B2566' => ['quantity' => 20, 'supplier' => 'Red Cross', 'date_received' => '2026-07-31', 'expiration_date' => null],
            'COM-66B48D11' => ['quantity' => 26, 'supplier' => 'Red Cross', 'date_received' => '2026-08-02', 'expiration_date' => null],
            'COM-E6DF404D' => ['quantity' => 12, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-04', 'expiration_date' => null],
            'COM-5CCE88DE' => ['quantity' => 18, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-06', 'expiration_date' => null],
            'SHE-E42909D0' => ['quantity' => 9, 'supplier' => 'NDRRMC', 'date_received' => '2026-08-08', 'expiration_date' => null],
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
