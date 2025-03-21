FROM php:8.2-apache

# Mise à jour et installation des dépendances
RUN apt-get update && apt-get install -y \
    build-essential curl zlib1g-dev g++ git libicu-dev zip libzip-dev \
    libpng-dev libjpeg-dev libwebp-dev libfreetype6-dev \
    # 👇 Installation de Node.js 18
    && curl -fsSL https://deb.nodesource.com/setup_18.x | bash - \
    && apt-get install -y nodejs \
    && docker-php-ext-install intl opcache pdo pdo_mysql zip gd exif \
    && pecl install apcu \
    && docker-php-ext-enable apcu \
    && a2enmod rewrite ssl socache_shmcb \
    && rm -rf /var/lib/apt/lists/*

# Définir le dossier de travail
WORKDIR /var/www

# Copie et permission du script d’entrée
COPY entrypoint.sh /entrypoint.sh
RUN chmod +x /entrypoint.sh

# Installer Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Permissions sur le projet
RUN chown -R www-data:www-data /var/www && chmod -R 775 /var/www

USER www-data

EXPOSE 80

ENTRYPOINT ["/entrypoint.sh"]
