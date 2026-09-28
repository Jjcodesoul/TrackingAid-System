<?php

namespace Database\Seeders;

use App\Models\Inventory;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        $requests = [
            ['REQ-2024-091', 'MEDICAL-MEDKIT-BOX-SM-ALL', 200, 'High',   'Pending',  'Typhoon Carina Relief',     'responder1@resqoperation.org', '2026-05-26'],
            ['REQ-2024-090', 'FOOD-RICE-SACK-50KG-ALL',   150, 'High',   'Pending',  'Flood Ops - Pampanga',      'responder2@resqoperation.org', '2026-05-25'],
            ['REQ-2024-089', 'RELIEF-BLANKET-PCS-REG-ALL', 60, 'High',   'Approved', 'Coastal Rescue - Batangas', 'responder3@resqoperation.org', '2026-05-24'],
            ['REQ-2024-088', 'FOOD-NOODLES-PACK-REG-ALL', 500, 'Medium', 'Rejected', 'Barangay Poblacion',        'responder4@resqoperation.org', '2026-05-23'],
            ['REQ-2024-087', 'MEDICAL-MEDKIT-BOX-SM-ALL',  30, 'High',   'Pending',  'Community Health Center',   'responder5@resqoperation.org', '2026-05-23'],
            ['REQ-2024-086', 'RELIEF-BLANKET-PCS-REG-ALL',100, 'Low',    'Approved', 'DSWD Shelter - Cavite',     'responder6@resqoperation.org', '2026-05-22'],
        ];

        foreach ($requests as [$code, $inventorySku, $qty, $priority, $status, $purpose, $email, $date]) {
            $inventoryId = Inventory::query()->where('sku', $inventorySku)->value('id');

            if (! $inventoryId) {
                throw new \RuntimeException("Cannot seed {$code}: inventory SKU {$inventorySku} was not found.");
            }

            DB::table('requests')->insertOrIgnore([
                'request_code'        => $code,
                'inventory_id'        => $inventoryId,
                'source'              => $purpose,
                'quantity'            => $qty,
                'priority'            => $priority,
                'status'              => $status,
                'purpose'             => $purpose,
                'responder_email'     => $email,
                'notification_status' => $status === 'Pending' ? null : "Responder notified: request {$status}",
                'approved_at'         => $status === 'Approved' ? now() : null,
                'rejected_at'         => $status === 'Rejected' ? now() : null,
                'created_at'          => $date . ' 08:00:00',
                'updated_at'          => now(),
            ]);
        }
    }
}
