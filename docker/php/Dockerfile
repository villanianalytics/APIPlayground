FROM php:7.2-fpm

RUN docker-php-ext-install pdo_mysql

RUN pecl install apcu

RUN apt-get update && \
apt-get install -y \
zlib1g-dev \ 
wget \ 
vim \ 
git -y

RUN docker-php-ext-install zip
RUN docker-php-ext-enable apcu

RUN curl -s https://getcomposer.org/installer | php
RUN mv composer.phar /usr/local/bin/composer

WORKDIR /usr/src/app

RUN wget https://get.symfony.com/cli/installer -O - | bash

RUN mv /root/.symfony/bin/symfony /usr/local/bin/symfony

RUN git config --global user.email "you@example.com"
RUN git config --global user.name "Your Name"

WORKDIR /usr/src/app/SampleRestApplication