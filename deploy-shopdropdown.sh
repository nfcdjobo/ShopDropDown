#!/bin/bash
# =============================================================
#  deploy-shopdropdown.sh — Déploiement complet Shop DropDown sur VPS
#  Usage : bash deploy-shopdropdown.sh
# =============================================================
set -e

DOMAIN="shopdropdown.barriservices.com"
APP_DIR="/var/www/shopdropdown"
# BACKUP_SQL="shopdropdown_backup_2026-06-07_01-25-53_I55Blb.sql"
PORT=8086
SRC_DIR="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"   # dossier de ce script

echo ""
echo "╔══════════════════════════════════════════════╗"
echo "║     DÉPLOIEMENT Shop DropDown — barriservices.com   ║"
echo "╚══════════════════════════════════════════════╝"
echo ""

# ─── 1. Créer le dossier du projet ──────────────────────────────
echo "📁 [1/6] Création du dossier projet..."
mkdir -p $APP_DIR/init

# ─── 2. Copier les fichiers de config ───────────────────────────
echo "📋 [2/6] Copie des fichiers de configuration..."
# Ces fichiers doivent être dans le même dossier que ce script
for f in docker-compose.yml .env Dockerfile .dockerignore docker-entrypoint.sh; do
    if [ -f "$SRC_DIR/$f" ]; then
        cp "$SRC_DIR/$f" "$APP_DIR/$f"
    else
        echo "    ⚠️  $f introuvable dans $SRC_DIR"
    fi
done
chmod +x $APP_DIR/docker-entrypoint.sh

# ─── 3. Placer le backup SQL dans init/ ─────────────────────────
echo "🗄️  [3/6] Placement du backup SQL..."
# Chargé automatiquement par PostgreSQL au 1er démarrage (volume vide uniquement)
# cp "$SRC_DIR/$BACKUP_SQL" $APP_DIR/init/backup.sql

# ─── 4. Sources Laravel ─────────────────────────────────────────
echo "📦 [4/6] Vérification des sources Laravel Shop DropDown..."
# Si tu as les sources dans un dossier local :
# cp -r /chemin/vers/shopdropdown-sources/* $APP_DIR/
# Sinon via Git :
# git clone https://github.com/barriservices/shopdropdown.git $APP_DIR
if [ ! -f "$APP_DIR/composer.json" ]; then
    echo "    ❌ Sources Laravel absentes de $APP_DIR (git clone ou copie manuelle)"
    exit 1
fi

# ─── 5. Config Nginx ────────────────────────────────────────────
# echo "🌐 [5/6] Configuration Nginx..."
# cp "$SRC_DIR/nginx-shopdropdown.conf" /etc/nginx/sites-available/$DOMAIN
# ln -sf /etc/nginx/sites-available/$DOMAIN /etc/nginx/sites-enabled/$DOMAIN
# nginx -t && systemctl reload nginx
# echo "    ✅ Nginx configuré"

# ─── 5. Réseau Traefik ──────────────────────────────────────────
echo "🌐 [5/6] Vérification du réseau Traefik..."
docker network inspect web >/dev/null 2>&1 || {
    echo "    ❌ Réseau web introuvable (Traefik est-il démarré ?)"
    exit 1
}
echo "    ✅ Réseau web présent"

# ─── 6. Lancer Docker Compose ───────────────────────────────────
echo "🐳 [6/6] Build et démarrage des containers Docker..."
cd $APP_DIR
docker compose up -d --build
echo "    ✅ Containers démarrés"
# Attente DB, seed (sans migrate) et caches Laravel : gérés par docker-entrypoint.sh

# ─── SSL avec Certbot ───────────────────────────────────────────
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
echo "  docker compose logs -f web     → Logs app (seed, caches)"
echo "  docker compose logs -f db      → Logs PostgreSQL"
echo "  docker exec -it shopdropdown bash → Shell container"
echo "  docker exec -it shopdropdown_db psql -U geasamwa_shopdropdown9858ojiu55 -d geasamwa_shopdropdown5fji858derr"
