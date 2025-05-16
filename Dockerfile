# 1. Image de base PHP + Apache
FROM php:8.2-apache

# 2. Installation des dépendances système
RUN apt-get update && apt-get install -y \
    git unzip libicu-dev libzip-dev zip libonig-dev \
    && docker-php-ext-install intl pdo_mysql zip

# 3. Activation du mod_rewrite pour Symfony
RUN a2enmod rewrite

# 4. Définition du dossier de travail
WORKDIR /var/www/html

# 5. Copie des fichiers de l’application
COPY . .

# 6. Installation de Composer (depuis l’image officielle)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# 7. Installation des dépendances PHP de Symfony
RUN composer install --no-dev --optimize-autoloader

# 8. Permissions sur le dossier var pour Apache
RUN chown -R www-data:www-data var

# 9. Passage en environnement de production
ENV APP_ENV=prod
ENV DATABASE_URL="mysql://if0_39001249:OH5HLy1D7dcD@sql105.infinityfree.com/if0_39001249_livrai"

# 10. Exposition du port 80
EXPOSE 80

# 11. Commande de démarrage Apache
CMD ["apache2-foreground"]
