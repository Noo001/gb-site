#!/bin/bash
set -e

# Wait for /mnt/c to be available
for i in $(seq 1 60); do
  if mountpoint -q /mnt/c; then
    echo '/mnt/c is mounted'
    break
  fi
  sleep 2
done

# Wait for Docker to be ready
for i in $(seq 1 60); do
  if /usr/bin/docker info >/dev/null 2>&1; then
    echo 'Docker is ready'
    break
  fi
  sleep 2
done

/usr/bin/docker network create shared-network 2>/dev/null || true

# Load central environment variables (DOMAIN, SERVER_IP, etc.)
if [ -f /mnt/c/up/server/proxy/.env ]; then
  set -a
  source /mnt/c/up/server/proxy/.env
  set +a
fi

cd /mnt/c/up/server/projects/gb-site
/usr/bin/docker compose -f docker-compose.caddy.yml --env-file .env.prod up -d --build

# Wait for gb-api to be ready
echo 'Waiting for gb-api to be ready...'
for i in $(seq 1 120); do
  if /usr/bin/docker exec gb-nginx-api wget -qO- --timeout=2 http://127.0.0.1/api/categories >/dev/null 2>&1; then
    echo 'gb-api is ready'
    break
  fi
  sleep 5
done

# Wait for gb-frontend to be ready
echo 'Waiting for gb-frontend to be ready...'
for i in $(seq 1 60); do
  if /usr/bin/docker exec caddy-proxy wget -qO- --timeout=2 http://gb-frontend:3000/ >/dev/null 2>&1; then
    echo 'gb-frontend is ready'
    break
  fi
  sleep 2
done

cd /mnt/c/up/server/proxy
/usr/bin/docker compose up -d
