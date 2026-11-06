<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->string('returned_by_name')->nullable()->after('processed_by_user_id');
            $table->foreignId('received_by_user_id')->nullable()->after('returned_by_name')
                ->constrained('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('returns', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('received_by_user_id');
            $table->dropColumn('returned_by_name');
        });
    }
};
