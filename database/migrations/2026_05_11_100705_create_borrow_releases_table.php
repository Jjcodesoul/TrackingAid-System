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
    Schema::create('borrow_releases', function (Blueprint $table) {
        $table->id();

        $table->foreignId('request_id')->constrained('requests')->onDelete('cascade');

        $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');

        $table->string('unit');

        $table->integer('quantity');

        $table->string('purpose');

        $table->string('location')->nullable();

        $table->timestamp('released_at');

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('borrow_releases');
    }
};
