# ALWAAB ERP+CRM — Setup Script (Windows PowerShell)
# Requires: Docker Desktop

Write-Host "=== ALWAAB ERP+CRM Setup ===" -ForegroundColor Cyan

if (-not (Get-Command docker -ErrorAction SilentlyContinue)) {
    Write-Host "Docker is required. Install Docker Desktop: https://www.docker.com/products/docker-desktop/" -ForegroundColor Red
    exit 1
}

if (-not (Test-Path .env)) {
    Copy-Item .env.example .env
    Write-Host "Created .env from .env.example" -ForegroundColor Green
}

Write-Host "Starting containers..." -ForegroundColor Yellow
docker compose up -d --build

Write-Host "Installing PHP dependencies..." -ForegroundColor Yellow
docker compose exec app composer install --no-interaction

Write-Host "Generating application key..." -ForegroundColor Yellow
docker compose exec app php artisan key:generate

Write-Host "Running migrations..." -ForegroundColor Yellow
docker compose exec app php artisan migrate --force

Write-Host "Seeding database..." -ForegroundColor Yellow
docker compose exec app php artisan db:seed --force

Write-Host "Creating storage link..." -ForegroundColor Yellow
docker compose exec app php artisan storage:link

Write-Host ""
Write-Host "=== Setup Complete ===" -ForegroundColor Green
Write-Host "Web App:  http://localhost:8080" -ForegroundColor Cyan
Write-Host "API:      http://localhost:8080/api/v1" -ForegroundColor Cyan
Write-Host ""
Write-Host "Default login: admin@alwaab.ae / password" -ForegroundColor Yellow
