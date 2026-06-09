#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${ROOT}/.env"
TEMPLATE="${ROOT}/docker/nginx/production.conf.template"
OUT="${ROOT}/docker/nginx/production.conf"

ERP_DOMAIN="${ERP_DOMAIN:-erp.alwaab.ae}"
WEBSITE_DOMAIN="${WEBSITE_DOMAIN:-alwaab.ae}"

if [[ -f "$ENV_FILE" ]]; then
  # shellcheck disable=SC1090
  source <(grep -E '^(ERP_DOMAIN|WEBSITE_DOMAIN)=' "$ENV_FILE" | sed 's/\r$//' || true)
fi

export ERP_DOMAIN WEBSITE_DOMAIN
envsubst '${ERP_DOMAIN} ${WEBSITE_DOMAIN}' < "$TEMPLATE" > "$OUT"
echo "✓ Generated nginx config for ${WEBSITE_DOMAIN} + ${ERP_DOMAIN}"
