# Use official PHP image with required extensions
FROM php:8.2-fpm

# Set working directory
WORKDIR /var/www

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl zip unzip git supervisor libpng-dev libonig-dev libxml2-dev

# Install PHP extensions
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy Laravel app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader

# Set correct permissions
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Copy Supervisor config for queues
COPY ./supervisor.conf /etc/supervisor/conf.d/laravel-worker.conf

# Expose no well-known ports
EXPOSE 9000

# Start Supervisor
CMD ["supervisord", "-c", "/etc/supervisor/supervisord.conf"]

CMD ["php-fpm"]
