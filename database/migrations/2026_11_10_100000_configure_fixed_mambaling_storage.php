<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            $timestamp = now();
            $location = DB::table('storage_locations')->where('name', 'Mambaling Storage')->first();

            if ($location) {
                DB::table('storage_locations')->where('id', $location->id)->update([
                    'warehouse_name' => 'Mambaling, Cebu City, Philippines',
                    'is_active' => true,
                    'updated_at' => $timestamp,
                ]);
                $locationId = $location->id;
            } else {
                $locationId = DB::table('storage_locations')->insertGetId([
                    'name' => 'Mambaling Storage',
                    'warehouse_name' => 'Mambaling, Cebu City, Philippines',
                    'is_active' => true,
                    'created_at' => $timestamp,
                    'updated_at' => $timestamp,
                ]);
            }

            // Keep old rows and transaction labels intact; remove them only from new selections.
            DB::table('storage_locations')->where('id', '!=', $locationId)->where('is_active', true)->update([
                'is_active' => false,
                'updated_at' => $timestamp,
            ]);
        });
    }

    public function down(): void
    {
        // Preserve the designated row and historical location records on rollback.
    }
};
