# Docker Setup Script for Dibuiltadi Backend (PowerShell)
Write-Host "🐳 Setting up Docker environment for Dibuiltadi Backend..." -ForegroundColor Cyan

# Check if .env file exists
if (-not (Test-Path ".env")) {
    if (Test-Path ".env.example") {
        Write-Host "📋 Copying .env.example to .env..." -ForegroundColor Yellow
        Copy-Item ".env.example" ".env"
    } else {
        Write-Host "📝 Creating .env file..." -ForegroundColor Yellow
        @'
APP_NAME="Dibuiltadi Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8080

DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=dibuiltadi_db
DB_USERNAME=dibuiltadi_user
DB_PASSWORD=dibuiltadi_password

SESSION_DRIVER=redis
CACHE_STORE=redis
QUEUE_CONNECTION=redis

REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

LOG_CHANNEL=stack
'@ | Out-File -FilePath ".env" -Encoding UTF8
    }
}

# Build and start containers
Write-Host "🏗️  Building Docker containers..." -ForegroundColor Green
docker-compose up -d --build

# Wait for database to be ready
Write-Host "⏳ Waiting for database to be ready..." -ForegroundColor Yellow
Start-Sleep -Seconds 15

# Generate APP_KEY if it's empty
Write-Host "🔑 Generating application key..." -ForegroundColor Green
docker-compose exec app php artisan key:generate

# Install composer dependencies
Write-Host "📦 Installing Composer dependencies..." -ForegroundColor Green
docker-compose exec app composer install

# Run database migrations
Write-Host "📊 Running database migrations..." -ForegroundColor Green
docker-compose exec app php artisan migrate --force

# Set proper permissions
Write-Host "🔒 Setting file permissions..." -ForegroundColor Green
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and cache configuration
Write-Host "🧹 Clearing and caching configuration..." -ForegroundColor Green
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear

Write-Host "✅ Docker setup completed!" -ForegroundColor Green
Write-Host ""
Write-Host "🌐 Your application is now running at: http://localhost:8080" -ForegroundColor Cyan
Write-Host "📊 PhpMyAdmin is available at: http://localhost:8081" -ForegroundColor Cyan
Write-Host "🔧 Vite dev server is running at: http://localhost:5173" -ForegroundColor Cyan
Write-Host ""
Write-Host "📚 Useful commands:" -ForegroundColor Yellow
Write-Host "  docker-compose up -d          # Start all services"
Write-Host "  docker-compose down           # Stop all services"
Write-Host "  docker-compose exec app bash  # Access the app container"
Write-Host "  docker-compose logs app       # View app logs" 