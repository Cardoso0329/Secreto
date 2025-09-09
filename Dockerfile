FROM php:8.2-cli

WORKDIR /app

# Copiar todo o projeto, incluindo vendor
COPY . .

# Criar pasta public/storage
RUN mkdir -p public/storage

# Expor porta do Render
EXPOSE 10000

# Comando padrão
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
