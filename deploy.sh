#!/bin/bash
set -e

echo "🚀 Iniciando deploy..."

echo "🔧 Executando migrations..."
php artisan migrate --force

echo "🌱 Executando seeders..."
php artisan db:seed --force

echo "⚡ Atualizando caches..."
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "✅ Deploy finalizado com sucesso!"
