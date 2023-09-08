ARG DEV=0
# Will be passed when building in ci action (see release.yml)
ARG APP_VERSION="latest"


# ============================================================ Apache/PHP
FROM php:8.2.8-apache AS php-apache

RUN pecl install xdebug pcov
RUN docker-php-ext-enable xdebug pcov

# Install packages and extensions
RUN apt-get -qq update && \
    apt-get -y install curl wget vim unzip libzip-dev libpq-dev libicu-dev apt-transport-https apt-transport-https \
    ca-certificates gnupg libaio1 iproute2 libxml2-dev locales locales-all supervisor && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip opcache pdo mysqli pdo_mysql soap sockets pcntl intl

RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod proxy && \
    a2enmod proxy_http && \
    a2enmod rewrite deflate

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer global require laravel/installer

# Copy assets and sources
COPY portal/assets/default.conf /etc/apache2/sites-available/000-default.conf
COPY portal/assets/entrypoint.sh /entrypoint.sh
COPY portal/assets/custom.ini /usr/local/etc/php/conf.d/custom.ini
COPY portal/assets/security.conf /etc/apache2/conf-available/security.conf
COPY portal/assets/apache2.conf /etc/apache2/apache2.conf
COPY portal/assets/ports.conf /etc/apache2/ports.conf
COPY portal/assets/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf

RUN chmod 755 /entrypoint.sh

# logs should go to stdout / stderr
RUN set -ex \
    && ln -sf /proc/1/fd/1 "/var/log/cron.log" \
    && ln -sf /proc/1/fd/1 "/var/log/syslog"

ENV PATH $PATH:/root/.composer/vendor/bin

# Default PHP settings
ENV PHP_OPCACHE_VALIDATE_TIMESTAMPS=0
ENV PHP_OPCACHE_MAX_ACCELERATED_FILES=10000
ENV PHP_OPCACHE_MEMORY_CONSUMPTION=192
ENV PHP_OPCACHE_MAX_WASTED_PERCENTAGE=10

# Default Apache settings
ENV APACHE_TIMEOUT=10
ENV APACHE_KEEP_ALIVE=on
ENV APACHE_MAX_KEEP_ALIVE_REQUESTS=100
ENV APACHE_KEEP_ALIVE_TIMEOUT=1
ENV APACHE_LOG_LEVEL=warn

WORKDIR /src

EXPOSE 8080/tcp

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]

# ============================================================ App dependencies
FROM php-apache as app-dependencies
ARG DEV

WORKDIR /src

COPY shared/packages /shared/packages
COPY gateway/src gateway/src/composer.lock /src/

RUN --mount=type=secret,id=composer_auth,target=/src/auth.json,required=true if [ "$DEV" = "1" ] ; then \
    composer install \
    --ignore-platform-reqs \
    --no-ansi \
    --no-autoloader \
    --no-interaction ;\
    else \
    composer install \
    --ignore-platform-reqs \
    --no-ansi \
    --no-autoloader \
    --no-interaction \
    --no-dev \
    --no-scripts ;\
    fi

COPY shared/library /shared/library
RUN true
COPY gateway/src /src/

RUN --mount=type=secret,id=composer_auth,target=/src/auth.json,required=true composer dump-autoload --optimize --classmap-authoritative


# ============================================================ App
FROM app-dependencies as app
ARG APP_VERSION

# Make the build-arg available in runtime
ENV APP_VERSION=$APP_VERSION

COPY --from=app-dependencies /shared /shared
COPY --from=app-dependencies /src /src

RUN usermod -u 1000 www-data && \
    chgrp -R www-data /src/storage && \
    chmod -R ug+rwx /src/storage
