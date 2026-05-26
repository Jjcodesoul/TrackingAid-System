<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            if (! Schema::hasColumn('requests', 'responder_email')) {
                $table->string('responder_email')->nullable()->after('purpose');
            }

            if (! Schema::hasColumn('requests', 'notification_status')) {
                $table->string('notification_status')->nullable()->after('responder_email');
            }

            if (! Schema::hasColumn('requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable()->after('notification_status');
            }

            if (! Schema::hasColumn('requests', 'rejected_at')) {
                $table->timestamp('rejected_at')->nullable()->after('approved_at');
            }
        });
    }

    public function down(): void
    {
        Schema::table('requests', function (Blueprint $table) {
            foreach (['rejected_at', 'approved_at', 'notification_status', 'responder_email'] as $column) {
                if (Schema::hasColumn('requests', $column)) {
                    $table->dropColumn($column);
                }
            }
        });
    }
};
