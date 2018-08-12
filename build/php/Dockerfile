FROM ubuntu:18.04

ENV TZ=Asia/Novosibirsk
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -my \
  curl \
  wget \
  php-curl \
  php-fpm \
  php-gd \
  php-xsl \
  php-mysqlnd \
  php-cli \
  php-intl \
  php-bz2 \
  php-zip \
  php-mbstring \
  git \
  zip \
  php-apcu \
  php-redis \
  php-opcache \
  php-pear \
  php7.2-dev \
  gcc \
  g++ \
  make

RUN mkdir /run/php
ADD conf/www.conf /etc/php/7.2/fpm/pool.d/www.conf
ADD conf/php-fpm.conf /etc/php/7.2/fpm/php-fpm.conf
RUN php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');"
RUN php composer-setup.php
RUN php -r "unlink('composer-setup.php');"
RUN mv composer.phar /usr/local/bin/composer
RUN chmod +x /usr/local/bin/composer

COPY ext/ssdeep.sh /tmp/ssdeep.sh
RUN chmod +x /tmp/ssdeep.sh
RUN /tmp/ssdeep.sh

RUN mkdir -p /var/www/html/symfony
RUN chown -R www-data:www-data /var/www/html/symfony

WORKDIR /var/www/html/symfony

# uncomment for code in container
# COPY ../symfony .
# RUN COMPOSER_ALLOW_SUPERUSER=1 composer install
# RUN COMPOSER_ALLOW_SUPERUSER=1 composer dump-autoload

EXPOSE 9000

CMD ["php-fpm7.2"]