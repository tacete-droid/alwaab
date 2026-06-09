# ALWAAB System

Monorepo: **ERP** + **Website** + **Mobile App**

| المشروع | المجلد | الوصف |
|---------|--------|-------|
| ERP+CRM | `alwaab-erp/` | Laravel 12 + Vue 3 + PostgreSQL |
| Website | `alwaab-website/` | موقع FlowGuard® CPVC (HTML) |
| Mobile | `alwaab-mobile/` | Flutter app |

## الربط بين الموقع والـ ERP

```
الموقع (alwaab.ae)  ──POST──►  ERP API (erp.alwaab.ae)
         quote form              /api/v1/website/quote-requests
         X-Website-Key           WEBSITE_API_KEY في .env
```

- طلبات عرض السعر من `contact.html` و `products.html` تصل مباشرة للـ ERP
- المفتاح السري **لا يُرفع على GitHub** — يُولَّد على السيرفر فقط
- CORS مضبوط ليقبل طلبات من دومين الموقع فقط

## التشغيل المحلي

### ERP
```powershell
cd alwaab-erp
docker compose up -d --build
```

### Website
```powershell
# انسخ الإعدادات (مرة واحدة)
copy alwaab-website\js\erp-config.example.js alwaab-website\js\erp-config.js
# عدّل apiKey ليطابق WEBSITE_API_KEY في alwaab-erp\.env
```

افتح `alwaab-website/index.html` أو شغّل Live Server.

## رفع المشروع على GitHub

```powershell
cd "e:\ALWAAB SYSTEM"
git init
git add .
git commit -m "Initial commit: ALWAAB ERP + Website + Mobile"
git branch -M main
git remote add origin https://github.com/YOUR_ORG/alwaab-system.git
git push -u origin main
```

> **مهم:** لا ترفع `.env` أو `erp-config.js` — موجودة في `.gitignore`

## النشر التلقائي على السيرفر

بعد دمج أي تعديل على `main` في GitHub → يُنشر تلقائياً على السيرفر.

### 1) إعداد السيرفر (مرة واحدة)

```bash
# VPS مع Docker
sudo apt update && sudo apt install -y docker.io docker-compose-plugin git
sudo mkdir -p /var/www/alwaab-system
sudo chown $USER:$USER /var/www/alwaab-system
git clone https://github.com/YOUR_ORG/alwaab-system.git /var/www/alwaab-system
```

### 2) ملفات البيئة على السيرفر

```bash
# alwaab-erp/.env — إعدادات الإنتاج
APP_ENV=production
APP_DEBUG=false
APP_URL=https://erp.alwaab.ae
WEBSITE_API_KEY=<مفتاح-قوي-32-حرف>
CORS_ALLOWED_ORIGINS=https://alwaab.ae,https://www.alwaab.ae

# .env في الجذر — دومينات النشر
ERP_DOMAIN=erp.alwaab.ae
WEBSITE_DOMAIN=alwaab.ae
DB_PASSWORD=<كلمة-مرور-قوية>
```

```bash
# توليد مفتاح قوي
php -r "echo bin2hex(random_bytes(32));"
```

### 3) أسرار GitHub (Settings → Secrets)

| Secret | القيمة |
|--------|--------|
| `SERVER_HOST` | IP السيرفر |
| `SERVER_USER` | اسم المستخدم SSH |
| `SERVER_SSH_KEY` | المفتاح الخاص SSH |
| `DEPLOY_PATH` | `/var/www/alwaab-system` |

### 4) سير العمل اليومي

```
تعديل محلي → git push → Pull Request → مراجعة → Merge → نشر تلقائي
```

## الأمان

- `erp-config.js` يُولَّد تلقائياً على السيرفر ولا يُخزَّن في Git
- `WEBSITE_API_KEY` يُقارَن بـ `hash_equals` (آمن ضد timing attacks)
- Rate limit: 10 طلبات/دقيقة على API الموقع
- المفتاح الافتراضي مرفوض في بيئة `production`
- CORS مقيد بدومين الموقع فقط
- ملفات PDF/عروض الأسعار مستبعدة من Git

## الدومينات المقترحة

| الدومين | الخدمة |
|---------|--------|
| `alwaab.ae` | الموقع الإلكتروني |
| `erp.alwaab.ae` | نظام ERP |

## الترخيص

Proprietary — ALWAAB © 2026
