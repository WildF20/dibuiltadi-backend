#!/bin/bash

# Docker Setup Script for Dibuiltadi Backend
echo "🐳 Setting up Docker environment for Dibuiltadi Backend..."

# Create .env file from .env.example if it doesn't exist
if [ ! -f .env ]; then
    if [ -f .env.example ]; then
        echo "📋 Copying .env.example to .env..."
        cp .env.example .env
    else
        echo "📝 Creating .env file..."
        cat > .env << 'EOF'
APP_NAME="Dibuiltadi Backend"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_TIMEZONE=Asia/Jakarta
APP_URL=http://localhost:8080

APP_LOCALE=en
APP_FALLBACK_LOCALE=en
APP_FAKER_LOCALE=en_US

APP_MAINTENANCE_DRIVER=file
APP_MAINTENANCE_STORE=database

BCRYPT_ROUNDS=12

LOG_CHANNEL=stack
LOG_STACK=single
LOG_DEPRECATIONS_CHANNEL=null
LOG_LEVEL=debug

DB_CONNECTION=mysql
DB_HOST=database
DB_PORT=3306
DB_DATABASE=dibuiltadi_db
DB_USERNAME=dibuiltadi_user
DB_PASSWORD=dibuiltadi_password

SESSION_DRIVER=redis
SESSION_LIFETIME=120
SESSION_ENCRYPT=false
SESSION_PATH=/
SESSION_DOMAIN=null

BROADCAST_CONNECTION=log
FILESYSTEM_DISK=local
QUEUE_CONNECTION=redis

CACHE_STORE=redis
CACHE_PREFIX=

MEMCACHED_HOST=127.0.0.1

REDIS_CLIENT=phpredis
REDIS_HOST=redis
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=log
MAIL_HOST=127.0.0.1
MAIL_PORT=2525
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="hello@example.com"
MAIL_FROM_NAME="${APP_NAME}"

AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=
AWS_USE_PATH_STYLE_ENDPOINT=false

VITE_APP_NAME="${APP_NAME}"
EOF
    fi
fi

# Build and start containers
echo "🏗️  Building Docker containers..."
docker-compose up -d --build

# Wait for database to be ready
echo "⏳ Waiting for database to be ready..."
sleep 10

# Generate APP_KEY if it's empty
echo "🔑 Generating application key..."
docker-compose exec app php artisan key:generate

# Run database migrations
echo "📊 Running database migrations..."
docker-compose exec app php artisan migrate --force

# Install composer dependencies
echo "📦 Installing Composer dependencies..."
docker-compose exec app composer install

# Set proper permissions
echo "🔒 Setting file permissions..."
docker-compose exec app chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
docker-compose exec app chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Clear and cache configuration
echo "🧹 Clearing and caching configuration..."
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear

echo "✅ Docker setup completed!"
echo ""
echo "🌐 Your application is now running at: http://localhost:8080"
echo "📊 PhpMyAdmin is available at: http://localhost:8081"
echo "🔧 Vite dev server is running at: http://localhost:5173"
echo ""
echo "📚 Useful commands:"
echo "  docker-compose up -d          # Start all services"
echo "  docker-compose down           # Stop all services"
echo "  docker-compose exec app bash  # Access the app container"
echo "  docker-compose logs app       # View app logs"
echo "  docker-compose restart app    # Restart app container" 