FROM php:7.4.22-fpm
MAINTAINER Drienerlo Engineering Solutions <hallo@drenso.nl>

# Update all dependencies
RUN apt-get update && apt-get upgrade -y

# Set timezone to UTC
RUN printf "date.timezone = \"UTC\"\n" >> `php -i | \
    grep php.ini | awk '{print $6'}`/php.ini

# Disable asserts
RUN printf "zend.assertions = -1\n" >> `php -i | grep php.ini | awk '{print $6'}`/php.ini

# ZIP extension
RUN apt-get install -y unzip zlib1g-dev libpcre3-dev libzip-dev && \
    docker-php-ext-install zip

# Install wget (for composer installation)
RUN apt-get install wget -y

# Install composer globally
COPY ./docker/argus/install-composer.sh .
RUN sh install-composer.sh && rm install-composer.sh

# MySQL extension
RUN docker-php-ext-install pdo_mysql

# Intl extenstion
RUN apt-get install libicu-dev -y && docker-php-ext-install intl

# Sockets extenstion
RUN docker-php-ext-install sockets

# Enable APCU
RUN pecl install apcu && docker-php-ext-enable apcu

# Set the default working directory
WORKDIR /usr/src/app

# Install wait-for script
RUN apt-get install netcat -y \
  && curl -s https://raw.githubusercontent.com/eficode/wait-for/master/wait-for -o wait-for \
  && chmod +x wait-for

# Install application
COPY . .

# Install php vendors
RUN cp .secrets.json.dist .secrets.json \
  && composer install -o --apcu-autoloader --no-dev --no-scripts \
  && composer install -o --apcu-autoloader --no-dev \
  && rm .secrets.json

# Clean stuff up and set some permissions
RUN mv docker/argus/docker-entry.sh . \
  && rm -rf docker \
  && mkdir -p var \
  && chmod o+r -R public \
  && find public -type d -exec chmod o+x {} \+ \
  && chown -R www-data . \
  && chmod u+rwx var

# Set entry point
ENTRYPOINT ["./docker-entry.sh"]
