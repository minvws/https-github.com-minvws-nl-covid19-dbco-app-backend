ARG DEV=0
ARG QA_BUILD=0
ARG QA_RESET_DB=0
ARG QA_GENERATE_TEST_DATA=0

# Will be passed when building in ci action (see release.yml)
ARG APP_VERSION="latest"

# ============================================================
FROM php:8.2.8-apache AS php-apache

RUN pecl install xdebug pcov
RUN docker-php-ext-enable xdebug pcov

# Install packages and extensions
RUN apt-get -qq update && \
    apt-get -y install curl wget vim unzip libzip-dev libpq-dev libicu-dev apt-transport-https apt-transport-https \
    ca-certificates gnupg libaio1 iproute2 libxml2-dev locales locales-all supervisor wait-for-it && \
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

# ============================================================ Backend Packages
FROM php-apache as backend
ARG DEV
ARG QA_BUILD



WORKDIR /src

COPY shared/packages /shared/packages
COPY portal/src/composer.json portal/src/composer.lock /src/

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
COPY portal/src /src/
COPY shared/packages/schema /shared/packages/schema

# Optimized autoloader
RUN --mount=type=secret,id=composer_auth,target=/src/auth.json,required=true composer dump-autoload --optimize --classmap-authoritative

# Generate JSON Schemas
RUN php artisan schema:generate-json-schemas

# Setup QA tweaks when needed
RUN if [ "$QA_BUILD" = "1" ]; then \
    cp /src/qa-migrate /src/migrate; \
    fi

# ============================================================ Frontend Packages

FROM node:16-bullseye as frontend-prod-build
ARG APP_VERSION
ENV APP_VERSION=$APP_VERSION

COPY .yarnrc.yml package.json yarn.lock /build/
COPY .yarn/releases /build/.yarn/releases
COPY .yarn/plugins /build/.yarn/plugins
COPY portal/src/package.json /build/portal/src/
COPY portal/systemtests/package.json /build/portal/systemtests/
COPY shared/packages/dbco-enum/package.json /build/shared/packages/dbco-enum/
COPY shared/packages/ui-library/package.json /build/shared/packages/ui-library/
COPY shared/packages/portal-open-api/package.json /build/shared/packages/portal-open-api/
COPY shared/packages/schema/package.json /build/shared/packages/schema/
COPY shared/packages/portal-api/package.json /build/shared/packages/portal-api/

WORKDIR /build/portal/src
RUN OPENCOLLECTIVE_HIDE=true yarn install --immutable

WORKDIR /build

COPY portal/src/tsconfig.json \
    portal/src/tsconfig.vue-tsc.json \
    portal/src/vite.config.js \
    portal/src/tailwind.config.js \
    portal/src/postcss.config.js \
    portal/src/public \
    /build/portal/src/

COPY portal/src/resources portal/src/resources
COPY shared/packages/dbco-enum shared/packages/dbco-enum
COPY shared/packages/ui-library shared/packages/ui-library
COPY shared/packages/portal-open-api shared/packages/portal-open-api
COPY shared/packages/schema shared/packages/schema
COPY shared/packages/portal-api shared/packages/portal-api

WORKDIR /build/portal/src

RUN VITE_APP_VERSION=$APP_VERSION yarn build

# ============================================================ App

FROM php-apache as base

# custom stop signal for a graceful stop of supervisord
STOPSIGNAL SIGINT

ARG APP_VERSION
ARG QA_RESET_DB
ARG QA_GENERATE_TEST_DATA
ENV APP_VERSION=$APP_VERSION
ENV QA_RESET_DB=$QA_RESET_DB
ENV QA_GENERATE_TEST_DATA=$QA_GENERATE_TEST_DATA

# files from backend
COPY --from=backend /shared /shared
RUN true
COPY --from=backend /src /src
RUN true

# files from host
COPY portal/assets/supervisord /etc/supervisor

COPY --from=frontend-prod-build /build/portal/src/public /src/public

# create dir to store pid file in
RUN mkdir /var/run/supervisord

# setup src dir
WORKDIR /src

# HSM requirements
RUN apt-get -qq update && \
    apt-get -y install \
    apt-transport-https ca-certificates libengine-pkcs11-openssl \
    python3-full python3-dev python3-pip && \
    pip install --break-system-packages python-pkcs11 && \
    rm -rf /var/lib/apt/lists/*

RUN mkdir /etc/utimaco
RUN mkdir /usr/lib/utimaco
COPY portal/assets/cs_pkcs11_R2.cfg /etc/utimaco/cs_pkcs11_R2.cfg
COPY portal/assets/libcs_pkcs11_R3.so /usr/lib/utimaco/libcs_pkcs11_R3.so
ENV PKCS_MODULE=/usr/lib/utimaco/libcs_pkcs11_R3.so
ENV SECURITY_MODULE_SLOT_LABEL="CryptoServer PKCS11 Token"

RUN usermod -u 1000 www-data && \
    chgrp -R www-data /src/storage /src/bootstrap/cache /var/run/supervisord && \
    chmod -R ug+rwx /src/storage /src/bootstrap/cache /var/run/supervisord
