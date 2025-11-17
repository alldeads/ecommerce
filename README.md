# E-Commerce Microservices System

A Laravel-based e-commerce system with 3 microservices architecture: Catalog, Checkout, and Email services.

## Table of Contents

- [Prerequisites](#prerequisites)
- [Quick Start with Docker](#quick-start-with-docker)
- [Local Development Setup](#local-development-setup)
- [Provisioning & Deployment](#provisioning--deployment)
- [Architecture Overview](#architecture-overview)
- [Testing](#testing)
- [API Documentation](#api-documentation)
- [Troubleshooting](#troubleshooting)

## Prerequisites

### For Docker Setup (Recommended)

- **Docker Desktop** (v20.10 or higher)
    - Download: https://www.docker.com/products/docker-desktop
    - Includes Docker Compose
- **Git** for version control
- **Minimum System Requirements**:
    - 4GB RAM
    - 10GB free disk space
    - macOS, Windows 10/11, or Linux

### For Local Development (Without Docker)

- **PHP** 8.3 or higher
    - Extensions: pdo_mysql, mbstring, exif, pcntl, bcmath, gd
- **Composer** (v2.0 or higher)
- **Node.js** 16.x or higher & npm
- **MySQL** 8.0 or higher
- **Redis** (optional, for caching and queues)
- **Git**

### Verification Commands

```bash
# Check Docker
docker --version
docker compose version

# Check PHP (for local setup)
php --version
composer --version

# Check Node.js
node --version
npm --version

# Check MySQL (for local setup)
mysql --version
```

## Quick Start with Docker

The easiest way to run this project is using Docker. Everything is containerized and ready to go!

### Running the Application

1. **Clone the repository**

    ```bash
    git clone <repository-url>
    cd ecommerce
    ```

2. **Start Docker containers**

    ```bash
    docker compose up -d
    ```

    This will start:
    - PHP-FPM (app)
    - Nginx web server (port 8000)
    - MySQL database (port 3307)
    - Redis cache (port 6379)
    - Queue worker

3. **Run the setup script**

    ```bash
    ./docker-setup.sh
    ```

    Or manually run:

    ```bash
    docker compose exec app composer install
    docker compose exec app npm install
    docker compose exec app npm run build
    docker compose exec app php artisan key:generate
    docker compose exec app php artisan migrate --seed
    ```

4. **Access the application**
    - **Web Application**: http://localhost:8000
    - **Database**: localhost:3307 (MySQL)
    - **Default Login**: test@example.com / password

5. **Run tests (optional)**

    ```bash
    docker compose exec app php artisan test
    ```

    Expected result: All 48 tests should pass ✅

### Useful Docker Commands

```bash
# View running containers
docker compose ps

# View logs
docker compose logs -f app

# Stop containers
docker compose down

# Rebuild containers (after Dockerfile changes)
docker compose build --no-cache
docker compose up -d

# Run artisan commands
docker compose exec app php artisan [command]

# Run npm commands
docker compose exec app npm run [command]

# Access database
docker compose exec db mysql -u root -p

# Access container shell
docker compose exec app bash

# Run tests
docker compose exec app php artisan test
```

### Environment Configuration

The Docker setup uses `.env.docker` which is pre-configured for the containerized environment. Key settings:

- **Database**: MySQL on `db:3306` (external port 3307)
- **Redis**: Available on `redis:6379`
- **Mail**: Uses MailHog on `mailhog:1025`
- **App URL**: http://localhost:8000

## Architecture Overview

### 1. Catalog Service

- **Purpose**: Manages product catalog and product information
- **Features**:
    - List all available products
    - View single product details
    - Search products by name or description
    - Filter active products only

### 2. Checkout Service

- **Purpose**: Handles order creation and management
- **Features**:
    - Create orders with multiple products
    - Validate product availability and stock
    - Calculate order totals
    - Manage order status
    - View order history

### 3. Email Service

- **Purpose**: Sends order confirmation emails to customers
- **Features**:
    - Automatic email on order creation
    - Detailed order summary with items and pricing
    - HTML formatted email template

## Database Schema

### Products Table

- `id` - Primary key
- `name` - Product name
- `description` - Product description
- `price` - Product price (decimal)
- `stock` - Available quantity
- `sku` - Stock keeping unit (unique)
- `image_url` - Product image URL
- `is_active` - Product availability status
- `timestamps` - Created/updated timestamps

### Orders Table

- `id` - Primary key
- `user_id` - Foreign key to users
- `order_number` - Unique order identifier
- `total_amount` - Order total (decimal)
- `status` - Order status (pending, processing, completed, cancelled)
- `customer_email` - Customer email address
- `customer_name` - Customer name
- `shipping_address` - Shipping address
- `timestamps` - Created/updated timestamps

### Order Items Table

- `id` - Primary key
- `order_id` - Foreign key to orders
- `product_id` - Foreign key to products
- `quantity` - Item quantity
- `price` - Price at time of purchase
- `subtotal` - Line item total
- `timestamps` - Created/updated timestamps

## API Endpoints

### Catalog Service (Public)

#### Get All Products

```
GET /api/catalog/products
```

Optional query parameter: `?search=keyword`

**Response:**

```json
{
    "success": true,
    "data": [
        {
            "id": 1,
            "name": "Laptop Pro 15",
            "description": "High-performance laptop...",
            "price": "1299.99",
            "stock": 25,
            "sku": "LAP-PRO-15",
            "image_url": "https://...",
            "is_active": true
        }
    ]
}
```

#### Get Single Product

```
GET /api/catalog/products/{id}
```

**Response:**

```json
{
    "success": true,
    "data": {
        "id": 1,
        "name": "Laptop Pro 15",
        "description": "High-performance laptop...",
        "price": "1299.99",
        "stock": 25,
        "sku": "LAP-PRO-15"
    }
}
```

### Checkout Service (Authenticated)

#### Create Order

```
POST /api/checkout/orders
Authorization: Session Cookie (auth middleware)
```

**Request Body:**

```json
{
    "items": [
        {
            "product_id": 1,
            "quantity": 2
        },
        {
            "product_id": 2,
            "quantity": 1
        }
    ],
    "customer_name": "John Doe",
    "customer_email": "john@example.com",
    "shipping_address": "123 Main St, City, State 12345"
}
```

**Response:**

```json
{
  "success": true,
  "message": "Order created successfully",
  "data": {
    "id": 1,
    "order_number": "ORD-123ABC",
    "total_amount": "2639.97",
    "status": "pending",
    "customer_email": "john@example.com",
    "orderItems": [...]
  }
}
```

#### Get User Orders

```
GET /api/checkout/orders
Authorization: Session Cookie (auth middleware)
```

#### Get Single Order

```
GET /api/checkout/orders/{id}
Authorization: Session Cookie (auth middleware)
```

## Setup Instructions

### Option 1: Docker (Recommended)

See the [Quick Start with Docker](#quick-start-with-docker) section at the top of this document.

### Option 2: Local Development (Without Docker)

#### 1. Install Dependencies

```bash
composer install
npm install
```

#### 2. Configure Environment

Copy `.env.example` to `.env` and configure:

```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=ecommerce
DB_USERNAME=root
DB_PASSWORD=

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS="noreply@example.com"
MAIL_FROM_NAME="${APP_NAME}"
```

### 3. Run Migrations

```bash
php artisan migrate
```

### 4. Seed Database

```bash
php artisan db:seed
```

This will create:

- 1 test user (test@example.com / password)
- 20 sample products

### 5. Start Development Server

```bash
php artisan serve
```

### 6. Test Email (Optional)

For local development, use MailHog or Laravel Log:

```env
MAIL_MAILER=log
```

## Provisioning & Deployment

### Docker Production Deployment

#### 1. Prepare Production Environment

```bash
# Clone repository
git clone <repository-url>
cd ecommerce

# Copy and configure production environment
cp .env.docker .env
nano .env  # Update production settings
```

#### 2. Configure Production Settings

Update the following in `.env`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

# Use strong random key
APP_KEY=base64:GENERATE_WITH_php_artisan_key:generate

# Production database
DB_HOST=your-db-host
DB_DATABASE=your-db-name
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password

# Production mail settings
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=587
MAIL_USERNAME=your-username
MAIL_PASSWORD=your-password
MAIL_ENCRYPTION=tls
```

#### 3. Build and Deploy

```bash
# Build optimized Docker images
docker compose -f docker-compose.yml build --no-cache

# Start services
docker compose up -d

# Run migrations and seed
docker compose exec app php artisan migrate --force
docker compose exec app php artisan db:seed --force

# Cache configurations for performance
docker compose exec app php artisan config:cache
docker compose exec app php artisan route:cache
docker compose exec app php artisan view:cache
```

#### 4. SSL/HTTPS Setup

For production, configure SSL certificates in your nginx configuration:

```bash
# Edit nginx config to include SSL
docker compose exec nginx nano /etc/nginx/conf.d/default.conf
```

### AWS EC2 Deployment

A sample CloudFormation template is provided in `laravel-ec2.yaml` for deploying to AWS EC2.

```bash
# Deploy to AWS using CloudFormation
aws cloudformation create-stack \
  --stack-name ecommerce-app \
  --template-body file://laravel-ec2.yaml \
  --parameters ParameterKey=KeyName,ParameterValue=your-key-pair
```

### Environment Variables Reference

| Variable           | Description                                | Default                    |
| ------------------ | ------------------------------------------ | -------------------------- |
| `APP_ENV`          | Application environment (local/production) | local                      |
| `APP_DEBUG`        | Enable debug mode                          | true                       |
| `APP_URL`          | Application URL                            | http://localhost:8000      |
| `DB_CONNECTION`    | Database driver                            | mysql                      |
| `DB_HOST`          | Database host                              | db (Docker) / 127.0.0.1    |
| `DB_PORT`          | Database port                              | 3306                       |
| `DB_DATABASE`      | Database name                              | ecommerce                  |
| `REDIS_HOST`       | Redis host                                 | redis (Docker) / 127.0.0.1 |
| `MAIL_MAILER`      | Mail driver                                | smtp                       |
| `QUEUE_CONNECTION` | Queue driver                               | redis                      |

## Testing the API

### 1. Get Products (No Auth Required)

```bash
curl http://localhost:8000/api/catalog/products
```

### 2. Create Order (With Authentication)

First, you need to be logged in. You can use the web interface or create an authenticated session.

```bash
# Using the web interface, log in first, then:
curl -X POST http://localhost:8000/api/checkout/orders \
  -H "Content-Type: application/json" \
  -H "Cookie: your-session-cookie" \
  -d '{
    "items": [
      {"product_id": 1, "quantity": 2},
      {"product_id": 2, "quantity": 1}
    ],
    "shipping_address": "123 Main St"
  }'
```

### 3. Run Automated Tests

```bash
php artisan test --filter MicroservicesTest
```

All tests validate:

- ✅ Catalog service lists products
- ✅ Catalog service shows single product details
- ✅ Catalog service searches products
- ✅ Checkout requires authentication
- ✅ Checkout creates orders successfully
- ✅ Checkout validates stock availability
- ✅ Checkout lists user orders
- ✅ Checkout prevents unauthorized access
- ✅ Email service sends confirmation emails

## Service Classes

### CatalogService (`app/Services/CatalogService.php`)

- `getAllProducts()` - Get all active products
- `getProductById($id)` - Get single product
- `getProductBySku($sku)` - Get product by SKU
- `searchProducts($query)` - Search products

### CheckoutService (`app/Services/CheckoutService.php`)

- `createOrder($user, $items, $orderData)` - Create new order
- `getOrder($orderId)` - Get order by ID
- `getUserOrders($user)` - Get all user orders

### EmailService (`app/Services/EmailService.php`)

- `sendOrderConfirmation($order)` - Send order confirmation email

## Key Features

✅ **Microservices Architecture** - Separated concerns for catalog, checkout, and email
✅ **Database Persistence** - Products and orders stored in MySQL/PostgreSQL
✅ **Automatic Emails** - Order confirmation sent on every new order
✅ **Stock Management** - Automatic inventory deduction on order
✅ **Transaction Safety** - Orders created atomically with rollback on failure
✅ **Authentication** - Protected checkout endpoints with session-based auth
✅ **Validation** - Input validation and stock checking
✅ **RESTful API** - Clean API design with proper HTTP methods and status codes
✅ **Comprehensive Tests** - Full test suite with 9 passing tests covering all microservices

## Testing

The application includes a comprehensive test suite covering all functionality.

### Running Tests with Docker

```bash
# Run all tests
docker compose exec app php artisan test

# Run specific test suite
docker compose exec app php artisan test --filter MicroservicesTest

# Run with code coverage
docker compose exec app php artisan test --coverage
```

### Running Tests Locally

```bash
# Run all tests
php artisan test

# Run specific test suite
php artisan test --filter MicroservicesTest
```

### Test Coverage

**Total: 48 tests passing** ✅

#### E-commerce Microservices Tests (9 tests)

- ✓ Catalog service can list all products
- ✓ Catalog service can show single product
- ✓ Catalog service can search products
- ✓ Checkout service requires authentication
- ✓ Checkout service can create order
- ✓ Checkout service validates stock availability
- ✓ Checkout service can list user orders
- ✓ Checkout service prevents accessing other users orders
- ✓ Email service sends confirmation on order creation

#### Authentication & Settings Tests (39 tests)

- Login, Registration, Logout (6 tests)
- Email Verification (6 tests)
- Password Confirmation (2 tests)
- Password Reset (5 tests)
- Two-Factor Authentication (4 tests)
- Profile & Settings Management (16 tests)

All tests validate:

- ✅ Catalog service product listing and details
- ✅ Checkout service order creation and validation
- ✅ Email service order confirmation
- ✅ Authentication and authorization
- ✅ Stock management
- ✅ User account management
- ✅ Security features (2FA, password reset)

## Troubleshooting

### Docker Issues

#### Containers won't start

```bash
# Check if ports are already in use
lsof -i :8000  # Web server
lsof -i :3307  # MySQL
lsof -i :6379  # Redis

# View detailed logs
docker compose logs app
docker compose logs db
docker compose logs nginx

# Remove all containers and start fresh
docker compose down -v
docker compose up -d
```

#### Permission issues

```bash
# Fix file permissions in container
docker compose exec app chown -R www-data:www-data /var/www/html/storage
docker compose exec app chown -R www-data:www-data /var/www/html/bootstrap/cache
docker compose exec app chmod -R 775 /var/www/html/storage
docker compose exec app chmod -R 775 /var/www/html/bootstrap/cache
```

#### Database connection issues

```bash
# Check if MySQL is running
docker compose ps db

# Check MySQL logs
docker compose logs db

# Verify .env.docker settings
cat .env.docker | grep DB_

# Test connection manually
docker compose exec db mysql -u root -p
```

#### Frontend build issues

```bash
# Clear node_modules and reinstall
docker compose exec app rm -rf node_modules package-lock.json
docker compose exec app npm install
docker compose exec app npm run build

# Check for Node.js version issues
docker compose exec app node --version  # Should be v16.x or higher
```

### Local Development Issues

#### Composer dependency conflicts

```bash
# Clear composer cache
composer clear-cache

# Remove vendor and reinstall
rm -rf vendor composer.lock
composer install
```

#### Database migration errors

```bash
# Reset database completely
php artisan migrate:fresh --seed

# Check migration status
php artisan migrate:status

# Rollback and re-run specific migration
php artisan migrate:rollback --step=1
php artisan migrate
```

#### Port already in use

```bash
# For macOS/Linux - find and kill process using port 8000
lsof -ti:8000 | xargs kill -9

# Use different port
php artisan serve --port=8001
```

#### NPM build failures

```bash
# Clear npm cache
npm cache clean --force

# Remove and reinstall
rm -rf node_modules package-lock.json
npm install

# Check Node.js version
node --version  # Should be v16.x or higher
nvm use 16  # If using nvm
```

### Common Application Issues

#### 500 Internal Server Error

```bash
# Check Laravel logs
tail -f storage/logs/laravel.log

# Clear all caches
php artisan cache:clear
php artisan config:clear
php artisan route:clear
php artisan view:clear

# Generate application key if missing
php artisan key:generate
```

#### Session/Authentication issues

```bash
# Clear session data
php artisan session:clear

# Check session driver in .env
# SESSION_DRIVER=file (or redis for production)

# Ensure storage/framework/sessions is writable
chmod -R 775 storage/
```

#### Email not sending

```bash
# Check mail configuration
php artisan config:clear

# Test mail locally with log driver
# In .env: MAIL_MAILER=log
# Check storage/logs/laravel.log for email content

# For MailHog (Docker)
# Access: http://localhost:8025
```

#### Queue jobs not processing

```bash
# Start queue worker
php artisan queue:work

# Or with Docker
docker compose exec app php artisan queue:work

# Check failed jobs
php artisan queue:failed

# Retry failed jobs
php artisan queue:retry all
```

### Performance Optimization

```bash
# Cache configuration (production)
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Optimize autoloader
composer install --optimize-autoloader --no-dev

# Clear all caches (development)
php artisan optimize:clear
```

### Getting Help

- **Documentation**: See full [Laravel documentation](https://laravel.com/docs)
- **Docker Docs**: See [Docker documentation](https://docs.docker.com)
- **GitHub Issues**: Report bugs at the repository issues page
- **Logs**: Always check `storage/logs/laravel.log` for detailed error messages

## Implementation Summary

This e-commerce microservices system has been successfully implemented with:

1. **Database Layer**: 3 tables (products, orders, order_items) with proper relationships and migrations
2. **Service Layer**: 3 independent services (CatalogService, CheckoutService, EmailService) with clear separation of concerns
3. **API Layer**: RESTful endpoints for catalog (public) and checkout (authenticated)
4. **Email Integration**: Automated order confirmation emails sent on every order
5. **Data Persistence**: All product and order data stored in the database
6. **Test Coverage**: Comprehensive test suite validating all functionality

The system is production-ready and follows Laravel best practices with proper error handling, validation, and transaction management.

## Technical Notes

- Email service is decoupled and won't fail orders if email sending fails
- All monetary values stored as decimals with 2 decimal places
- Stock automatically decremented on successful order
- Orders include snapshot of product prices at time of purchase
- Order numbers are unique and generated automatically
- Session-based authentication is used (not Sanctum) for simplicity and security
- Frontend built with Vue 3, Inertia.js, and Tailwind CSS v4
- Database supports both SQLite (development) and MySQL (production)

## Project Structure

```
ecommerce/
├── app/
│   ├── Http/Controllers/      # API and Web Controllers
│   ├── Models/                 # Eloquent Models
│   ├── Services/               # Business Logic (Microservices)
│   └── Mail/                   # Email Templates
├── database/
│   ├── migrations/             # Database Schema
│   ├── seeders/                # Test Data
│   └── factories/              # Model Factories
├── resources/
│   ├── js/                     # Vue Components & Frontend
│   └── views/                  # Blade Templates
├── routes/
│   ├── web.php                 # Web Routes
│   └── api.php                 # API Routes
├── tests/                      # PHPUnit Tests
├── docker-compose.yml          # Docker Configuration
├── Dockerfile                  # Docker Image Definition
└── README.md                   # This File
```
