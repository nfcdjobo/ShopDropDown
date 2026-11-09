#!/bin/bash
# =============================================================
#  deploy-shopdropdown.sh — Déploiement complet AhouBa sur VPS
#  Usage : bash deploy-shopdropdown.sh
# =============================================================
set -e

DOMAIN="shopdropdown.barriservices.com"
APP_DIR="/var/www/shopdropdown"
BACKUP_SQL="shopdropdown_backup_2026-06-07_01-25-53_I55Blb.sql"
PORT=8086

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║     DÉPLOIEMENT Shop DropDown — barriservices.com   ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# ─── 1. Créer le dossier du projet ──────────────────────────────
echo "📁 [1/7] Création du dossier projet..."
mkdir -p $APP_DIR/init
cd $APP_DIR

# ─── 2. Copier les fichiers de config ───────────────────────────
echo "📋 [2/7] Copie des fichiers de configuration..."
# Ces fichiers doivent être dans le même dossier que ce script
cp docker-compose.yml $APP_DIR/
cp .env $APP_DIR/
cp Dockerfile $APP_DIR/          # Si build local
cp supervisord.conf $APP_DIR/    # Si utilisé dans l'image
cp .dockerignore $APP_DIR/

# ─── 3. Placer le backup SQL dans init/ ─────────────────────────
echo "🗄️  [3/7] Placement du backup SQL..."
# Le fichier SQL sera chargé automatiquement par PostgreSQL au 1er démarrage
cp $BACKUP_SQL $APP_DIR/init/backup.sql

# ─── 4. Copier les sources Laravel ──────────────────────────────
echo "📦 [4/7] Copie des sources Laravel AhouBa..."
# Si tu as les sources dans un dossier local :
# cp -r /chemin/vers/shopdropdown-sources/* $APP_DIR/
# Sinon via Git :
# git clone https://github.com/barriservices/shopdropdown.git $APP_DIR

echo "    ⚠️  Pensez à copier les sources Laravel dans $APP_DIR/"
echo "    (git clone ou copie manuelle)"

# ─── 5. Config Nginx ────────────────────────────────────────────
echo "🌐 [5/7] Configuration Nginx..."
cp nginx-shopdropdown.conf /etc/nginx/sites-available/$DOMAIN
ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/$DOMAIN
nginx -t && systemctl reload nginx
echo "    ✅ Nginx configuré"

# ─── 6. Lancer Docker Compose ───────────────────────────────────
echo "🐳 [6/7] Démarrage des containers Docker..."
cd $APP_DIR
docker compose up -d --build
echo "    ✅ Containers démarrés"

# Attendre que la DB soit prête
echo "    ⏳ Attente PostgreSQL..."
sleep 10

# ─── 7. Migrations Laravel ──────────────────────────────────────
echo "🔧 [7/7] Finalisation Laravel..."
docker exec shopdropdown bash -c "
    php artisan config:cache &&
    php artisan route:cache &&
    php artisan view:cache &&
    echo '✅ Cache Laravel généré'
"

# ─── 8. SSL avec Certbot ────────────────────────────────────────
echo ""
echo "🔒 [SSL] Génération du certificat HTTPS..."
certbot --nginx -d $DOMAIN --non-interactive --agree-tos -m admin@barriservices.com || \
    echo "⚠️  Certbot : vérifiez que le DNS est bien propagé"

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║  ✅ DÉPLOIEMENT TERMINÉ                      ║"
echo "║  🌐 https://$DOMAIN     ║"
echo "║  🐳 Container : shopdropdown (port $PORT)       ║"
echo "╚══════════════════════════════════════════════╝"
echo ""
echo "Commandes utiles :"
echo "  docker compose logs -f web     → Logs app"
echo "  docker compose logs -f db      → Logs PostgreSQL"
echo "  docker exec -it shopdropdown bash → Shell container"
echo "  docker exec -it shopdropdown_db psql -U geasamwa_shopdropdown9858ojiu55 -d geasamwa_shopdropdown5fji858derr"
bs_admin_bd@server1:/var/www/shopdropdown$
