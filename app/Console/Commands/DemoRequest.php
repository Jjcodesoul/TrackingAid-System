<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class DemoRequest extends Command
{
    /**
     * php artisan demo:request            -> 3 units of the first item, Medium priority
     * php artisan demo:request 5 --item=2 --priority=High
     *
     * Creates a PENDING request, exactly like the ResQOperation website does
     * through the API. TrackingAid has no "create request" button of its own,
     * so use this to test Requests -> Approve -> Borrow/Release.
     */
    protected $signature = 'demo:request
        {quantity=3 : How many units are requested}
        {--item= : Inventory id (defaults to the first item)}
        {--priority=Medium : Low, Medium or High}
        {--purpose=Test request : What the request is for}';

    protected $description = 'Create a Pending test request (what the ResQOperation API would send)';

    public function handle(): int
    {
        $priority = ucfirst(strtolower((string) $this->option('priority')));

        if (! in_array($priority, ['Low', 'Medium', 'High'], true)) {
            $this->error('Priority must be Low, Medium or High.');

            return self::FAILURE;
        }

        $quantity = (int) $this->argument('quantity');

        if ($quantity < 1) {
            $this->error('Quantity must be at least 1.');

            return self::FAILURE;
        }

        $query = DB::table('inventory')->whereNull('deleted_at');
        $inventory = $this->option('item')
            ? $query->where('id', (int) $this->option('item'))->first()
            : $query->orderBy('id')->first();

        if (! $inventory) {
            $this->error('No item found. Add an item first (Inventory > Add Item), then add stock in Stock In.');

            return self::FAILURE;
        }

        $number = DB::table('requests')->count() + 1;
        do {
            $code = sprintf('REQ-TEST-%03d', $number++);
        } while (DB::table('requests')->where('request_code', $code)->exists());

        DB::table('requests')->insert([
            'request_code' => $code,
            'inventory_id' => $inventory->id,
            'source'       => 'ResQOperation',
            'quantity'     => $quantity,
            'priority'     => $priority,
            'status'       => 'Pending',
            'purpose'      => (string) $this->option('purpose'),
            'created_at'   => now(),
            'updated_at'   => now(),
        ]);

        $this->table(
            ['Request', 'Item', 'SKU', 'Qty', 'Priority', 'Status'],
            [[$code, $inventory->name, $inventory->sku, $quantity, $priority, 'Pending']]
        );
        $this->info('Open the Requests page to approve it.');

        return self::SUCCESS;
    }
}
