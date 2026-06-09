#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
cd "$ROOT"

echo "==> ALWAAB deploy started"

bash deploy/generate-nginx-config.sh
bash deploy/generate-website-config.sh

cd alwaab-erp

if [[ ! -f .env ]]; then
  echo "ERROR: alwaab-erp/.env missing on server"
  exit 1
fi

composer install --no-dev --optimize-autoloader --no-interaction
npm ci
npm run build

php artisan migrate --force
php artisan storage:link --force 2>/dev/null || true
php artisan config:cache
php artisan route:cache
php artisan view:cache

cd "$ROOT"
docker compose -f docker-compose.prod.yml up -d --build

echo "==> Deploy complete"
docker compose -f docker-compose.prod.yml ps
