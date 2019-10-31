FROM composer:1.9 AS build
COPY --chown=www-data:www-data ./composer.json ./composer.lock ./
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest --no-interaction
COPY --chown=www-data:www-data . .
RUN composer dump-autoload \
  && composer run-script post-root-package-install \
  && composer run-script post-create-project-cmd

FROM php:7.3-apache

ARG TZ=Asia/Tokyo
ENV TZ ${TZ}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

RUN apt-get update && apt-get install -y \
  libzip-dev \
  && docker-php-ext-install \
  bcmath \
  pdo_mysql \
  zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

COPY ./docker/my.ini /usr/local/etc/php/conf.d/

RUN a2enmod rewrite
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

COPY --from=build --chown=www-data:www-data ./app/ .
