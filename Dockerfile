# Étape 1 — Builder les assets avec Node
FROM node:20 AS build-assets

WORKDIR /app

# Copier les fichiers nécessaires pour le build assets
COPY package*.json ./  
COPY webpack.config.js ./  
COPY tailwind.config.js ./  
COPY assets ./assets  

# Installer les dépendances Node et compiler
RUN npm install  
RUN npm run build  


# Étape 2 — Symfony + Apache
FROM php:8.2-apache

WORKDIR /var/www/html

# 1) Installer les extensions PHP et activer mod_rewrite
RUN apt-get update && apt-get install -y \
      git zip unzip curl libicu-dev libonig-dev libzip-dev \
    && docker-php-ext-install intl pdo pdo_mysql zip \
    && a2enmod rewrite

# 2) Installer Composer et autoriser l’exécution en root
ENV COMPOSER_ALLOW_SUPERUSER=1
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 3) Copier les définitions de dépendances PHP pour profiter du cache Docker
COPY composer.json composer.lock ./

# 4) Installer les dépendances PHP
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 5) Copier le reste de l’application Symfony
COPY . .

# 6) Rendre bin/console exécutable et exécuter les auto-scripts Symfony
RUN chmod +x bin/console \
 && php bin/console cache:clear --no-warmup --no-interaction \
 && php bin/console assets:install public --no-interaction

# 7) Récupérer les assets compilés depuis l’étape Node
COPY --from=build-assets /app/public/build /var/www/html/public/build

# 8) Définir les permissions correctes
RUN chown -R www-data:www-data var public

# 9) Mettre à jour le DocumentRoot Apache
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!DocumentRoot /var/www/html!DocumentRoot ${APACHE_DOCUMENT_ROOT}!g' \
    /etc/apache2/sites-available/*.conf

EXPOSE 80
