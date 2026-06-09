# رفع ALWAAB بدون Terminal — alwaabcp @ alwaabcpvc.ae

> كل شيء عبر **cPanel File Manager** + **متصفح** فقط

---

## ما تحتاجه

- ملف ZIP الكامل (مع vendor)
- قاعدة MySQL في cPanel
- 30–45 دقيقة

---

# الخطوة 1 — قاعدة البيانات MySQL

1. cPanel → **MySQL® Databases**
2. قاعدة: `alwaab_erp` → **`alwaabcp_alwaab_erp`**
3. مستخدم: `erpuser` → **`alwaabcp_erpuser`** + كلمة مرور
4. اربطهما → **ALL PRIVILEGES**

---

# الخطوة 2 — PHP 8.2+

1. cPanel → **MultiPHP Manager** → PHP 8.2 أو أحدث
2. **Select PHP Version** → فعّل: `pdo_mysql`, `mbstring`, `zip`, `gd`, `exif`, `bcmath`, `fileinfo`, `curl`, `xml`, `intl`

---

# الخطوة 3 — رفع ZIP

1. cPanel → **File Manager**
2. اذهب إلى **`/home/alwaabcp/`**
3. **Upload** → ارفع `alwaab-cpanel-upload.zip`
4. كليك يمين → **Extract**
5. تأكد من وجود:
   ```
   /home/alwaabcp/alwaab/alwaab-erp/
   /home/alwaabcp/alwaab/alwaab-website/
   ```

---

# الخطوة 4 — Subdomain للـ ERP

1. cPanel → **Domains** → **Create A New Domain**
2. `erp.alwaabcpvc.ae`
3. Document Root:
   ```
   /home/alwaabcp/alwaab/alwaab-erp/public
   ```

---

# الخطوة 5 — ملف .env

1. File Manager → `/home/alwaabcp/alwaab/alwaab-erp/`
2. انسخ **`env-عدّلني.txt`** → أعد تسمية النسخة إلى **`.env`**
3. Edit → عبّئ:

```env
DB_DATABASE=alwaabcp_alwaab_erp
DB_USERNAME=alwaabcp_erpuser
DB_PASSWORD=كلمة_المرور
WEBSITE_API_KEY=ضع_مفتاح_طويل_عشوائي_هنا_32_حرف
```

> للمفتاح: اكتب أي نص عشوائي طويل (مثل: `a8f3k9m2x7p1q5w8e4r6t0y3u7i9o2p5`)

---

# الخطوة 6 — التثبيت من المتصفح (بدون Terminal)

1. افتح في المتصفح:
   ```
   https://erp.alwaabcpvc.ae/install-once.php?token=alwaab-setup-2026
   ```
2. انتظر حتى تظهر رسائل النجاح الخضراء
3. **احذف فوراً** ملف `install-once.php` من File Manager:
   ```
   /home/alwaabcp/alwaab/alwaab-erp/public/install-once.php
   ```

---

# الخطوة 7 — نشر الموقع

1. File Manager → `/home/alwaabcp/alwaab/alwaab-website/`
2. حدّد **كل الملفات** → **Copy**
3. الصق في **`/home/alwaabcp/public_html/`**
4. إذا سُئلت عن استبدال — اختر **Replace** أو **Merge**

> ملف `erp-config.js` يُنشأ تلقائياً عند التثبيت في الخطوة 6

---

# الخطوة 8 — صلاحيات المجلدات

1. File Manager → `/home/alwaabcp/alwaab/alwaab-erp/storage`
2. كليك يمين → **Permissions** → **775** → Apply to subdirectories
3. كرّر لـ `bootstrap/cache`

---

# الخطوة 9 — SSL

cPanel → **SSL/TLS Status** → AutoSSL لـ:
- alwaabcpvc.ae
- erp.alwaabcpvc.ae

---

# الخطوة 10 — Cron Jobs (مهم للـ AI Studio)

cPanel → **Cron Jobs**:

```
* * * * * /usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan schedule:run >> /dev/null 2>&1
```

```
* * * * * /usr/local/bin/php /home/alwaabcp/alwaab/alwaab-erp/artisan queue:work database --stop-when-empty --max-time=55 >> /dev/null 2>&1
```

> إذا لم يعمل المسار، جرّب `php` بدل `/usr/local/bin/php`

---

# الاختبار

| الرابط | المتوقع |
|--------|---------|
| https://alwaabcpvc.ae | الموقع |
| https://erp.alwaabcpvc.ae | تسجيل دخول |
| admin@alwaab.ae / password | دخول ERP |
| contact.html → إرسال طلب | يصل للـ ERP |

---

# استكشاف الأخطاء

| المشكلة | الحل |
|---------|------|
| 500 على ERP | File Manager → `storage/logs/laravel.log` |
| install-once.php Forbidden | تأكد من `?token=alwaab-setup-2026` |
| vendor not found | ارفع ZIP الكامل (مع vendor) |
| Database error | راجع أسماء DB في `.env` |
| الموقع لا يرسل طلبات | تحقق من `public_html/js/erp-config.js` |
