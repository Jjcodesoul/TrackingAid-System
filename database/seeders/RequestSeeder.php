<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RequestSeeder extends Seeder
{
    public function run(): void
    {
        // inventory table IDs: 1=Rice, 2=Medical Kit, 3=Blanket, 4=Noodles
        $requests = [
            ['REQ-2024-091', 2, 200, 'Critical', 'Pending',  'Typhoon Carina Relief',    'responder1@resqoperation.org', '2026-05-26'],
            ['REQ-2024-090', 1, 150, 'High',     'Approved', 'Flood Ops - Pampanga',     'responder2@resqoperation.org', '2026-05-25'],
            ['REQ-2024-089', 3,  60, 'High',     'Approved', 'Coastal Rescue - Batangas','responder3@resqoperation.org', '2026-05-24'],
            ['REQ-2024-088', 4, 500, 'Medium',   'Rejected', 'Barangay Poblacion',       'responder4@resqoperation.org', '2026-05-23'],
            ['REQ-2024-087', 2,  30, 'High',     'Pending',  'Community Health Center',  'responder5@resqoperation.org', '2026-05-23'],
            ['REQ-2024-086', 3, 100, 'Low',      'Approved', 'DSWD Shelter - Cavite',    'responder6@resqoperation.org', '2026-05-22'],
        ];

        foreach ($requests as [$code, $inventoryId, $qty, $priority, $status, $purpose, $email, $date]) {
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
