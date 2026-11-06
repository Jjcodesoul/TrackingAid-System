<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Legacy text values such as "Warehouse A" are not proof of a
        // physical Mambaling warehouse. Keep the old assignments untouched;
        // admins must register verified locations before they become options.
        DB::table('storage_locations')
            ->where('warehouse_name', 'not like', '%Mambaling%')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Keep location records when rolling back: inventory and stock batches
        // may already reference these labels.
    }
};
