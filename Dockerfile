FROM php:8.3-fpm

RUN apt-get update \
	&& apt-get install -y git unzip libpng-dev libonig-dev libzip-dev \
	&& docker-php-ext-install pdo pdo_mysql mbstring zip

WORKDIR /var/www/html

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY . .

RUN composer install --no-interaction --no-progress --prefer-dist \
	&& cp .env.example .env || true \
	&& php artisan key:generate || true

CMD ["php-fpm"]

