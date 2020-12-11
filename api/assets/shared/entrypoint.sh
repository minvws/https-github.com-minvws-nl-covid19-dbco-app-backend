#!/usr/bin/env bash
declare -p | grep -Ev 'BASHOPTS|BASH_VERSINFO|EUID|PPID|SHELLOPTS|UID' > /container.env

cd /src

if [ "$APP_ENV" != "production" ]; then
    echo "Restoring default MPM prefork settings (not running in production)"
    cp -f /etc/apache2/mods-available/mpm_prefork_default.conf /etc/apache2/mods-available/mpm_prefork.conf

    echo "Disabling opcache"
    rm  /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini || true
fi

touch /var/log/cron.log


exec "$@"
