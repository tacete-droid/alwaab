# ALWAAB Mobile — تطبيق الميدان

تطبيق Flutter للعمل الميداني: زيارات GPS + كاميرا + تتبع مباشر للمدير.

## المتطلبات

- Flutter 3.16+
- Laravel API يعمل على `http://localhost:8080`

## الإعداد

```bash
cd alwaab-mobile

# إنشاء ملفات المنصة (مرة واحدة)
flutter create . --project-name alwaab_mobile

flutter pub get
```

## تشغيل

### Android Emulator (يتصل بـ Docker على الجهاز المضيف)

```bash
flutter run
```

العنوان الافتراضي: `http://10.0.2.2:8080/api/v1`

### جهاز حقيقي (استبدل IP الكمبيوتر)

```bash
flutter run --dart-define=API_BASE_URL=http://192.168.1.100:8080/api/v1
```

## الحسابات التجريبية

| الدور | البريد | كلمة المرور |
|-------|--------|-------------|
| مدير | manager@alwaab.ae | password |
| مدير نظام | admin@alwaab.ae | password |
| ميداني | field@alwaab.ae | password |

## الميزات

- تسجيل دخول عبر Sanctum API
- بدء زيارة مع GPS فعلي
- تتبع موقع كل 45 ثانية أثناء الزيارة
- التقاط صور بالكاميرا + رفع مع GPS
- **وضع عدم الاتصال**: حفظ GPS والصور وإنهاء الزيارة محلياً + مزامنة تلقائية
- **إشعارات**: polling كل 30 ثانية + شاشة قائمة + شارة غير مقروء
- تتبع GPS مباشر للمدير (`visits.manage`)
- واجهة RTL عربية + تنقل سفلي للجوال

## صلاحيات Android (بعد `flutter create`)

أضف في `android/app/src/main/AndroidManifest.xml`:

```xml
<uses-permission android:name="android.permission.INTERNET"/>
<uses-permission android:name="android.permission.ACCESS_FINE_LOCATION"/>
<uses-permission android:name="android.permission.ACCESS_COARSE_LOCATION"/>
<uses-permission android:name="android.permission.CAMERA"/>
```

## الصلاحيات

| الصلاحية | الوظيفة |
|----------|---------|
| `visits.create` | بدء زيارة + كاميرا + GPS |
| `visits.manage` | خريطة التتبع المباشر |
| `visits.view` | سجل الزيارات |
