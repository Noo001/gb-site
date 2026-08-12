<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthModalTest extends TestCase
{
    use RefreshDatabase;

    public function test_home_page_shows_auth_button_and_modal(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('open-auth-modal', false);
        $response->assertSee('auth-modal-overlay', false);
        $response->assertSee('auth-modal__tab', false);
    }

    public function test_registration_with_valid_data_creates_user_and_redirects(): void
    {
        $this->get('/captcha');
        $captcha = session('captcha_code');

        $response = $this->post('/register', [
            'name' => 'Иван Иванов',
            'email' => 'ivan@example.com',
            'phone' => '+7 (900) 123-45-67',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'privacy' => '1',
            'captcha' => $captcha,
        ]);

        $response->assertRedirect('/');
        $this->assertDatabaseHas('users', [
            'email' => 'ivan@example.com',
            'phone' => '79001234567',
            'bonus_balance' => 500,
        ]);
        $this->assertAuthenticated();
    }

    public function test_registration_fails_with_wrong_captcha(): void
    {
        $response = $this->post('/register', [
            'name' => 'Иван Иванов',
            'email' => 'ivan2@example.com',
            'phone' => '+7 (900) 123-45-68',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
            'privacy' => '1',
            'captcha' => 'WRONG',
        ]);

        $response->assertSessionHasErrors('captcha');
        $this->assertDatabaseMissing('users', [
            'email' => 'ivan2@example.com',
        ]);
    }
}
