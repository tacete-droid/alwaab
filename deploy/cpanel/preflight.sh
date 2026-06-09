#!/usr/bin/env bash
# فحص جاهزية السيرفر قبل النشر
set -euo pipefail

PHP_BIN="${PHP_BIN:-php}"
ERR=0

check() {
  if eval "$2" &>/dev/null; then
    echo "  OK  $1"
  else
    echo "  FAIL $1"
    ERR=1
  fi
}

echo "==> ALWAAB cPanel preflight"
echo ""

PHP_VER=$($PHP_BIN -r 'echo PHP_MAJOR_VERSION.".".PHP_MINOR_VERSION;')
echo "PHP version: $PHP_VER"
if $PHP_BIN -r 'exit(version_compare(PHP_VERSION, "8.2.0", ">=") ? 0 : 1);'; then
  echo "  OK  PHP >= 8.2"
else
  echo "  FAIL PHP >= 8.2 required (current: $PHP_VER)"
  ERR=1
fi

for ext in mbstring xml curl zip gd exif bcmath fileinfo intl pdo; do
  check "ext-$ext" "$PHP_BIN -m | grep -qi '^${ext}$'"
done

if $PHP_BIN -m | grep -qi '^pdo_pgsql$'; then
  echo "  OK  PostgreSQL (pdo_pgsql)"
  DB_DRIVER=pgsql
elif $PHP_BIN -m | grep -qi '^pdo_mysql$'; then
  echo "  OK  MySQL (pdo_mysql) — استخدم env.mysql.template"
  DB_DRIVER=mysql
else
  echo "  FAIL لا يوجد pdo_pgsql ولا pdo_mysql"
  ERR=1
fi

check "git" "command -v git"
check "composer" "command -v composer || test -f ~/composer.phar"
check "ssh" "test -d ~/.ssh || true"

echo ""
if [[ $ERR -eq 0 ]]; then
  echo "==> السيرفر جاهز للنشر"
  [[ -n "${DB_DRIVER:-}" ]] && echo "    قاعدة البيانات المقترحة: $DB_DRIVER"
else
  echo "==> يوجد مشاكل — راجع الأعلى قبل النشر"
  exit 1
fi
