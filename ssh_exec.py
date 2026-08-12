#!/usr/bin/env python3
"""SSH helper for Beget server. Reads credentials from .env.secrets."""
import paramiko
import os
import re
import sys


def read_secrets(path=".env.secrets"):
    secrets = {}
    with open(path, "r", encoding="utf-8") as f:
        for line in f:
            line = line.strip()
            if not line or line.startswith("#"):
                continue
            if "=" in line:
                key, value = line.split("=", 1)
                secrets[key.strip()] = value.strip()
    return secrets


def run_command(cmd, secrets=None):
    if secrets is None:
        secrets = read_secrets()
    host = secrets.get("SSH_HOST", "gbsale.ru")
    login = secrets.get("SSH_LOGIN")
    password = secrets.get("SSH_PASSWORD")
    project_path = secrets.get("PROJECT_PATH", "/home/m/mastak97/gbsale.ru")
    php_cli = secrets.get("PHP_CLI", "/usr/local/bin/php8.4")

    client = paramiko.SSHClient()
    client.set_missing_host_key_policy(paramiko.AutoAddPolicy())
    client.connect(host, username=login, password=password, timeout=30)

    full_cmd = f"cd {project_path}/api && {php_cli} {cmd}"
    stdin, stdout, stderr = client.exec_command(full_cmd, timeout=300)
    out = stdout.read().decode("utf-8", errors="replace")
    err = stderr.read().decode("utf-8", errors="replace")
    exit_code = stdout.channel.recv_exit_status()
    client.close()
    return exit_code, out, err


if __name__ == "__main__":
    cmd = " ".join(sys.argv[1:]) if len(sys.argv) > 1 else "artisan --version"
    exit_code, out, err = run_command(cmd)
    print("--- STDOUT ---")
    print(out)
    if err:
        print("--- STDERR ---")
        print(err)
    print(f"--- EXIT CODE: {exit_code} ---")
    sys.exit(exit_code)
