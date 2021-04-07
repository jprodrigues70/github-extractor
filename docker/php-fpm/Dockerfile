FROM php:7.2-fpm

ARG GITLAB_UID=1000
ARG GITLAB_GID=1000

RUN getent group $GITLAB_GID || groupadd -g $GITLAB_GID docker
RUN useradd --shell /bin/bash -u $GITLAB_UID -m -o -c "" -g $GITLAB_GID gitlab-runner

RUN apt-get update && apt-get install -y default-mysql-client --no-install-recommends \
    && rm /etc/apt/preferences.d/no-debian-php \
    && apt-get update -yqq \
    && apt-get install -y --force-yes --no-install-recommends \
    unzip \
    libz-dev \
    libpq-dev \
    libjpeg-dev \
    libpng-dev \
    libfreetype6-dev \
    libssl-dev \
    libmcrypt-dev \
    openssh-server \
    libmagickwand-dev \
    git \
    libxml2-dev \
    ghostscript

RUN docker-php-ext-install pdo_mysql soap

# install the PHP zip library
RUN docker-php-ext-install zip

# Install for image manipulation
RUN docker-php-ext-install exif

# Install the PHP Imagick library
RUN pecl install imagick && \
    docker-php-ext-enable imagick

# Install the PHP gd library
# --enable-gd-native-ttf \
RUN docker-php-ext-install gd && \
    docker-php-ext-configure gd \
    --with-jpeg-dir=/usr/lib \
    --with-freetype-dir=/usr/include/freetype2 && \
    docker-php-ext-install gd

# PHP conf
ADD ./docker/php-fpm/php.ini /usr/local/etc/php/conf.d

# Install node and npm
RUN curl -sL https://deb.nodesource.com/setup_10.x | bash -
RUN apt-get install -y nodejs

# Install composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
