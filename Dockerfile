FROM php:8.2-cli

# Instalar dependências essenciais e PostgreSQL
RUN apt-get update && apt-get install -y --no-install-recommends \
    unzip git zip libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-install pdo_pgsql mbstring bcmath zip xml fileinfo opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /app
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Criar pastas necessárias e permissões
RUN mkdir -p storage/framework/{cache,data,sessions,views} storage/logs bootstrap/cache \
    && chmod -R 777 storage bootstrap/cache

# Porta
EXPOSE 8000

# Comando padrão para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
