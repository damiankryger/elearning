FROM php:8.4-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    git \
    curl \
    libpng-dev \
    libxml2-dev \
    zip \
    unzip \
    icu-dev \
    oniguruma-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    intl \
    mbstring \
    xml \
    gd

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Create www-data user if it doesn't exist
RUN set -ex; \
    addgroup -g 1000 www-data 2>/dev/null || true; \
    adduser -u 1000 -G www-data -s /bin/sh -D www-data 2>/dev/null || true

# Change ownership of working directory
RUN chown -R www-data:www-data /var/www/html

# Switch to www-data user
USER www-data

# Expose port 9000
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
