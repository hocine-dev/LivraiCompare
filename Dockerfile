# ===== STAGE 1 : construction des assets (Node) =====
FROM node:18 AS build-assets

# 1. Définir le dossier de travail pour le build JS
WORKDIR /app

# 2. Copier tout le projet pour que Webpack accède à src/, controllers.json, assets/, etc.
COPY . .

# 3. Installer les dépendances JS
RUN npm ci

# 4. Compiler les assets (génère public/build/manifest.json, CSS, JS optimisés)
RUN npm run build


# ===== STAGE 2 : application PHP + Apache =====
FROM php:8.2-apache

# 1. Installer les dépendances système et extensions PHP nécessaires
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# 2. Activer mod_rewrite pour Symfony
RUN a2enmod rewrite

# 3. Mettre à jour la config Apache pour servir le répertoire public/
RUN sed -ri \
      -e 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
      -e 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' \
      /etc/apache2/sites-available/*.conf \
    && sed -ri \
      -e 's|/var/www/html|/var/www/html/public|g' \
      /etc/apache2/apache2.conf

# 4. Définir le dossier de travail pour le code PHP
WORKDIR /var/www/html

# 5. Copier le code Symfony
COPY . .

# 6. Copier les assets compilés depuis le stage Node
COPY --from=build-assets /app/public/build public/build

# 7. Installer Composer et les dépendances PHP sans auto-scripts
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --no-scripts

# 8. Exécuter manuellement les scripts Symfony essentiels
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# 9. Ajuster les permissions
RUN chown -R www-data:www-data var public/build

# 10. Variables d’environnement de production (+ base MySQL)
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# 11. Exposer le port Apache
EXPOSE 80

# 12. Démarrer Apache au premier plan
CMD ["apache2-foreground"]
