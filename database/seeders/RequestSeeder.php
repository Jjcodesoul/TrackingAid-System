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
            ['REQ-2024-091', 'MED-381AB4AC', 200, 'High',   'Pending',  'Typhoon Carina Relief',     'responder1@resqoperation.org', 1, null, null],
            ['REQ-2024-090', 'FOO-669FB372',   150, 'High',   'Pending',  'Flood Ops - Pampanga',      'responder2@resqoperation.org', 2, null, null],
            ['REQ-2024-089', 'REL-02B4AA7D', 60, 'High',   'Approved', 'Coastal Rescue - Batangas', 'responder3@resqoperation.org', 3, 6, 'Adjusted to the 6 units available in seeded stock.'],
            ['REQ-2024-088', 'FOO-EF61C25B', 500, 'Medium', 'Rejected', 'Barangay Poblacion',        'responder4@resqoperation.org', 4, null, null],
            ['REQ-2024-087', 'MED-381AB4AC',  30, 'High',   'Pending',  'Community Health Center',   'responder5@resqoperation.org', 5, null, null],
            ['REQ-2024-086', 'REL-02B4AA7D',100, 'Low',    'Pending',  'DSWD Shelter - Cavite',     'responder6@resqoperation.org', 6, null, null],
        ];

        $adminId = \App\Models\User::query()->where('role', 'admin')->value('id');

        foreach ($requests as [$code, $inventorySku, $qty, $priority, $status, $purpose, $email, $ageInDays, $approvedQuantity, $approvalNotes]) {
            $inventoryId = Inventory::query()->where('sku', $inventorySku)->value('id');

            if (! $inventoryId) {
                throw new \RuntimeException("Cannot seed {$code}: inventory SKU {$inventorySku} was not found.");
            }

            $createdAt = now()->subDays($ageInDays)->setTime(8, 0);
            $decisionAt = $createdAt->copy()->addHours(2);

            DB::table('requests')->updateOrInsert(
                ['request_code' => $code],
                [
                'request_code'        => $code,
                'inventory_id'        => $inventoryId,
                'source'              => $purpose,
                'quantity'            => $qty,
                'approved_quantity'   => $approvedQuantity,
                'approved_by_user_id' => $status === 'Approved' ? $adminId : null,
                'approval_notes'      => $approvalNotes,
                'priority'            => $priority,
                'status'              => $status,
                'purpose'             => $purpose,
                'responder_email'     => $email,
                'notification_status' => null,
                'approved_at'         => $status === 'Approved' ? $decisionAt : null,
                'rejected_at'         => $status === 'Rejected' ? $decisionAt : null,
                'created_at'          => $createdAt,
                'updated_at'          => $status === 'Pending' ? $createdAt : $decisionAt,
                ]
            );
        }
    }
}
