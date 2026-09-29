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

The seeders create sample/demo records. Confirm inventory, supplier, batch, request, and expiration details against operational records before using seeded data for decisions.

## QA

Run the automated suite with `php artisan test`. The report and data-integrity test cases, including expected values, are documented in [QA_TEST_CASES.md](QA_TEST_CASES.md).
