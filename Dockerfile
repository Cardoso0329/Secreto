FROM php:8.2-cli

# Instalar dependências do sistema
RUN apt-get update && apt-get install -y \
    unzip \
    git \
    libzip-dev \
    zip \
    && docker-php-ext-install pdo_mysql \
    && rm -rf /var/lib/apt/lists/*

# Instalar Composer
COPY --from=composer:2.6 /usr/bin/composer /usr/bin/composer

# Definir diretório de trabalho
WORKDIR /app

# Copiar projeto para dentro do container
COPY . .

# Instalar dependências PHP do Laravel
RUN composer install --no-dev --optimize-autoloader

# Expor porta do Laravel
EXPOSE 10000

# Comando padrão para rodar o servidor
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
