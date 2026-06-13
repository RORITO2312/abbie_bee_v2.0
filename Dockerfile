FROM php:8.2-cli

RUN docker-php-ext-install pdo pdo_mysql mysqli

RUN echo "default_charset = UTF-8" >> /usr/local/etc/php/php.ini && \
    echo "mbstring.internal_encoding = UTF-8" >> /usr/local/etc/php/php.ini && \
    echo "mysql.default_charset = utf8mb4" >> /usr/local/etc/php/php.ini

WORKDIR /app
COPY . /app/

EXPOSE 8080

CMD ["php", "-S", "0.0.0.0:8080", "-t", "/app"]