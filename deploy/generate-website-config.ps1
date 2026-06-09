# Generate alwaab-website/js/erp-config.js from alwaab-erp/.env (Windows local dev)
$root = Split-Path -Parent $PSScriptRoot
$envFile = Join-Path $root "alwaab-erp\.env"
$out = Join-Path $root "alwaab-website\js\erp-config.js"

if (-not (Test-Path $envFile)) {
    Write-Error "alwaab-erp\.env not found"
    exit 1
}

$appUrl = ""
$apiKey = ""
Get-Content $envFile | ForEach-Object {
    if ($_ -match '^APP_URL=(.+)$') { $appUrl = $matches[1].Trim() }
    if ($_ -match '^WEBSITE_API_KEY=(.+)$') { $apiKey = $matches[1].Trim() }
}

if (-not $apiKey) {
    Write-Error "WEBSITE_API_KEY is empty in .env"
    exit 1
}

$apiUrl = "$($appUrl.TrimEnd('/'))/api/v1/website/quote-requests"

@"

// Auto-generated — do not commit
window.ALWAAB_ERP = {
  apiUrl: "$apiUrl",
  apiKey: "$apiKey",
};
"@ | Set-Content -Path $out -Encoding UTF8

Write-Host "Generated $out"
