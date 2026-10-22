#!/bin/sh
set -e

cd /var/www/html

# DB sudah sehat saat script ini jalan (depends_on: service_healthy).
php artisan migrate --force
php artisan db:seed --force

# --no-reload: semua variabel environment dari Compose diteruskan ke server.
exec php artisan serve --host=0.0.0.0 --port=8000 --no-reload
