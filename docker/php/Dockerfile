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

RUN mkdir -p /var/logs/xm-test

# Install Google Chrome:
RUN curl -sS -o - https://dl-ssl.google.com/linux/linux_signing_key.pub | apt-key add && \
    echo "deb https://dl.google.com/linux/chrome/deb/ stable main" >> /etc/apt/sources.list.d/google-chrome.list && \
    apt-get -y update && \
    apt-get -y install google-chrome-stable

# Install ChromeDriver:
RUN apt-get update && \
    apt-get install -y unzip && \
    wget https://edgedl.me.gvt1.com/edgedl/chrome/chrome-for-testing/115.0.5790.170/linux64/chromedriver-linux64.zip && \
    unzip chromedriver-linux64.zip && \
    rm chromedriver-linux64.zip && \
    chmod +x chromedriver-linux64/chromedriver && \
    mv -f chromedriver-linux64/chromedriver /usr/local/bin/chromedriver

# Cleanup:
RUN apt-get -y autoremove && \
    apt-get clean && \
    rm -rf /var/lib/apt/lists/* /tmp/* /var/tmp/*

COPY ./supervisord.conf /etc/supervisor

RUN echo '#!/bin/sh' > /start.sh && \
    echo 'php-fpm &' >> /start.sh && \
    echo '/usr/bin/supervisord -n' >> /start.sh && \
    chmod +x /start.sh

# Start the shell script as the main command
CMD ["/start.sh"]