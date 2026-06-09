#!/usr/bin/env bash
# التثبيت الأول على cPanel — شغّل مرة واحدة عبر SSH
set -euo pipefail

REPO="${REPO_URL:-https://github.com/tacete-droid/alwaab.git}"
ROOT="${DEPLOY_PATH:-$HOME/alwaab}"
ERP="${ROOT}/alwaab-erp"
WEB_DST="${WEBSITE_PUBLIC_HTML:-$HOME/public_html}"
PHP_BIN="${PHP_BIN:-php}"

echo "=========================================="
echo "  ALWAAB — التثبيت الأول على cPanel"
echo "=========================================="

if [[ -d "$ROOT/.git" ]]; then
  echo "المجلد موجود: $ROOT"
else
  echo "==> استنساخ المشروع..."
  git clone "$REPO" "$ROOT"
fi

cd "$ROOT"

if [[ ! -f "$ERP/.env" ]]; then
  if [[ -f "$ROOT/deploy/cpanel/env.template" ]]; then
    cp "$ROOT/deploy/cpanel/env.template" "$ERP/.env"
  else
    cp "$ROOT/deploy/cpanel/env.mysql.template" "$ERP/.env"
  fi
  echo ""
  echo "!! عدّل الملف قبل المتابعة: $ERP/.env"
  echo "   - APP_URL"
  echo "   - DB_* (PostgreSQL أو MySQL)"
  echo "   - WEBSITE_API_KEY"
  echo ""
  read -r -p "اضغط Enter بعد حفظ .env..." _
fi

cd "$ERP"

echo "==> توليد APP_KEY..."
$PHP_BIN artisan key:generate --force

echo "==> تثبيت Composer..."
if command -v composer &>/dev/null; then
  composer install --no-dev --optimize-autoloader --no-interaction
elif [[ -f "$HOME/composer.phar" ]]; then
  $PHP_BIN "$HOME/composer.phar" install --no-dev --optimize-autoloader --no-interaction
else
  echo "ERROR: composer غير موجود. ثبّته: curl -sS https://getcomposer.org/installer | php"
  exit 1
fi

if [[ ! -d public/build ]] || [[ -z "$(ls -A public/build 2>/dev/null)" ]]; then
  if command -v npm &>/dev/null; then
    echo "==> بناء الواجهة..."
    npm ci && npm run build
  else
    echo "WARN: public/build فارغ و npm غير متوفر."
    echo "      ارفع مجلد public/build من جهازك عبر File Manager."
  fi
fi

echo "==> قاعدة البيانات..."
$PHP_BIN artisan migrate --seed --force
$PHP_BIN artisan storage:link --force 2>/dev/null || true

echo "==> تحسين الأداء..."
$PHP_BIN artisan config:cache
$PHP_BIN artisan route:cache
$PHP_BIN artisan view:cache

echo "==> ربط الموقع بالـ ERP..."
bash "$ROOT/deploy/generate-website-config.sh"

echo "==> نشر الموقع إلى public_html..."
mkdir -p "$WEB_DST"
if command -v rsync &>/dev/null; then
  rsync -a "${ROOT}/alwaab-website/" "$WEB_DST/"
else
  cp -r "${ROOT}/alwaab-website/"* "$WEB_DST/"
fi
cp "${ROOT}/alwaab-website/js/erp-config.js" "$WEB_DST/js/erp-config.js"

chmod -R 775 storage bootstrap/cache 2>/dev/null || true

echo ""
echo "=========================================="
echo "  تم التثبيت!"
echo "  ERP:    راجع APP_URL في .env"
echo "  موقع:   public_html"
echo "  دخول:   admin@alwaab.ae / password"
echo "=========================================="
echo ""
echo "الخطوات المتبقية في cPanel:"
echo "  1. Subdomain erp → Document Root: $ERP/public"
echo "  2. Cron Jobs من deploy/cpanel/cron.txt"
echo "  3. SSL → AutoSSL"
