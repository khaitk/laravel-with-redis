FROM public.ecr.aws/amazonlinux/amazonlinux:2023

ENV TZ=Asia/Tokyo
WORKDIR /var/www/html

RUN dnf update -y && \
    dnf install -y \
    httpd \
    git \
    unzip \
    tar \
    gzip \
    shadow-utils \
    php8.4 \
    php8.4-cli \
    php8.4-fpm \
    php8.4-common \
    php8.4-mysqlnd \
    php8.4-pdo \
    php8.4-mbstring \
    php8.4-xml \
    php8.4-gd \
    php8.4-zip \
    php8.4-bcmath \
    php8.4-intl \
    php8.4-opcache \
    php8.4-pecl-redis6 \
    php8.4-pecl-apcu && \
    dnf clean all && \
    rm -rf /var/cache/dnf

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

RUN mkdir -p /run/php-fpm && \
    chown -R apache:apache /run/php-fpm /var/log/php-fpm /var/lib/php

RUN sed -i 's/;clear_env = no/clear_env = no/g' /etc/php-fpm.d/www.conf

RUN sed -i 's#DocumentRoot "/var/www/html"#DocumentRoot "/var/www/html/public"#' /etc/httpd/conf/httpd.conf && \
    sed -i 's#<Directory "/var/www/html">#<Directory "/var/www/html/public">#' /etc/httpd/conf/httpd.conf && \
    sed -i '/<Directory "\/var\/www\/html\/public">/,/<\/Directory>/ s/AllowOverride None/AllowOverride All/' /etc/httpd/conf/httpd.conf

COPY . /var/www/html

RUN ln -sf /dev/stdout /var/log/httpd/access_log && \
    ln -sf /dev/stderr /var/log/httpd/error_log

RUN chown -R apache:apache /var/www/html

EXPOSE 80

CMD ["sh", "-c", "php-fpm -D && httpd -D FOREGROUND"]