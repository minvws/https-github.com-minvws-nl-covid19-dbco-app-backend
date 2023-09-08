FROM php:8.1.6-cli

# install packages and extensions
RUN apt update

RUN apt-get -qq update && \
    apt-get -y install libpq-dev procps && \
    rm -rf /var/lib/apt/lists/*

RUN docker-php-ext-install pdo pdo_pgsql mysqli pdo_mysql pcntl

USER www-data

WORKDIR /src

CMD ["php", "artisan", "rabbitmq:consume", "rabbitmq", "--queue=test_result"]
