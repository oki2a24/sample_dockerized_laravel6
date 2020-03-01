FROM php:7.4.3-apache AS shared
ARG TZ=Asia/Tokyo
ENV TZ ${TZ}
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone
RUN apt-get update && apt-get install -y \
  libpq-dev \
  libzip-dev \
  unzip \
  && docker-php-ext-install \
  bcmath \
  pdo_mysql \
  pdo_pgsql \
  zip \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*
COPY ./docker/my.ini /usr/local/etc/php/conf.d/
RUN a2enmod rewrite
COPY ./docker/000-default.conf /etc/apache2/sites-available/000-default.conf
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf
ENV APP_ENV laravel

FROM composer:1.9.3 AS composer
ENV APP_ENV laravel

FROM shared AS develop
COPY --from=composer /usr/bin/composer /usr/bin/composer
ENV COMPOSER_ALLOW_SUPERUSER=1
RUN curl -sL https://deb.nodesource.com/setup_12.x | bash - \
  && apt-get install -y nodejs \
  && apt-get clean \
  && rm -rf /var/lib/apt/lists/*

FROM composer AS build_composer
COPY --chown=www-data:www-data ./composer.json ./composer.lock ./
RUN composer install --prefer-dist --no-dev --no-autoloader --no-scripts --no-progress --no-suggest --no-interaction
COPY --chown=www-data:www-data . .
RUN composer dump-autoload \
  && composer run-script post-root-package-install \
  && composer run-script post-create-project-cmd

FROM node:12.16.1 AS build_npm
WORKDIR /app
COPY ./package.json ./package-lock.json ./
RUN npm install
COPY . .
RUN npm run production

FROM shared AS production
COPY --from=build_composer --chown=www-data:www-data ./app/ .
RUN touch ./database/database.sqlite
COPY --from=build_npm --chown=www-data:www-data ./app/public/css ./public/css
COPY --from=build_npm --chown=www-data:www-data ./app/public/js ./public/js
COPY --from=build_npm --chown=www-data:www-data ./app/public/mix-manifest.json ./public/mix-manifest.json
