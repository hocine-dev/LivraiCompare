# 1. Image de base PHP + Apache
FROM php:8.2-apache

# 2. Installation des dépendances système et extensions PHP
RUN apt-get update && apt-get install -y \
      git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# 3. Activation de mod_rewrite
RUN a2enmod rewrite

# 4. Remplacer le VirtualHost par notre config (sert public/)
COPY ./.docker/apache/000-default.conf /etc/apache2/sites-available/000-default.conf

# 5. Copier le code Symfony (inclut public/build avec ton app.css)
WORKDIR /var/www/html
COPY . .

# 6. Installer Composer et dépendances PHP sans auto-scripts
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install \
      --no-dev --optimize-autoloader --no-interaction --no-scripts

# 7. Exécuter manuellement les scripts Symfony essentiels
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# 8. Ajuster les permissions
RUN chown -R www-data:www-data var public/build

# 9. Variables d’environnement
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# 10. Exposer le port Apache
EXPOSE 80

# 11. Démarrer Apache
CMD ["apache2-foreground"]
