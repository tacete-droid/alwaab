#!/usr/bin/env bash
# الإعداد الأولي على cPanel — شغّل مرة واحدة بعد git clone
set -euo pipefail

ROOT="${DEPLOY_PATH:-$HOME/alwaab}"
ERP="${ROOT}/alwaab-erp"
WEB_DST="${WEBSITE_PUBLIC_HTML:-$HOME/public_html}"
PHP_BIN="${PHP_BIN:-php}"

echo "==> ALWAAB first-time cPanel setup"

bash "${ROOT}/deploy/cpanel/preflight.sh"

cd "$ROOT"

if [[ ! -f "${ERP}/.env" ]]; then
  if $PHP_BIN -m | grep -qi '^pdo_pgsql$'; then
    cp deploy/cpanel/env.template "${ERP}/.env"
    echo "    أنشئت .env من env.template (PostgreSQL)"
  else
    cp deploy/cpanel/env.mysql.template "${ERP}/.env"
    echo "    أنشئت .env من env.mysql.template (MySQL)"
  fi
  echo ""
  echo "!! عدّل ${ERP}/.env ثم شغّل هذا السكربت مرة أخرى"
  exit 0
fi

cd "$ERP"

if ! grep -q '^APP_KEY=base64:' .env 2>/dev/null; then
  $PHP_BIN artisan key:generate --force
fi

COMPOSER_BIN="${COMPOSER_BIN:-composer}"
if command -v "$COMPOSER_BIN" &>/dev/null; then
  $PHP_BIN "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction
elif [[ -f "$HOME/composer.phar" ]]; then
  $PHP_BIN "$HOME/composer.phar" install --no-dev --optimize-autoloader --no-interaction
else
  echo "ERROR: composer غير موجود. ثبّته: curl -sS https://getcomposer.org/installer | php"
  exit 1
fi

if command -v npm &>/dev/null; then
  npm ci && npm run build
else
  echo "WARN: npm غير متوفر — ارفع public/build/ يدوياً من جهازك"
fi

$PHP_BIN artisan migrate --seed --force
$PHP_BIN artisan storage:link --force 2>/dev/null || true
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

bash "${ROOT}/deploy/generate-website-config.sh"

if [[ -d "$WEB_DST" ]]; then
  echo "==> نشر الموقع إلى ${WEB_DST}"
  if command -v rsync &>/dev/null; then
    rsync -a "${ROOT}/alwaab-website/" "${WEB_DST}/"
  else
    cp -R "${ROOT}/alwaab-website/"* "${WEB_DST}/"
  fi
  mkdir -p "${WEB_DST}/js"
  cp "${ROOT}/alwaab-website/js/erp-config.js" "${WEB_DST}/js/erp-config.js"
fi

echo ""
echo "==> الإعداد الأولي اكتمل"
echo "    ERP:  افتح subdomain (erp.alwaab.ae)"
echo "    Site: افتح الدومين الرئيسي"
echo "    Login: admin@alwaab.ae / password"
echo ""
echo "    أضف Cron Jobs من: deploy/cpanel/cron.txt"
