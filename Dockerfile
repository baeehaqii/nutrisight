# Stage 1: Build dependencies using a Debian-based PHP image
FROM php:8.3-cli as vendor

# Install system dependencies
RUN apt-get update && apt-get install -y git unzip libicu-dev libzip-dev zip

# Install PHP extensions
RUN docker-php-ext-install intl zip pdo pdo_mysql

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set up application directory
WORKDIR /app

# Copy only composer files to leverage Docker cache
COPY database/ database/
COPY composer.json composer.json
COPY composer.lock composer.lock

# 1. Install dependencies WITHOUT running scripts to avoid .env issues
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist

# 2. Now copy the rest of the application code
COPY . .

# 3. Create the .env file
RUN cp .env.example .env

# 4. Generate the key (now artisan can run because vendor/ exists)
RUN php artisan key:generate

# 5. Generate the optimized autoloader and other scripts manually
RUN composer dump-autoload --optimize


# Stage 2: Build the final production image (This stage does not change)
FROM php:8.3-apache

# Install required system packages and PHP extensions
RUN apt-get update && apt-get install -y \
      libpng-dev \
      libjpeg-dev \
      libfreetype6-dev \
      libzip-dev \
      zip \
      unzip \
      libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl

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