<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Парольная заглушка снята: главная открыта.
     */
    public function test_home_page_is_public(): void
    {
        $response = $this->get('/');

        $response->assertStatus(200);
        $response->assertSee('Gadget');
    }
}
