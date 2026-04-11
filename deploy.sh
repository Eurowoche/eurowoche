#!/bin/bash
# === DEPLOY EUROWOCHE ZU STRATO ===
# Baut Hugo und synchronisiert public/ per FTP zu Strato
#
# EINRICHTUNG:
# 1. Trage deine Strato-Zugangsdaten unten ein
# 2. chmod +x deploy.sh
# 3. ./deploy.sh

# --- STRATO ZUGANGSDATEN (HIER EINTRAGEN) ---
FTP_HOST="51850960.ssh.w1.strato.hosting"  # Strato SFTP-Host
FTP_USER="stu134533248"                    # Strato Benutzer
FTP_PASS="V1v@europa2026!"                   # ← Hier dein Passwort eintragen
REMOTE_DIR="/"                      # Zielverzeichnis auf Strato (meist / oder /htdocs)
# ---------------------------------------------

# Projektverzeichnis (wo dieses Skript liegt)
PROJECT_DIR="$(cd "$(dirname "$0")" && pwd)"
cd "$PROJECT_DIR"

echo "🔨 Hugo Build starten..."
hugo --gc --minify
if [ $? -ne 0 ]; then
    echo "❌ Hugo Build fehlgeschlagen!"
    exit 1
fi
echo "✅ Hugo Build erfolgreich"

echo ""
echo "🔒 Berechtigungen lokal setzen..."
find public/ -type f -exec chmod 644 {} +
find public/ -type d -exec chmod 755 {} +

echo "📤 Upload zu Strato ($FTP_HOST)..."
lftp -u "$FTP_USER","$FTP_PASS" sftp://"$FTP_HOST" -e "
    set sftp:auto-confirm yes;
    set xfer:clobber on;
    mirror --reverse --delete --verbose --parallel=4 --ignore-time public/ $REMOTE_DIR;
    quit
"

if [ $? -eq 0 ]; then
    echo ""
    echo "✅ Deploy erfolgreich! Seite ist live auf eurowoche.ceminco.com"
else
    echo ""
    echo "❌ Upload fehlgeschlagen. Prüfe Zugangsdaten."
    exit 1
fi
