# Étape 1 — Builder les assets avec Node
FROM node:20 AS build-assets

WORKDIR /app

COPY package*.json ./
COPY webpack.config.js ./
COPY tailwind.config.js ./
COPY assets ./assets

RUN npm install
RUN npm run build


# Étape 2 — Symfony + Apache
FROM php:8.2-apache

WORKDIR /var/www/html

# 1) Installer les extensions PHP & activer mod_rewrite (ignore l'erreur si déjà activé)
RUN apt-get update \
 && apt-get install -y git zip unzip curl libicu-dev libonig-dev libzip-dev \
 && docker-php-ext-install intl pdo pdo_mysql zip \
 && a2enmod rewrite || true

# 2) Autoriser Composer en root & installer composer
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3) Cacher composer.json/composer.lock pour tirer parti du cache Docker
COPY composer.json composer.lock ./

# 4) Installer les dépendances PHP sans scripts auto-exécutés
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# 5) Copier le reste du code
COPY . .

# 6) Copier les assets compilés depuis Node
COPY --from=build-assets /app/public/build public/build

# 7) Ajuster les permissions
RUN chmod +x bin/console \
 && chown -R www-data:www-data var public

# 8) Mettre à jour le DocumentRoot Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

EXPOSE 80
