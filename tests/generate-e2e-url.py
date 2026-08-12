#!/usr/bin/env python3
"""Generate a signed /e2e/login URL on the Beget server."""
import sys
import os
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))
import ssh_exec

EMAIL = 'e2e-2026-08-12@gbsale.ru'
REDIRECT = '/account/bonuses'

php = f"""
$user = \\App\\Models\\User::firstOrCreate(
    ["email" => "{EMAIL}"],
    [
        "name" => "E2E Test",
        "phone" => "79000000000",
        "password" => bcrypt("e2e-test-password"),
        "bonus_balance" => 500,
        "phone_verified_at" => now(),
        "accepted_bonus_terms_at" => now(),
        "accepted_bonus_terms_version" => 1,
    ]
);
$user->update(["bonus_balance" => 500, "accepted_bonus_terms_at" => now(), "accepted_bonus_terms_version" => 1]);
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
