FROM php:7.4-apache

# Install packages and extensions
RUN apt update

RUN apt-get -qq update && \
    apt-get -y install curl git wget vim unzip libzip-dev libpq-dev libicu-dev apt-transport-https apt-transport-https ca-certificates gnupg npm libaio1 \
    && rm -rf /var/lib/apt/lists/*

# Update npm to latest version because package bundle version is outdated
RUN npm install npm@6 -g

# RUN cd /tmp && curl -L https://download.oracle.com/otn_software/linux/instantclient/19800/instantclient-basic-linux.x64-19.8.0.0.0dbru.zip -O
# RUN cd /tmp && curl -L https://download.oracle.com/otn_software/linux/instantclient/19800/instantclient-sdk-linux.x64-19.8.0.0.0dbru.zip -O

# RUN unzip /tmp/instantclient-basic-linux.x64-19.8.0.0.0dbru.zip -d /usr/local/ && \
#     ln -s /usr/local/instantclient_19_8/ /usr/local/instantclient && \
#     ln -s /usr/local/instantclient/lib* /usr/lib

# RUN unzip /tmp/instantclient-sdk-linux.x64-19.8.0.0.0dbru.zip -d /usr/local

RUN docker-php-ext-install zip opcache pdo pdo_pgsql mysqli pdo_mysql

# RUN docker-php-ext-configure pdo_oci --with-pdo-oci=instantclient,/usr/local/instantclient,19.8 && \
#     echo 'instantclient,/usr/local/instantclient' | pecl install oci8 && \
#     docker-php-ext-install pdo_oci && \
#     docker-php-ext-enable oci8

RUN a2enmod rewrite && \
    a2enmod headers && \
    a2enmod proxy && \
    a2enmod proxy_http && \
    a2enmod rewrite deflate

# Install Composer and prestissimo globally for increased performance
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer && \
    composer self-update

# Copy assets and sources
COPY assets/default.conf /etc/apache2/sites-available/000-default.conf
COPY assets/entrypoint.sh /entrypoint.sh
RUN chmod 755 /entrypoint.sh
COPY assets/portal.ini /usr/local/etc/php/conf.d/portal.ini
COPY assets/security.conf /etc/apache2/conf-available/security.conf
COPY assets/apache2.conf /etc/apache2/apache2.conf
COPY assets/ports.conf /etc/apache2/ports.conf
COPY assets/mpm_prefork.conf /etc/apache2/mods-available/mpm_prefork.conf
COPY assets/mpm_prefork_default.conf /etc/apache2/mods-available/mpm_prefork_default.conf

COPY src /src

RUN cd /src && composer install

# logs should go to stdout / stderr
RUN set -ex \
	&& ln -sf /proc/1/fd/1 "/var/log/cron.log" \
	&& ln -sf /proc/1/fd/1 "/var/log/syslog"

# Install Laravel cmdline
RUN composer global require laravel/installer

# Environment
ENV PATH $PATH:/root/.composer/vendor/bin

WORKDIR /src

RUN usermod -u 1000 www-data && \
  chown -R www-data:www-data /src

EXPOSE 8080/tcp

ENTRYPOINT ["/entrypoint.sh"]
CMD ["apache2-foreground"]
