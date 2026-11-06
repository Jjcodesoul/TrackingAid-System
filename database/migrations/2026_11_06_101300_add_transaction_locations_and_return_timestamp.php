<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_releases', function (Blueprint $table): void {
            $table->string('source_storage_location')->nullable()->after('location');
        });

        Schema::table('returns', function (Blueprint $table): void {
            $table->string('storage_location')->nullable()->after('condition');
            $table->timestamp('returned_at')->nullable()->after('received_by_user_id');
        });

        // Historical source/return locations are intentionally left null:
        // current item locations cannot prove where an older movement occurred.
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->dropColumn(['storage_location', 'returned_at']);
        });

        Schema::table('borrow_releases', function (Blueprint $table): void {
            $table->dropColumn('source_storage_location');
        });
    }
};
