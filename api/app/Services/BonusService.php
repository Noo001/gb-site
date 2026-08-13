<?php

namespace App\Services;

use App\Models\BonusOperation;
use App\Models\BonusTerm;
use App\Models\RouletteSector;
use App\Models\RouletteSpin;
use App\Models\Setting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class BonusService
{
    private const TIMEZONE = 'Europe/Moscow';

    // --- настройки (fallback) ---
    private function setting(string $key, mixed $default): mixed
    {
        return Setting::get($key, $default);
    }

    public function registrationBonus(): int
    {
        return (int) $this->setting('bonus_registration_amount', 500);
    }

    public function dailyAmount(): int
    {
        return (int) $this->setting('bonus_daily_amount', 10);
    }

    public function streakAmount(): int
    {
        return (int) $this->setting('bonus_streak_amount', 30);
    }

    public function spinCost(): int
    {
        return (int) $this->setting('bonus_spin_cost', 10);
    }

    public function purchasePercent(): float
    {
        return (float) $this->setting('bonus_purchase_percent', 0.25);
    }

    public function timezone(): string
    {
        return self::TIMEZONE;
    }

    public function today(): Carbon
    {
        return now()->timezone(self::TIMEZONE)->startOfDay();
    }

    // --- основная операция ---
    public function addOperation(User $user, string $type, int $amount, string $description = '', ?array $payload = null, ?object $related = null): BonusOperation
    {
        return DB::transaction(function () use ($user, $type, $amount, $description, $payload, $related) {
            $user->refresh();
            $newBalance = $user->bonus_balance + $amount;

            $operation = BonusOperation::create([
                'user_id' => $user->id,
                'type' => $type,
                'amount' => $amount,
                'balance_after' => $newBalance,
                'description' => $description,
                'payload' => $payload,
                'related_type' => $related ? get_class($related) : null,
                'related_id' => $related ? $related->id : null,
            ]);

            $user->update(['bonus_balance' => $newBalance]);

            return $operation;
        });
    }

    // --- ежедневный сбор ---
    public function canCollectDaily(User $user): bool
    {
        if (! $user->last_daily_bonus_at) {
            return true;
        }

        return $user->last_daily_bonus_at->timezone(self::TIMEZONE)->startOfDay()->lt($this->today());
    }

    public function freeSpinsEnabled(): bool
    {
        return (bool) $this->setting('bonus_free_spins_enabled', true);
    }

    public function collectDaily(User $user): array
    {
        return DB::transaction(function () use ($user) {
            $user->refresh();

            if (! $this->canCollectDaily($user)) {
                throw ValidationException::withMessages([
                    'daily' => ['Бонусы сегодня уже собраны. Возвращайтесь завтра.'],
                ]);
            }

            $today = $this->today();
            $yesterday = $today->copy()->subDay();

            if ($user->last_daily_bonus_at && $user->last_daily_bonus_at->timezone(self::TIMEZONE)->startOfDay()->equalTo($yesterday)) {
                $user->daily_streak_count += 1;
            } else {
                $user->daily_streak_count = 1;
            }

            $earned = $this->dailyAmount();
            $description = "Ежедневный сбор: +{$earned} бонусов";

            $awardedStreak = false;
            if ($user->daily_streak_count >= 7) {
                $streakBonus = $this->streakAmount();
                $earned += $streakBonus;
                $description .= ", серия 7 дней: +{$streakBonus} бонусов";
                $user->daily_streak_count = 0;
                $awardedStreak = true;
            }

            $user->last_daily_bonus_at = now();
            if ($this->freeSpinsEnabled()) {
                $user->free_spins_available += 1; // бесплатная прокрутка за сбор
            }
            $user->save();

            $this->addOperation($user, 'daily', $earned, $description);

            return [
                'earned' => $earned,
                'streak_awarded' => $awardedStreak,
                'new_balance' => $user->bonus_balance,
            ];
        });
    }

    // --- рулетка ---
    public function ensureDefaultSectors(): void
    {
        if (RouletteSector::where('is_active', true)->exists()) {
            return;
        }

        $defaults = [
            ['label' => '5 бонусов', 'type' => 'bonus', 'value' => 5, 'probability_weight' => 25, 'sort' => 1],
            ['label' => '10 бонусов', 'type' => 'bonus', 'value' => 10, 'probability_weight' => 20, 'sort' => 2],
            ['label' => '20 бонусов', 'type' => 'bonus', 'value' => 20, 'probability_weight' => 15, 'sort' => 3],
            ['label' => '50 бонусов', 'type' => 'bonus', 'value' => 50, 'probability_weight' => 8, 'sort' => 4],
            ['label' => '100 бонусов', 'type' => 'bonus', 'value' => 100, 'probability_weight' => 4, 'sort' => 5],
            ['label' => 'Бесплатная попытка', 'type' => 'free_spin', 'value' => 1, 'probability_weight' => 10, 'sort' => 6],
            ['label' => 'Чистка динамиков', 'type' => 'service', 'value' => 1, 'probability_weight' => 8, 'sort' => 7],
            ['label' => 'Установка стекла', 'type' => 'service', 'value' => 1, 'probability_weight' => 6, 'sort' => 8],
            ['label' => 'Установка 1 приложения', 'type' => 'service', 'value' => 1, 'probability_weight' => 6, 'sort' => 9],
            ['label' => 'Суперприз', 'type' => 'super', 'value' => 1, 'probability_weight' => 1, 'sort' => 10],
        ];

        foreach ($defaults as $sector) {
            RouletteSector::create($sector);
        }
    }

    public function activeSectors()
    {
        return RouletteSector::active()->get();
    }

    public function canSpinFree(User $user): bool
    {
        return $this->freeSpinsEnabled() && $user->free_spins_available > 0;
    }

    public function spin(User $user, bool $useFree = false): array
    {
        $this->ensureDefaultSectors();

        return DB::transaction(function () use ($user, $useFree) {
            $user->refresh();

            $cost = $this->spinCost();

            if ($useFree) {
                if (! $this->freeSpinsEnabled()) {
                    throw ValidationException::withMessages([
                        'spin' => ['Бесплатные прокрутки временно отключены.'],
                    ]);
                }
                if ($user->free_spins_available <= 0) {
                    throw ValidationException::withMessages([
                        'spin' => ['Бесплатных попыток нет.'],
                    ]);
                }
            } else {
                if ($user->bonus_balance < $cost) {
                    throw ValidationException::withMessages([
                        'spin' => ['Недостаточно бонусов для прокрутки.'],
                    ]);
                }
            }

            $sectors = $this->activeSectors();
            if ($sectors->isEmpty()) {
                throw ValidationException::withMessages([
                    'spin' => ['Рулетка временно недоступна.'],
                ]);
            }

            $sector = $this->pickSector($sectors);

            $spinCost = $useFree ? 0 : $cost;
            if (! $useFree) {
                $this->addOperation($user, 'spin_cost', -$cost, 'Платная прокрутка рулетки');
            } else {
                $user->free_spins_available -= 1;
            }

            $result = $this->awardSector($user, $sector);

            $spin = RouletteSpin::create([
                'user_id' => $user->id,
                'sector_id' => $sector->id,
                'is_free' => $useFree,
                'cost_bonus' => $spinCost,
                'status' => 'awarded',
                'result_payload' => $result,
            ]);

            $user->last_free_spin_at = now();
            $user->save();

            return [
                'sector' => $sector,
                'spin' => $spin,
                'result' => $result,
                'used_free' => $useFree,
                'new_balance' => $user->bonus_balance,
                'free_spins_left' => $user->free_spins_available,
            ];
        });
    }

    private function pickSector($sectors): RouletteSector
    {
        $totalWeight = $sectors->sum('probability_weight');
        $random = mt_rand(1, $totalWeight);
        $current = 0;

        foreach ($sectors as $sector) {
            $current += $sector->probability_weight;
            if ($random <= $current) {
                return $sector;
            }
        }

        return $sectors->last();
    }

    private function awardSector(User $user, RouletteSector $sector): array
    {
        switch ($sector->type) {
            case 'bonus':
                $this->addOperation($user, 'roulette', $sector->value, "Рулетка: {$sector->label}", ['sector_id' => $sector->id]);
                return ['type' => 'bonus', 'value' => $sector->value];

            case 'free_spin':
                $user->free_spins_available += $sector->value;
                $user->save();
                $this->addOperation($user, 'roulette', 0, "Рулетка: {$sector->label}", ['sector_id' => $sector->id, 'free_spins' => $sector->value]);
                return ['type' => 'free_spin', 'value' => $sector->value];

            case 'service':
            case 'material':
            case 'super':
                $this->addOperation($user, 'roulette', 0, "Рулетка: {$sector->label}", ['sector_id' => $sector->id]);
                return ['type' => $sector->type, 'label' => $sector->label, 'status' => 'awarded'];

            default:
                return ['type' => 'unknown'];
        }
    }

    // --- покупка ---
    public function calculatePurchaseBonus(float $total): int
    {
        return (int) floor($total * ($this->purchasePercent() / 100));
    }

    public function awardPurchaseBonus(User $user, float $total, object $order): int
    {
        $amount = $this->calculatePurchaseBonus($total);

        if ($amount <= 0) {
            return 0;
        }

        $this->addOperation($user, 'purchase_pending', $amount, "Бонусы за заказ #{$order->id} (будут доступны через 7 дней)", ['order_id' => $order->id], $order);

        return $amount;
    }

    // --- условия ---
    public function currentTerms(): ?BonusTerm
    {
        return BonusTerm::current();
    }
}
