<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // These labels were previously seeded with a Mambaling address, but
        // no source verifies that they identify real buildings there. Keep
        // all references and history; remove them from new selections.
        DB::table('storage_locations')
            ->whereIn('name', ['Warehouse A', 'Warehouse B', 'Warehouse C', 'Equipment Bay'])
            ->update(['is_active' => false]);

        DB::table('storage_locations')
            ->where('warehouse_name', 'not like', '%Mambaling%')
            ->update(['is_active' => false]);
    }

    public function down(): void
    {
        // Intentionally do not reactivate legacy location records on rollback.
    }
};
