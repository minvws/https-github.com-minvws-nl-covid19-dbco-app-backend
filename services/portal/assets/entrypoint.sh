#!/usr/bin/env bash

cd /src

if [ "$APP_ENV" != "production" ]; then
    echo "Disabling opcache"
    if command -v phpdismod &> /dev/null; then
        phpdismod opcache
    else
        rm -f /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini
    fi
fi

exec "$@"
