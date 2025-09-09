FROM php:8.2-cli

# Instalar extensões do PHP necessárias
RUN apt-get update && apt-get install -y \
    libzip-dev zip \
    libonig-dev \
    libxml2-dev \
    default-mysql-client \
    && docker-php-ext-install pdo_mysql mbstring bcmath tokenizer ctype fileinfo xml opcache \
    && rm -rf /var/lib/apt/lists/*

WORKDIR /app

# Copiar projeto completo (incluindo vendor e public/build)
COPY . .

# Criar storage
RUN mkdir -p public/storage

EXPOSE 10000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
