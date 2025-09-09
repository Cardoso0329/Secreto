FROM php:8.2-cli

# Instalar dependências necessárias do sistema
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
    default-mysql-client \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
       pdo_mysql \
       pdo_pgsql \
       mbstring \
       bcmath \
       gd \
       tokenizer \
       ctype \
       fileinfo \
       xml \
       opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir pasta de trabalho
WORKDIR /app

# Copiar código do Laravel
COPY . .

# Instalar dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Dar permissões ao storage e bootstrap/cache
RUN chmod -R 777 storage bootstrap/cache
