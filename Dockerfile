FROM php:8.2-cli

# Instalar dependências e PostgreSQL
RUN apt-get update && apt-get install -y \
    unzip git zip libpng-dev libjpeg-dev libfreetype6-dev libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_pgsql mbstring bcmath gd zip xml fileinfo opcache \
    && rm -rf /var/lib/apt/lists/*

# Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Diretório de trabalho
WORKDIR /app
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Pastas necessárias e permissões
RUN mkdir -p storage/framework/{cache,data,sessions,views} storage/logs && chmod -R 777 storage bootstrap/cache

# Porta
EXPOSE 8000

# Comando para iniciar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
