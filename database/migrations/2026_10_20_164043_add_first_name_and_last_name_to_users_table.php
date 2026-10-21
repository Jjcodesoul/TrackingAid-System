<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('first_name')->nullable()->after('id');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('name')->nullable()->change();
        });

        // Backfill existing users by splitting their current name
        $existingUsers = DB::table('users')->get();
        foreach ($existingUsers as $user) {
            $fullName = trim((string) ($user->name ?? ''));
            $parts = explode(' ', $fullName, 2);
            $firstName = $parts[0] !== '' ? $parts[0] : 'User';
            $lastName = $parts[1] ?? '';

            DB::table('users')->where('id', $user->id)->update([
                'first_name' => $firstName,
                'last_name' => $lastName,
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['first_name', 'last_name']);
        });
    }
};
