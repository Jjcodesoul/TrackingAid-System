<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->enum('status', [
                'Pending',
                'Approved',
                'Partially Released',
                'Released',
                'Rejected',
                'Cancelled',
            ])->default('Pending')->change();
        });

        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->foreignId('processed_by_user_id')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete();
            $table->uuid('idempotency_key')->nullable()->unique();
        });
    }

    public function down(): void
    {
        DB::table('requests')
            ->where('status', 'Partially Released')
            ->update(['status' => 'Approved']);

        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->dropForeign(['processed_by_user_id']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['processed_by_user_id', 'idempotency_key']);
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->enum('status', [
                'Pending',
                'Approved',
                'Rejected',
                'Released',
            ])->default('Pending')->change();
        });
    }
};