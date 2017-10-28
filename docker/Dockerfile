FROM ubuntu:xenial

MAINTAINER Ivan Podorozhny <ivan@podorozhny.ru>

ENV LAST_UPDATED "2017-10-28 03:00"
ENV DEBIAN_FRONTED=noninteractive

RUN apt-get update -q \
    && apt-get install --no-install-recommends --no-install-suggests -y \
        ca-certificates \
        curl \
        nano \
        git-core \
        build-essential \
        net-tools \
        iputils-ping \
        htop \
        wget \
        apt-transport-https \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN update-ca-certificates

RUN echo "Europe/Moscow" > /etc/timezone \
    && echo "LANG=en_US.UTF-8" >> /etc/default/locale

RUN echo "deb http://ppa.launchpad.net/ondrej/php/ubuntu xenial main" > /etc/apt/sources.list.d/ondrej-ubuntu-php-xenial.list \
    && apt-key adv --keyserver keyserver.ubuntu.com --recv-keys E5267A6C \
    && apt-get update -q \
    && apt-get install --no-install-recommends --no-install-suggests -y \
        php7.1-apcu \
        php7.1-imagick \
        php7.1-bcmath \
        php7.1-cgi \
        php7.1-cli \
        php7.1-common \
        php7.1-curl \
        php7.1-gd \
        php7.1-intl \
        php7.1-json \
        php7.1-mbstring \
        php7.1-mcrypt \
        php7.1-mysql \
        php7.1-xdebug \
        php7.1-xml \
        php7.1-zip \
    && apt-get clean && rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

RUN wget -O /tmp/installer.php https://getcomposer.org/installer \
    && php -r " \
        \$signature = '544e09ee996cdf60ece3804abc52599c22b1f40f4323403c44d44fdfdd586475ca9813a858088ffbc1f233e9b180f061'; \
        \$hash = hash('SHA384', file_get_contents('/tmp/installer.php')); \
        if (!hash_equals(\$signature, \$hash)) { \
            unlink('/tmp/installer.php'); \
            echo 'Integrity check failed, installer is either corrupt or worse.' . PHP_EOL; \
            exit(1); \
        }" \
    && php /tmp/installer.php --no-ansi --install-dir=/usr/bin --filename=composer \
    && rm /tmp/installer.php \
    && composer --ansi --version --no-interaction

#COPY ./docker/php /etc/php/7.1/

COPY ./composer.json ./composer.lock /app/

RUN cd /app && composer install \
    --no-ansi \
    --no-interaction \
    --no-progress \
    --no-plugins \
    --no-suggest \
    --no-scripts \
    --optimize-autoloader

COPY ./ /app/

WORKDIR /app/

CMD ["php", "/app/main.php"]
