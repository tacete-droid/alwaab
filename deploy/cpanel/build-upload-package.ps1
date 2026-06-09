# يبني حزمة ZIP للرفع عبر cPanel File Manager (بدون SSH)
$root = Split-Path (Split-Path $PSScriptRoot -Parent) -Parent
$outDir = Join-Path $root "dist"
$zipPath = Join-Path $outDir "alwaab-cpanel-upload.zip"

Write-Host "==> بناء الواجهة..."
Push-Location (Join-Path $root "alwaab-erp")
npm run build
Pop-Location

Write-Host "==> تجهيز الحزمة..."
if (Test-Path $outDir) { Remove-Item $outDir -Recurse -Force }
New-Item -ItemType Directory -Path $outDir | Out-Null

$staging = Join-Path $outDir "alwaab"
New-Item -ItemType Directory -Path $staging | Out-Null

# نسخ بدون vendor/node_modules/.env
$exclude = @('vendor', 'node_modules', '.env', 'storage\logs', 'storage\framework\cache\data', 'storage\framework\sessions', 'storage\framework\views')
robocopy (Join-Path $root "alwaab-erp") (Join-Path $staging "alwaab-erp") /E /XD vendor node_modules /XF .env /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
robocopy (Join-Path $root "alwaab-website") (Join-Path $staging "alwaab-website") /E /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null
robocopy (Join-Path $root "deploy") (Join-Path $staging "deploy") /E /NFL /NDL /NJH /NJS /nc /ns /np | Out-Null

if (Test-Path $zipPath) { Remove-Item $zipPath -Force }
Compress-Archive -Path "$staging\*" -DestinationPath $zipPath -Force

Write-Host ""
Write-Host "تم: $zipPath"
Write-Host ""
Write-Host "ارفع الملف عبر cPanel File Manager إلى /home/USERNAME/"
Write-Host "ثم فك الضغط وشغّل first-install.sh عبر Terminal"
