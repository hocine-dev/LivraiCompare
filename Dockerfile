# Utilise l'image PHP 8.1 CLI
FROM php:8.1-cli

# Installe les extensions PDO pour MySQL (ou pdo_pgsql si PostgreSQL)
RUN docker-php-ext-install pdo pdo_mysql

# Installe Composer
RUN php -r "copy('https://getcomposer.org/installer', 'installer.php');" \
 && php installer.php --install-dir=/usr/local/bin --filename=composer \
 && rm installer.php

# Définit le dossier de travail
WORKDIR /app

# Copie les définitions de dépendances et installe
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Copie tout le code
COPY . .

# Expose le port utilisé par le serveur PHP intégré
EXPOSE 10000

# Démarre le serveur PHP intégré sur 0.0.0.0:10000
CMD ["php", "-S", "0.0.0.0:10000", "-t", "public"]
