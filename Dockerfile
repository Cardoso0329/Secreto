FROM php:8.2-cli

# Instalar dependências do sistema e extensões PHP necessárias
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    zip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring bcmath gd \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /app

# Copiar apenas os ficheiros de dependências primeiro (melhora cache do Docker)
COPY composer.json composer.lock ./

# Instalar dependências PHP do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# Copiar o restante do projeto
COPY . .

# Expor porta do Laravel
EXPOSE 10000

# Comando padrão para rodar o servidor
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
