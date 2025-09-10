# 1. Base PHP
FROM php:8.2-cli

# 2. Instalar dependências mínimas para Laravel + SQLite
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip \
    git \
    zip \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_sqlite mbstring bcmath \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4. Diretório de trabalho
WORKDIR /app

# 5. Copiar composer.json e composer.lock
COPY composer.json composer.lock ./

# 6. Instalar dependências Laravel
RUN composer install --no-dev --optimize-autoloader

# 7. Copiar o restante do código
COPY . .

# 8. Criar pastas necessárias e permissões
RUN mkdir -p storage/framework/{cache,data,sessions,views} \
    && mkdir -p storage/logs \
    && mkdir -p database \
    && touch database/database.sqlite \
    && chmod -R 777 storage bootstrap/cache database

# 9. Limpar caches Laravel
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear

# 10. Expor porta
EXPOSE 8000

# 11. Comando padrão
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
