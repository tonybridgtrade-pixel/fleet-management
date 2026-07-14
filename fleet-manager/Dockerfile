FROM php:8.2-apache

# تعطيل الـ MPM الافتراضي وتفعيل mpm_prefork لحل التعارض
RUN a2dismod mpm_event && a2enmod mpm_prefork

COPY . /var/www/html/

RUN docker-php-ext-install mysqli pdo pdo_mysql

EXPOSE 80
