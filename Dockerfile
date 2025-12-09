# Usa uma imagem base oficial do PHP com o FPM (FastCGI Process Manager)
FROM php:8.2-fpm-alpine

# Instala dependências do sistema e extensões PHP necessárias para o Laravel
RUN apk add --no-cache \
    $php_packages \
    nginx \
    # Dependências do sistema
    supervisor \
    libzip \
    libpng \
    libjpeg \
    mysql-client \
    # Extensões PHP
    && docker-php-ext-install pdo_mysql \
    && docker-php-ext-install opcache \
    && docker-php-ext-install bcmath \
    && docker-php-ext-install zip \
    && docker-php-ext-install gd \
    && docker-php-ext-install sockets \
    && docker-php-ext-install exif \
    && docker-php-ext-install pcntl \
    && docker-php-ext-install pdo

# Instala o Composer globalmente
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Define o diretório de trabalho dentro do container
WORKDIR /var/www/html

# Copia o código da aplicação
COPY . .

# Instala as dependências do Laravel
RUN composer install --no-dev --optimize-autoloader

# Gera a chave da aplicação (se não estiver no .env)
# Recomenda-se que isso seja feito fora do Dockerfile se possível,
# mas se precisar:
# RUN php artisan key:generate

# Dá permissão ao PHP-FPM para os arquivos (pode variar dependendo do usuário que o PHP-FPM usa,
# mas 'www-data' ou 'root' são comuns, 'root' no caso do alpine)
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Expor a porta do PHP-FPM (geralmente 9000)
EXPOSE 9000

# Comando padrão para iniciar o PHP-FPM
CMD ["php-fpm"]