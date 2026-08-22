FROM php:8.4-fpm

RUN apt update && apt install -y \
    nginx \
    unzip \
    libzip-dev \
    libonig-dev \
    libxml2-dev \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring zip exif pcntl sockets \
    && apt clean && rm -rf /var/lib/apt/lists/*

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader && rm -rf /root/.composer

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chown -R 755 public

EXPOSE 80

CMD ["sh", "-c", "php-fpm --daemonize && nginx -g 'daemon off;'"]