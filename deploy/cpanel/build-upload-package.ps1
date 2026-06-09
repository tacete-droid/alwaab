# بناء حزمة ZIP كاملة للرفع على cPanel (بدون SSH من ويندوز)
$root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$outDir = Join-Path $root "dist"
$zipPath = Join-Path $outDir "alwaab-cpanel-upload.zip"
$stamp = Get-Date -Format "yyyyMMdd-HHmmss"
$erpDir = Join-Path $root "alwaab-erp"

Write-Host "==> 1/4 بناء واجهة Vue..."
Push-Location $erpDir
npm run build
if ($LASTEXITCODE -ne 0) { Pop-Location; exit 1 }
Pop-Location

Write-Host "==> 2/4 تجهيز الملفات (مع vendor — بدون Terminal على السيرفر)..."
if (-not (Test-Path (Join-Path $erpDir "vendor"))) {
    Write-Host "WARN: vendor غير موجود. شغّل Docker: docker compose exec app composer install --no-dev"
}
New-Item -ItemType Directory -Path $outDir -Force | Out-Null
if (Test-Path $zipPath) { Remove-Item $zipPath -Force -ErrorAction SilentlyContinue }
$staging = Join-Path $env:TEMP "alwaab-staging-$stamp"
if (Test-Path $staging) { Remove-Item $staging -Recurse -Force -ErrorAction SilentlyContinue }
New-Item -ItemType Directory -Path $staging -Force | Out-Null

robocopy $erpDir (Join-Path $staging "alwaab-erp") /E `
    /XD node_modules ".git" "public\storage" `
    "storage\logs" "storage\framework\cache\data" "storage\framework\sessions" "storage\framework\views" `
    /XF .env `
    /NFL /NDL /NJH /NJS /nc /ns /np /R:1 /W:1 | Out-Null

robocopy (Join-Path $root "alwaab-website") (Join-Path $staging "alwaab-website") /E `
    /XF "erp-config.js" `
    /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null

robocopy (Join-Path $root "deploy") (Join-Path $staging "deploy") /E /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null

# إزالة symlink storage (يسبب مشاكل في ZIP على ويندوز)
$storageLink = Join-Path $staging "alwaab-erp\public\storage"
if (Test-Path $storageLink) { cmd /c "rmdir `"$storageLink`"" 2>$null; Remove-Item $storageLink -Force -Recurse -ErrorAction SilentlyContinue }

# ملف env جاهز للتعديل
Copy-Item (Join-Path $root "deploy\cpanel\env.mysql.template") (Join-Path $staging "alwaab-erp\env-عدّلني.txt")

# دليل الرفع
Copy-Item (Join-Path $root "deploy\cpanel\DEPLOY-NO-TERMINAL-alwaabcpvc.md") (Join-Path $staging "README-INSTALL.txt") -Force

Write-Host "==> 3/4 ضغط ZIP..."
if (Get-Command tar -ErrorAction SilentlyContinue) {
    Push-Location $staging
    tar -a -c -f $zipPath *
    Pop-Location
} else {
    Compress-Archive -Path "$staging\*" -DestinationPath $zipPath -CompressionLevel Optimal -Force
}
Remove-Item $staging -Recurse -Force -ErrorAction SilentlyContinue

if (-not (Test-Path $zipPath) -or (Get-Item $zipPath).Length -lt 1MB) {
    Write-Error "فشل إنشاء ZIP — أغلق البرامج التي تستخدم المشروع وحاول مرة أخرى"
    exit 1
}
$sizeMb = [math]::Round((Get-Item $zipPath).Length / 1MB, 1)
Write-Host ""
Write-Host "=========================================="
Write-Host "  تم بنجاح!"
Write-Host "  الملف: $zipPath"
Write-Host "  الحجم: $sizeMb MB"
Write-Host "=========================================="
Write-Host ""
Write-Host "الخطوة التالية:"
Write-Host "  1. ادخل cPanel → File Manager"
Write-Host "  2. ارفع alwaab-cpanel-upload.zip إلى /home/alwaabcp/"
Write-Host "  3. فك الضغط (Extract)"
Write-Host "  4. اتبع: deploy/cpanel/DEPLOY-ZIP-alwaabcpvc.md"
