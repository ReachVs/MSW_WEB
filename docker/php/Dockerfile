FROM php:8.5-fpm-alpine

WORKDIR /var/www/html

RUN apk add --no-cache \
    bash \
    git \
    unzip \
    zip \
    $PHPIZE_DEPS \
    && docker-php-ext-install bcmath pcntl pdo_mysql \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY backend ./
RUN composer install --no-interaction --prefer-dist --optimize-autoloader

COPY docker/php/php.ini /usr/local/etc/php/conf.d/app.ini
COPY docker/php/entrypoint.sh /usr/local/bin/msw-entrypoint

RUN chmod +x /usr/local/bin/msw-entrypoint \
    && chown -R www-data:www-data storage bootstrap/cache

ENTRYPOINT ["msw-entrypoint"]
CMD ["php-fpm"]
