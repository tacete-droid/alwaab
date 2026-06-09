# رفع ALWAAB عبر ZIP — alwaabcp @ alwaabcpvc.ae

> **بدون SSH من ويندوز** — فقط cPanel File Manager + Terminal داخل cPanel

---

## ما تحتاجه قبل البدء

- [ ] ملف `alwaab-cpanel-upload.zip` (يُبنى من جهازك)
- [ ] قاعدة MySQL جاهزة في cPanel
- [ ] PHP 8.2+ مفعّل

---

# الخطوة 1 — بناء ZIP على جهازك (ويندوز)

```powershell
cd "e:\ALWAAB SYSTEM"
powershell -File deploy\cpanel\build-upload-package.ps1
```

الملف يظهر هنا:
```
e:\ALWAAB SYSTEM\dist\alwaab-cpanel-upload.zip
```

---

# الخطوة 2 — قاعدة البيانات (cPanel)

1. cPanel → **MySQL® Databases**
2. أنشئ قاعدة: `alwaab_erp` → تصبح **`alwaabcp_alwaab_erp`**
3. أنشئ مستخدم: `erpuser` → **`alwaabcp_erpuser`** + كلمة مرور
4. اربط المستخدم بالقاعدة → **ALL PRIVILEGES**

احفظ:
```
DB_DATABASE=alwaabcp_alwaab_erp
DB_USERNAME=alwaabcp_erpuser
DB_PASSWORD=كلمة_المرور
```

---

# الخطوة 3 — رفع ZIP وفك الضغط

1. cPanel → **File Manager**
2. اذهب إلى **`/home/alwaabcp/`** (المجلد الرئيسي، ليس public_html)
3. اضغط **Upload** → ارفع `alwaab-cpanel-upload.zip`
4. بعد اكتمال الرفع: كليك يمين على الملف → **Extract**
5. تأكد أن المجلدات ظهرت:
   ```
   /home/alwaabcp/alwaab/alwaab-erp/
   /home/alwaabcp/alwaab/alwaab-website/
   /home/alwaabcp/alwaab/deploy/
   ```

> إذا فُك الضغط في `alwaab-cpanel-upload/` انقل المحتويات إلى `alwaab/`

---

# الخطوة 4 — Subdomain للـ ERP

1. cPanel → **Domains** → **Create A New Domain**
2. Domain: **`erp.alwaabcpvc.ae`**
3. Document Root:
   ```
   /home/alwaabcp/alwaab/alwaab-erp/public
   ```

---

# الخطوة 5 — ملف .env

1. File Manager → `/home/alwaabcp/alwaab/alwaab-erp/`
2. انسخ `env-عدّلني.txt` → أعد تسميته إلى **`.env`**
3. Edit → عبّئ:

```env
DB_DATABASE=alwaabcp_alwaab_erp
DB_USERNAME=alwaabcp_erpuser
DB_PASSWORD=كلمة_المرور_الحقيقية
WEBSITE_API_KEY=ضع_مفتاح_طويل_عشوائي
```

لتوليد مفتاح عشوائي من cPanel Terminal:
```bash
php -r "echo bin2hex(random_bytes(32));"
```

---

# الخطوة 6 — أوامر التثبيت (cPanel Terminal)

1. cPanel → **Terminal** (ابحث عنه في cPanel)
2. الصق هذه الأوامر واحداً واحداً:

```bash
cd ~/alwaab/alwaab-erp
composer install --no-dev --optimize-autoloader --no-interaction
php artisan key:generate --force
php artisan migrate --seed --force
php artisan storage:link --force
php artisan config:cache
php artisan route:cache
php artisan view:cache
chmod -R 775 storage bootstrap/cache
```

> **إذا لا يوجد Terminal:** اطلب من دعم الاستضافة تشغيل هذه الأوامر مرة واحدة.

---

# الخطوة 7 — نشر الموقع

في cPanel Terminal:

```bash
cd ~/alwaab
php -r "
\$env = file_get_contents('alwaab-erp/.env');
preg_match('/APP_URL=(.+)/', \$env, \$m);
\$url = rtrim(trim(\$m[1]), '/').'/api/v1/website/quote-requests';
preg_match('/WEBSITE_API_KEY=(.+)/', \$env, \$k);
\$key = trim(\$k[1]);
\$js = \"window.ALWAAB_ERP = { apiUrl: '\$url', apiKey: '\$key' };\";
file_put_contents('alwaab-website/js/erp-config.js', \$js);
"
cp -r ~/alwaab/alwaab-website/* ~/public_html/
mkdir -p ~/public_html/js
cp ~/alwaab/alwaab-website/js/erp-config.js ~/public_html/js/
```

**أو يدوياً من File Manager:**
- انسخ كل محتويات `alwaab/alwaab-website/` إلى `public_html/`
- أنشئ `public_html/js/erp-config.js`:

```javascript
window.ALWAAB_ERP = {
  apiUrl: "https://erp.alwaabcpvc.ae/api/v1/website/quote-requests",
  apiKey: "نفس WEBSITE_API_KEY من .env"
};
```

---

# الخطوة 8 — SSL

cPanel → **SSL/TLS Status** → AutoSSL لـ:
- alwaabcpvc.ae
- erp.alwaabcpvc.ae

---

# الخطوة 9 — Cron Jobs

cPanel → **Cron Jobs**:

```
* * * * * /usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan schedule:run >> /dev/null 2>&1
```

```
* * * * * /usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

---

# الخطوة 10 — اختبار

| الرابط | المتوقع |
|--------|---------|
| https://alwaabcpvc.ae | الموقع |
| https://erp.alwaabcpvc.ae | تسجيل دخول |
| admin@alwaab.ae / password | دخول ERP |
| contact.html → إرسال | يصل للـ ERP |

---

# ملخص سريع

```
جهازك  →  build ZIP  →  cPanel Upload  →  Extract
         →  .env  →  Terminal (artisan)  →  نسخ الموقع لـ public_html
```
