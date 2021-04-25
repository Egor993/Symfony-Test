#!/usr/bin/env bash

STATE=$1;

case "${STATE}" in
 "on") echo "zend_extension=/usr/local/lib/php/extensions/no-debug-non-zts-20180731/xdebug.so" > /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini; ;;
 "off") rm /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini ;;
esac