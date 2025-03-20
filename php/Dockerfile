# Utilisation de l'image officielle PHP avec Apache
FROM php:8.2-apache

# Mise à jour des paquets et installation des extensions PHP
RUN apt-get update && apt-get install -y \
    build-essential curl zlib1g-dev g++ git libicu-dev zip libzip-dev \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip gd exif \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && a2enmod rewrite ssl socache_shmcb \
    && rm -rf /var/lib/apt/lists/*

# Définit le répertoire de travail
WORKDIR /var/www

# Copie et rend exécutable le script d'entrypoint
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Installe Composer sous root pour éviter les problèmes de permissions
USER root
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
    && composer self-update \
    && composer clear-cache

# Ajuste les permissions après installation
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www

# Retour à l'utilisateur www-data
USER www-data

# Expose le port 80
EXPOSE 80

# Démarre Apache via le script d’entrypoint
ENTRYPOINT ["/entrypoint.sh"]