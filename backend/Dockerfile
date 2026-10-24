# Base image: FrankenPHP (Caddy + PHP 8.2, Alpine). Versi dipin, bukan latest.
# Ganti <VERSI> dengan versi FrankenPHP dari `frankenphp version` (contoh bentuk: 1.9.3).
FROM dunglas/frankenphp:1.13.3-php8.2-alpine

# Ekstensi MySQL (installer disediakan image FrankenPHP) + unzip untuk composer
RUN apk add --no-cache unzip \
    && install-php-extensions pdo_mysql

# Composer dari image resmi, versi dipin
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# 1) composer.json + composer.lock dulu supaya layer dependency ter-cache.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# 2) Baru kode aplikasi.
COPY . .

# 3) Autoloader + package discovery setelah kode ada; siapkan folder runtime.
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

# Konfigurasi FrankenPHP (Caddy): melayani /app/public di port 8000.
COPY Caddyfile /etc/frankenphp/Caddyfile

# Entrypoint: migrate + seed, lalu jalankan FrankenPHP. Tidak ada nilai rahasia di sini.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
