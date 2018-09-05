#!/usr/bin/env bash

pecl install xdebug

xdebug_path=$(find / -name 'xdebug.so')
echo xdebug_path = ${xdebug_path}

echo -e "zend_extension="${xdebug_path}"" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.remote_enable=on" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.remote_autostart=on" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.remote_host=172.17.0.1" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.remote_port=9000" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.idekey=PHPSTORM" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
echo -e "xdebug.max_nesting_level=1000" >> /etc/php/7.2/fpm/conf.d/docker-php-ext-xdebug.ini
