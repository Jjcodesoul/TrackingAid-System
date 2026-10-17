<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            $table->unsignedInteger('approved_quantity')->nullable()->after('quantity');
            $table->foreignId('approved_by_user_id')->nullable()->after('approved_at')
                ->constrained('users')->nullOnDelete();
            $table->text('approval_notes')->nullable()->after('notification_status');
        });

        Schema::table('returns', function (Blueprint $table) {
            $table->foreignId('borrow_release_id')->nullable()->after('inventory_id')
                ->constrained('borrow_releases')->nullOnDelete();
            $table->foreignId('processed_by_user_id')->nullable()->after('borrow_release_id')
                ->constrained('users')->nullOnDelete();
            $table->uuid('idempotency_key')->nullable()->unique();
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table) {
            $table->dropForeign(['borrow_release_id']);
            $table->dropForeign(['processed_by_user_id']);
            $table->dropUnique(['idempotency_key']);
            $table->dropColumn(['borrow_release_id', 'processed_by_user_id', 'idempotency_key']);
        });

        Schema::table('requests', function (Blueprint $table) {
            $table->dropForeign(['approved_by_user_id']);
            $table->dropColumn(['approved_quantity', 'approved_by_user_id', 'approval_notes']);
        });
    }
};