# 1. Base PHP
FROM php:8.2-cli

# 2. Instalar dependências do sistema + extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    unzip git zip sqlite3 libsqlite3-dev libonig-dev libxml2-dev \
    && docker-php-ext-install mbstring bcmath pdo_sqlite xml zip fileinfo opcache \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4. Diretório de trabalho
WORKDIR /app

# 5. Copiar código sem a pasta vendor
COPY composer.json composer.lock ./

# 6. Rodar composer install
RUN composer install --no-dev --optimize-autoloader

# 7. Copiar o restante do código
COPY . .

# 8. Criar pastas e permissões
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
