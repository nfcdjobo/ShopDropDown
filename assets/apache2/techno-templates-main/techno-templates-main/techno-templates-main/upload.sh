#!/bin/bash
set -e

# --- Variables ---
NEXUS_LOGIN=$1
NEXUS_PASSWORD=$2
NEXUS_REPO="https://artefacts.tresormoney.ci/repository/resources/templates"
FILES=$(find . -type f ! -path "./.git/*" ! -name ".*" | sed 's|^\./||')

# --- Checking des prerequis ---
[ -n "$NEXUS_LOGIN" ] && [ -n "$NEXUS_PASSWORD" ] && [ -n "$NEXUS_REPO" ] || { echo -e "Usage : $0 <nexus_login> <nexus_password>\nExemple : $0 admin pass"; exit 1; }

# --- Upload des fichiers ---
for file in ${FILES[@]}; do
        [ $file = "upload.sh" ] || curl -u "$NEXUS_LOGIN:$NEXUS_PASSWORD" --upload-file $file "$NEXUS_REPO/$file"
done

echo -e "=== [OK] Opération réalisée avec succès ! ==="
