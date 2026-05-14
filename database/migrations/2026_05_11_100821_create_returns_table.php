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
    Schema::create('returns', function (Blueprint $table) {
        $table->id();

        $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');

        $table->integer('quantity');

        $table->enum('condition', [
            'Good',
            'Damaged',
            'Missing'
        ]);

        $table->text('notes')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('returns');
    }
};
