<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Http\Controllers\Api\AuthController as ApiAuthController;
use App\Http\Middleware\PasswordGate;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class AuthController extends Controller
{
    public function loginForm()  { return view('auth.login'); }
    public function registerForm() { return view('auth.register'); }

    public function login(Request $request)
    {
        $request->merge(['login' => $request->input('email')]);
        $response = app(ApiAuthController::class)->login($request);
        if ($response->status() >= 400) {
            return back()->withInput()->withErrors(['login' => 'Неверный логин или пароль.']);
        }
        $data = $response->getData(true);
        Auth::loginUsingId($data['user']['id']);
        return redirect()->intended(route('home'));
    }

    public function register(Request $request)
    {
        $request->merge(['privacy' => '1']);
        $response = app(ApiAuthController::class)->register($request);
        if ($response->status() >= 400) {
            return back()->withInput()->withErrors(['register' => 'Не удалось зарегистрироваться.']);
        }
        $data = $response->getData(true);
        Auth::loginUsingId($data['user']['id']);
        return redirect()->route('home');
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
        if ($request->input('password') === PasswordGate::PASSWORD) {
            if ($request->expectsJson()) {
                return response()->json(['success' => true]);
            }
            // Legacy server-side session fallback (kept for non-JS clients).
            session(['site_access_granted' => true]);
            return redirect()->intended(route('home'));
        }

        if ($request->expectsJson()) {
            return response()->json(['success' => false, 'message' => 'Неверный пароль.'], 422);
        }

        return back()->withErrors(['password' => 'Неверный пароль.']);
    }
}
