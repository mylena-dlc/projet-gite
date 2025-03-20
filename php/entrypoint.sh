#!/bin/bash
set -e

# Appliquer les permissions correctes
chown -R www-data:www-data /var/www
chmod -R 775 /var/www

# Vérifier si .env existe et le charger
if [ -f /var/www/.env ]; then
    echo "✅ .env trouvé, chargement des variables..."
    export $(grep -v '^#' /var/www/.env | xargs)
    env | grep DATABASE_URL  # Affiche DATABASE_URL pour contrôle
else
    echo "⚠️ Avertissement : .env introuvable !"
fi

# Exécuter la commande de base
exec "$@"