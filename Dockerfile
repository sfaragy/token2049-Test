FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
    zip unzip curl libpng-dev libonig-dev libxml2-dev \
    libssl-dev pkg-config libzip-dev git

RUN docker-php-ext-install pdo pdo_mysql zip pcntl

#RUN pecl clear-cache \
#    && pecl install redis \
#    && docker-php-ext-enable redis

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer
RUN curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs


WORKDIR /var/www
COPY ./src /var/www
RUN if [ ! -f .env ]; then cp .env.example .env; fi

RUN mkdir -p bootstrap/cache && chmod -R 775 bootstrap/cache

RUN composer install --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 9000
CMD ["php-fpm"]
