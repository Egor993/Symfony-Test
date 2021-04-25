FROM php:7.4.2-fpm-buster

# Yandex for speed & postgres 11 client
COPY ./sources.list /etc/apt/sources.list

ARG APP_ENV='prod'

# settings
ENV COMPOSER_ALLOW_SUPERUSER=1
ENV DEBIAN_FRONTEND noninteractive

# Install needed packages
RUN apt-get update; \
    apt-get -y upgrade; \
    apt-get install -y; \
    apt-get install -y --no-install-recommends apt-utils apt-transport-https; \
    apt-get install -y --no-install-recommends \
        nano libxslt1-dev libonig-dev libpq-dev wget gnupg git sudo \
        lsb-release ca-certificates openssh-server iproute2 \
        libzip-dev zlib1g-dev libpcre3-dev unzip libicu-dev \
        libhiredis-dev libjpeg-dev libpng-dev sshpass \
        libwebp-dev libxpm-dev libfreetype6-dev;

RUN echo "\ndeb http://apt.postgresql.org/pub/repos/apt/ buster-pgdg main\n" >> /etc/apt/sources.list; \
    wget --quiet -O - https://www.postgresql.org/media/keys/ACCC4CF8.asc | sudo apt-key add -; \
    apt-get update; \
    apt-get -y upgrade; \
    apt-get install -y --no-install-recommends postgresql-client-11;

# fish shell
RUN echo "deb http://download.opensuse.org/repositories/shells:/fish:/release:/3/Debian_10/ /" > /etc/apt/sources.list.d/fish.list; \
    apt-get update; \
    wget -qO - https://download.opensuse.org/repositories/shells:fish:release:3/Debian_10/Release.key | apt-key add -; \
    apt-get update; \
    apt-get install -y fish;

#
# SSH interpreter shell for user `www-data` with password `oddsdev`
#
# @NOTE: please add this lines to ~/.ssh/config:
#
# Host localhost
#     StrictHostKeyChecking no
#     UserKnownHostsFile=/dev/null
#
COPY ./entrypoint-append.sh /entrypoint-append.sh
ARG ENABLE_BUILTIN_SSH
ENV ENABLE_BUILTIN_SSH $ENABLE_BUILTIN_SSH
RUN mkdir /var/run/sshd; \
    echo 'root:oddsdev' | chpasswd; \
    echo 'www-data:oddsdev' | chpasswd; \
    sed -i 's/#PermitRootLogin prohibit-password/PermitRootLogin yes/' /etc/ssh/sshd_config; \
    bash -c "echo -e \"#!/bin/sh\nENABLE_BUILTIN_SSH=$ENABLE_BUILTIN_SSH\n\" > /entrypoint-prepend.sh"; \
    cat /entrypoint-prepend.sh /entrypoint-append.sh > /entrypoint.sh; \
    rm /entrypoint-prepend.sh /entrypoint-append.sh; \
    chmod +x /entrypoint.sh

ENTRYPOINT [ "/entrypoint.sh" ]

# config
COPY ./www.conf /usr/local/etc/php-fpm.d/www.conf
WORKDIR /var/www/symfony

# php extensions
RUN docker-php-ext-install xsl pdo_pgsql pdo_mysql iconv zip mbstring intl sockets

# gd with full config
RUN docker-php-ext-configure gd \
    --enable-gd \
    --with-webp \
    --with-jpeg \
    --with-xpm \
    --with-freetype

RUN docker-php-ext-install gd

# phpiredis
RUN git clone https://github.com/nrk/phpiredis.git -b v1.0 --depth=1 /tmp/phpiredis; \
    cd /tmp/phpiredis; \
    docker-php-ext-configure /tmp/phpiredis --enable-phpiredis; \
    docker-php-ext-install /tmp/phpiredis; \
    rm -rf /tmp/phpiredis;

# composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
RUN composer --version

# phpcs
RUN composer global require "squizlabs/php_codesniffer=*"; \
    composer global require escapestudios/symfony2-coding-standard:3.x-dev; \
    cp -r /root/.composer/vendor/escapestudios/symfony2-coding-standard/Symfony/ /root/.composer/vendor/squizlabs/php_codesniffer/src/Standards/Symfony/;

# phpdox
RUN wget https://github.com/theseer/phpdox/releases/download/0.11.2/phpdox-0.11.2.phar -O /usr/bin/phpdox; \
    chmod +x /usr/bin/phpdox;

RUN touch /var/run/php7.2-fpm.pid; \
    chown www-data /var/run/php7.2-fpm.pid;

# change /var/www owner to www-data
RUN chown www-data:www-data -R /var/www;

# xdebug
RUN if [ ${APP_ENV} = 'dev' ]; then \
    pecl install xdebug-2.9.1 \
    && docker-php-ext-enable xdebug \
    && echo "xdebug.remote_enable=on" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.force_display_errors=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.extended_info=1" >> /usr/local/etc/php/conf.d/xdebug.ini \
    && echo "xdebug.remote_autostart=off" >> /usr/local/etc/php/conf.d/xdebug.ini \
;fi

# and more extensions;
RUN yes '' | pecl install igbinary apcu apcu_bc swoole; \
    docker-php-ext-enable igbinary.so apcu.so gd.so phpiredis.so swoole.so; \
    echo "extension=apc.so" >> /usr/local/etc/php/conf.d/docker-php-ext-apcu.ini; \
    echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20190902/opcache.so" \
        > /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini;

COPY ./fixperms.sh /fixperms.sh
RUN chmod +x /fixperms.sh

# args for fixperms...
ARG LOCAL_UID
ENV LOCAL_UID $LOCAL_UID

ARG LOCAL_GID
ENV LOCAL_GID $LOCAL_GID

ARG APP_USER
ENV APP_USER $APP_USER

ARG APP_GROUP
ENV APP_GROUP $APP_GROUP

RUN mkdir -p /var/log/symfony
RUN chmod a+rwx -R /var/log/symfony

# add www-data to sudoers with no-password
RUN echo "www-data ALL=(ALL) NOPASSWD: ALL" >> /etc/sudoers

COPY ./xdebug-switch.sh /usr/bin/xdebug-switch.sh
RUN chmod +x /usr/bin/xdebug-switch.sh

RUN rm -rf /tmp/* /var/tmp/* /usr/share/doc/*

# Configure FPM to run properly on docker
RUN sed -i "/listen = .*/c\listen = [::]:9000" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "/;access.log = .*/c\access.log = /proc/self/fd/2" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "/;clear_env = .*/c\clear_env = no" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "/;catch_workers_output = .*/c\catch_workers_output = yes" /usr/local/etc/php-fpm.d/www.conf \
    && sed -i "/pid = .*/c\pid = /var/run/php7.2-fpm.pid" /usr/local/etc/php-fpm.conf \
    && sed -i "/;daemonize = .*/c\daemonize = no" /usr/local/etc/php-fpm.conf \
    && sed -i "/error_log = .*/c\error_log = /proc/self/fd/2" /usr/local/etc/php-fpm.conf

# The following runs FPM and removes all its extraneous log output on top of what your app outputs to stdout
CMD php-fpm -F -O 2>&1 | sed -u 's,.*: \"\(.*\)$,\1,'| sed -u 's,"$,,' 1>&1

# Yarn
RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | sudo apt-key add -; \
    curl -sL deb.nodesource.com/setup_10.x | sudo -E bash -; \
    echo "deb https://dl.yarnpkg.com/debian/ stable main" | sudo tee /etc/apt/sources.list.d/yarn.list; \
    apt-get update; \
    apt-get install -y yarn;

RUN apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*;

ENV IN_CONTAINER 'yep'

ENV DEBIAN_FRONTEND teletype