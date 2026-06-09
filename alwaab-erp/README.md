# ALWAAB ERP+CRM

نظام إدارة موارد المؤسسة وإدارة علاقات العملاء لشركة الوعاب — مبني على **Laravel 12 + Vue 3 + Inertia.js + PostgreSQL**.

## المتطلبات

- [Docker Desktop](https://www.docker.com/products/docker-desktop/) (موصى به)
- أو: PHP 8.4+, Composer, PostgreSQL 16, Redis 7, Node.js 20+

## التشغيل السريع (Docker)

```powershell
cd alwaab-erp
.\setup.ps1
```

أو يدوياً:

```bash
docker compose up -d --build
docker compose exec app composer install
docker compose exec app php artisan key:generate
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
npm install && npm run build
```

## الروابط

| الخدمة | الرابط |
|--------|--------|
| لوحة التحكم | http://localhost:8080 |
| REST API | http://localhost:8080/api/v1 |
| PostgreSQL | localhost:5432 (alwaab / secret) |
| Redis | localhost:6379 |

## حسابات تجريبية

| الدور | البريد | كلمة المرور |
|-------|--------|-------------|
| Super Admin | admin@alwaab.ae | password |
| Manager | manager@alwaab.ae | password |
| Sales Rep | sales@alwaab.ae | password |
| Field Officer | field@alwaab.ae | password |
| Warehouse | warehouse@alwaab.ae | password |

## هيكل المشروع

```
app/
├── Domain/           # DDD — CRM, Projects, Inventory, Quotations, FieldVisit, Chat
├── Http/
│   ├── Controllers/Api/V1/
│   ├── Requests/
│   └── Resources/
├── Services/         # Dashboard, Inventory, Watermark
├── Enums/
└── Traits/
database/
├── migrations/       # 15+ migration files
└── seeders/          # Roles, Users, Demo Data
```

## قاعدة البيانات

### الجداول الرئيسية

- **users** — مستخدمون (UUID) مع locale عربي/إنجليزي
- **contacts, leads, activities** — CRM
- **projects** — المشاريع مع GPS
- **products, product_categories, warehouses, inventory, stock_movements** — المخزون
- **rfqs, quotations, quotation_items** — العروض
- **field_visits, visit_photos** — الزيارات الميدانية
- **conversations, messages** — المحادثات
- **roles, permissions** — Spatie RBAC

## API

```bash
# تسجيل الدخول
curl -X POST http://localhost:8080/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email":"admin@alwaab.ae","password":"password"}'

# KPIs
curl http://localhost:8080/api/v1/dashboard/kpis \
  -H "Authorization: Bearer {token}" \
  -H "Accept-Language: ar"
```

### نقاط النهاية الرئيسية

- `POST /auth/login` — المصادقة
- `GET /dashboard/kpis` — مؤشرات الأداء
- `CRUD /contacts`, `/leads`, `/projects`
- `GET /products`, `/inventory/stock`
- `POST /visits`, `/visits/{id}/photos`

## الأدوار والصلاحيات

| الدور | الوصف |
|-------|-------|
| super_admin | وصول كامل |
| manager | إدارة + موافقة العروض |
| sales_rep | CRM + مشاريع + عروض |
| field_officer | زيارات ميدانية |
| warehouse_staff | إدارة المخزون |
| customer | بوابة العملاء |

## المرحلة التالية (Roadmap)

1. ✅ Foundation — Laravel + DB + Auth + RBAC + Dashboard
2. 🔲 CRM UI كامل + Lead Pipeline
3. 🔲 RFQ + Quote Generator + PDF
4. 🔲 Flutter Mobile App
5. 🔲 Laravel Reverb Chat + HR Module

## الترخيص

Proprietary — ALWAAB © 2026
