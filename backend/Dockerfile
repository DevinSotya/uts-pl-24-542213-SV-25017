# Versi base image dipin (bukan latest).
FROM php:8.2-cli-alpine3.21

# Ekstensi MySQL + unzip (dipakai composer untuk mengekstrak paket)
RUN apk add --no-cache unzip \
    && docker-php-ext-install pdo_mysql

# Composer dari image resmi, versi dipin
COPY --from=composer:2.8 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# 1) Salin composer.json + composer.lock dulu dan pasang dependency,
#    supaya layer ini ter-cache selama dua file itu tidak berubah.
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# 2) Baru salin kode aplikasi.
COPY . .

# 3) Autoloader + package discovery setelah kode ada; siapkan folder runtime.
RUN composer dump-autoload --optimize --no-dev \
    && mkdir -p storage/logs storage/framework/cache/data storage/framework/sessions storage/framework/views bootstrap/cache

# Entrypoint: migrate + seed lalu jalankan server. Tidak ada nilai rahasia di sini;
# APP_KEY, kredensial DB, dll. datang dari environment saat container dijalankan.
COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh
RUN sed -i 's/\r$//' /usr/local/bin/docker-entrypoint.sh \
    && chmod +x /usr/local/bin/docker-entrypoint.sh

EXPOSE 8000
ENTRYPOINT ["docker-entrypoint.sh"]
