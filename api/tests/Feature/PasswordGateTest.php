<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\URL;
use Tests\TestCase;

class PasswordGateTest extends TestCase
{
    use RefreshDatabase;

    public function test_password_gate_does_not_block_public_pages(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertViewIs('auth.login');
    }

    public function test_access_check_grants_access_and_redirects(): void
    {
        $response = $this->post('/access-check', ['password' => '111']);
        $response->assertRedirect('/');
        $response->assertCookie('site_access', 'granted');
    }

    public function test_e2e_captcha_returns_code_for_signed_url(): void
    {
        $this->get('/captcha');
        $expected = session('captcha_code');
        $this->assertNotNull($expected);

        $url = URL::temporarySignedRoute('e2e.captcha', now()->addMinutes(5));

        $response = $this->getJson($url);
        $response->assertOk();
        $response->assertJson(['code' => $expected]);
    }

    public function test_e2e_captcha_rejects_unsigned_url(): void
    {
        $response = $this->getJson('/e2e/captcha');
        $response->assertForbidden();
    }
}
