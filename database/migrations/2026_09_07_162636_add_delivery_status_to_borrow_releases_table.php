<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->string('delivery_status')
                ->default('Loading')
                ->after('released_at');

            $table->timestamp('dispatched_at')
                ->nullable()
                ->after('delivery_status');

            $table->timestamp('in_transit_at')
                ->nullable()
                ->after('dispatched_at');

            $table->timestamp('arrived_at')
                ->nullable()
                ->after('in_transit_at');
        });
    }

    public function down(): void
    {
        Schema::table('borrow_releases', function (Blueprint $table) {
            $table->dropColumn([
                'delivery_status',
                'dispatched_at',
                'in_transit_at',
                'arrived_at',
            ]);
        });
    }
};