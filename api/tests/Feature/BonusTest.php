<?php

namespace Tests\Feature;

use App\Models\BonusTerm;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BonusTest extends TestCase
{
    use RefreshDatabase;

    public function test_bonuses_page_requires_authentication(): void
    {
        $response = $this->get(route('account.bonuses'));

        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_sees_bonus_page(): void
    {
        $user = User::factory()->create(['bonus_balance' => 100]);
        BonusTerm::factory()->create([
            'version' => 1,
            'content' => 'Условия бонусной программы.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)->get(route('account.bonuses'));

        $response->assertStatus(200);
        $response->assertSee('Бонусная программа');
        $response->assertSee('100');
        $response->assertSee('Баланс бонусов');
        $response->assertSee('Условия бонусной программы');
    }

    public function test_user_can_accept_terms(): void
    {
        $user = User::factory()->create();
        $terms = BonusTerm::factory()->create([
            'version' => 2,
            'content' => 'Условия.',
            'is_active' => true,
        ]);

        $response = $this->actingAs($user)
            ->post(route('account.bonuses.terms'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertNotNull($user->accepted_bonus_terms_at);
        $this->assertEquals($terms->version, $user->accepted_bonus_terms_version);
    }

    public function test_daily_collection_requires_accepted_terms(): void
    {
        $user = User::factory()->create();
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->post(route('account.bonuses.daily'));

        $response->assertRedirect();
        $response->assertSessionHasErrors('terms');
    }

    public function test_daily_collection_awards_bonuses(): void
    {
        $user = User::factory()->create([
            'accepted_bonus_terms_at' => now(),
            'accepted_bonus_terms_version' => 1,
        ]);
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->post(route('account.bonuses.daily'));

        $response->assertRedirect();
        $response->assertSessionHas('success');

        $user->refresh();
        $this->assertGreaterThan(0, $user->bonus_balance);
        $this->assertEquals(1, $user->free_spins_available);
        $this->assertNotNull($user->last_daily_bonus_at);
    }

    public function test_spin_requires_accepted_terms(): void
    {
        $user = User::factory()->create(['bonus_balance' => 100]);
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson(route('account.bonuses.spin'), ['free' => false]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Сначала нужно принять условия бонусной программы.');
    }

    public function test_paid_spin_works_when_balance_is_sufficient(): void
    {
        $user = User::factory()->create([
            'bonus_balance' => 50,
            'accepted_bonus_terms_at' => now(),
            'accepted_bonus_terms_version' => 1,
        ]);
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson(route('account.bonuses.spin'), ['free' => false]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonStructure([
            'success',
            'sector' => ['id', 'label', 'type', 'value'],
            'used_free',
            'new_balance',
            'free_spins_left',
            'message',
        ]);

        $user->refresh();
        // Баланс не может быть ниже стоимости прокрутки (10) с учётом возможного выигрыша.
        $this->assertGreaterThanOrEqual(40, $user->bonus_balance);
    }

    public function test_free_spin_works_when_available(): void
    {
        $user = User::factory()->create([
            'bonus_balance' => 0,
            'free_spins_available' => 1,
            'accepted_bonus_terms_at' => now(),
            'accepted_bonus_terms_version' => 1,
        ]);
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson(route('account.bonuses.spin'), ['free' => true]);

        $response->assertOk();
        $response->assertJsonPath('success', true);
        $response->assertJsonPath('used_free', true);

        $user->refresh();
        // Бесплатная прокрутка потрачена, но может выпасть сектор "+бесплатная попытка".
        $this->assertGreaterThanOrEqual(0, $user->free_spins_available);
    }

    public function test_spin_fails_when_balance_is_insufficient(): void
    {
        $user = User::factory()->create([
            'bonus_balance' => 0,
            'accepted_bonus_terms_at' => now(),
            'accepted_bonus_terms_version' => 1,
        ]);
        BonusTerm::factory()->create(['is_active' => true]);

        $response = $this->actingAs($user)
            ->postJson(route('account.bonuses.spin'), ['free' => false]);

        $response->assertStatus(422);
        $response->assertJsonPath('error', 'Недостаточно бонусов для прокрутки.');
    }
}
