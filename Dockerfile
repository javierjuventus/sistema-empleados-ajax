FROM dunglas/frankenphp:php8.4.27-trixie

RUN install-php-extensions pdo_mysql

ENV SERVER_NAME=:8080
ENV DOCUMENT_ROOT=/app

EXPOSE 8080