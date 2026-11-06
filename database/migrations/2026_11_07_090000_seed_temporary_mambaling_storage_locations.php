<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $timestamp = now();
        $temporaryWarehouseLabel = 'Mambaling — temporary labels; physical locations pending confirmation';

        foreach ([
            'Main Warehouse',
            'Relief Goods Storage',
            'Food and Water Storage',
            'Medical Supplies Storage',
            'Clothing and Essentials Storage',
            'Equipment and Tools Storage',
        ] as $name) {
            $existing = DB::table('storage_locations')->where('name', $name)->first();
            if ($existing) {
                DB::table('storage_locations')->where('id', $existing->id)->update([
                    'warehouse_name' => $temporaryWarehouseLabel,
                    'is_active' => true,
                    'updated_at' => $timestamp,
                ]);
            } else {
                DB::table('storage_locations')->insert([
                    'name' => $name,
                    'warehouse_name' => $temporaryWarehouseLabel,
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }
        }
    }

    public function down(): void
    {
        // Keep these records if transactions have started referencing them.
    }
};
