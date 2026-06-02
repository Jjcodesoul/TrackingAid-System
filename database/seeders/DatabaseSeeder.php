<?php

namespace Database\Seeders;

<<<<<<< HEAD
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        User::updateOrCreate(
            ['email' => 'admin@trackingaid.org'],
            [
                'name' => 'Admin User',
                'password' => Hash::make('password123'),
                'role' => 'admin',
            ]
        );
    }
}
=======
use App\Models\Inventory;
use App\Models\Request as SupplyRequest;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::firstOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => bcrypt('password'),
            ]
        );

        $rice = Inventory::firstOrCreate(
            ['sku' => 'FOOD-RICE-SACK-50KG-ALL'],
            [
                'name' => 'Rice Sack 50KG',
                'category' => 'Food',
                'type' => 'Consumable',
                'quantity' => 1200,
                'storage_location' => 'Warehouse A',
            ]
        );

        $medkit = Inventory::firstOrCreate(
            ['sku' => 'MED-MEDKIT-BOX-SMALL-ALL'],
            [
                'name' => 'Small Medical Kit',
                'category' => 'Medical',
                'type' => 'Returnable',
                'quantity' => 300,
                'storage_location' => 'Warehouse B',
            ]
        );

        $blanket = Inventory::firstOrCreate(
            ['sku' => 'RELIEF-BLANKET-PCS-MEDIUM-ALL'],
            [
                'name' => 'Medium Relief Blanket',
                'category' => 'Relief',
                'type' => 'Returnable',
                'quantity' => 900,
                'storage_location' => 'Warehouse C',
            ]
        );

        $noodles = Inventory::firstOrCreate(
            ['sku' => 'FOOD-NOODLES-PACK-REG-ADULT'],
            [
                'name' => 'Regular Noodles Pack',
                'category' => 'Food',
                'type' => 'Consumable',
                'quantity' => 700,
                'storage_location' => 'Warehouse A',
            ]
        );

        $requests = [
            ['REQ-2024-001', $rice->id, 500, 'High', 'Pending', 'Emergency food distribution', 'responder1@resqoperation.org'],
            ['REQ-2024-002', $medkit->id, 200, 'High', 'Pending', 'Medical response supply', 'responder2@resqoperation.org'],
            ['REQ-2024-003', $blanket->id, 300, 'Medium', 'Approved', 'Evacuation center support', 'responder3@resqoperation.org'],
            ['REQ-2024-004', $noodles->id, 100, 'Low', 'Rejected', 'Additional food buffer', 'responder4@resqoperation.org'],
        ];

        foreach ($requests as [$code, $inventoryId, $quantity, $priority, $status, $purpose, $email]) {
            SupplyRequest::firstOrCreate(
                ['request_code' => $code],
                [
                    'inventory_id' => $inventoryId,
                    'source' => 'ResQOperation',
                    'quantity' => $quantity,
                    'priority' => $priority,
                    'status' => $status,
                    'purpose' => $purpose,
                    'responder_email' => $email,
                    'notification_status' => $status === 'Pending' ? null : "Responder notified: request {$status}",
                    'approved_at' => $status === 'Approved' ? now() : null,
                    'rejected_at' => $status === 'Rejected' ? now() : null,
                ]
            );
        }
    }
}
>>>>>>> db5ef8e73ac4431ebbfc800ae78adb114a103e05
