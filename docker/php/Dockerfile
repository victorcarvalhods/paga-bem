FROM php:8.4-fpm

# Working dir
WORKDIR /var/www

# Install necessary system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    default-mysql-client

# Install PHP extensions needed for Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath zip

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install Composer (PHP dependency manager)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY . /var/www
# # Entrypoint script to wait for DB and run migrations and seeders

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

ARG UID=1000
ARG GID=1000

RUN groupadd -g ${GID} --non-unique appgroup && \
    useradd -u ${UID} -ms /bin/bash -g appgroup appuser

RUN chown -R appuser:appgroup /var/www/storage /var/www/bootstrap/cache

USER appuser
EXPOSE 9000
CMD ["php-fpm", "-F"]
