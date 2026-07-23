<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\SocialAccount;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Laravel\Socialite\Facades\Socialite;

class SocialAuthController extends Controller
{
    public function redirect(string $provider)
    {
        if (! in_array($provider, ['yandex', 'vkontakte', 'vk'])) {
            abort(404);
        }

        $driver = $provider === 'vk' ? 'vkontakte' : $provider;

        return Socialite::driver($driver)->stateless()->redirect();
    }

    public function callback(string $provider): JsonResponse
    {
        if (! in_array($provider, ['yandex', 'vkontakte', 'vk'])) {
            return response()->json(['message' => 'Неизвестный провайдер'], 400);
        }

        $driver = $provider === 'vk' ? 'vkontakte' : $provider;

        try {
            $socialUser = Socialite::driver($driver)->stateless()->user();
        } catch (\Throwable $e) {
            return response()->json(['message' => 'Ошибка авторизации: '.$e->getMessage()], 400);
        }

        $providerId = (string) $socialUser->getId();

        $socialAccount = SocialAccount::where('provider', $provider)
            ->where('provider_id', $providerId)
            ->first();

        if ($socialAccount) {
            $user = $socialAccount->user;
        } else {
            $email = $socialUser->getEmail();
            $user = $email ? User::where('email', $email)->first() : null;

            if (! $user) {
                $user = User::create([
                    'name' => $socialUser->getName() ?? $socialUser->getNickname() ?? 'Пользователь',
                    'email' => $email,
                    'password' => bcrypt(uniqid()),
                ]);
            }

            $user->socialAccounts()->create([
                'provider' => $provider,
                'provider_id' => $providerId,
                'avatar' => $socialUser->getAvatar(),
                'payload' => (array) $socialUser->user,
            ]);
        }

        $token = $user->createToken('api')->plainTextToken;

        return response()->json([
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'phone' => $user->phone,
            ],
            'token' => $token,
        ]);
    }
}
