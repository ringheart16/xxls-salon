FROM php:8.3-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libpq-dev \
    && docker-php-ext-install pdo_mysql pdo_pgsql \
    && rm -rf /var/lib/apt/lists/*

RUN a2enmod headers \
    && echo "ServerName localhost" > /etc/apache2/conf-available/servername.conf \
    && a2enconf servername \
    && printf '%s\n' \
        '<FilesMatch "\.(sql|md|sh|log|ini|env)$">' \
        '    Require all denied' \
        '</FilesMatch>' \
        '<Directory /var/www/html>' \
        '    Options -Indexes +FollowSymLinks' \
        '    AllowOverride None' \
        '    Require all granted' \
        '</Directory>' \
        > /etc/apache2/conf-available/xxls.conf \
    && a2enconf xxls

RUN printf '%s\n' \
    'expose_php = Off' \
    'display_errors = Off' \
    'log_errors = On' \
    'error_log = /dev/stderr' \
    'session.save_path = "/tmp"' \
    'session.cookie_httponly = 1' \
    'upload_max_filesize = 10M' \
    'post_max_size = 12M' \
    > /usr/local/etc/php/conf.d/zz-xxls.ini

WORKDIR /var/www/html
COPY . /var/www/html/

COPY docker-entrypoint.sh /usr/local/bin/xxls-entrypoint
RUN chmod +x /usr/local/bin/xxls-entrypoint

EXPOSE 10000

ENTRYPOINT ["xxls-entrypoint"]
CMD ["apache2-foreground"]
