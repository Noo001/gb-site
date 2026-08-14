<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Middleware\PasswordGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;

class AuthController extends Controller
{
    public function loginForm()
    {
        return view('auth.login');
    }

    public function registerForm()
    {
        return view('auth.register');
    }

    public function login(Request $request)
    {
        try {
            $response = app(ApiAuthController::class)->login($request);
            $data = $response->getData(true);
            Auth::loginUsingId($data['user']['id'], $request->boolean('remember'));

            return redirect()->intended(route('home'));
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('Web login failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withInput()->withErrors(['login' => 'Не удалось войти. Попробуйте ещё раз позже.']);
        }
    }

    public function register(Request $request)
    {
        try {
            $response = app(ApiAuthController::class)->register($request);
            $data = $response->getData(true);
            Auth::loginUsingId($data['user']['id']);

            return redirect()->route('home');
        } catch (ValidationException $e) {
            return back()->withInput()->withErrors($e->errors());
        } catch (\Throwable $e) {
            Log::error('Web registration failed', ['error' => $e->getMessage(), 'trace' => $e->getTraceAsString()]);

            return back()->withInput()->withErrors(['register' => 'Не удалось зарегистрироваться. Попробуйте ещё раз позже.']);
        }
    }

    public function logout(Request $request)
    {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function accessCheck(Request $request)
    {
        // Заглушка отключена: сразу выдаём «доступ разрешён» и куку, чтобы старые
        // закладки / формы не ломались. См. PasswordGate.
        $cookie = cookie(PasswordGate::COOKIE_NAME, PasswordGate::COOKIE_VALUE, 60 * 24 * 30, '/', null, false, false, false, 'Lax');

        if ($request->expectsJson()) {
            return response()->json(['success' => true])->withCookie($cookie);
        }

        return redirect()->intended(route('home'))->withCookie($cookie);
    }
}
