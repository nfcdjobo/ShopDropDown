ARG PHP_VERSION=8.3

# ========== STAGE 1 : Build des assets Vite avec Node ==========
FROM node:20-alpine AS assets
WORKDIR /build
COPY package.json package-lock.json ./
RUN npm ci
COPY . .
RUN npm run build

# ========== STAGE 2 : Image applicative PHP/Apache ==========
FROM djobo/apache2-php-fpm-full:v8.3
SHELL ["/bin/bash", "-c"]

ARG APP_DIR=/var/www/app

# Installer pg_dump 16 + cron
RUN apt-get update && apt-get install -y --no-install-recommends \
    gnupg curl lsb-release cron \
    && curl -fsSL https://www.postgresql.org/media/keys/ACCC4CF8.asc | gpg --dearmor -o /usr/share/keyrings/postgresql.gpg \
    && echo "deb [signed-by=/usr/share/keyrings/postgresql.gpg] https://apt.postgresql.org/pub/repos/apt $(lsb_release -cs)-pgdg main" > /etc/apt/sources.list.d/pgdg.list \
    && apt-get update && apt-get install -y --no-install-recommends \
    postgresql-client-16 \
    && rm -rf /var/lib/apt/lists/*

# Tache cron Laravel
RUN printf "* * * * * www-data php /var/www/app/artisan schedule:run >> /var/log/laravel-cron.log 2>&1\n" \
    > /etc/cron.d/laravel-scheduler \
    && chmod 0644 /etc/cron.d/laravel-scheduler \
    && chown root:root /etc/cron.d/laravel-scheduler

RUN touch /var/log/laravel-cron.log \
    && chown www-data:www-data /var/log/laravel-cron.log

WORKDIR $APP_DIR

# Dependances PHP
COPY composer.json composer.lock $APP_DIR/
RUN composer install --no-dev --optimize-autoloader --no-scripts

# Sources
COPY . $APP_DIR/

# Copier les assets compiles depuis le stage Node
COPY --from=assets /build/public/build $APP_DIR/public/build

# Creer les dossiers de cache Laravel (absents du depot)
RUN mkdir -p $APP_DIR/storage/framework/cache/data \
    $APP_DIR/storage/framework/sessions \
    $APP_DIR/storage/framework/views \
    $APP_DIR/storage/logs \
    $APP_DIR/bootstrap/cache

# Permissions + autoload
RUN chown -R www-data:www-data $APP_DIR $APP_DIR/storage $APP_DIR/bootstrap/cache && \
    chmod -R 775 $APP_DIR/storage $APP_DIR/bootstrap/cache && \
    composer run-script post-autoload-dump && \
    chmod +x $APP_DIR/docker-entrypoint.sh

CMD ["/var/www/app/docker-entrypoint.sh"]
