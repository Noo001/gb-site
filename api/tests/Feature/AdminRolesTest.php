<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class AdminRolesTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesAndPermissionsSeeder', '--force' => true]);
    }

    public function test_superadmin_can_access_users_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('superadmin');

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_manager_cannot_access_users_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $this->actingAs($user)
            ->get('/admin/users')
            ->assertForbidden();
    }

    public function test_user_without_role_cannot_access_admin(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/admin')
            ->assertForbidden();
    }

    public function test_manager_can_access_products_resource(): void
    {
        $user = User::factory()->create();
        $user->assignRole('manager');

        $this->actingAs($user)
            ->get('/admin/products')
            ->assertOk();
    }

    public function test_content_manager_cannot_access_products_create(): void
    {
        $user = User::factory()->create();
        $user->assignRole('content');

        $this->actingAs($user)
            ->get('/admin/products/create')
            ->assertForbidden();
    }
}
