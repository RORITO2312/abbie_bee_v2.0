FROM dunglas/frankenphp:latest-php8.2

RUN install-php-extensions pdo_mysql mysqli

COPY . /app

EXPOSE 80