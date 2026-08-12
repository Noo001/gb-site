<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CaptchaTest extends TestCase
{
    use RefreshDatabase;
    public function test_captcha_endpoint_returns_png_and_stores_code(): void
    {
        $response = $this->get('/captcha');

        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'image/png');
        $this->assertNotNull(session('captcha_code'));
        $this->assertMatchesRegularExpression('/^[A-Z0-9]{5}$/', session('captcha_code'));
    }
}
