FROM php:7-fpm

VOLUME /var/www

# install the PHP extensions we need
#RUN apt-get update && apt-get install -y libpng12-dev libjpeg-dev libpq-dev \
#   && rm -rf /var/lib/apt/lists/* \
#   && docker-php-ext-configure gd --with-png-dir=/usr --with-jpeg-dir=/usr \
#   && docker-php-ext-install gd mbstring pdo pdo_mysql pdo_pgsql

#RUN apt-get update && apt-get install -y \
#        libfreetype6-dev \
#        libjpeg62-turbo-dev \
#        libmcrypt-dev \
#        libpng12-dev \
#        mysql-client \
#        git \
#        zip \
#        unzip \
#        nano \
#        vim \
#    && docker-php-ext-install -j$(nproc) iconv mcrypt \
#    && docker-php-ext-configure gd --with-freetype-dir=/usr/include/ --with-jpeg-dir=/usr/include/ \
#    && docker-php-ext-install -j$(nproc) gd

#RUN docker-php-ext-install pdo mysql pdo_mysql mysqli
RUN docker-php-ext-install pdo pdo_mysql

RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/bin/ --filename=composer

#RUN apt-get update && apt-get install -y libmemcached-dev \
#    && pecl install memcached \
#    && docker-php-ext-enable memcached

RUN echo "date.timezone = 'Europe/Paris'" >> /usr/local/etc/php/conf.d/docker-php-weka-php.ini \
 && echo "memory_limit = '2G'" >> /usr/local/etc/php/conf.d/docker-php-weka-php.ini \
 && echo "log_errors = 'On'" >> /usr/local/etc/php/conf.d/docker-php-weka-php.ini \
 && echo "error_reporting = 'E_ALL'" >> /usr/local/etc/php/conf.d/docker-php-weka-php.ini \
 && echo "max_execution_time = '3600'" >> /usr/local/etc/php/conf.d/docker-php-weka-php.ini
 