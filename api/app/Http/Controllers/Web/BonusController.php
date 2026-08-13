<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BonusTerm;
use App\Models\User;
use App\Services\BonusService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class BonusController extends Controller
{
    public function __construct(private BonusService $bonusService)
    {
    }

    public function index(Request $request): View
    {
        /** @var User $user */
        $user = Auth::user();
        $user->loadCount(['orders']);

        $operations = $user->bonusOperations()
            ->orderByDesc('created_at')
            ->paginate(20);

        $this->bonusService->ensureDefaultSectors();
        $terms = $this->bonusService->currentTerms();
        $needsAccept = $terms && ! $user->accepted_bonus_terms_at;

        return view('account.bonuses', [
            'balance' => $user->bonus_balance,
            'operations' => $operations,
            'terms' => $terms,
            'needsAccept' => $needsAccept,
            'canCollectDaily' => $this->bonusService->canCollectDaily($user),
            'freeSpins' => $user->free_spins_available,
            'freeSpinsEnabled' => $this->bonusService->freeSpinsEnabled(),
            'spinCost' => $this->bonusService->spinCost(),
            'sectors' => $this->bonusService->activeSectors(),
        ]);
    }

    public function acceptTerms(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();
        $terms = $this->bonusService->currentTerms();

        if (! $terms) {
            return back()->withErrors(['terms' => 'Условия программы временно недоступны.']);
        }

        $user->update([
            'accepted_bonus_terms_at' => now(),
            'accepted_bonus_terms_version' => $terms->version,
        ]);

        return back()->with('success', 'Условия бонусной программы приняты.');
    }

    public function daily(Request $request): RedirectResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->accepted_bonus_terms_at) {
            return back()->withErrors(['terms' => 'Сначала нужно принять условия бонусной программы.']);
        }

        try {
            $result = $this->bonusService->collectDaily($user);
            return back()->with('success', "Вы получили {$result['earned']} бонусов. Новый баланс: {$result['new_balance']}.");
        } catch (\Throwable $e) {
            return back()->withErrors(['daily' => $e instanceof \Illuminate\Validation\ValidationException
                ? $e->validator->errors()->first('daily')
                : 'Не удалось собрать бонусы.']);
        }
    }

    public function spin(Request $request): JsonResponse
    {
        /** @var User $user */
        $user = Auth::user();

        if (! $user->accepted_bonus_terms_at) {
            return response()->json(['error' => 'Сначала нужно принять условия бонусной программы.'], 422);
        }

        $useFree = (bool) $request->input('free', false);

        try {
            $result = $this->bonusService->spin($user, $useFree);

            return response()->json([
                'success' => true,
                'sector' => [
                    'id' => $result['sector']->id,
                    'label' => $result['sector']->label,
                    'type' => $result['sector']->type,
                    'value' => $result['sector']->value,
                ],
                'used_free' => $result['used_free'],
                'new_balance' => $result['new_balance'],
                'free_spins_left' => $result['free_spins_left'],
                'message' => $this->spinMessage($result),
            ]);
        } catch (\Throwable $e) {
            return response()->json([
                'error' => $e instanceof \Illuminate\Validation\ValidationException
                    ? $e->validator->errors()->first('spin')
                    : 'Не удалось прокрутить рулетку.',
            ], 422);
        }
    }

    private function spinMessage(array $result): string
    {
        $sector = $result['sector'];

        if ($sector->type === 'bonus') {
            return "Вы выиграли {$sector->value} бонусов!";
        }

        if ($sector->type === 'free_spin') {
            return "Вы выиграли дополнительную бесплатную попытку!";
        }

        if ($sector->type === 'super') {
            return "Поздравляем! Выпал суперприз: {$sector->label}. Свяжемся с вами для выдачи.";
        }

        return "Вы выиграли: {$sector->label}. Свяжемся для выдачи.";
    }
}
