FROM php:8.4-fpm

# Installer les dépendances système nécessaires
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Installer les extensions PHP indispensables pour Laravel
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip

# Récupérer Composer depuis l'image officielle
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Définir le dossier de travail dans le conteneur
WORKDIR /var/www/html

# Copier l'intégralité des fichiers du projet
COPY . .

# Installer les dépendances PHP en forçant le clonage Git (--prefer-source)
# Cela évite le téléchargement des archives zip corrompues (Erreurs HTTP/2 400 de GitHub)
RUN composer install --no-dev --no-interaction --optimize-autoloader --prefer-source

# Ajuster les permissions pour que Laravel puisse écrire dans ses répertoires de stockage
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage

# Exposer le port par défaut
EXPOSE 80

# Laisser s'exécuter l'image php-fpm par défaut
CMD ["php-fpm"]