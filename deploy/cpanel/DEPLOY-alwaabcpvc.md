# دليل النشر الكامل — alwaabcp @ alwaabcpvc.ae (MySQL + cPanel)

> حسابك: **alwaabcp** | الدومين: **alwaabcpvc.ae** | قاعدة البيانات: **MySQL** | SSH: **نعم**

---

## خريطة المشروع على السيرفر

```
/home/alwaabcp/
├── alwaab/                              ← المشروع (git clone)
│   ├── alwaab-erp/                      ← نظام ERP
│   │   ├── .env                         ← إعدادات الإنتاج (سري)
│   │   ├── public/                      ← جذر subdomain الـ ERP
│   │   └── storage/
│   ├── alwaab-website/                  ← ملفات الموقع المصدر
│   └── deploy/
└── public_html/                         ← الموقع الحي https://alwaabcpvc.ae
```

| الخدمة | الرابط | المجلد على السيرفر |
|--------|--------|-------------------|
| الموقع | https://alwaabcpvc.ae | `/home/alwaabcp/public_html` |
| ERP | https://erp.alwaabcpvc.ae | `/home/alwaabcp/alwaab/alwaab-erp/public` |

---

# الجزء 1 — إعداد cPanel (من المتصفح)

## 1.1 إنشاء قاعدة بيانات MySQL

1. سجّل دخول **cPanel**
2. ابحث عن **MySQL® Databases** (قواعد بيانات MySQL)
3. في **Create New Database**:
   - الاسم: `alwaab_erp`
   - cPanel سيحوّله تلقائياً إلى: **`alwaabcp_alwaab_erp`**
   - اضغط **Create Database**

4. في **MySQL Users → Add New User**:
   - Username: `erpuser`
   - سيصبح: **`alwaabcp_erpuser`**
   - Password: اختر كلمة مرور قوية (احفظها!)
   - اضغط **Create User**

5. في **Add User To Database**:
   - User: `alwaabcp_erpuser`
   - Database: `alwaabcp_alwaab_erp`
   - اضغط **Add**
   - فعّل **ALL PRIVILEGES** → **Make Changes**

**سجّل هذه القيم:**
```
DB_DATABASE=alwaabcp_alwaab_erp
DB_USERNAME=alwaabcp_erpuser
DB_PASSWORD=كلمة_المرور_التي_اخترتها
DB_HOST=localhost
```

---

## 1.2 تفعيل PHP 8.2 أو أحدث

1. cPanel → **MultiPHP Manager**
2. حدّد الدومين **`alwaabcpvc.ae`** → اختر **PHP 8.2** أو **8.3** أو **8.4**
3. كرّر لـ **`erp.alwaabcpvc.ae`** (بعد إنشائه)

4. cPanel → **Select PHP Version** (أو PHP Extensions):
   فعّل هذه الإضافات:
   - `pdo_mysql`
   - `mysqli`
   - `mbstring`
   - `xml`
   - `curl`
   - `zip`
   - `gd`
   - `exif`
   - `bcmath`
   - `fileinfo`
   - `intl`
   - `openssl`
   - `tokenizer`

---

## 1.3 إنشاء Subdomain للـ ERP

1. cPanel → **Domains** (أو Subdomains)
2. **Create A New Domain** أو **Create Subdomain**:
   - Domain: `erp.alwaabcpvc.ae`
   - Document Root (مهم جداً):
     ```
     /home/alwaabcp/alwaab/alwaab-erp/public
     ```
   - **لا** تستخدم `public_html/erp`
3. احفظ

> إذا لم يُنشأ المجلد بعد، أنشئه لاحقاً عبر SSH بعد `git clone`

---

## 1.4 تفعيل SSL (HTTPS)

1. cPanel → **SSL/TLS Status**
2. حدّد:
   - `alwaabcpvc.ae`
   - `www.alwaabcpvc.ae`
   - `erp.alwaabcpvc.ae`
3. اضغط **Run AutoSSL** أو **Install** (Let's Encrypt)

انتظر 5–15 دقيقة حتى يفعّل الشهادة.

---

## 1.5 تفعيل SSH (تحقق)

1. cPanel → **SSH Access** أو **Terminal**
2. إذا SSH مفعّل، ستجد بيانات الاتصال:
   ```
   Host: alwaabcpvc.ae (أو IP السيرفر)
   Port: 22
   User: alwaabcp
   ```

---

# الجزء 2 — الاتصال عبر SSH

## 2.1 من Windows (PowerShell أو CMD)

```powershell
ssh alwaabcp@alwaabcpvc.ae
```

أو إذا لم يعمل بالدومين:
```powershell
ssh alwaabcp@IP_SERVER
```

أدخل كلمة مرور cPanel عند الطلب.

## 2.2 تثبيت Composer (إذا غير موجود)

```bash
cd ~
curl -sS https://getcomposer.org/installer | php
echo 'alias composer="php ~/composer.phar"' >> ~/.bashrc
source ~/.bashrc
composer --version
```

---

# الجزء 3 — استنساخ المشروع

```bash
cd ~
git clone https://github.com/tacete-droid/alwaab.git alwaab
cd alwaab
bash deploy/cpanel/preflight.sh
```

إذا ظهرت أخطاء في preflight، أصلحها قبل المتابعة (PHP أو extensions).

---

# الجزء 4 — ملف .env (الأهم)

```bash
cd ~/alwaab
cp deploy/cpanel/env.mysql.template alwaab-erp/.env
nano alwaab-erp/.env
```

**الصق وعدّل بهذا الشكل:**

```env
APP_NAME="ALWAAB ERP+CRM"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_TIMEZONE=Asia/Dubai
APP_URL=https://erp.alwaabcpvc.ae
APP_LOCALE=ar
APP_FALLBACK_LOCALE=en

LOG_CHANNEL=stack
LOG_LEVEL=error

DB_CONNECTION=mysql
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=alwaabcp_alwaab_erp
DB_USERNAME=alwaabcp_erpuser
DB_PASSWORD=ضع_كلمة_المرور_هنا

SESSION_DRIVER=database
SESSION_LIFETIME=120
CACHE_STORE=database
QUEUE_CONNECTION=database
BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local

MAIL_MAILER=smtp
MAIL_HOST=mail.alwaabcpvc.ae
MAIL_PORT=465
MAIL_USERNAME=info@alwaab.ae
MAIL_PASSWORD=
MAIL_ENCRYPTION=ssl
MAIL_FROM_ADDRESS=info@alwaab.ae
MAIL_FROM_NAME="Al Waab Building Materials"

WEBSITE_API_KEY=ضع_مفتاح_قوي_هنا
CORS_ALLOWED_ORIGINS=https://alwaabcpvc.ae,https://www.alwaabcpvc.ae

OPENAI_API_KEY=
LUMA_API_KEY=
AI_STUDIO_DEFAULT_CREDITS=10

SANCTUM_STATEFUL_DOMAINS=erp.alwaabcpvc.ae,www.erp.alwaabcpvc.ae

VITE_APP_NAME="${APP_NAME}"
```

### توليد المفاتيح:

```bash
# APP_KEY
cd ~/alwaab/alwaab-erp
php artisan key:generate

# WEBSITE_API_KEY (انسخ الناتج إلى .env)
php -r "echo bin2hex(random_bytes(32));"
```

احفظ الملف: في nano → `Ctrl+O` ثم Enter ثم `Ctrl+X`

---

# الجزء 5 — تثبيت النظام

```bash
cd ~/alwaab/alwaab-erp

# تثبيت مكتبات PHP
composer install --no-dev --optimize-autoloader --no-interaction
# أو إذا composer غير في PATH:
php ~/composer.phar install --no-dev --optimize-autoloader --no-interaction
```

### بناء واجهة Vue

**إذا npm متوفر على السيرفر:**
```bash
npm ci && npm run build
```

**إذا npm غير متوفر** — من جهازك Windows:
```powershell
cd "e:\ALWAAB SYSTEM\alwaab-erp"
npm run build
```
ثم ارفع مجلد `public/build/` بالكامل إلى  
`/home/alwaabcp/alwaab/alwaab-erp/public/build/`  
عبر **cPanel File Manager**

### قاعدة البيانات والبيانات التجريبية

```bash
cd ~/alwaab/alwaab-erp
php artisan migrate --seed --force
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

### صلاحيات المجلدات

```bash
chmod -R 775 ~/alwaab/alwaab-erp/storage
chmod -R 775 ~/alwaab/alwaab-erp/bootstrap/cache
```

---

# الجزء 6 — نشر الموقع وربطه بالـ ERP

```bash
cd ~/alwaab
bash deploy/generate-website-config.sh
```

هذا ينشئ `alwaab-website/js/erp-config.js` تلقائياً من `.env`

```bash
# نسخ الموقع إلى public_html
cp -r ~/alwaab/alwaab-website/* ~/public_html/
mkdir -p ~/public_html/js
cp ~/alwaab/alwaab-website/js/erp-config.js ~/public_html/js/erp-config.js
```

تحقق أن الملف موجود:
```bash
cat ~/public_html/js/erp-config.js
```

يجب أن يحتوي:
```javascript
apiUrl: "https://erp.alwaabcpvc.ae/api/v1/website/quote-requests",
apiKey: "نفس WEBSITE_API_KEY من .env",
```

---

# الجزء 7 — Cron Jobs

cPanel → **Cron Jobs** → **Add New Cron Job**

### المهمة 1 — Laravel Scheduler
| الحقل | القيمة |
|-------|--------|
| Minute | `*` |
| Hour | `*` |
| Day | `*` |
| Month | `*` |
| Weekday | `*` |
| Command | `/usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan schedule:run >> /dev/null 2>&1` |

### المهمة 2 — Queue (AI Studio + إيميلات)
| الحقل | القيمة |
|-------|--------|
| Command | `/usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1` |

> إذا `/usr/local/bin/php` لم يعمل، جرّب: `php` فقط أو اسأل الدعم عن مسار PHP.

---

# الجزء 8 — الاختبار

| # | ماذا تفعل | النتيجة المتوقعة |
|---|-----------|------------------|
| 1 | افتح https://alwaabcpvc.ae | الصفحة الرئيسية تظهر |
| 2 | افتح https://erp.alwaabcpvc.ae | صفحة تسجيل دخول ERP |
| 3 | سجّل دخول: `admin@alwaab.ae` / `password` | لوحة التحكم |
| 4 | https://alwaabcpvc.ae/contact.html → أرسل طلب | رسالة نجاح |
| 5 | ERP → RFQ / Leads | الطلب يظهر |

---

# الجزء 9 — النشر التلقائي (GitHub Actions)

في GitHub → [tacete-droid/alwaab](https://github.com/tacete-droid/alwaab) → **Settings → Secrets**

| Secret | القيمة |
|--------|--------|
| `SERVER_HOST` | `alwaabcpvc.ae` أو IP السيرفر |
| `SERVER_USER` | `alwaabcp` |
| `SERVER_SSH_KEY` | المفتاح الخاص SSH |
| `DEPLOY_PATH` | `/home/alwaabcp/alwaab` |
| `WEBSITE_PUBLIC_HTML` | `/home/alwaabcp/public_html` |

بعدها كل `git push` على `main` يُحدّث السيرفر.

---

# استكشاف الأخطاء

### خطأ 500 على ERP
```bash
tail -50 ~/alwaab/alwaab-erp/storage/logs/laravel.log
```

### صفحة بيضاء
```bash
chmod -R 775 ~/alwaab/alwaab-erp/storage ~/alwaab/alwaab-erp/bootstrap/cache
php ~/alwaab/alwaab-erp/artisan config:clear
```

### الموقع لا يرسل طلبات للـ ERP
- تحقق من `~/public_html/js/erp-config.js`
- تحقق من `WEBSITE_API_KEY` في `.env`
- افتح Console في المتصفح (F12) عند الإرسال

### CORS error
تأكد في `.env`:
```
CORS_ALLOWED_ORIGINS=https://alwaabcpvc.ae,https://www.alwaabcpvc.ae
```
ثم:
```bash
php artisan config:cache
```

### Database connection refused
- تحقق أسماء DB في cPanel (مع بادئة `alwaabcp_`)
- `DB_HOST=localhost` وليس IP

---

# أوامر سريعة (نسخ ولصق)

```bash
# التثبيت الكامل من الصفر
cd ~ && git clone https://github.com/tacete-droid/alwaab.git alwaab
cd alwaab && cp deploy/cpanel/env.mysql.template alwaab-erp/.env
nano alwaab-erp/.env
# عدّل .env ثم:
cd alwaab-erp && php artisan key:generate
composer install --no-dev --optimize-autoloader
php artisan migrate --seed --force
php artisan storage:link --force
php artisan config:cache && php artisan route:cache && php artisan view:cache
cd ~/alwaab && bash deploy/generate-website-config.sh
cp -r alwaab-website/* ~/public_html/
chmod -R 775 ~/alwaab/alwaab-erp/storage ~/alwaab/alwaab-erp/bootstrap/cache
```

---

# تغيير كلمة مرور الأدمن بعد النشر

```bash
cd ~/alwaab/alwaab-erp
php artisan tinker --execute="App\Models\User::where('email','admin@alwaab.ae')->first()->update(['password'=>bcrypt('كلمة_مرور_جديدة')]);"
```

---

**الدعم:** إذا واجهت خطأ، أرسل نص الخطأ من `laravel.log` وسأساعدك.
