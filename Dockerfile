FROM php:8.2-cli

# Instalar extensões do PHP
RUN apt-get update && apt-get install -y \
    unzip git libzip-dev zip \
    libpng-dev libjpeg-dev libfreetype6-dev \
    libonig-dev libxml2-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        zip \
        pdo_pgsql \
        mbstring \
        bcmath \
        gd \
        xml \
        fileinfo \
        opcache \
    && rm -rf /var/lib/apt/lists/*


# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /app

# Copiar código (sem arquivos ignorados)
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Criar storage e cache dentro do container
RUN mkdir -p storage/app/public \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 777 storage bootstrap/cache \
    && php artisan storage:link
