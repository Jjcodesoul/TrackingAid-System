<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->foreignId('delivery_personnel_id')
                ->nullable()
                ->after('inventory_id')
                ->constrained('users')
                ->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->dropForeign(['delivery_personnel_id']);
            $table->dropColumn('delivery_personnel_id');
        });
    }
};