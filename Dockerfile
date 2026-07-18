FROM php:8.2-cli-bookworm AS php-extensions

ARG REDIS_EXTENSION_VERSION=6.1.0
ARG SWOOLE_VERSION=5.1.5

# Build PHP extensions in an isolated stage. Octane is its own HTTP server, so
# PHP-FPM is deliberately not included in the production image.
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        $PHPIZE_DEPS \
        libfreetype6-dev \
        libcurl4-openssl-dev \
        libjpeg62-turbo-dev \
        libonig-dev \
        libpng-dev \
        libpq-dev \
        libssl-dev \
        libxml2-dev \
        libzip-dev \
        zlib1g-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        bcmath curl gd mbstring pcntl pdo_mysql pdo_pgsql soap sockets zip \
    && pecl install "redis-${REDIS_EXTENSION_VERSION}" "swoole-${SWOOLE_VERSION}" \
    && docker-php-ext-enable opcache redis swoole \
    && rm -rf /tmp/pear /var/lib/apt/lists/*

FROM php-extensions AS vendor

WORKDIR /var/www/app
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
COPY app/composer.json app/composer.lock ./
RUN composer install \
        --no-dev \
        --no-interaction \
        --no-scripts \
        --prefer-dist \
        --optimize-autoloader

FROM node:20-bookworm-slim AS frontend

WORKDIR /var/www/app
COPY app/package.json ./
# The repository has no JavaScript lockfile, so npm ci cannot be used yet.
RUN npm install --no-audit --no-fund
COPY app/resources ./resources
COPY app/public ./public
COPY app/vite.config.js ./
RUN npm run build

FROM php:8.2-cli-bookworm AS production

ARG WWWUSER=1000
ARG WWWGROUP=1000

ENV APP_ENV=production \
    APP_DEBUG=false \
    OCTANE_SERVER=swoole \
    PHP_OPCACHE_ENABLE=1

WORKDIR /var/www/app

RUN apt-get update \
    && apt-get install -y --no-install-recommends \
        libfreetype6 \
        libcurl4 \
        libjpeg62-turbo \
        libonig5 \
        libpng16-16 \
        libpq5 \
        libssl3 \
        libzip4 \
    && rm -rf /var/lib/apt/lists/* \
    && groupadd --gid "${WWWGROUP}" octane \
    && useradd --uid "${WWWUSER}" --gid octane --create-home --shell /usr/sbin/nologin octane

COPY --from=php-extensions /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=php-extensions /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --chown=octane:octane app/ ./
COPY --from=vendor --chown=octane:octane /var/www/app/vendor ./vendor
COPY --from=frontend --chown=octane:octane /var/www/app/public/build ./public/build
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && php artisan package:discover --ansi \
    && chown -R octane:octane storage bootstrap/cache \
    && chmod 0555 /usr/local/bin/docker-entrypoint \
    && printf '%s\n' \
        'opcache.enable=1' \
        'opcache.validate_timestamps=0' \
        'opcache.memory_consumption=128' \
        'opcache.max_accelerated_files=20000' \
        > /usr/local/etc/php/conf.d/opcache-production.ini

USER octane

ENTRYPOINT ["/usr/local/bin/docker-entrypoint"]

EXPOSE 8000 10000

HEALTHCHECK --interval=30s --timeout=3s --start-period=15s --retries=3 \
    CMD php -r '$socket = @fsockopen("127.0.0.1", (int) (getenv("PORT") ?: 8000)); if (! $socket) { exit(1); } fclose($socket);'

CMD ["sh", "-c", "exec php artisan octane:start --server=swoole --host=0.0.0.0 --port=\"${PORT:-8000}\" --workers=auto --max-requests=500"]
