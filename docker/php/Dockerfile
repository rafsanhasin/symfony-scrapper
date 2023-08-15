FROM php:8.2-rc-fpm

RUN apt-get update

RUN apt-get install -y nano curl libzip-dev zip libpq-dev librabbitmq-dev gnupg2 supervisor

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

RUN docker-php-ext-install pdo_mysql
RUN docker-php-ext-install zip
RUN docker-php-ext-install bcmath

RUN pecl install amqp \
&& docker-php-ext-enable amqp

RUN pecl install -o -f redis \
&&  rm -rf /tmp/pear \
&&  docker-php-ext-enable redis

RUN mkdir -p /var/logs/nordstreet-test

COPY ./supervisord.conf /etc/supervisor

RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm &' >> /start.sh && \
    echo '/usr/bin/supervisord -n' >> /start.sh && \
    chmod +x /start.sh

# Start the shell script as the main command
CMD ["/start.sh"]