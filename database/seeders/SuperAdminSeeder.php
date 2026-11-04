<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use RuntimeException;

class SuperAdminSeeder extends Seeder
{
    public function run(): void
    {
        $password = env('SUPER_ADMIN_PASSWORD');

        if (! is_string($password) || $password === '') {
            throw new RuntimeException('Set SUPER_ADMIN_PASSWORD in the environment before running this seeder.');
        }

        if (! Schema::hasColumn('users', 'is_active') || ! Schema::hasColumn('users', 'auth_version')) {
            throw new RuntimeException('Run the database migrations before running this seeder.');
        }

        DB::transaction(function () use ($password): void {
            $email = 'superadmin@gmail.com';
            $user = User::where('email', $email)->lockForUpdate()->first();

            if ($user && $user->role !== 'super_admin') {
                throw new RuntimeException("The email {$email} is already assigned to a non-Super Admin account.");
            }

            if (! $user && User::where('role', 'super_admin')->exists()) {
                throw new RuntimeException('A different Super Admin account already exists.');
            }

            $isNew = $user === null;
            $user ??= new User;

            $user->forceFill([
                'name' => 'Super Admin',
                'first_name' => 'Super',
                'last_name' => 'Admin',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'is_active' => true,
                'must_change_password' => false,
                'auth_version' => $isNew ? 0 : $user->auth_version + 1,
            ])->save();

            if (! $isNew) {
                $user->tokens()->delete();
            }
        });

        $this->command?->info('Super Admin account seeded for superadmin@gmail.com.');
    }
}
