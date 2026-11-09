#!/bin/bash
set -e

APP_DIR=/var/www/app
cd $APP_DIR

echo "==> Entrypoint AhouBa"

if [ ! -f "$APP_DIR/.env" ]; then
    echo "ERREUR: .env absent (secret non monte ?)"
    exit 1
fi

echo "==> Generation des caches Laravel"
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link || true

echo "==> Correction des permissions"
chown -R www-data:www-data $APP_DIR/storage $APP_DIR/bootstrap/cache
chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache

echo "==> Export env pour cron"
printenv | grep -v '^_=' | grep -v '^SHLVL=' | grep -v '^HOSTNAME=' >> /etc/environment

echo "==> Demarrage des services"
service cron start
service php8.4-fpm start
exec apache2-foreground

