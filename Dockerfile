# Stage 1: Build dependencies with Composer
FROM composer:2.8 as vendor

WORKDIR /app
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock
RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

# Stage 2: Build the final production image
FROM php:8.4-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      libzip-dev \
      zip \
      unzip \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip

# Enable Apache mod_rewrite for Laravel's routing
RUN a2enmod rewrite

# Copy Apache virtual host configuration FIRST
COPY .docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Copy Composer's vendor directory
COPY --from=vendor /app/vendor/ /var/www/html/vendor/

# Copy the application code LAST, being specific
COPY app /var/www/html/app
COPY bootstrap /var/www/html/bootstrap
COPY config /var/www/html/config
COPY public /var/www/html/public
COPY resources /var/www/html/resources
COPY routes /var/www/html/routes
COPY storage /var/www/html/storage
COPY artisan /var/www/html/artisan

# Set correct permissions for Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache

# Expose port 80 for Apache
EXPOSE 80