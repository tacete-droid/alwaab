#!/usr/bin/env bash
# نشر ALWAAB على cPanel عبر SSH
set -euo pipefail

ROOT="${DEPLOY_PATH:-$HOME/alwaab}"
ERP="${ROOT}/alwaab-erp"
WEB_SRC="${ROOT}/alwaab-website"
WEB_DST="${WEBSITE_PUBLIC_HTML:-$HOME/public_html}"

echo "==> ALWAAB cPanel deploy"
echo "    Root: ${ROOT}"

cd "$ROOT"
git fetch origin main
git reset --hard origin/main

cd "$ERP"

if [[ ! -f .env ]]; then
  echo "ERROR: ${ERP}/.env غير موجود. انسخ deploy/cpanel/env.template أولاً."
  exit 1
fi

# Composer (cPanel: غالباً /usr/local/bin/php و composer في ~/bin)
PHP_BIN="${PHP_BIN:-php}"
COMPOSER_BIN="${COMPOSER_BIN:-composer}"

$PHP_BIN "$COMPOSER_BIN" install --no-dev --optimize-autoloader --no-interaction 2>/dev/null \
  || $COMPOSER_BIN install --no-dev --optimize-autoloader --no-interaction

# بناء الواجهة إذا Node متوفر
if command -v npm &>/dev/null && [[ -f package.json ]]; then
  npm ci
  npm run build
else
  echo "WARN: npm غير متوفر — تأكد أن public/build موجود (يُبنى من GitHub Actions)"
fi

$PHP_BIN artisan migrate --force
$PHP_BIN artisan storage:link --force 2>/dev/null || true
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

# إعداد ربط الموقع بالـ ERP
bash "${ROOT}/deploy/generate-website-config.sh"

# نشر ملفات الموقع إلى public_html
if [[ -d "$WEB_DST" ]]; then
  echo "==> نشر الموقع إلى ${WEB_DST}"
  if command -v rsync &>/dev/null; then
    rsync -a --delete \
      --exclude 'js/erp-config.example.js' \
      --exclude '.gitignore' \
      "${WEB_SRC}/" "${WEB_DST}/"
  else
    find "${WEB_SRC}" -mindepth 1 -maxdepth 1 ! -name '.gitignore' \
      -exec cp -R {} "${WEB_DST}/" \;
  fi
  mkdir -p "${WEB_DST}/js"
  cp "${WEB_SRC}/js/erp-config.js" "${WEB_DST}/js/erp-config.js"
fi

echo "==> تم النشر بنجاح"
