$root = Split-Path -Parent $PSScriptRoot
$template = Join-Path $root "docker\nginx\production.conf.template"
$out = Join-Path $root "docker\nginx\production.conf"
$envFile = Join-Path $root ".env"

$erpDomain = "erp.alwaab.ae"
$websiteDomain = "alwaab.ae"

if (Test-Path $envFile) {
    Get-Content $envFile | ForEach-Object {
        if ($_ -match '^ERP_DOMAIN=(.+)$') { $erpDomain = $matches[1].Trim() }
        if ($_ -match '^WEBSITE_DOMAIN=(.+)$') { $websiteDomain = $matches[1].Trim() }
    }
}

$content = Get-Content $template -Raw
$content = $content -replace '\$\{ERP_DOMAIN\}', $erpDomain
$content = $content -replace '\$\{WEBSITE_DOMAIN\}', $websiteDomain
Set-Content -Path $out -Value $content -Encoding UTF8
Write-Host "Generated nginx config for $websiteDomain + $erpDomain"
