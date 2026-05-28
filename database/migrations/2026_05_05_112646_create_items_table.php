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
    Schema::create('items', function (Blueprint $table) {
        $table->id();
        $table->string('name');
        $table->string('category');
        $table->string('unit_type');
        $table->string('size_weight')->nullable();
        $table->string('target_beneficiary')->nullable();
        $table->string('variant')->nullable();
        $table->enum('type', ['consumable', 'returnable']);
        $table->string('storage_location')->nullable();
        $table->string('sku')->unique();
        $table->softDeletes();
        $table->timestamps();
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('items');
    }
};


