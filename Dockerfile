# 1. Base PHP
FROM php:8.2-cli

# 2. Instalar dependências do sistema e PostgreSQL
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    zip \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo_pgsql \
        mbstring \
        bcmath \
        gd \
        zip \
        xml \
        fileinfo \
        opcache \
    && rm -rf /var/lib/apt/lists/*

# 3. Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# 4. Diretório de trabalho
WORKDIR /app

# 5. Copiar código
COPY . .

# 6. Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# 7. Criar pastas necessárias e permissões
RUN mkdir -p storage/app/public \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 777 storage bootstrap/cache

# 8. Limpar cache do Laravel e gerar cache de configs
RUN php artisan config:clear \
    && php artisan route:clear \
    && php artisan view:clear


# 9. Expor porta
EXPOSE 8000

# 10. Comando padrão
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
