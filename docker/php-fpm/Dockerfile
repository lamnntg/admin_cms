FROM php:7.4-fpm

ARG user=appuser
ARG group=appuser
ARG uid=1000
ARG gid=1000
RUN groupadd -g ${gid} ${group} && useradd -m -u ${uid} -g ${group} -s /bin/sh ${user}

RUN apt-get update -y
RUN apt-get install -y
RUN apt-get install -y \
    libzip-dev unzip libonig-dev libpng-dev \
    libwebp-dev libjpeg62-turbo-dev libxpm-dev \
    libfreetype6-dev
RUN docker-php-ext-install pdo pdo_mysql zip mbstring

# config and install php gd
RUN docker-php-ext-configure gd \
    --with-jpeg \
    --with-freetype
RUN docker-php-ext-install gd

# install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# install nodejs
RUN apt-get install npm -y \
    && npm cache clean -f \
    && npm install -g n \
    && n 12.18.4 \
    && mkdir /.npm \
    && chown $UID:$GID /.npm -R

USER ${user}
