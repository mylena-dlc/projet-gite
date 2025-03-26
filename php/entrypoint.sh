#!/bin/bash
set -e

echo "Initialisation du conteneur PHP..."

# Corriger uniquement les dossiers nécessaires
chmod -R 775 /var/www/var || echo "Impossible de changer les permissions de /var/www/var"
chmod -R 775 /var/www/public/build || echo "Impossible de changer les permissions de /var/www/public/build"


# Exécuter Apache au lieu de tourner en boucle 
echo "Lancement d'Apache"
exec apache2-foreground



