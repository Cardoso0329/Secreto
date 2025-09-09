# Base pronta com PHP + Composer
FROM composer:2.6

# Definir diretório de trabalho
WORKDIR /app

# Copiar ficheiros de dependências primeiro
COPY composer.json composer.lock ./

# Instalar dependências PHP do Laravel
RUN composer install --no-dev --optimize-autoloader --no-interaction --prefer-dist \
    && composer clear-cache

# Criar pasta de storage pública para evitar erros de build
RUN mkdir -p public/storage

# Copiar resto do projeto
COPY . .

# Expor a porta que o Render vai usar
EXPOSE 10000

# Comando padrão para rodar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
