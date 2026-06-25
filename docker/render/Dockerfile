# Production Dockerfile for Render (single container: PHP API + Next.js frontend + nginx)
FROM php:8.3-fpm

# Install system dependencies, nginx, supervisor, Node.js
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    gnupg \
    ca-certificates \
    nginx \
    supervisor \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libwebp-dev \
    libxpm-dev \
    libicu-dev \
    && rm -rf /var/lib/apt/lists/*

# Node.js 22
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs \
    && rm -rf /var/lib/apt/lists/*

# PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg --with-webp --with-xpm \
    && docker-php-ext-install -j$(nproc) \
        pdo_pgsql \
        pgsql \
        zip \
        gd \
        opcache \
        exif \
        intl

# Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Runtime configs
COPY docker/render/php.ini /usr/local/etc/php/php.ini
COPY docker/render/supervisord.conf /etc/supervisor/conf.d/supervisord.conf
COPY docker/render/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/render/start.sh /start.sh
RUN chmod +x /start.sh

# Backend
WORKDIR /var/www/api
COPY api/composer.json api/composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist
COPY api ./
RUN composer dump-autoload --optimize

# Frontend
WORKDIR /var/www/frontend
COPY frontend/package.json frontend/package-lock.json* ./
RUN npm ci
COPY frontend ./
ENV NEXT_PUBLIC_API_URL=/api
ENV API_URL=http://localhost:8000
RUN npm run build

# Render exposes PORT env variable (default 10000). We also use 8000 internally.
EXPOSE 10000 8000

CMD ["/start.sh"]
