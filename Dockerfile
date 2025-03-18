FROM php:8.4-fpm

RUN apt update && apt install -y \
    nginx \
    git \
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

RUN composer install --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

EXPOSE 80

CMD php-fpm --daemonize && \
    nginx -g "daemon off;" 