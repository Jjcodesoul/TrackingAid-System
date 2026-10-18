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
            'FOORICE50KGCST01'      => ['quantity' => 152, 'supplier' => 'DSWD',      'date_received' => '2026-01-10', 'expiration_date' => '2026-12-31'],
            'MEDMEDKISMCST01'    => ['quantity' => 8,   'supplier' => 'Red Cross',  'date_received' => '2026-02-15', 'expiration_date' => '2026-08-01'],
            'MEDN95MAREGCST01' => ['quantity' => 1240,'supplier' => 'DOH',        'date_received' => '2026-01-20', 'expiration_date' => '2026-07-15'],
            'RESLIFVEMDRST01' => ['quantity' => 12,  'supplier' => 'NDRRMC',     'date_received' => '2026-03-01', 'expiration_date' => null],
            'FOOINSNOREGCST01'    => ['quantity' => 2340,'supplier' => 'DSWD',       'date_received' => '2026-02-01', 'expiration_date' => '2026-07-20'],
            'RELBLANREGRST01'   => ['quantity' => 6,   'supplier' => 'Red Cross',  'date_received' => '2026-01-05', 'expiration_date' => null],
            'FOOWATE500MLCST01'     => ['quantity' => 3600,'supplier' => 'LGU Cebu',   'date_received' => '2026-03-10', 'expiration_date' => '2026-07-10'],
            'RESRESROLGRST01'     => ['quantity' => 24,  'supplier' => 'NDRRMC',     'date_received' => '2026-02-20', 'expiration_date' => null],
            'HYGFAMHYFAMCST01' => ['quantity' => 85, 'supplier' => 'DSWD', 'date_received' => '2026-04-05', 'expiration_date' => null],
            'HYGBATSOREGCUN01' => ['quantity' => 620, 'supplier' => 'UNICEF', 'date_received' => '2026-04-10', 'expiration_date' => '2028-02-28'],
            'HYGSANPAREGCRE01' => ['quantity' => 310, 'supplier' => 'UNFPA', 'date_received' => '2026-04-12', 'expiration_date' => '2028-06-30'],
            'HYGHANSA500MLC7001' => ['quantity' => 180, 'supplier' => 'DOH', 'date_received' => '2026-04-15', 'expiration_date' => '2027-11-30'],
            'HYGTOOKIREGCST01' => ['quantity' => 260, 'supplier' => 'UNICEF', 'date_received' => '2026-04-18', 'expiration_date' => null],
            'HYGREUFAREGRWA01' => ['quantity' => 75, 'supplier' => 'DOH', 'date_received' => '2026-04-20', 'expiration_date' => null],
            'HYGWATPU50TABCCH01' => ['quantity' => 140, 'supplier' => 'Red Cross', 'date_received' => '2026-04-22', 'expiration_date' => '2028-09-30'],
            'SHEFAMREFAMR4P01' => ['quantity' => 18, 'supplier' => 'NDRRMC', 'date_received' => '2026-04-25', 'expiration_date' => null],
            'SHETARSH3X4MRBL01' => ['quantity' => 42, 'supplier' => 'LGU Cebu', 'date_received' => '2026-04-27', 'expiration_date' => null],
            'SHESLEMAREGRFO01' => ['quantity' => 65, 'supplier' => 'Red Cross', 'date_received' => '2026-04-29', 'expiration_date' => null],
            'SHEMOSNEREGRHA01' => ['quantity' => 50, 'supplier' => 'DSWD', 'date_received' => '2026-05-01', 'expiration_date' => null],
            'SHEEMELALEDRRE01' => ['quantity' => 32, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-03', 'expiration_date' => null],
            'SHEUTIRO10MRNY01' => ['quantity' => 28, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-05', 'expiration_date' => null],
            'SHESHEREREGRST01' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-07', 'expiration_date' => null],
            'COMHANRAVHFRTW01' => ['quantity' => 16, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-10', 'expiration_date' => null],
            'COMEMEPO10000RUS01' => ['quantity' => 24, 'supplier' => 'Red Cross', 'date_received' => '2026-05-12', 'expiration_date' => null],
            'COMMEGAREGRBA01' => ['quantity' => 10, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-14', 'expiration_date' => null],
            'COMEMEWHREGRPE01' => ['quantity' => 90, 'supplier' => 'NDRRMC', 'date_received' => '2026-05-16', 'expiration_date' => null],
            'COMAABAAACAL01' => ['quantity' => 120, 'supplier' => 'DOH', 'date_received' => '2026-05-18', 'expiration_date' => '2031-01-31'],
            'COMPORSO20WRFO01' => ['quantity' => 12, 'supplier' => 'Red Cross', 'date_received' => '2026-05-20', 'expiration_date' => null],
            'HYGSHAM250MLCMI01' => ['quantity' => 150, 'supplier' => 'UNICEF', 'date_received' => '2026-05-22', 'expiration_date' => '2028-04-30'],
            'HYGTOOT100GCFL01' => ['quantity' => 175, 'supplier' => 'UNICEF', 'date_received' => '2026-05-24', 'expiration_date' => '2028-05-31'],
            'HYGWIDTOREGCPL01' => ['quantity' => 120, 'supplier' => 'DSWD', 'date_received' => '2026-05-26', 'expiration_date' => null],
            'HYGNAICLREGRST01' => ['quantity' => 95, 'supplier' => 'Red Cross', 'date_received' => '2026-05-28', 'expiration_date' => null],
            'HYGWASBU20LRPL01' => ['quantity' => 70, 'supplier' => 'LGU Cebu', 'date_received' => '2026-05-30', 'expiration_date' => null],
            'HYGWASHREGCCO01' => ['quantity' => 110, 'supplier' => 'DSWD', 'date_received' => '2026-06-01', 'expiration_date' => null],
            'HYGLAUSOREGCBA01' => ['quantity' => 240, 'supplier' => 'DSWD', 'date_received' => '2026-06-03', 'expiration_date' => '2029-01-31'],
            'HYGDISDIMCDA01' => ['quantity' => 80, 'supplier' => 'UNICEF', 'date_received' => '2026-06-05', 'expiration_date' => null],
            'HYGDISDILCDA01' => ['quantity' => 75, 'supplier' => 'UNICEF', 'date_received' => '2026-06-07', 'expiration_date' => null],
            'HYGBABWI80CTCUN01' => ['quantity' => 130, 'supplier' => 'UNICEF', 'date_received' => '2026-06-09', 'expiration_date' => '2028-07-31'],
            'HYGINSRE100MLCSP01' => ['quantity' => 90, 'supplier' => 'DOH', 'date_received' => '2026-06-11', 'expiration_date' => '2028-10-31'],
            'HYGHAIBRREGCPL01' => ['quantity' => 100, 'supplier' => 'DSWD', 'date_received' => '2026-06-13', 'expiration_date' => null],
            'HYGSURDI1LCLI01' => ['quantity' => 65, 'supplier' => 'DOH', 'date_received' => '2026-06-15', 'expiration_date' => '2028-03-31'],
            'SHELARRE6PRFA01' => ['quantity' => 10, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-17', 'expiration_date' => null],
            'SHEPLASH4X25MCCL01' => ['quantity' => 30, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-19', 'expiration_date' => null],
            'SHESLEPIREGRFO01' => ['quantity' => 80, 'supplier' => 'Red Cross', 'date_received' => '2026-06-21', 'expiration_date' => null],
            'SHEBEDROREGRCO01' => ['quantity' => 55, 'supplier' => 'DSWD', 'date_received' => '2026-06-23', 'expiration_date' => null],
            'SHEGROSH2X3MRWA01' => ['quantity' => 45, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-25', 'expiration_date' => null],
            'SHETENST12PCSRST01' => ['quantity' => 36, 'supplier' => 'NDRRMC', 'date_received' => '2026-06-27', 'expiration_date' => null],
            'SHETENHAREGRCL01' => ['quantity' => 22, 'supplier' => 'LGU Cebu', 'date_received' => '2026-06-29', 'expiration_date' => null],
            'SHEHANAXREGRCA01' => ['quantity' => 12, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-01', 'expiration_date' => null],
            'SHEUTIBRREGROU01' => ['quantity' => 28, 'supplier' => 'DSWD', 'date_received' => '2026-07-03', 'expiration_date' => null],
            'SHEWATCO20LRST01' => ['quantity' => 60, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-05', 'expiration_date' => null],
            'SHECANREREGCPA01' => ['quantity' => 18, 'supplier' => 'Red Cross', 'date_received' => '2026-07-07', 'expiration_date' => null],
            'SHERAINREGRRE01' => ['quantity' => 48, 'supplier' => 'DSWD', 'date_received' => '2026-07-09', 'expiration_date' => null],
            'COMBASRAVHFRDE01' => ['quantity' => 6, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-11', 'expiration_date' => null],
            'COMHEADLEDRUS01' => ['quantity' => 35, 'supplier' => 'Red Cross', 'date_received' => '2026-07-13', 'expiration_date' => null],
            'COMLEDTOLEDRHA01' => ['quantity' => 42, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-15', 'expiration_date' => null],
            'COMAAABAAAACAL01' => ['quantity' => 85, 'supplier' => 'DOH', 'date_received' => '2026-07-17', 'expiration_date' => '2031-02-28'],
            'COM9VBA9VCAL01' => ['quantity' => 40, 'supplier' => 'DOH', 'date_received' => '2026-07-19', 'expiration_date' => '2031-03-31'],
            'COMCHACA3IN1CUS01' => ['quantity' => 30, 'supplier' => 'Red Cross', 'date_received' => '2026-07-21', 'expiration_date' => null],
            'COMPOWST6PORTRSU01' => ['quantity' => 14, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-23', 'expiration_date' => null],
            'COMEMESI4GCPR01' => ['quantity' => 25, 'supplier' => 'LGU Cebu', 'date_received' => '2026-07-25', 'expiration_date' => null],
            'COMRADANVHFRFL01' => ['quantity' => 15, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-27', 'expiration_date' => null],
            'COMPORSI12VRAL01' => ['quantity' => 8, 'supplier' => 'NDRRMC', 'date_received' => '2026-07-29', 'expiration_date' => null],
            'COMMEMCA64GBRMI01' => ['quantity' => 20, 'supplier' => 'Red Cross', 'date_received' => '2026-07-31', 'expiration_date' => null],
            'COMSOLLALEDRSO01' => ['quantity' => 26, 'supplier' => 'Red Cross', 'date_received' => '2026-08-02', 'expiration_date' => null],
            'COMUSBCH4PORTRUS01' => ['quantity' => 12, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-04', 'expiration_date' => null],
            'COMEXTCA10MROU01' => ['quantity' => 18, 'supplier' => 'LGU Cebu', 'date_received' => '2026-08-06', 'expiration_date' => null],
            'SHERECBUREGRRE01' => ['quantity' => 9, 'supplier' => 'NDRRMC', 'date_received' => '2026-08-08', 'expiration_date' => null],
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
