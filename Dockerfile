# Multi-stage build para otimizar o tamanho da imagem
FROM php:8.2-fpm-alpine AS builder

# Define o diretório de trabalho
WORKDIR /var/www/html

# Instala o Composer
COPY --from=composer:latest /usr/bin/composer /usr/local/bin/composer

# Copia apenas os arquivos de dependências primeiro (melhor cache)
COPY composer.json composer.lock ./

# Instala dependências de build temporárias
RUN apk add --no-cache --virtual .build-deps \
    libzip-dev \
    libpng-dev \
    libjpeg-turbo-dev \
    zlib-dev \
    linux-headers \
    && docker-php-ext-configure gd --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
    pdo_mysql \
    opcache \
    bcmath \
    zip \
    gd \
    sockets \
    exif \
    pcntl \
    pdo

# Instala dependências do Composer
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copia o resto da aplicação
COPY . .

# Executa scripts pós-instalação
RUN composer dump-autoload --optimize

# ============================================
# Stage 2: Imagem de produção
# ============================================
FROM php:8.2-fpm-alpine

# Define variáveis de ambiente
ENV TZ=America/Sao_Paulo \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILESIZE=20M \
    PHP_POST_MAX_SIZE=25M

# Instala dependências do sistema (apenas runtime, sem -dev)
RUN apk add --no-cache \
    supervisor \
    libzip \
    libpng \
    libjpeg-turbo \
    mysql-client \
    tzdata \
    && ln -snf /usr/share/zoneinfo/$TZ /etc/localtime \
    && echo $TZ > /etc/timezone

# Instala extensões PHP (copiando dos binários já compilados do builder)
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/

# Copia configurações PHP customizadas
COPY docker/php/php.ini /usr/local/etc/php/conf.d/custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Define o diretório de trabalho
WORKDIR /var/www/html

# Copia a aplicação do builder
COPY --from=builder /var/www/html /var/www/html

# Cria usuário não-root para segurança
RUN addgroup -g 1000 laravel \
    && adduser -D -u 1000 -G laravel laravel

# Cria diretórios necessários e ajusta permissões
RUN mkdir -p \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    && chown -R laravel:laravel \
    storage \
    bootstrap/cache \
    && chmod -R 775 \
    storage \
    bootstrap/cache

# Cria diretório para logs do PHP-FPM
RUN mkdir -p /var/log/php-fpm && chown -R laravel:laravel /var/log/php-fpm

# Configura PHP-FPM para rodar como usuário laravel
RUN sed -i 's/user = www-data/user = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/group = www-data/group = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/listen.owner = www-data/listen.owner = laravel/g' /usr/local/etc/php-fpm.d/www.conf \
    && sed -i 's/listen.group = www-data/listen.group = laravel/g' /usr/local/etc/php-fpm.d/www.conf

# Muda para usuário não-root
USER laravel

# Expõe a porta do PHP-FPM
EXPOSE 9000

# Healthcheck
HEALTHCHECK --interval=30s --timeout=3s --retries=3 --start-period=40s \
    CMD php-fpm-healthcheck || exit 1

# Comando padrão para iniciar o PHP-FPM
CMD ["php-fpm"]
