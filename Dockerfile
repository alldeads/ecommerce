FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . /var/www/html
RUN chown -R www-data:www-data /var/www/html

# Copy .env file
COPY .env /var/www/html/.env
RUN chown www-data:www-data /var/www/html/.env

# Install PHP dependencies
RUN composer install --no-interaction --no-dev --optimize-autoloader

# Install Node dependencies and build assets
RUN npm ci && npm run build

# Expose port 80
EXPOSE 80

# Start PHP built-in server on container boot
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=80"]
