<?php
/**
 * مثبّت لمرة واحدة — للاستضافة بدون Terminal
 * افتح: https://erp.alwaabcpvc.ae/install-once.php?token=alwaab-setup-2026
 * احذف هذا الملف فوراً بعد نجاح التثبيت
 */
declare(strict_types=1);

const INSTALL_TOKEN = 'alwaab-setup-2026';

header('Content-Type: text/html; charset=utf-8');

if (($_GET['token'] ?? '') !== INSTALL_TOKEN) {
    http_response_code(403);
    exit('Forbidden — رمز التثبيت غير صحيح');
}

$base = dirname(__DIR__);
$envFile = $base.'/.env';

function out(string $msg, string $class = ''): void
{
    $c = $class ? " class=\"$class\"" : '';
    echo "<p$c>".htmlspecialchars($msg, ENT_QUOTES, 'UTF-8')."</p>\n";
}

?>
<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <title>ALWAAB — مثبّت</title>
    <style>
        body { font-family: system-ui, sans-serif; max-width: 640px; margin: 2rem auto; padding: 1rem; background: #0f172a; color: #e2e8f0; }
        h1 { color: #38bdf8; }
        .ok { color: #4ade80; } .err { color: #f87171; } .warn { color: #fbbf24; }
        code { background: #1e293b; padding: 2px 6px; border-radius: 4px; }
    </style>
</head>
<body>
<h1>مثبّت ALWAAB ERP</h1>
<?php

if (! file_exists($envFile)) {
    out('ملف .env غير موجود. انسخ env-عدّلني.txt إلى .env وعبّئ بيانات MySQL أولاً.', 'err');
    exit('</body></html>');
}

if (! is_dir($base.'/vendor')) {
    out('مجلد vendor غير موجود. ارفع الحزمة الكاملة (مع vendor).', 'err');
    exit('</body></html>');
}

require $base.'/vendor/autoload.php';
$app = require_once $base.'/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$steps = [];

try {
    if (! str_contains((string) file_get_contents($envFile), 'APP_KEY=base64:')) {
        $kernel->call('key:generate', ['--force' => true]);
        $steps[] = ['ok', 'تم توليد APP_KEY'];
    } else {
        $steps[] = ['ok', 'APP_KEY موجود مسبقاً'];
    }

    $kernel->call('migrate', ['--force' => true]);
    $steps[] = ['ok', 'تم إنشاء جداول قاعدة البيانات'];

    $kernel->call('db:seed', ['--force' => true]);
    $steps[] = ['ok', 'تم إدخال البيانات التجريبية (admin@alwaab.ae / password)'];

    $publicStorage = $base.'/public/storage';
    $appStorage = $base.'/storage/app/public';
    if (! is_dir($publicStorage) && is_dir($appStorage)) {
        @mkdir($publicStorage, 0755, true);
        foreach (glob($appStorage.'/*') ?: [] as $item) {
            $dest = $publicStorage.'/'.basename($item);
            if (is_dir($item)) {
                if (! is_dir($dest)) {
                    mkdir($dest, 0755, true);
                }
            } else {
                copy($item, $dest);
            }
        }
        $steps[] = ['ok', 'تم إعداد مجلد public/storage'];
    } else {
        $kernel->call('storage:link', ['--force' => true]);
        $steps[] = ['ok', 'تم ربط storage'];
    }

    $kernel->call('config:cache');
    $kernel->call('route:cache');
    $kernel->call('view:cache');
    $steps[] = ['ok', 'تم تحسين الأداء (cache)'];

    $env = file_get_contents($envFile);
    preg_match('/APP_URL=(.+)/', $env, $urlM);
    preg_match('/WEBSITE_API_KEY=(.+)/', $env, $keyM);
    $apiUrl = rtrim(trim($urlM[1] ?? ''), '/').'/api/v1/website/quote-requests';
    $apiKey = trim($keyM[1] ?? '');

    $erpConfig = "window.ALWAAB_ERP = {\n  apiUrl: \"{$apiUrl}\",\n  apiKey: \"{$apiKey}\",\n};\n";

    $websiteJs = dirname($base).'/alwaab-website/js/erp-config.js';
    if (file_exists(dirname($websiteJs))) {
        file_put_contents($websiteJs, $erpConfig);
        $steps[] = ['ok', 'تم إنشاء alwaab-website/js/erp-config.js'];
    }

    $home = dirname(dirname($base));
    $publicHtml = $home.'/public_html/js/erp-config.js';
    if (is_dir(dirname($publicHtml))) {
        @mkdir(dirname($publicHtml), 0755, true);
        file_put_contents($publicHtml, $erpConfig);
        $steps[] = ['ok', 'تم إنشاء public_html/js/erp-config.js'];
    } else {
        $steps[] = ['warn', 'انسخ erp-config.js يدوياً إلى public_html/js/'];
    }

} catch (Throwable $e) {
    $steps[] = ['err', 'خطأ: '.$e->getMessage()];
}

foreach ($steps as [$class, $msg]) {
    out($msg, $class);
}

if (! in_array('err', array_column($steps, 0), true)) {
    echo '<hr><p class="ok"><strong>اكتمل التثبيت!</strong></p>';
    echo '<p>ERP: <a href="https://erp.alwaabcpvc.ae" style="color:#38bdf8">erp.alwaabcpvc.ae</a></p>';
    echo '<p>دخول: <code>admin@alwaab.ae</code> / <code>password</code></p>';
    echo '<p class="warn"><strong>احذف ملف install-once.php الآن من File Manager!</strong></p>';
}

?>
</body>
</html>
