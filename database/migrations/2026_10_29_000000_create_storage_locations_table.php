<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('storage_locations', function (Blueprint $table) {
            $table->id();
            $table->string('name')->unique();
            $table->string('warehouse_name')->default('Mambaling, Cebu City, Philippines');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::table('items', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('id')->constrained('users')->nullOnDelete();
        });

        Schema::table('stock_batches', function (Blueprint $table) {
            $table->foreignId('created_by_user_id')->nullable()->after('item_id')->constrained('users')->nullOnDelete();
            $table->string('movement_type')->default('stock-in')->after('quantity');
            $table->string('storage_location')->nullable()->after('movement_type');
        });
    }

    public function down(): void
    {
        Schema::table('stock_batches', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
            $table->dropColumn(['movement_type', 'storage_location']);
        });
        Schema::table('items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('created_by_user_id');
        });
        Schema::dropIfExists('storage_locations');
    }
};
