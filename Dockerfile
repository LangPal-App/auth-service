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
    wget \
    apache2-utils \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo_mysql mbstring zip exif pcntl

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

WORKDIR /var/www/html

COPY . .

RUN composer install --optimize-autoloader

RUN chown -R www-data:www-data storage bootstrap/cache

# Install Prometheus
RUN wget https://github.com/prometheus/prometheus/releases/download/v2.47.0/prometheus-2.47.0.linux-amd64.tar.gz -O /tmp/prometheus.tar.gz && \
    tar -xzf /tmp/prometheus.tar.gz -C /opt && \
    mv /opt/prometheus-2.47.0.linux-amd64 /opt/prometheus && \
    rm /tmp/prometheus.tar.gz

COPY .docker/prometheus.yaml /opt/prometheus/prometheus.yaml

# Install Grafana
RUN apt update && \
    apt install -y apt-transport-https software-properties-common && \
    wget -q -O - https://packages.grafana.com/gpg.key | apt-key add - && \
    echo "deb https://packages.grafana.com/oss/deb stable main" | tee -a /etc/apt/sources.list.d/grafana.list && \
    apt update && \
    apt install -y grafana

COPY .docker/nginx/nginx.conf /etc/nginx/nginx.conf

COPY .docker/nginx/nginx-laravel.conf /etc/nginx/sites-available/laravel.conf

RUN mkdir -p /etc/nginx/prometheus
COPY .docker/nginx/nginx-prometheus.conf /etc/nginx/prometheus/nginx-prometheus.conf

RUN htpasswd -cb /etc/nginx/prometheus/.htpasswd admin password123

EXPOSE 80 9091 3000

CMD php-fpm --daemonize && \
    nginx -g "daemon off;" & \
    /opt/prometheus/prometheus --config.file=/opt/prometheus/prometheus.yaml & \
    /usr/sbin/grafana-server --config /etc/grafana/grafana.ini --homepath /usr/share/grafana