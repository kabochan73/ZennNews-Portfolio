FROM php:8.4-fpm-alpine AS base

RUN apk add --no-cache \
    nginx \
    supervisor \
    postgresql-dev \
    zip \
    unzip \
    git \
    curl

RUN docker-php-ext-install pdo pdo_pgsql opcache

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/www.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/entrypoint.sh /entrypoint.sh
COPY docker/crontab /etc/crontabs/root
RUN chmod +x /entrypoint.sh

EXPOSE 80

# 開発用
FROM base AS dev

COPY composer.json composer.lock ./
RUN composer install --no-scripts --no-autoloader --prefer-dist

COPY . .
RUN composer dump-autoload

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENTRYPOINT ["/entrypoint.sh"]

# フロントエンドビルド
FROM node:22-alpine AS node-builder

WORKDIR /app
COPY package*.json ./
RUN npm install --prefer-offline
COPY . .
RUN npm run build

# 本番用
FROM base AS prod

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist

COPY . .
COPY --from=node-builder /app/public/build ./public/build
RUN composer dump-autoload --optimize

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

ENTRYPOINT ["/entrypoint.sh"]
