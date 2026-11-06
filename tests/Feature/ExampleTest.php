<?php

namespace Tests\Feature;

// use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_public_landing_page_introduces_the_system_and_links_to_login(): void
    {
        $response = $this->get('/');

        $response->assertOk()
            ->assertSee('TrackingAid')
            ->assertSee('Managing Relief Resources.')
            ->assertSee('Supporting Communities.')
            ->assertSee('What TrackingAid Offers')
            ->assertSee('Inventory Management')
            ->assertSee('Request Management')
            ->assertSee('Stock Release')
            ->assertSee('Reports and Monitoring')
            ->assertSee('images/relief-warehouse.svg')
            ->assertSee('images/trackingaid-logo.jpg')
            ->assertSee(route('login'));
    }
}
