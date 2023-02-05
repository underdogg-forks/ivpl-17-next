FROM php:8.1-fpm-alpine

# Set environment variables
ARG TZ=UTC
ENV TZ ${TZ}

#COPY --from=mlocati/php-extension-installer /usr/bin/install-php-extensions /usr/local/bin/
ADD https://github.com/mlocati/docker-php-extension-installer/releases/latest/download/install-php-extensions /usr/local/bin/

RUN apk add --no-cache --virtual .build-deps $PHPIZE_DEPS \
    && pecl install uploadprogress \
    && docker-php-ext-enable uploadprogress \
    && apk del .build-deps $PHPIZE_DEPS \
    && chmod uga+x /usr/local/bin/install-php-extensions && sync \
    && install-php-extensions \
    bcmath \
    curl \
    exif \
    gd \
    iconv \
    imagick \
    imap \
    intl \
    mbstring \
    mcrypt \
    mysqli \
    opcache \
    openssl \
    pdo \
    pdo_mysql \
    redis \
    soap \
    xsl \
    zip \
    &&  echo -e "\n opcache.enable=1 \n opcache.enable_cli=1 \n opcache.memory_consumption=128 \n opcache.interned_strings_buffer=8 \n opcache.max_accelerated_files=4000 \n opcache.revalidate_freq=60 \n opcache.fast_shutdown=1" >> /usr/local/etc/php/conf.d/docker-php-ext-opcache.ini \
    &&  echo -e "\n xdebug.remote_enable=1 \n xdebug.remote_host=localhost \n xdebug.remote_port=9000" >> /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini \
    &&  echo -e "\n xhprof.output_dir='/var/tmp/xhprof'" >> /usr/local/etc/php/conf.d/docker-php-ext-xhprof.ini \
    && cd ~ \
    # Install composer
    && php -r "copy('https://getcomposer.org/installer', 'composer-setup.php');" \
    && php -r "copy('https://composer.github.io/installer.sig', 'signature');" \
    && php -r "if (hash_file('SHA384', 'composer-setup.php') === trim(file_get_contents('signature'))) { echo 'Installer verified'; } else { echo 'Installer corrupt'; unlink('composer-setup.php'); } echo PHP_EOL;" \
    && php composer-setup.php --install-dir=/usr/local/bin --filename=composer \
    && php -r "unlink('composer-setup.php');"

# Set the timezone
RUN ln -snf /usr/share/zoneinfo/$TZ /etc/localtime && echo $TZ > /etc/timezone

# Comment out xdebug extension line per default
#RUN sed -i 's/^zend_extension=/;zend_extension=/g' /usr/local/etc/php/conf.d/docker-php-ext-xdebug.ini

# Copy xdebug configuration for remote debugging
#COPY ./xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini

#
#--------------------------------------------------------------------------
# Final Touches
#--------------------------------------------------------------------------
#

# Copy the php-fpm config
COPY ./fpm.conf /usr/local/etc/php-fpm.d/
COPY ./php-dev.ini /usr/local/etc/php/conf.d/

USER root

# Cleanup all downloaded packages
#RUN apt-get -y autoclean && \
#    apt-get -y autoremove && \
#    apt-get -y clean && \
#    rm -rf /var/lib/apt/lists/* && \
#    rm /var/log/lastlog /var/log/faillog

# Configure non-root user.
ARG PUID=1000
ENV PUID ${PUID}
ARG PGID=1000
ENV PGID ${PGID}

# modify www-data user to have id 1000
RUN apk add \
    --no-cache \
    --repository http://dl-3.alpinelinux.org/alpine/edge/community/ --allow-untrusted \
    --virtual .shadow-deps \
    shadow \
    && usermod -u ${PUID} www-data \
    && groupmod -g ${PGID} www-data \
    && apk del .shadow-deps

WORKDIR /var/www/projects/invoiceplane

CMD ["php-fpm"]
