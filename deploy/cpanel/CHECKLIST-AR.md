# قائمة مراجعة النشر على cPanel

## قبل الرفع

- [ ] PHP 8.2+ مفعّل (MultiPHP Manager)
- [ ] PostgreSQL **أو** MySQL متوفر
- [ ] SSH / Terminal متاح
- [ ] Subdomain `erp` مُنشأ → Document Root: `~/alwaab/alwaab-erp/public`
- [ ] SSL (AutoSSL) لـ `alwaab.ae` و `erp.alwaab.ae`

## على السيرفر (أول مرة)

```bash
cd ~
git clone https://github.com/tacete-droid/alwaab.git alwaab
cd alwaab
bash deploy/cpanel/setup-first-time.sh
```

- [ ] عدّل `alwaab-erp/.env` (قاعدة البيانات + WEBSITE_API_KEY)
- [ ] شغّل `setup-first-time.sh` مرة ثانية
- [ ] أضف Cron Jobs من `deploy/cpanel/cron.txt`
- [ ] صلاحيات `storage` و `bootstrap/cache` = 775

## اختبار بعد النشر

| # | الاختبار | النتيجة المتوقعة |
|---|----------|------------------|
| 1 | https://alwaab.ae | الموقع يفتح |
| 2 | https://erp.alwaab.ae | صفحة تسجيل الدخول |
| 3 | admin@alwaab.ae / password | دخول ناجح |
| 4 | contact.html → إرسال طلب | يظهر في ERP → RFQ |
| 5 | products.html → طلب عرض سعر | يصل للـ ERP |

## GitHub Actions (نشر تلقائي)

Secrets المطلوبة في GitHub:

| Secret | مثال |
|--------|------|
| SERVER_HOST | `123.45.67.89` |
| SERVER_USER | `cpaneluser` |
| SERVER_SSH_KEY | المفتاح الخاص |
| DEPLOY_PATH | `/home/cpaneluser/alwaab` |
| WEBSITE_PUBLIC_HTML | `/home/cpaneluser/public_html` |

## استكشاف الأخطاء

```bash
# فحص السيرفر
bash ~/alwaab/deploy/cpanel/preflight.sh

# سجل الأخطاء
tail -50 ~/alwaab/alwaab-erp/storage/logs/laravel.log
```
