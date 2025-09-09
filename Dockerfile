FROM php:8.2-cli

# Instalar dependências do sistema necessárias para Laravel + GD
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
    libssl-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd tokenizer ctype fileinfo xml opcache \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /app

# Copiar apenas ficheiros de dependências primeiro
COPY composer.json composer.lock ./

# Instalar dependências PHP do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# Criar pasta de storage pública para evitar erros de build
RUN mkdir -p public/storage

# Copiar o restante do projeto
COPY . .

# Expor porta que o Render vai usar
EXPOSE 10000

# Comando padrão para rodar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
