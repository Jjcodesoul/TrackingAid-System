<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('resqperation_forwarded_requests', function (Blueprint $table): void {
            $table->foreignId('inventory_id')->nullable()->constrained('inventory')->nullOnDelete();
            $table->unsignedInteger('approved_quantity')->nullable();
            $table->foreignId('approved_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('rejected_by_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
        });

        Schema::table('borrow_releases', function (Blueprint $table): void {
            $table->foreignId('request_id')->nullable()->change();
            $table->foreignId('forwarded_request_id')
                ->nullable()
                ->constrained('resqperation_forwarded_requests')
                ->nullOnDelete();
        });

        Schema::create('notifications', function (Blueprint $table): void {
            $table->uuid('id')->primary();
            $table->string('type');
            $table->morphs('notifiable');
            $table->text('data');
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('notifications');

        Schema::table('borrow_releases', function (Blueprint $table): void {
            $table->dropForeign(['forwarded_request_id']);
            $table->dropColumn('forwarded_request_id');
            $table->foreignId('request_id')->nullable(false)->change();
        });

        Schema::table('resqperation_forwarded_requests', function (Blueprint $table): void {
            $table->dropForeign(['inventory_id']);
            $table->dropForeign(['approved_by_user_id']);
            $table->dropForeign(['rejected_by_user_id']);
            $table->dropColumn([
                'inventory_id',
                'approved_quantity',
                'approved_by_user_id',
                'rejected_by_user_id',
                'approved_at',
                'rejected_at',
            ]);
        });
    }
};
