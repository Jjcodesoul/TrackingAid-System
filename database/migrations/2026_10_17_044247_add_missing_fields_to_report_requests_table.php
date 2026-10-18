
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('report_requests', function (Blueprint $table) {
            if (! Schema::hasColumn('report_requests', 'member_id')) {
                $table->string('member_id', 120)->nullable();
            }

            if (! Schema::hasColumn('report_requests', 'format')) {
                $table->string('format', 10)->nullable()->default('pdf');
            }

            if (! Schema::hasColumn('report_requests', 'file_name')) {
                $table->string('file_name')->nullable();
            }

            if (! Schema::hasColumn('report_requests', 'file_path')) {
                $table->string('file_path')->nullable();
            }
        });
    }

    public function down(): void
    {
        // Intentionally leave existing columns untouched.
    }
};
