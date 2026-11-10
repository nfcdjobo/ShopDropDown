#!/bin/bash
set -e

APP_DIR=/var/www/app
cd $APP_DIR

echo "==> Entrypoint Shop DropDown"

if [ ! -f "$APP_DIR/.env" ]; then
    echo "ERREUR: .env absent (secret non monte ?)"
    exit 1
fi

echo "==> Attente de la base de donnees"
for i in $(seq 1 30); do
    if php -r '
        require "vendor/autoload.php";
        $app = require "bootstrap/app.php";
        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();
        Illuminate\Support\Facades\DB::connection()->getPdo();
    ' >/dev/null 2>&1; then
        echo "    DB prete"
        break
    fi
    if [ "$i" -eq 30 ]; then
        echo "ERREUR: base de donnees injoignable apres 60s"
        exit 1
    fi
    sleep 2
done

echo "==> Seed (sans migration)"
# AVANT config:cache : sinon env() ne lit plus le .env et les valeurs
# SUPER_ADMIN_* seraient ignorees (fallbacks utilises)
php artisan db:seed --force || echo "AVERTISSEMENT: seed en echec, demarrage poursuivi"

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
