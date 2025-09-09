# Usar PHP CLI 8.2
FROM php:8.2-cli

# Definir diretório de trabalho
WORKDIR /app

# Copiar todo o projeto (incluindo vendor e public/build)
COPY . .

# Criar pasta de storage pública para evitar erros
RUN mkdir -p public/storage

# Expor porta que o Render vai usar
EXPOSE 10000

# Comando padrão para rodar Laravel
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=10000"]
