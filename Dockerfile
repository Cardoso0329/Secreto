FROM php:8.2-cli

# Instalar dependências do sistema e extensões do PHP
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
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd tokenizer ctype fileinfo xml opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copiar projeto
COPY . .

# Garantir que storage existe
RUN mkdir -p public/storage

# Expor porta usada no Render
EXPOSE 10000

# Rodar servidor Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
