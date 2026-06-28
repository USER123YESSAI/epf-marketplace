#!/bin/sh

# On s'arrête immédiatement si une commande échoue
set -e

echo "🚀 Starting Laravel production setup..."

# 1. Gestion des caches Laravel
php artisan config:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# 2. Exécution des migrations sur ton cluster TiDB Cloud
echo "🗄️ Running database migrations..."
php artisan migrate --force

echo "✅ Optimization and migrations completed!"
echo "🌐 Starting Laravel web server..."

# 3. CRUCIAL : Lancer le serveur au premier plan pour maintenir le conteneur en vie
php artisan serve --host=0.0.0.0 --port=80