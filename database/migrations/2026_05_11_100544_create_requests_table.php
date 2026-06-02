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
    Schema::create('requests', function (Blueprint $table) {
        $table->id();

        $table->string('request_code')->unique();

        $table->foreignId('inventory_id')->constrained('inventory')->onDelete('cascade');

        $table->string('source')->default('ResQOperation');

        $table->integer('quantity');

        $table->enum('priority', ['Low', 'Medium', 'High']);

        $table->enum('status', [
            'Pending',
            'Approved',
            'Rejected',
            'Released'
        ])->default('Pending');

        $table->text('purpose')->nullable();

        $table->string('responder_email')->nullable();

        $table->string('notification_status')->nullable();

        $table->timestamp('approved_at')->nullable();

        $table->timestamp('rejected_at')->nullable();

        $table->timestamps();
    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('requests');
    }
};
