<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ResetDemoData extends Command
{
    /**
     * php artisan demo:reset
     *
     * Wipes all items, stock, requests, releases and returns back to ZERO so
     * you can test the whole system from a clean start (Dashboard, Reports and
     * every card should show 0 and then update as you add data).
     *
     * Your users (logins) are NOT touched. Notifications are not stored in the
     * database — they are computed live — so they clear themselves.
     */
    protected $signature = 'demo:reset {--force : Skip the confirmation prompt}';

    protected $description = 'Reset all items/stock/requests/releases/returns to zero (users are kept)';

    /** Child tables first, parent tables last. */
    private const TABLES = [
        'stock_batches',
        'borrow_releases',
        'returns',
        'requests',
        'inventory',
        'items',
    ];

    public function handle(): int
    {
        $database = DB::connection()->getDatabaseName();

        $this->warn("Database: {$database}");

        if (! $this->option('force') && ! $this->confirm(
            "This permanently deletes ALL items, stock, requests, releases and returns in '{$database}'. User accounts are kept. Continue?"
        )) {
            $this->info('Cancelled — nothing was deleted.');

            return self::SUCCESS;
        }

        $rows = [];

        Schema::disableForeignKeyConstraints();

        try {
            foreach (self::TABLES as $table) {
                if (! Schema::hasTable($table)) {
                    continue;
                }

                $before = DB::table($table)->count();
                DB::table($table)->truncate();

                $rows[] = [$table, $before, DB::table($table)->count()];
            }
        } finally {
            Schema::enableForeignKeyConstraints();
        }

        $this->table(['Table', 'Rows before', 'Rows now'], $rows);
        $this->info('Done. Everything is at zero. Reload the pages and check that they show 0 / empty states.');

        return self::SUCCESS;
    }
}
