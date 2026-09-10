FROM php:8.3-fpm

# Install system dependencies, PostgreSQL libs, Zip, Nginx, and Node.js/NPM
RUN apt-get update && apt-get install -y \
    git unzip libpng-dev libonig-dev libxml2-dev zip curl nginx libpq-dev libzip-dev nodejs npm

# Install required PHP extensions
RUN docker-php-ext-install pdo pdo_pgsql pgsql pdo_mysql mbstring xml bcmath gd zip

# Configure PHP upload limits (Increase max file size & post max size)
RUN echo "upload_max_filesize = 20M" > /usr/local/etc/php/conf.d/uploads.ini \
 && echo "post_max_size = 25M" >> /usr/local/etc/php/conf.d/uploads.ini

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www

# Copy application files
COPY . .

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader

# Install Node dependencies and build Vite production assets
RUN npm ci || npm install
RUN npm run build

# Set directory permissions for storage, cache, and upload temp directory
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
 && chmod -R 775 /var/www/storage /var/www/bootstrap/cache \
 && chmod 777 /tmp

EXPOSE 80
CMD php artisan config:clear && php artisan cache:clear && php artisan config:cache && php artisan route:cache && php artisan migrate --force && php artisan storage:link --force && php artisan schedule:work & php artisan serve --host=0.0.0.0 --port=80