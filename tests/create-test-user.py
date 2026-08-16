import sys
from pathlib import Path
import time

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))
import ssh_exec

EMAIL = 'e2e-wheel@gbsale.ru'
PASSWORD = 'e2e-test-password'
PHONE = f'79{int(time.time()) % 100000000:08d}'

php = f"""
$user = \\App\\Models\\User::firstOrCreate(
    ["email" => "{EMAIL}"],
    [
        "name" => "E2E Wheel Test",
        "phone" => "{PHONE}",
        "password" => bcrypt("{PASSWORD}"),
        "bonus_balance" => 500,
        "phone_verified_at" => now(),
        "accepted_bonus_terms_at" => now(),
        "accepted_bonus_terms_version" => 1,
    ]
);
$user->update([
    "password" => bcrypt("{PASSWORD}"),
    "bonus_balance" => 500,
    "accepted_bonus_terms_at" => now(),
    "accepted_bonus_terms_version" => 1,
    "free_spins_available" => 0,
    "last_daily_bonus_at" => null,
    "last_free_spin_at" => null,
    "daily_streak_count" => 0,
]);
echo $user->email;
""".strip()

cmd = f"artisan tinker --execute='{php}'"
exit_code, out, err = ssh_exec.run_command(cmd)
if exit_code != 0:
    print('STDERR:', err, file=sys.stderr)
    sys.exit(exit_code)
print(out.strip())
