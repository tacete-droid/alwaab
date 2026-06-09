#!/usr/bin/env bash
set -euo pipefail

# First-time server setup for ALWAAB System
# Usage: bash deploy/setup-server.sh

REPO_URL="${REPO_URL:-https://github.com/YOUR_ORG/alwaab-system.git}"
DEPLOY_PATH="${DEPLOY_PATH:-/var/www/alwaab-system}"

echo "==> Installing Docker (if missing)..."
if ! command -v docker &>/dev/null; then
  curl -fsSL https://get.docker.com | sh
  sudo usermod -aG docker "$USER"
  echo "Log out and back in for docker group, then re-run."
fi

echo "==> Cloning repository..."
sudo mkdir -p "$DEPLOY_PATH"
sudo chown "$USER:$USER" "$DEPLOY_PATH"

if [[ ! -d "$DEPLOY_PATH/.git" ]]; then
  git clone "$REPO_URL" "$DEPLOY_PATH"
fi

cd "$DEPLOY_PATH"

if [[ ! -f alwaab-erp/.env ]]; then
  cp alwaab-erp/.env.example alwaab-erp/.env
  echo ""
  echo "!! Edit alwaab-erp/.env before deploying:"
  echo "   - APP_ENV=production"
  echo "   - APP_URL=https://erp.alwaab.ae"
  echo "   - WEBSITE_API_KEY=<strong-random-key>"
  echo "   - CORS_ALLOWED_ORIGINS=https://alwaab.ae,https://www.alwaab.ae"
  echo "   - DB_PASSWORD=<strong-password>"
fi

if [[ ! -f .env ]]; then
  cp .env.example .env
fi

echo "==> Server ready. Run: bash deploy/deploy.sh"
