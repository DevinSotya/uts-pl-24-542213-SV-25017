#!/bin/sh
set -e

cd /app

# DB sudah sehat saat script ini jalan (depends_on: service_healthy).
php artisan migrate --force
php artisan db:seed --force

# FrankenPHP (Caddy) melayani API di :8000 sesuai /etc/frankenphp/Caddyfile.
exec frankenphp run --config /etc/frankenphp/Caddyfile --adapter caddyfile
