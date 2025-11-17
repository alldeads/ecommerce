#!/bin/bash

echo "🚀 Starting E-commerce Docker Setup..."

# Detect docker-compose command
if command -v docker-compose &> /dev/null; then
    DOCKER_COMPOSE="docker-compose"
elif docker compose version &> /dev/null; then
    DOCKER_COMPOSE="docker compose"
else
    echo "❌ Error: Docker Compose is not installed!"
    exit 1
fi

# Copy environment file if it doesn't exist
if [ ! -f .env ]; then
    echo "📝 Copying .env.docker to .env..."
    cp .env.docker .env
fi

# Build and start containers
echo "🐳 Building Docker containers..."
$DOCKER_COMPOSE up -d --build

# Wait for MySQL to be ready
echo "⏳ Waiting for MySQL to be ready..."
sleep 10

# Install dependencies
echo "📦 Installing Composer dependencies..."
$DOCKER_COMPOSE exec app composer install

echo "📦 Installing NPM dependencies..."
$DOCKER_COMPOSE exec app npm install

# Generate application key
echo "🔑 Generating application key..."
$DOCKER_COMPOSE exec app php artisan key:generate

# Run migrations
echo "🗄️ Running database migrations..."
$DOCKER_COMPOSE exec app php artisan migrate

# Seed database
echo "🌱 Seeding database..."
$DOCKER_COMPOSE exec app php artisan db:seed

# Build frontend assets
echo "🎨 Building frontend assets..."
$DOCKER_COMPOSE exec app npm run build

# Set permissions
echo "🔐 Setting storage permissions..."
$DOCKER_COMPOSE exec app chmod -R 775 storage bootstrap/cache
$DOCKER_COMPOSE exec app chown -R www-data:www-data storage bootstrap/cache

# Clear and cache config
echo "🧹 Clearing and caching configuration..."
$DOCKER_COMPOSE exec app php artisan config:clear
$DOCKER_COMPOSE exec app php artisan cache:clear
$DOCKER_COMPOSE exec app php artisan route:clear
$DOCKER_COMPOSE exec app php artisan view:clear

echo "✅ Setup complete!"
echo ""
echo "🌐 Application is running at: http://localhost:8000"
echo "📊 Database: MySQL on port 3306"
echo "📮 Redis: Running on port 6379"
echo ""
echo "Useful commands:"
echo "  $DOCKER_COMPOSE ps          - View running containers"
echo "  $DOCKER_COMPOSE logs -f     - View logs"
echo "  $DOCKER_COMPOSE exec app bash - Access app container"
echo "  $DOCKER_COMPOSE down        - Stop containers"
echo "  $DOCKER_COMPOSE down -v     - Stop and remove volumes"
