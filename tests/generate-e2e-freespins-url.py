#!/usr/bin/env python3
"""Generate a signed /e2e/login URL with free spins on the Beget server."""
import sys
import os
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))
import ssh_exec

EMAIL = 'e2e-free-2026-08-12@gbsale.ru'
REDIRECT = '/account/bonuses'

php = f"""
$user = \\App\\Models\\User::firstOrCreate(
    ["email" => "{EMAIL}"],
    [
        "name" => "E2E Free Spins",
        "phone" => "79000000001",
        "password" => bcrypt("e2e-test-password"),
        "bonus_balance" => 500,
        "phone_verified_at" => now(),
        "accepted_bonus_terms_at" => now(),
        "accepted_bonus_terms_version" => 1,
    ]
);
$user->update([
    "bonus_balance" => 500,
    "accepted_bonus_terms_at" => now(),
    "accepted_bonus_terms_version" => 1,
    "free_spins_available" => 3,
    "last_daily_bonus_at" => null,
    "last_free_spin_at" => null,
    "daily_streak_count" => 0,
]);
echo \\Illuminate\\Support\\Facades\\URL::temporarySignedRoute(
    "e2e.login",
    now()->addMinutes(5),
    ["email" => $user->email, "redirect" => "{REDIRECT}"]
);
""".strip()

cmd = f"artisan tinker --execute='{php}'"
exit_code, out, err = ssh_exec.run_command(cmd)

if exit_code != 0:
    print('STDERR:', err, file=sys.stderr)
    sys.exit(exit_code)

url = out.strip()
print(url)
