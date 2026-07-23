#!/bin/sh
set -e

# Deploy GADGET·BAR on a local server using Docker Compose + Traefik.
# Assumes .env.prod is configured.

ENV_FILE="${ENV_FILE:-.env.prod}"
COMPOSE_FILES="-f docker-compose.traefik.yml -f docker-compose.prod.yml"

if [ ! -f "$ENV_FILE" ]; then
    echo "Environment file not found: $ENV_FILE"
    echo "Copy and edit: cp .env.prod $ENV_FILE"
    exit 1
fi

# Ensure shared Traefik network exists
if ! docker network inspect traefik-public >/dev/null 2>&1; then
    echo "Creating shared network: traefik-public"
    docker network create traefik-public
fi

echo "Building images..."
docker compose $COMPOSE_FILES --env-file "$ENV_FILE" build

echo "Starting services..."
docker compose $COMPOSE_FILES --env-file "$ENV_FILE" up -d

echo ""
echo "Deployment complete."
echo "Add the following line to /etc/hosts (or DNS) for local testing:"
echo "  127.0.0.1  gadget-bar.local s3.gadget-bar.local console.gadget-bar.local"
echo ""
echo "Useful commands:"
echo "  docker compose $COMPOSE_FILES --env-file $ENV_FILE logs -f"
echo "  docker compose -f docker-compose.prod.yml --env-file $ENV_FILE exec api php artisan import:images --type=products"
