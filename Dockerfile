# ===== STAGE 1 : construction des assets (Node) =====
FROM node:18 AS build-assets

WORKDIR /app

# Copier uniquement les fichiers de configuration NPM
COPY package.json package-lock.json ./

# Installer les dépendances JS
RUN npm ci

# Copier le dossier assets et tout le reste du code nécessaire pour le build
COPY assets assets
# (Si vous avez d'autres dossiers JS, ajoutez-les également)

# Compiler les assets (génère public/build/manifest.json, CSS, JS optimisés)
RUN npm run build



# ===== STAGE 2 : application PHP =====
FROM php:8.2-apache

# Installer les dépendances système + extensions PHP
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# Activer mod_rewrite
RUN a2enmod rewrite

# Mettre à jour la config Apache pour servir le répertoire public/
RUN sed -ri \
      -e 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
      -e 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' \
      /etc/apache2/sites-available/*.conf \
    && sed -ri \
      -e 's|/var/www/html|/var/www/html/public|g' \
      /etc/apache2/apache2.conf

# Définir le dossier de travail
WORKDIR /var/www/html

# Copier l'ensemble du projet Symfony
COPY . .

# Copier les assets construits depuis le stage Node
COPY --from=build-assets /app/public/build public/build

# Copier Composer et installer les dépendances PHP sans scripts auto
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --no-scripts

# Exécuter manuellement les auto-scripts essentiels
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# Redéfinir les permissions
RUN chown -R www-data:www-data var public/build

# Variables d'environnement de production
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# Exposer le port Apache
EXPOSE 80

# Démarrer Apache au premier plan
CMD ["apache2-foreground"]
