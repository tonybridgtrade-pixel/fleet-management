FROM php:8.2-apache

COPY . /var/www/html/

# تثبيت الامتدادات الضرورية
RUN docker-php-ext-install mysqli pdo pdo_mysql

# إعداد الصلاحيات للمجلد
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
