# Étape 1 — Builder les assets avec Node
FROM node:20 AS build-assets

WORKDIR /app

# Copier les fichiers nécessaires pour le build assets
COPY package*.json ./
COPY webpack.config.js ./
COPY postcss.config.js ./
COPY tailwind.config.js ./
COPY assets ./assets

# Installer les dépendances Node
RUN npm install

# Compiler les assets
RUN npm run build

# Étape 2 — Symfony + Apache
FROM php:8.2-apache

WORKDIR /var/www/html

# Installer les extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
    git zip unzip curl libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip

# Activer mod_rewrite
RUN a2enmod rewrite

# Copier le reste du projet Symfony
COPY . .

# Installer Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader

# Copier les assets compilés (build) dans l'image finale
COPY --from=build-assets /app/public/build /var/www/html/public/build

# Définir les permissions nécessaires
RUN chown -R www-data:www-data var public

# Définir DocumentRoot
ENV APACHE_DOCUMENT_ROOT /var/www/html/public

# Configurer Apache
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf

EXPOSE 80
