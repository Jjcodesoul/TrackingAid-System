<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Validator;

class CreateSuperAdmin extends Command
{
    protected $signature = 'super-admin:create';

    protected $description = 'Create the initial Super Admin account using secure interactive prompts';

    public function handle(): int
    {
        if (! Schema::hasTable('activity_logs')) {
            $this->error('Database migrations are pending. Run "php artisan migrate" before creating a Super Admin account.');

            return self::FAILURE;
        }

        if (User::where('role', 'super_admin')->exists()) {
            $this->error('A Super Admin account already exists. This command only bootstraps the initial account.');

            return self::FAILURE;
        }

        $name = trim((string) $this->ask('Full name'));
        $email = strtolower(trim((string) $this->ask('Email address')));
        $password = (string) $this->secret('Password (minimum 12 characters)');
        $passwordConfirmation = (string) $this->secret('Confirm password');

        $validator = Validator::make([
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'password_confirmation' => $passwordConfirmation,
        ], [
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:12', 'same:password_confirmation'],
        ]);

        if ($validator->fails()) {
            foreach ($validator->errors()->all() as $error) {
                $this->error($error);
            }

            return self::FAILURE;
        }

        $nameParts = explode(' ', $name, 2);

        DB::transaction(function () use ($name, $nameParts, $email, $password): void {
            User::create([
                'name' => $name,
                'first_name' => $nameParts[0],
                'last_name' => $nameParts[1] ?? '',
                'email' => $email,
                'password' => Hash::make($password),
                'role' => 'super_admin',
                'must_change_password' => false,
            ]);
        });

        $this->info("Super Admin account created for {$email}.");

        return self::SUCCESS;
    }
}
