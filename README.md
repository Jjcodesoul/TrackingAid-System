# TrackingAid System

Web-based logistics and inventory management for disaster relief and rescue operations, with a mobile delivery tracking API.

## Local setup

Requirements: PHP 8.3+, Composer, Node.js, and npm.

```bash
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
npm install
npm run build
php artisan serve
```

The public overview is available at `/`. Use its **Login** link to reach the existing sign-in page. Admin-created user accounts receive an expiring password setup link by email; configure the Laravel mail transport in the deployment environment before creating real accounts. The architecture overview, project timeline template, and research validation plan are in [docs/project-documentation.md](docs/project-documentation.md).

The seeders create sample/demo records. Confirm inventory, supplier, batch, request, and expiration details against operational records before using seeded data for decisions.

## QA

Run the automated suite with `php artisan test`. The report and data-integrity test cases, including expected values, are documented in [QA_TEST_CASES.md](QA_TEST_CASES.md).

## Initial Super Admin

After migrations are applied, create the initial Super Admin interactively:

```bash
php artisan super-admin:create
```

The command prompts for a name, email, and a confirmed password of at least 12 characters. It does not print or store the password in plaintext, and it refuses to create another Super Admin if one already exists. Super Admin accounts are intentionally excluded from the regular user-management form and must be protected through the deployment's account recovery process.

Super Admin account controls include activation/deactivation, reset links, and session revocation. Sign-in history and these controls require the account-security migration to be applied with `php artisan migrate`.

To seed or reset the initial Super Admin account, set `SUPER_ADMIN_PASSWORD` in the environment and run `php artisan db:seed --class=SuperAdminSeeder --force`. The seeder creates or updates `superadmin@gmail.com`, stores only a password hash, and revokes existing sessions and API tokens when resetting an existing account. Do not commit the password or place it in source control; use a strong password and change the provided initial password promptly.
