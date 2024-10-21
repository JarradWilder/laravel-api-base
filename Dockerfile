# Use the official PHP image
FROM php:8.3-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    git \
    unzip \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo pdo_pgsql zip

# Install Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Install PHP extensions for Laravel
RUN docker-php-ext-install pdo_pgsql

# Copy existing application directory
COPY ./src /var/www/html

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port 8000
EXPOSE 8000

# Run Laravel's Artisan server
CMD tail -f /dev/null