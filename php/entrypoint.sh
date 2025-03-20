#!/bin/bash
set -e

echo "🔧 Initialisation du conteneur PHP..."

# Vérifier si .env existe et le charger
if [ -f /var/www/.env ]; then
    echo "✅ .env trouvé, chargement des variables..."
    export $(grep -v '^#' /var/www/.env | xargs)
else
    echo "⚠️ Avertissement : .env introuvable !"
fi

# Assurer que les permissions sont correctes
chmod -R 775 /var/www

# 📌 Exécuter Apache au lieu de tourner en boucle !
echo "🚀 Lancement d'Apache..."
exec apache2-foreground