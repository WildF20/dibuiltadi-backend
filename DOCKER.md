# 🐳 Docker Setup for Dibuiltadi Backend

This document provides a complete guide for setting up and running the Dibuiltadi Backend Laravel application using Docker Compose.

## 📋 Prerequisites

- [Docker](https://docs.docker.com/get-docker/) installed
- [Docker Compose](https://docs.docker.com/compose/install/) installed
- At least 4GB of available RAM

## 🏗️ Architecture

The Docker setup includes the following services:

### Core Services
- **app**: PHP 8.2-FPM application container
- **webserver**: Nginx web server (port 8080)
- **database**: MySQL 8.0 database (port 3306)
- **redis**: Redis cache server (port 6379)

### Development Services
- **queue**: Laravel queue worker
- **scheduler**: Laravel task scheduler
- **node**: Node.js for Vite asset compilation (port 5173)
- **phpmyadmin**: Database management interface (port 8081)

## 🚀 Quick Start

### Option 1: Automated Setup (Recommended)

```bash
# Make the setup script executable
chmod +x docker-setup.sh

# Run the automated setup
./docker-setup.sh
```

### Option 2: Manual Setup

1. **Build and start containers:**
   ```bash
   docker-compose up -d --build
   ```

2. **Install dependencies and setup:**
   ```bash
   docker-compose exec app composer install
   docker-compose exec app php artisan key:generate
   docker-compose exec app php artisan migrate
   ```

## 🌐 Access Points

After successful setup, your application will be available at:

- **Main Application**: http://localhost:8080
- **PhpMyAdmin**: http://localhost:8081
- **Vite Dev Server**: http://localhost:5173

## 📊 Database Access

### Default Credentials
- **Database**: `dibuiltadi_db`
- **Username**: `dibuiltadi_user`
- **Password**: `dibuiltadi_password`
- **Root Password**: `root_password`

### Direct Database Connection
```bash
docker-compose exec database mysql -u dibuiltadi_user -p dibuiltadi_db
```

## 🛠️ Common Commands

### Container Management
```bash
# Start all services
docker-compose up -d

# Stop all services
docker-compose down

# Restart specific service
docker-compose restart app

# View logs
docker-compose logs app

# Access container shell
docker-compose exec app bash
```

### Laravel Commands
```bash
# Artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan cache:clear

# Composer commands
docker-compose exec app composer install

# Run tests
docker-compose exec app ./vendor/bin/pest
```

### File Permissions
```bash
# Fix storage permissions
docker-compose exec app chown -R www-data:www-data /var/www/html/storage
docker-compose exec app chmod -R 775 /var/www/html/storage
```

### Testing
```bash
# Run tests
docker-compose exec app php artisan test
docker-compose exec app ./vendor/bin/pest

# Run specific test file
docker-compose exec app ./vendor/bin/pest tests/Unit/OrderControllerTest.php
```

## 🔧 Development Workflow

### Making Code Changes
1. Edit files on your host machine
2. Changes are automatically reflected in containers (volume mounted)
3. For PHP configuration changes, restart the app container:
   ```bash
   docker-compose restart app
   ```

### Database Changes
```bash
# Create new migration
docker-compose exec app php artisan make:migration create_new_table

# Run migrations
docker-compose exec app php artisan migrate

# Reset database
docker-compose exec app php artisan migrate:fresh --seed
```

### Asset Compilation
```bash
# Install npm packages
docker-compose exec node npm install

# Build assets for development
docker-compose exec node npm run dev

# Build assets for production
docker-compose exec node npm run build
```

## 🐛 Troubleshooting

### Container Issues
```bash
# Check container status
docker-compose ps

# Check container logs
docker-compose logs app
docker-compose logs database

# Rebuild containers
docker-compose down
docker-compose up -d --build
```

### Database Connection Issues
```bash
# Check database container
docker-compose logs database

# Test database connection
docker-compose exec app php artisan tinker
> DB::connection()->getPdo();
```

### Permission Issues
```bash
# Fix file permissions
docker-compose exec app chown -R www-data:www-data /var/www/html
docker-compose exec app chmod -R 755 /var/www/html/storage
docker-compose exec app chmod -R 755 /var/www/html/bootstrap/cache
```

### Cache Issues
```bash
# Clear all caches
docker-compose exec app php artisan config:clear
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan route:clear
docker-compose exec app php artisan view:clear
```

## 🚀 Production Deployment

For production deployment, consider:

1. **Update environment variables:**
   - Set `APP_ENV=production`
   - Set `APP_DEBUG=false`
   - Use secure database passwords
   - Configure proper logging

2. **Use production-optimized Dockerfile:**
   - Remove development tools
   - Use multi-stage builds
   - Optimize image size

3. **Security considerations:**
   - Use secrets management
   - Enable HTTPS
   - Configure proper firewall rules
   - Regular security updates

## 📝 File Structure

```
dibuiltadi-backend/
├── docker/
│   ├── nginx/
│   │   ├── default.conf
│   │   └── nginx.conf
│   ├── php/
│   │   └── local.ini
│   ├── mysql/
│   │   └── my.cnf
│   └── supervisor/
│       └── supervisord.conf
├── docker-compose.yml
├── Dockerfile
├── docker-setup.sh
└── DOCKER.md
```

## 🆘 Support

If you encounter any issues:

1. Check the troubleshooting section above
2. Review container logs: `docker-compose logs`
3. Ensure Docker and Docker Compose are up to date
4. Check available system resources (RAM, disk space)

For Laravel-specific issues, refer to the [Laravel Documentation](https://laravel.com/docs). 