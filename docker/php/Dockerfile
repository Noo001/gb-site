FROM php:8.3-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install -j$(nproc) \
    pdo_pgsql \
    pgsql \
    zip \
    gd \
    opcache \
    exif \
    intl

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/api

# Create a non-root user for production usage (kept available for runtime override)
RUN groupadd -g 1000 www && useradd -u 1000 -g www -G www-data -s /bin/bash www

# Default to root so bind mounts work during local development.
# In production, override the user via docker-compose or orchestrator config.

# Run php-fpm workers as root for local Windows bind-mount compatibility.
RUN sed -i 's/^user = .*/user = root/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^group = .*/group = root/' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/^;listen = .*/listen = 0.0.0.0:9000/' /usr/local/etc/php-fpm.d/www.conf \
    && grep -q '^listen =' /usr/local/etc/php-fpm.d/www.conf || echo 'listen = 0.0.0.0:9000' >> /usr/local/etc/php-fpm.d/www.conf

