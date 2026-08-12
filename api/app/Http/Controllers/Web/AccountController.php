<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\BonusOperation;
use App\Models\Order;
use App\Models\WishlistItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;

class AccountController extends Controller
{
    public function dashboard()
    {
        $user = Auth::user();

        $ordersCount = $user->orders()->count();
        $wishlistCount = $user->wishlistItems()->count();
        $bonusBalance = $user->bonus_balance ?? 0;

        return view('account.dashboard', compact('ordersCount', 'wishlistCount', 'bonusBalance'));
    }

    public function profile()
    {
        $user = Auth::user();
        $socialProviders = ['yandex' => 'Яндекс', 'vk' => 'ВКонтакте'];
        $linkedProviders = $user->socialAccounts()->pluck('provider')->flip();

        return view('account.profile', compact('user', 'socialProviders', 'linkedProviders'));
    }

    public function updateProfile(Request $request)
    {
        $user = Auth::user();

        $validated = $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:50', Rule::unique('users', 'phone')->ignore($user->id)],
        ]);

        $user->update($validated);

        return redirect()->route('account.profile')->with('success', 'Данные профиля обновлены.');
    }

    public function updatePassword(Request $request)
    {
        $validated = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => ['required', 'string', 'confirmed', Password::defaults()],
        ]);

        $user = Auth::user();

        if (! Hash::check($validated['current_password'], $user->password)) {
            return back()->withErrors(['current_password' => 'Неверный текущий пароль.']);
        }

        $user->update(['password' => Hash::make($validated['password'])]);

        return redirect()->route('account.profile')->with('success', 'Пароль успешно изменён.');
    }

    public function orders(Request $request)
    {
        $user = Auth::user();

        $query = $user->orders();

        if ($request->filled('status') && array_key_exists($request->input('status'), Order::$statuses)) {
            $query->where('status', $request->input('status'));
        }

        if ($request->filled('payment_status') && array_key_exists($request->input('payment_status'), Order::$paymentStatuses)) {
            $query->where('payment_status', $request->input('payment_status'));
        }

        $period = $request->input('period');
        if ($period === 'week') {
            $query->where('created_at', '>=', now()->subWeek());
        } elseif ($period === 'month') {
            $query->where('created_at', '>=', now()->subMonth());
        } elseif ($period === 'year') {
            $query->where('created_at', '>=', now()->subYear());
        }

        $orders = $query->withCount('items')->latest('created_at')->paginate(10)->withQueryString();

        $statuses = Order::$statuses;
        $paymentStatuses = Order::$paymentStatuses;

        return view('account.orders', compact('orders', 'statuses', 'paymentStatuses'));
    }

    public function wishlist()
    {
        $user = Auth::user();

        $items = $user->wishlistItems()
            ->with(['product' => fn ($q) => $q->with('offers.prices')])
            ->latest()
            ->get();

        return view('account.wishlist', compact('items'));
    }

    public function bonuses()
    {
        $user = Auth::user();

        $balance = $user->bonus_balance ?? 0;
        $operations = BonusOperation::where('user_id', $user->id)
            ->latest('created_at')
            ->paginate(20);

        return view('account.bonuses', compact('balance', 'operations'));
    }
}
