# ===== STAGE 1 : construction des assets (Node) =====
FROM node:18 AS build-assets

# 1. Travailler dans /app pour le build JS/CSS
WORKDIR /app

# 2. Copier tout le projet (incluant package.json, webpack.config.js, assets/, templates/, etc.)
COPY . .

# 3. Installer les dépendances JavaScript
RUN npm ci

# 4. Compiler les assets (génère public/build/manifest.json, app.css, etc.)
RUN npm run build



# ===== STAGE 2 : application PHP + Apache =====
FROM php:8.2-apache

# 1. Installer les dépendances système + extensions PHP Symfony
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# 2. Activer mod_rewrite pour les URL Symfony
RUN a2enmod rewrite

# 3. Remplacer le VirtualHost par notre config personnalisée
COPY ./.docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# 4. Définir le dossier de travail  
WORKDIR /var/www/html

# 5. Copier le code complet de l’application depuis la racine du contexte Docker
COPY . .

# 6. Copier les assets compilés depuis le stage Node
COPY --from=build-assets /app/public/build public/build

# 7. Installer Composer et les dépendances PHP sans auto-scripts (évite symfony-cmd)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --no-scripts

# 8. Exécuter manuellement les auto-scripts Symfony indispensables
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# 9. Ajuster les permissions, en rendant le chown conditionnel sur public/build
RUN chown -R www-data:www-data var \
 && if [ -d public/build ]; then chown -R www-data:www-data public/build; fi

# 10. Variables d’environnement de production + base MySQL
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# 11. Exposer le port Apache par défaut
EXPOSE 80

# 12. Lancer Apache au premier plan
CMD ["apache2-foreground"]
