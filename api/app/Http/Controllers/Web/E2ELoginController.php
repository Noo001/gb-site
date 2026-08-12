<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

class E2ELoginController extends Controller
{
    /**
     * Authenticate a user through a Laravel signed URL.
     *
     * This endpoint is intended only for end-to-end testing. It does nothing
     * unless the request carries a valid signature and has not expired.
     */
    public function __invoke(Request $request)
    {
        if (! $request->hasValidSignature()) {
            abort(403, 'Invalid or expired link.');
        }

        $email = $request->input('email');
        $redirect = $request->input('redirect', route('account.dashboard'));

        if (! filter_var($email, FILTER_VALIDATE_EMAIL)) {
            abort(422, 'Invalid email.');
        }

        $user = User::firstOrCreate(
            ['email' => $email],
            [
                'name' => 'E2E Test User',
                'phone' => '79000000000',
                'password' => Hash::make('e2e-test-password'),
                'bonus_balance' => 500,
            ]
        );

        Auth::login($user);

        return redirect()->to($redirect);
    }
}
