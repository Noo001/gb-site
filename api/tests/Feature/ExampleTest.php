<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Сайт закрыт парольной заглушкой (PasswordGate) до запуска,
     * поэтому анонимный запрос главной возвращает 403.
     */
    public function test_the_application_returns_password_gate_response(): void
    {
        $response = $this->get('/');

        $response->assertStatus(403);
    }
}
