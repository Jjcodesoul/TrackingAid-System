<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->index(['actor_id', 'created_at'], 'activity_logs_actor_created_at_index');
            $table->index(['subject_type', 'created_at'], 'activity_logs_subject_created_at_index');
        });
    }

    public function down(): void
    {
        Schema::table('activity_logs', function (Blueprint $table) {
            $table->dropIndex('activity_logs_actor_created_at_index');
            $table->dropIndex('activity_logs_subject_created_at_index');
        });
    }
};
