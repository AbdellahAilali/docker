FROM php:7.2-fpm

RUN apt-get update \
      && apt-get clean; rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/* /usr/share/doc/*

RUN docker-php-ext-install pdo pdo_mysql

WORKDIR "/var/www/html"
