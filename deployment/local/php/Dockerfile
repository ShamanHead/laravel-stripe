FROM php:8.2-fpm

#https://github.com/mlocati/docker-php-extension-installer
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN chmod +x /usr/local/bin/install-php-extensions && \
    install-php-extensions openssl mbstring pdo pdo_mysql redis xdebug-3.3.0alpha3

COPY ./xdebug.ini /usr/local/etc/php/conf.d/
