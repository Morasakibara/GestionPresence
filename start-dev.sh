#!/usr/bin/env bash
# =====================================================================
#  Démarre Mailpit + le serveur Laravel en une seule commande.
#  Usage :  bash start-dev.sh   (ou ./start-dev.sh sous Git Bash)
# =====================================================================

cd "$(dirname "$0")"

echo "==> Vérification de Mailpit (UI http://127.0.0.1:8025)..."
if curl -s -o /dev/null http://127.0.0.1:8025/ 2>/dev/null; then
    echo "    Mailpit déjà en cours d'exécution."
else
    if [ -f "tools/mailpit/mailpit.exe" ]; then
        echo "    Démarrage de Mailpit (SMTP 127.0.0.1:2525, UI http://127.0.0.1:8025)"
        (cd tools/mailpit && ./mailpit.exe --smtp 127.0.0.1:2525 --listen 127.0.0.1:8025 > /tmp/mailpit.log 2>&1 &)
        sleep 2
    else
        echo "    !! Mailpit introuvable dans tools/mailpit/ — ignorez ce message ou installez-le."
    fi
fi

echo "==> Vérification du serveur Laravel (http://127.0.0.1:8000)..."
if curl -s -o /dev/null http://127.0.0.1:8000/up 2>/dev/null; then
    echo "    Serveur Laravel déjà en cours d'exécution."
else
    if [ ! -f "vendor/autoload.php" ]; then
        echo "    !! vendor/ manquant — lancez d'abord : composer install"
    else
        echo "    Démarrage du serveur Laravel sur http://127.0.0.1:8000"
        (php -S 127.0.0.1:8000 -t public > /tmp/laravel_server.log 2>&1 &)
        sleep 2
    fi
fi

echo ""
echo "=================================================================="
echo "  Application :  http://127.0.0.1:8000"
echo "  Mailpit      :  http://127.0.0.1:8025"
echo "=================================================================="
