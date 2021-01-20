# Build backend sources
FROM composer as backend
WORKDIR /src

COPY shared/packages /shared/packages
COPY portal/src/composer.json portal/src/composer.lock /src/
RUN composer install \
    --ignore-platform-reqs \
    --no-ansi \
    --no-autoloader \
    --no-dev \
    --no-interaction \
    --no-scripts

COPY shared/library /shared/library
COPY shared/packages /shared/packages
COPY portal/src/ /src/
RUN composer dump-autoload --optimize --classmap-authoritative

# Build frontend assets
FROM node as frontend
WORKDIR /src

COPY portal/src/package.json portal/src/package-lock.json portal/src/webpack.mix.js /src/
RUN npm install

COPY portal/src/resources /src/resources
RUN cd /src && npm run production

# Build main image
FROM php:7.4-apache

# Install packages and extensions
RUN apt update

RUN apt-get -qq update && \
    apt-get -y install curl git wget vim unzip libzip-dev libpq-dev libicu-dev apt-transport-https apt-transport-https ca-certificates gnupg npm libaio1 \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip opcache pdo pdo_pgsql mysqli pdo_mysql

RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod proxy && \
    a2enmod proxy_http && \
    a2enmod rewrite deflate

# Copy assets and sources
COPY portal/assets/default.conf /etc/apache2/sites-available/000-default.conf
COPY portal/assets/entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh
COPY portal/assets/portal.ini /usr/local/etc/php/conf.d/portal.ini
COPY portal/assets/security.conf /etc/apache2/conf-available/security.conf
COPY portal/assets/apache2.conf /etc/apache2/apache2.conf
COPY portal/assets/ports.conf /etc/apache2/ports.conf
COPY portal/assets/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
COPY portal/assets/mpm_prefork_default.conf /etc/apache2/mods-available/mpm_prefork_default.conf

WORKDIR /src
COPY --from=backend /shared /shared
COPY --from=backend /src /src
COPY --from=frontend /src/public /src/public

RUN usermod -u 1000 www-data && \
    chgrp -R www-data /src/storage /src/bootstrap/cache && \
    chmod -R ug+rwx /src/storage /src/bootstrap/cache

# logs should go to stdout / stderr
RUN set -ex \
	&& ln -sf /proc/1/fd/1 "/var/log/cron.log" \
	&& ln -sf /proc/1/fd/1 "/var/log/syslog"

ENV PATH $PATH:/root/.composer/vendor/bin

ARG INSTALL_DEV_TOOLS=0
RUN if [ "$INSTALL_DEV_TOOLS" = "1" ] ; then \
        npm install npm@6 -g && \
        curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
        composer global require laravel/installer; \
    fi

EXPOSE 8080/tcp

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]