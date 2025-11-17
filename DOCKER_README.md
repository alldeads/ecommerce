# Docker Setup for E-commerce Application

This Laravel e-commerce application is fully containerized with Docker for easy development and deployment.

## 🚀 Quick Start

### Prerequisites

- Docker Desktop installed
- Docker Compose installed
- Ports 8000, 3306, and 6379 available

### Setup Instructions

1. **Run the automated setup script:**

    ```bash
    ./docker-setup.sh
    ```

    This script will:
    - Copy environment configuration
    - Build Docker containers
    - Install dependencies
    - Run migrations and seeders
    - Build frontend assets
    - Set proper permissions

2. **Access the application:**
    - **Web Application**: http://localhost:8000
    - **MySQL Database**: localhost:3306
    - **Redis**: localhost:6379

### Manual Setup (Alternative)

If you prefer manual setup:

```bash
# Copy environment file
cp .env.docker .env

# Build and start containers
docker-compose up -d --build

# Install dependencies
docker-compose exec app composer install
docker-compose exec app npm install

# Generate app key
docker-compose exec app php artisan key:generate

# Run migrations and seed
docker-compose exec app php artisan migrate --seed

# Build assets
docker-compose exec app npm run build

# Set permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
```

## 📦 Services

The Docker setup includes:

- **app**: PHP 8.3-FPM application container
- **nginx**: Nginx web server (port 8000)
- **db**: MySQL 8.0 database (port 3306)
- **redis**: Redis cache and queue driver (port 6379)
- **queue**: Laravel queue worker

## 🛠️ Common Commands

### Container Management

```bash
# Start containers
docker-compose up -d

# Stop containers
docker-compose down

# Stop and remove volumes (⚠️ deletes database data)
docker-compose down -v

# View container status
docker-compose ps

# View logs
docker-compose logs -f

# View specific service logs
docker-compose logs -f app
docker-compose logs -f nginx
```

### Application Commands

```bash
# Access app container shell
docker-compose exec app bash

# Run artisan commands
docker-compose exec app php artisan migrate
docker-compose exec app php artisan db:seed
docker-compose exec app php artisan cache:clear
docker-compose exec app php artisan config:clear

# Run tests
docker-compose exec app php artisan test

# Install new composer packages
docker-compose exec app composer require package/name

# Install new npm packages
docker-compose exec app npm install package-name

# Build frontend assets
docker-compose exec app npm run build
docker-compose exec app npm run dev
```

### Database Commands

```bash
# Access MySQL CLI
docker-compose exec db mysql -u ecommerce_user -p ecommerce

# Create database backup
docker-compose exec db mysqldump -u ecommerce_user -p ecommerce > backup.sql

# Restore database backup
docker-compose exec -T db mysql -u ecommerce_user -p ecommerce < backup.sql
```

## 🔧 Configuration

### Environment Variables

The `.env.docker` file contains Docker-specific configuration:

- `DB_HOST=db` - MySQL container hostname
- `REDIS_HOST=redis` - Redis container hostname
- `APP_URL=http://localhost:8000` - Application URL

### Database Credentials (Development)

- **Host**: localhost (or `db` from within containers)
- **Port**: 3306
- **Database**: ecommerce
- **Username**: ecommerce_user
- **Password**: secret

> ⚠️ **Security Note**: Change these credentials for production!

## 📝 Development Workflow

### Hot Module Replacement (HMR) with Vite

To enable HMR during development:

```bash
# In one terminal, start Vite dev server
docker-compose exec app npm run dev

# Application will be available at http://localhost:8000
# Changes to Vue/JS files will hot-reload automatically
```

### Running Tests

```bash
# Run all tests
docker-compose exec app php artisan test

# Run specific test file
docker-compose exec app php artisan test --filter=MicroservicesTest

# Run with coverage
docker-compose exec app php artisan test --coverage
```

### Queue Worker

The queue worker runs automatically in a separate container. To restart it:

```bash
docker-compose restart queue
```

## 🐛 Troubleshooting

### Permission Issues

```bash
# Fix storage permissions
docker-compose exec app chmod -R 775 storage bootstrap/cache
docker-compose exec app chown -R www-data:www-data storage bootstrap/cache
```

### Database Connection Issues

```bash
# Restart database container
docker-compose restart db

# Check database logs
docker-compose logs db

# Wait longer for MySQL to initialize (first run)
sleep 15 && docker-compose exec app php artisan migrate
```

### Port Already in Use

If ports are already in use, modify `docker-compose.yml`:

```yaml
nginx:
    ports:
        - '8080:80' # Change 8000 to 8080 or any available port
```

### Clear Everything and Start Fresh

```bash
# Stop and remove all containers, volumes, and images
docker-compose down -v --rmi all

# Rebuild from scratch
./docker-setup.sh
```

## 🚢 Production Deployment

For production:

1. Update `.env` with production credentials
2. Set `APP_ENV=production` and `APP_DEBUG=false`
3. Use proper SSL/TLS certificates with Nginx
4. Configure proper backup strategy for MySQL
5. Use managed services for Redis if available
6. Set up proper logging and monitoring

### Production Docker Compose Example

Create `docker-compose.prod.yml`:

```yaml
version: '3.8'

services:
    app:
        build:
            context: .
            dockerfile: Dockerfile
        restart: always
        environment:
            - APP_ENV=production
            - APP_DEBUG=false
```

Run with: `docker-compose -f docker-compose.yml -f docker-compose.prod.yml up -d`

## 📚 Additional Resources

- [Laravel Docker Documentation](https://laravel.com/docs/deployment#docker)
- [Docker Compose Documentation](https://docs.docker.com/compose/)
- [Nginx Configuration](https://nginx.org/en/docs/)

## 💡 Tips

- Use `docker-compose exec` instead of `docker exec` for convenience
- Keep your Docker images updated with `docker-compose pull`
- Use `.dockerignore` to reduce image size
- Monitor container resources with `docker stats`
