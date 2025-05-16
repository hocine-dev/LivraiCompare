# 1. Image de base PHP + Apache
FROM php:8.2-apache

# 2. Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# 3. Activation du mod_rewrite pour Symfony
RUN a2enmod rewrite

# 3bis. Modifier le DocumentRoot pour servir /public
RUN sed -ri \
      -e 's|DocumentRoot /var/www/html|DocumentRoot /var/www/html/public|g' \
      -e 's|<Directory /var/www/html>|<Directory /var/www/html/public>|g' \
      /etc/apache2/sites-available/*.conf \
    && sed -ri \
      -e 's|/var/www/html|/var/www/html/public|g' \
      /etc/apache2/apache2.conf

# 4. Définition du dossier de travail
WORKDIR /var/www/html

# 5. Copie des fichiers de l’application
COPY . .

# 6. Installation de Composer (depuis l’image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 7. Installation des dépendances PHP de Symfony sans scripts auto
#    - COMPOSER_ALLOW_SUPERUSER=1 : autorise l’exécution des plugins en root
#    - --no-scripts : empêche l’erreur symfony-cmd
RUN COMPOSER_ALLOW_SUPERUSER=1 composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# 8. Exécution manuelle des auto-scripts essentiels
RUN php bin/console cache:clear --no-warmup --env=prod \
 && php bin/console cache:warmup --env=prod

# 9. Permissions sur le dossier var pour Apache
RUN chown -R www-data:www-data var

# 10. Passage en environnement de production + base MySQL
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# 11. Exposition du port 80
EXPOSE 80

# 12. Commande de démarrage Apache
CMD ["apache2-foreground"]
