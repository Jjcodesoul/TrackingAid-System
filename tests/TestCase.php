<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use App\Models\StorageLocation;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        if (app()->environment('testing') && \Illuminate\Support\Facades\Schema::hasTable('storage_locations')) {
            foreach (['Warehouse A', 'Warehouse B', 'Warehouse C', 'Equipment Bay'] as $name) {
                StorageLocation::firstOrCreate(
                    ['name' => $name],
                    ['warehouse_name' => 'Mambaling Test Fixture', 'is_active' => true],
                );
            }
        }
    }
}
