<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_requests', function (Blueprint $table) {
            $table->string('member_id', 120)->nullable()->after('report_type');
            $table->string('format', 10)->nullable()->after('status');
            $table->string('file_name')->nullable()->after('filters');
            $table->string('file_path')->nullable()->after('file_name');
        });
    }

    public function down(): void
    {
        Schema::table('report_requests', function (Blueprint $table) {
            $table->dropColumn(['member_id', 'format', 'file_name', 'file_path']);
        });
    }
};
