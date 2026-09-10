FROM php:8.3-fpm

# Install system dependencies & PHP extensions needed for Laravel
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx

RUN docker-php-ext-install pdo pdo_mysql mbstring exts xml bcmath gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy existing application code
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set directory permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Configure Nginx entrypoint
EXPOSE 80
CMD php artisan config:cache && php artisan route:cache && php artisan migrate --force && php artisan serve --host=0.0.0.0 --port=80