# ===== STAGE 1 : build des assets (Node) =====
FROM node:18 AS build-assets

WORKDIR /app
COPY . .
RUN npm ci
RUN npm run build



# ===== STAGE 2 : application PHP + Apache =====
FROM php:8.2-apache

# Installer dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# Activer mod_rewrite
RUN a2enmod rewrite

# Autoriser les .htaccess dans toute la conf Apache
RUN sed -ri 's/AllowOverride None/AllowOverride All/g' /etc/apache2/apache2.conf

# Remplacer le VirtualHost pour intégrer un fallback vers index.php
COPY ./.docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# Préparer l’application
WORKDIR /var/www/html

# Copier le code Symfony (inclut public/ sans build)
COPY . .

# Copier les assets générés
COPY --from=build-assets /app/public/build public/build

# Installer Composer et dépendances PHP sans auto-scripts
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --no-scripts

# Vider et réchauffer le cache Symfony
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# Permissions sur var et public/build
RUN chown -R www-data:www-data var \
 && if [ -d public/build ]; then chown -R www-data:www-data public/build; fi

# Variables d’environnement
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# Exposer le port Apache
EXPOSE 80

# Démarrer Apache
CMD ["apache2-foreground"]
