FROM dunglas/frankenphp:php8.4.27-trixie

RUN install-php-extensions pdo_mysql

COPY . /app/public

ENV SERVER_NAME=:8080

EXPOSE 8080