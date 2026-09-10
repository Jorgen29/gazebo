FROM php:8.3-fpm

# Install system dependencies including libpq-dev for Postgres & libzip-dev for zip
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx libpq-dev libzip-dev

# Install required PHP extensions (including zip, pdo_pgsql, and pgsql)
RUN docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql mbstring xml bcmath gd zip

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Set permissions for Laravel
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

EXPOSE 80
CMD php artisan config:cache && php artisan route:cache && php artisan serve --host=0.0.0.0 --port=80