# Utilisation de l'image officielle PHP avec Apache
FROM php:8.2-apache

# Met à jour les paquets et installe les dépendances nécessaires
RUN apt-get update \
    && apt-get install -y build-essential curl zlib1g-dev g++ git libicu-dev zip libzip-dev libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    && docker-php-ext-install intl opcache pdo pdo_mysql \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && docker-php-ext-configure zip \
    && docker-php-ext-install zip

# Installation de GD (pour manipuler les images)
RUN docker-php-ext-configure gd --with-freetype --with-webp --with-jpeg \
    && docker-php-ext-install gd \
    && docker-php-ext-install exif

# Active les modules Apache nécessaires
RUN a2enmod rewrite && a2enmod ssl && a2enmod socache_shmcb

# Définit le répertoire de travail dans le conteneur
WORKDIR /var/www

# Installe Composer (gestionnaire de dépendances PHP)
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Installe Symfony CLI
RUN curl -sS https://get.symfony.com/cli/installer | bash
RUN mv /root/.symfony*/bin/symfony /usr/local/bin/symfony

# Configure Git (vérifie que cela est nécessaire)
RUN git config --global user.email "mylena.delacote"
RUN git config --global user.name "Mylena delacote"

# Vérifie les permissions et les ajuster au besoin
RUN chown -R www-data:www-data /var/www
RUN chmod -R 775 /var/www

RUN ls -lah /var/www && stat /var/www

# Change l'utilisateur pour www-data et les permissions des fichiers dans le répertoire /var/www
USER www-data

# Expose le port 80 pour Apache
EXPOSE 80

# Démarre Apache au démarrage
CMD ["apache2-foreground"]