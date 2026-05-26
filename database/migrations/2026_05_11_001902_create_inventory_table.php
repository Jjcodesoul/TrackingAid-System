<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inventory', function (Blueprint $table) {

            $table->id();

            $table->string('name');

            $table->string('category');

            $table->string('sku')->unique();

            $table->enum('type', [
                'Consumable',
                'Returnable'
            ]);

            $table->integer('quantity')->default(0);

            $table->date('expiration')->nullable();

            $table->string('storage_location')->nullable();

            $table->softDeletes();

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory');
    }
};