<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function register(Request $request): JsonResponse
    {
        $data = $request->validate(
            [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255', 'unique:users,email'],
                'phone' => ['nullable', 'string', 'max:50', $this->phoneRule()],
                'password' => ['required', 'string', 'min:6', 'confirmed'],
                'privacy' => ['required', 'accepted'],
            ],
            [
                'email.required' => 'Укажите e-mail.',
                'email.email' => 'Введите корректный e-mail.',
                'email.unique' => 'Этот e-mail уже зарегистрирован.',
                'phone.regex' => 'Введите корректный номер телефона.',
                'password.min' => 'Пароль должен содержать не менее 6 символов.',
                'privacy.required' => 'Необходимо согласиться с политикой конфиденциальности.',
                'privacy.accepted' => 'Необходимо согласиться с политикой конфиденциальности.',
            ]
        );

        $phone = isset($data['phone']) ? $this->normalizePhone($data['phone']) : null;

        if ($phone && User::where('phone', $phone)->exists()) {
            throw ValidationException::withMessages([
                'phone' => ['Этот номер телефона уже зарегистрирован.'],
            ]);
        }

        $user = User::create([
            'name' => $data['name'],
            'email' => $data['email'] ?? null,
            'phone' => $phone,
            'password' => Hash::make($data['password']),
        ]);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $this->userResource($user),
            'token' => $token,
        ], 201);
    }

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'login' => ['required', 'string'],
            'password' => ['required', 'string'],
            'remember' => ['boolean'],
        ]);

        $isEmail = filter_var($data['login'], FILTER_VALIDATE_EMAIL);
        $field = $isEmail ? 'email' : 'phone';
        $value = $isEmail ? $data['login'] : $this->normalizePhone($data['login']);

        $user = User::where($field, $value)->first();

        if (! $user || ! Hash::check($data['password'], $user->password)) {
            throw ValidationException::withMessages([
                'login' => ['Неверный логин или пароль.'],
            ]);
        }

        Auth::login($user, $data['remember'] ?? false);

        $this->mergeGuestCart($request, $user);

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => $this->userResource($user),
            'token' => $token,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Выход выполнен.']);
    }

    public function user(Request $request): JsonResponse
    {
        return response()->json([
            'data' => $this->userResource($request->user()),
        ]);
    }

    public function forgotPassword(Request $request): JsonResponse
    {
        $request->validate(['login' => ['required', 'string']]);

        // TODO: реализовать отправку кода/ссылки на email или телефон
        return response()->json([
            'message' => 'Функция восстановления пароля в разработке.',
        ]);
    }

    private function mergeGuestCart(Request $request, User $user): void
    {
        $sessionId = $request->cookie('cart_session_id');
        if (! $sessionId) {
            return;
        }

        $guestItems = CartItem::where('session_id', $sessionId)->whereNull('user_id')->get();

        foreach ($guestItems as $item) {
            $existing = CartItem::where('user_id', $user->id)
                ->where('product_id', $item->product_id)
                ->where('offer_id', $item->offer_id)
                ->first();

            if ($existing) {
                $existing->increment('quantity', $item->quantity);
                $item->delete();
            } else {
                $item->update(['user_id' => $user->id, 'session_id' => null]);
            }
        }
    }

    private function phoneRule(): string
    {
        return 'regex:/^(\+7|7|8)?[\s\-]?\(?[0-9]{3}\)?[\s\-]?[0-9]{3}[\s\-]?[0-9]{2}[\s\-]?[0-9]{2}$/';
    }

    private function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D/', '', $phone);

        if (strlen($digits) === 10) {
            $digits = '7'.$digits;
        }

        if (strlen($digits) === 11 && $digits[0] === '8') {
            $digits[0] = '7';
        }

        return $digits;
    }

    private function userResource(User $user): array
    {
        return [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'phone' => $user->phone,
        ];
    }
}
