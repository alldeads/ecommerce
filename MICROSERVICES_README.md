# E-Commerce Microservices System

A Laravel-based e-commerce system with 3 microservices architecture: Catalog, Checkout, and Email services.

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

### 1. Install Dependencies

```bash
composer install
npm install
```

### 2. Configure Environment

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

Run the full test suite:

```bash
php artisan test --filter MicroservicesTest
```

All 9 tests pass, covering:

- Catalog service product listing and details
- Checkout service order creation and validation
- Email service order confirmation
- Authentication and authorization
- Stock management

## Implementation Summary

This e-commerce microservices system has been successfully implemented with:

1. **Database Layer**: 3 tables (products, orders, order_items) with proper relationships and migrations
2. **Service Layer**: 3 independent services (CatalogService, CheckoutService, EmailService) with clear separation of concerns
3. **API Layer**: RESTful endpoints for catalog (public) and checkout (authenticated)
4. **Email Integration**: Automated order confirmation emails sent on every order
5. **Data Persistence**: All product and order data stored in the database
6. **Test Coverage**: Comprehensive test suite validating all functionality

The system is production-ready and follows Laravel best practices with proper error handling, validation, and transaction management.

## Notes

- Email service is decoupled and won't fail orders if email sending fails
- All monetary values stored as decimals with 2 decimal places
- Stock automatically decremented on successful order
- Orders include snapshot of product prices at time of purchase
- Order numbers are unique and generated automatically
