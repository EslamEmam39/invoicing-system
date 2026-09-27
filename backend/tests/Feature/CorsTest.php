<?php

namespace Tests\Feature;

use Tests\TestCase;

class CorsTest extends TestCase
{
    public function test_api_preflight_allows_configured_frontend_and_bearer_header(): void
    {
        config(['cors.allowed_origins' => ['http://localhost:5173']]);

        $this->options('/api/products', [], [
            'Origin' => 'http://localhost:5173',
            'Access-Control-Request-Method' => 'POST',
            'Access-Control-Request-Headers' => 'authorization,content-type',
        ])->assertSuccessful()
            ->assertHeader('Access-Control-Allow-Origin', 'http://localhost:5173')
            ->assertHeaderMissing('Access-Control-Allow-Credentials');
    }

    public function test_unlisted_origin_has_no_cors_permission(): void
    {
        config(['cors.allowed_origins' => ['http://localhost:5173', 'http://127.0.0.1:5173']]);

        $this->options('/api/products', [], [
            'Origin' => 'https://unlisted.example',
            'Access-Control-Request-Method' => 'POST',
        ])->assertHeaderMissing('Access-Control-Allow-Origin');
    }
}
