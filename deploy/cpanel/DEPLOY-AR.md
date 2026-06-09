# رفع ALWAAB على cPanel — دليل خطوة بخطوة

## المتطلبات على الاستضافة

| المتطلب | الحالة |
|---------|--------|
| PHP **8.2+** (8.4 موصى به) | cPanel → MultiPHP Manager |
| PostgreSQL **أو** MySQL | PostgreSQL موصى به — أو MySQL من cPanel → MySQL Databases |
| SSH / Terminal | موصى به بشدة |
| SSL | cPanel → SSL/TLS → Let's Encrypt |
| Composer | عبر SSH: `curl -sS https://getcomposer.org/installer \| php` |

> **ملاحظة:** الاستضافة المشتركة بدون PostgreSQL أو PHP 8.4 لن تشغّل النظام. تواصل مع الدعم لتفعيلهما.

---

## هيكل الملفات على السيرفر

```
/home/USERNAME/
├── alwaab/                      ← git clone هنا
│   ├── alwaab-erp/
│   │   └── public/              ← جذر subdomain الـ ERP
│   ├── alwaab-website/
│   └── deploy/
└── public_html/                 ← الموقع الرئيسي alwaab.ae
```

---

## الخطوة 1 — قاعدة البيانات

1. cPanel → **PostgreSQL Databases**
2. أنشئ قاعدة: `alwaab_erp`
3. أنشئ مستخدم واربطه بالقاعدة (ALL PRIVILEGES)
4. سجّل: الاسم، المستخدم، كلمة المرور، Host (غالباً `localhost`)

---

## الخطوة 2 — PHP 8.2+

1. cPanel → **MultiPHP Manager**
2. اختر PHP **8.2** أو أحدث للدومين الرئيسي و subdomain الـ ERP
3. cPanel → **Select PHP Version** → Extensions:
   - `pdo_pgsql`, `pgsql`, `mbstring`, `xml`, `curl`, `zip`, `gd`, `exif`, `bcmath`, `fileinfo`, `intl`

---

## الإعداد السريع (أول مرة)

```bash
cd ~
git clone https://github.com/tacete-droid/alwaab.git alwaab
cd alwaab
bash deploy/cpanel/preflight.sh          # فحص السيرفر
bash deploy/cpanel/setup-first-time.sh   # إعداد تلقائي
```

> راجع أيضاً: `deploy/cpanel/CHECKLIST-AR.md`

## الخطوة 3 — استنساخ المشروع

### عبر SSH (موصى به)
```bash
cd ~
git clone https://github.com/tacete-droid/alwaab.git alwaab
cd alwaab
```

### أو عبر cPanel → Git Version Control
- Repository URL: `https://github.com/tacete-droid/alwaab.git`
- Directory: `alwaab`

---

## الخطوة 4 — Subdomain للـ ERP

1. cPanel → **Domains** → **Create Subdomain**
2. Subdomain: `erp` → `erp.alwaab.ae`
3. Document Root: `/home/USERNAME/alwaab/alwaab-erp/public`
4. **مهم:** لا تضع Laravel داخل `public_html` مباشرة

---

## الخطوة 5 — ملف .env

```bash
cd ~/alwaab
cp deploy/cpanel/env.template alwaab-erp/.env
nano alwaab-erp/.env   # أو عدّل من File Manager
```

عبّئ:
- `APP_URL=https://erp.alwaab.ae`
- بيانات PostgreSQL من الخطوة 1
- `WEBSITE_API_KEY` — مفتاح قوي:
  ```bash
  php -r "echo bin2hex(random_bytes(32));"
  ```

ثم:
```bash
cd ~/alwaab/alwaab-erp
php artisan key:generate
```

---

## الخطوة 6 — تثبيت الاعتماديات

```bash
cd ~/alwaab/alwaab-erp
composer install --no-dev --optimize-autoloader
php artisan migrate --seed --force
php artisan storage:link
```

### بناء واجهة Vue
**خيار أ — على جهازك (ثم ارفع `public/build/` عبر File Manager)**

**خيار ب — على السيرفر (إذا Node.js متوفر)**
```bash
npm ci && npm run build
```

**خيار ج — تلقائي من GitHub Actions** (بعد إعداد Secrets)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## الخطوة 7 — نشر الموقع

```bash
cd ~/alwaab
bash deploy/generate-website-config.sh
rsync -a alwaab-website/ ~/public_html/
cp alwaab-website/js/erp-config.js ~/public_html/js/
```

أو يدوياً: انسخ محتويات `alwaab-website/` إلى `public_html/` عبر File Manager.

---

## الخطوة 8 — صلاحيات المجلدات

```bash
chmod -R 775 ~/alwaab/alwaab-erp/storage
chmod -R 775 ~/alwaab/alwaab-erp/bootstrap/cache
```

cPanel → File Manager → storage و bootstrap/cache → Permissions → **775**

---

## الخطوة 9 — Cron Jobs

cPanel → **Cron Jobs** — انسخ من `deploy/cpanel/cron.txt`  
استبدل `USERNAME` باسم مستخدم cPanel.

---

## الخطوة 10 — SSL

cPanel → **SSL/TLS Status** → Run AutoSSL لـ:
- `alwaab.ae`
- `www.alwaab.ae`
- `erp.alwaab.ae`

---

## الخطوة 11 — اختبار الربط

| الاختبار | الرابط |
|----------|--------|
| الموقع | https://alwaab.ae |
| ERP | https://erp.alwaab.ae |
| تسجيل الدخول | admin@alwaab.ae / password |
| طلب عرض سعر | https://alwaab.ae/contact.html |

---

## النشر التلقائي (GitHub Actions)

أضف في GitHub → Settings → Secrets:

| Secret | مثال |
|--------|------|
| `SERVER_HOST` | `server.alwaab.ae` أو IP |
| `SERVER_USER` | اسم مستخدم cPanel |
| `SERVER_SSH_KEY` | المفتاح الخاص SSH |
| `DEPLOY_PATH` | `/home/USERNAME/alwaab` |
| `WEBSITE_PUBLIC_HTML` | `/home/USERNAME/public_html` |

بعدها كل `git push` على `main` يُحدّث السيرفر تلقائياً.

---

## استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| 500 Error | راجع `alwaab-erp/storage/logs/laravel.log` |
| صفحة بيضاء | `APP_DEBUG=true` مؤقتاً، أو راجع صلاحيات storage |
| الموقع لا يرسل طلبات | تحقق من `public_html/js/erp-config.js` |
| CORS error | تأكد `CORS_ALLOWED_ORIGINS` في .env |
| Composer not found | `php ~/composer.phar install` |
