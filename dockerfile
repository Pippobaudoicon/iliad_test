FROM php:8.1-fpm

RUN apt-get update && apt-get install -y \
    zip \
    unzip \
    git \
    curl \
    libpq-dev \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        mbstring \
        zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installa Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html
COPY ./iliad_test /var/www/html/
RUN composer install

EXPOSE 8000
CMD php artisan serve --host=0.0.0.0 --port=8000
