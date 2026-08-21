FROM php:8.3-fpm

RUN apt-get update && apt-get install -y \
        libpq-dev libicu-dev libzip-dev unzip git \
    && docker-php-ext-install pdo pdo_pgsql intl zip \
    && rm -rf /var/lib/apt/lists/*

# The repo is bind-mounted from the host, so its files are owned by the host
# user, not by root/www-data inside the container. Tell git not to refuse it.
RUN git config --system --add safe.directory /var/www/html

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Match php-fpm's www-data to the host user so bind-mounted storage/ and
# bootstrap/cache are writable in local dev. Override with build args if your
# host UID/GID differ: --build-arg UID=$(id -u) --build-arg GID=$(id -g).
ARG UID=1000
ARG GID=1000
RUN usermod -u ${UID} www-data \
    && groupmod -g ${GID} www-data \
    && sed -i "s/^user = www-data/user = www-data/; s/^group = www-data/group = www-data/" /usr/local/etc/php-fpm.d/www.conf

WORKDIR /var/www/html
