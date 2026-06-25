#!/usr/bin/env python3
import os
import sys
import json
import hmac
import hashlib
import subprocess
import threading
from datetime import datetime, timezone
from http.server import BaseHTTPRequestHandler, HTTPServer

REPO_DIR = os.environ.get("REPO_DIR", "/repo")
ENV_FILE = os.environ.get("ENV_FILE", "/repo/.env.prod")
COMPOSE_FILE = os.environ.get("COMPOSE_FILE", "/repo/docker-compose.caddy.yml")
WEBHOOK_SECRET = os.environ.get("WEBHOOK_SECRET", "")
DEPLOY_TOKEN = os.environ.get("DEPLOY_TOKEN", "")
STATUS_FILE = os.environ.get("STATUS_FILE", "/var/lib/deploy/status.json")
LOG_FILE = os.environ.get("LOG_FILE", "/var/lib/deploy/deploy.log")
PROJECT_NAME = os.environ.get("PROJECT_NAME", "gb-site")
BRANCH = os.environ.get("BRANCH", "main")

os.makedirs(os.path.dirname(STATUS_FILE), exist_ok=True)


def now():
    return datetime.now(timezone.utc).isoformat()


def write_status(status, message=""):
    data = {
        "last_update": now(),
        "status": status,
        "message": message,
    }
    with open(STATUS_FILE, "w", encoding="utf-8") as f:
        json.dump(data, f, ensure_ascii=False, indent=2)


def log(line):
    with open(LOG_FILE, "a", encoding="utf-8") as f:
        f.write(f"[{now()}] {line}\n")


def run_cmd(cmd, cwd=None, timeout=600):
    log(f"Running: {' '.join(cmd)}")
    result = subprocess.run(
        cmd,
        cwd=cwd,
        stdout=subprocess.PIPE,
        stderr=subprocess.STDOUT,
        text=True,
        timeout=timeout,
    )
    log(result.stdout)
    if result.returncode != 0:
        raise RuntimeError(f"Command failed with code {result.returncode}")
    return result.stdout


def run_deploy():
    write_status("running", "Deploy started")
    log("=== Deploy started ===")
    try:
        log("Configuring git safe directory")
        run_cmd(["git", "config", "--global", "--add", "safe.directory", REPO_DIR])

        log("Stashing local changes")
        subprocess.run(
            ["git", "-C", REPO_DIR, "stash", "push", "-u", "-m", "autodeploy-stash"],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True,
        )

        log("Pulling latest code")
        run_cmd(["git", "-C", REPO_DIR, "pull", "origin", BRANCH])

        log("Restoring stashed local changes")
        subprocess.run(
            ["git", "-C", REPO_DIR, "stash", "pop"],
            stdout=subprocess.PIPE,
            stderr=subprocess.STDOUT,
            text=True,
        )

        log("Building and restarting services")
        run_cmd([
            "docker", "compose",
            "-p", PROJECT_NAME,
            "-f", COMPOSE_FILE,
            "--env-file", ENV_FILE,
            "up", "-d", "--build",
        ])

        msg = "Deploy finished successfully"
        log(msg)
        write_status("success", msg)
    except Exception as e:
        msg = f"Deploy failed: {e}"
        log(msg)
        write_status("failed", msg)


class Handler(BaseHTTPRequestHandler):
    def _json(self, code, data):
        body = json.dumps(data, ensure_ascii=False).encode("utf-8")
        self.send_response(code)
        self.send_header("Content-Type", "application/json; charset=utf-8")
        self.send_header("Content-Length", str(len(body)))
        self.end_headers()
        self.wfile.write(body)

    def do_GET(self):
        if self.path.startswith("/deploy-status"):
            try:
                with open(STATUS_FILE, "r", encoding="utf-8") as f:
                    status = json.load(f)
            except Exception:
                status = {"status": "unknown", "message": "No status yet"}
            self._json(200, status)
            return
        self._json(404, {"error": "Not found"})

    def do_POST(self):
        if not self.path.startswith("/deploy-hook"):
            self._json(404, {"error": "Not found"})
            return

        content_length = int(self.headers.get("Content-Length", 0))
        body = self.rfile.read(content_length)

        # Token via query string (generic trigger)
        query = self.path.split("?", 1)[1] if "?" in self.path else ""
        params = {}
        for part in query.split("&"):
            if "=" in part:
                k, v = part.split("=", 1)
                params[k] = v
        token = params.get("token", "")
        if DEPLOY_TOKEN and token != DEPLOY_TOKEN:
            self._json(403, {"error": "Invalid token"})
            return

        # GitHub signature verification (only if header present)
        sig_header = self.headers.get("X-Hub-Signature-256", "")
        if WEBHOOK_SECRET and sig_header:
            expected = "sha256=" + hmac.new(
                WEBHOOK_SECRET.encode(), body, hashlib.sha256
            ).hexdigest()
            if not hmac.compare_digest(sig_header, expected):
                self._json(403, {"error": "Invalid signature"})
                return

        threading.Thread(target=run_deploy, daemon=True).start()
        self._json(202, {"status": "accepted", "check": "/deploy-status"})


if __name__ == "__main__":
    port = int(os.environ.get("PORT", "9000"))
    server = HTTPServer(("0.0.0.0", port), Handler)
    log(f"Webhook server listening on port {port}")
    try:
        server.serve_forever()
    except KeyboardInterrupt:
        sys.exit(0)
