<?php

namespace Tests\Feature;

use App\Providers\AppServiceProvider;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    /**
     * A basic test example.
     */
    public function test_the_application_returns_a_successful_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
    }

    public function test_production_asset_urls_use_https(): void
    {
        $this->app['env'] = 'production';
        (new AppServiceProvider($this->app))->boot();

        try {
            $this->get('/')
                ->assertOk()
                ->assertSee('href="https://127.0.0.1:8000/build/assets/', false);
        } finally {
            URL::forceScheme(null);
        }
    }
}
