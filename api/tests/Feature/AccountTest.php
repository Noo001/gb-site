<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AccountTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_requires_authentication(): void
    {
        $response = $this->get(route('account.dashboard'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_dashboard_with_data(): void
    {
        $user = User::factory()->create([
            'name' => 'Иван Иванов',
            'bonus_balance' => 1250,
        ]);

        $response = $this->actingAs($user)->get(route('account.dashboard'));

        $response->assertStatus(200);
        $response->assertSee('Иван Иванов');
        $response->assertSee('1 250 ₽');
        $response->assertSee('Личный кабинет');
    }

    public function test_profile_update_works(): void
    {
        $user = User::factory()->create([
            'name' => 'Старый Имя',
            'email' => 'old@example.com',
            'phone' => '79001112233',
        ]);

        $response = $this->actingAs($user)
            ->post(route('account.profile.update'), [
                'name' => 'Новое Имя',
                'email' => 'new@example.com',
                'phone' => '79001112244',
            ]);

        $response->assertRedirect(route('account.profile'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'name' => 'Новое Имя',
            'email' => 'new@example.com',
            'phone' => '79001112244',
        ]);
    }

    public function test_password_update_works_with_valid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)
            ->post(route('account.password.update'), [
                'current_password' => 'old-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('account.profile'));
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertTrue(Hash::check('new-password', $user->password));
    }

    public function test_password_update_fails_with_invalid_current_password(): void
    {
        $user = User::factory()->create([
            'password' => Hash::make('old-password'),
        ]);

        $response = $this->actingAs($user)
            ->from(route('account.profile'))
            ->post(route('account.password.update'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ]);

        $response->assertRedirect(route('account.profile'));
        $response->assertSessionHasErrors('current_password');
    }

    public function test_orders_page_shows_user_orders(): void
    {
        $user = User::factory()->create();
        Order::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? '79000000000',
            'status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'total' => 9990,
        ]);

        $response = $this->actingAs($user)->get(route('account.orders'));

        $response->assertStatus(200);
        $response->assertSee('Заказ №');
        $response->assertSee('9 990 ₽');
    }

    public function test_orders_filters_work(): void
    {
        $user = User::factory()->create();
        Order::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? '79000000000',
            'status' => Order::STATUS_PENDING,
            'payment_status' => Order::PAYMENT_PENDING,
            'total' => 1000,
            'created_at' => now()->subMonth(2),
        ]);
        Order::create([
            'user_id' => $user->id,
            'customer_name' => $user->name,
            'customer_phone' => $user->phone ?? '79000000000',
            'status' => Order::STATUS_COMPLETED,
            'payment_status' => Order::PAYMENT_PAID,
            'total' => 2000,
            'created_at' => now()->subDay(),
        ]);

        $response = $this->actingAs($user)
            ->get(route('account.orders', ['status' => Order::STATUS_COMPLETED, 'period' => 'week']));

        $response->assertStatus(200);
        $response->assertSee('2 000 ₽');
        $response->assertDontSee('1 000 ₽');
    }
}
