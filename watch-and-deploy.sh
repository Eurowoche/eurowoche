#!/bin/bash
# === AUTO-DEPLOY: Überwacht Änderungen und deployt automatisch ===
# Nutzt fswatch um das Projekt zu überwachen.
# Bei jeder Änderung wird Hugo gebaut und zu Strato hochgeladen.
#
# EINRICHTUNG:
# 1. brew install fswatch lftp hugo   (einmalig)
# 2. Zugangsdaten in deploy.sh eintragen
# 3. chmod +x watch-and-deploy.sh deploy.sh
# 4. ./watch-and-deploy.sh
#
# Zum Beenden: Ctrl+C

PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
DEPLOY_SCRIPT="$PROJECT_DIR/deploy.sh"

echo "👁️  Überwache Projekt auf Änderungen..."
echo "   Ordner: $PROJECT_DIR"
echo "   (Ctrl+C zum Beenden)"
echo ""

# fswatch überwacht alles außer public/ und .git/
fswatch -o \
    --exclude="$PROJECT_DIR/public" \
    --exclude="$PROJECT_DIR/.git" \
    --exclude="$PROJECT_DIR/resources" \
    "$PROJECT_DIR/content" \
    "$PROJECT_DIR/layouts" \
    "$PROJECT_DIR/assets" \
    "$PROJECT_DIR/static" \
    "$PROJECT_DIR/data" \
    "$PROJECT_DIR/i18n" \
    "$PROJECT_DIR/hugo.toml" \
| while read num; do
    echo ""
    echo "📝 Änderung erkannt! Deploy wird gestartet..."
    echo "   $(date '+%H:%M:%S')"
    bash "$DEPLOY_SCRIPT"
    echo ""
    echo "👁️  Warte auf nächste Änderung..."
done
