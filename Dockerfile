FROM php:8.2-cli

# Instalar extensões do PHP necessárias
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

# Copiar código para o container
COPY . .

# Instalar dependências do Laravel (sem dev para produção)
RUN composer install --no-dev --optimize-autoloader

# Dar permissões de escrita
RUN mkdir -p storage/app/public \
    && mkdir -p storage/framework/cache/data \
    && mkdir -p storage/framework/sessions \
    && mkdir -p storage/framework/views \
    && mkdir -p storage/logs \
    && chmod -R 777 storage bootstrap/cache

# Definir Environment Variables (substituir valores reais conforme necessário)
ENV APP_NAME=Laravel
ENV APP_ENV=production
ENV APP_DEBUG=false
ENV APP_KEY=base64:4W3gnOmC0fTFjcPolS0HQx/AxEMlle3Ihuu1tzzuJaY=
ENV APP_URL=https://secreto-ijxi.onrender.com

ENV DB_CONNECTION=pgsql
ENV DB_HOST=dpg-d302qq75r7bs73avj4u0-a
ENV DB_PORT=5432
ENV DB_DATABASE=sccs_db_6j3h
ENV DB_USERNAME=sccs_db_6j3h_user
ENV DB_PASSWORD=2itrgXZZEfidQ3d6wtQGuQ77n7YbKspA

ENV SESSION_DRIVER=database
ENV CACHE_STORE=database

# Expor porta 8080 (Render usa 8080)
EXPOSE 8080

# Comando final para rodar migrations, seeds, storage link e iniciar Laravel
CMD php artisan migrate --force && php artisan db:seed && php artisan storage:link && php artisan serve --host=0.0.0.0 --port=8080
