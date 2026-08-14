#!/usr/bin/env python3
"""Generate a signed /e2e/captcha URL on the Beget server."""
import sys
from pathlib import Path

sys.path.insert(0, str(Path(__file__).resolve().parent.parent))
import ssh_exec

php = r"""
echo \Illuminate\Support\Facades\URL::temporarySignedRoute(
    "e2e.captcha",
    now()->addMinutes(5)
);
""".strip()

cmd = f"artisan tinker --execute='{php}'"
exit_code, out, err = ssh_exec.run_command(cmd)

if exit_code != 0:
    print('STDERR:', err, file=sys.stderr)
    sys.exit(exit_code)

url = out.strip()
print(url)
