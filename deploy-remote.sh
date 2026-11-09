#!/bin/bash
# Usage : bash deploy-remote.sh   (lit ./deploy.conf)
set -e

CONF="$(dirname "${BASH_SOURCE[0]}")/deploy.conf"
[ -f "$CONF" ] || { echo "ERREUR: deploy.conf absent (copie deploy.conf.example)"; exit 1; }
source "$CONF"

REMOTE_TMP="/tmp/deploy-shopdropdown"
APP_DIR="/var/www/shopdropdown"
PORT="${VPS_PORT:-22}"
TARGET="${VPS_USER}@${VPS_HOST}"

# FILES=(deploy-shopdropdown.sh docker-compose.yml .env Dockerfile .dockerignore docker-entrypoint.sh nginx-shopdropdown.conf "$BACKUP_SQL")
FILES=(deploy-shopdropdown.sh docker-compose.yml .env Dockerfile .dockerignore docker-entrypoint.sh nginx-shopdropdown.conf)

echo "==> Vérification des fichiers locaux"
for f in "${FILES[@]}"; do
    [ -f "$f" ] || { echo "ERREUR: $f introuvable"; exit 1; }
done

# Script exécuté sur le VPS (généré localement, variables déjà résolues)
cat > .remote-run.sh <<EOF
#!/bin/bash
set -e
cd $REMOTE_TMP
sed -i 's/\r\$//' deploy-shopdropdown.sh docker-entrypoint.sh nginx-shopdropdown.conf .env
mkdir -p $APP_DIR
if [ -d "$APP_DIR/.git" ]; then
    git -C $APP_DIR pull
else
    git clone $REPO $APP_DIR
fi
bash deploy-shopdropdown.sh
EOF

echo "==> Envoi des fichiers"
ssh -p "$PORT" "$TARGET" "mkdir -p $REMOTE_TMP && chmod 700 $REMOTE_TMP"
scp -P "$PORT" "${FILES[@]}" .remote-run.sh "$TARGET:$REMOTE_TMP/"
rm -f .remote-run.sh

echo "==> Déploiement sur le VPS (sudo demandera le mot de passe)"
ssh -t -p "$PORT" "$TARGET" "chmod 600 $REMOTE_TMP/.env && sudo bash $REMOTE_TMP/.remote-run.sh"

echo "==> Terminé"
echo "Nettoyage conseillé : ssh -p $PORT $TARGET 'rm -rf $REMOTE_TMP'"
