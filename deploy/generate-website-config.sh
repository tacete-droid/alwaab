#!/usr/bin/env bash
set -euo pipefail

ROOT="$(cd "$(dirname "$0")/.." && pwd)"
ENV_FILE="${ROOT}/alwaab-erp/.env"
OUT="${ROOT}/alwaab-website/js/erp-config.js"

if [[ ! -f "$ENV_FILE" ]]; then
  echo "ERROR: alwaab-erp/.env not found"
  exit 1
fi

# shellcheck disable=SC1090
source <(grep -E '^(APP_URL|WEBSITE_API_KEY)=' "$ENV_FILE" | sed 's/\r$//')

if [[ -z "${WEBSITE_API_KEY:-}" ]]; then
  echo "ERROR: WEBSITE_API_KEY is empty in .env"
  exit 1
fi

API_URL="${APP_URL%/}/api/v1/website/quote-requests"

cat > "$OUT" <<EOF
// Auto-generated — do not commit. Created by deploy/generate-website-config.sh
window.ALWAAB_ERP = {
  apiUrl: "${API_URL}",
  apiKey: "${WEBSITE_API_KEY}",
};
EOF

chmod 644 "$OUT"
echo "✓ Generated ${OUT}"
