FROM php:7.4

# Install packages and extensions
RUN apt-get -qq update && \
    apt-get -y install curl git wget vim unzip libzip-dev libpq-dev libicu-dev apt-transport-https apt-transport-https ca-certificates gnupg unzip \
                       openssl opensc libengine-pkcs11-openssl apt-utils \
    && rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install zip && \
    docker-php-ext-install pdo_pgsql && \
    docker-php-ext-install opcache

# Install Composer and prestissimo globally for increased performance
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer self-update

COPY src /src
RUN cd /src && composer install

# Environment
ENV PATH $PATH:/root/.composer/vendor/bin

# Logs should go to stdout / stderr
RUN set -ex \
    && ln -sf /proc/1/fd/1 "/var/log/cron.log" \
    && ln -sf /proc/1/fd/1 "/var/log/syslog"

WORKDIR /src

ENTRYPOINT ["/src/console"]
