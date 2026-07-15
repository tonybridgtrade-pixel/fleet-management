FROM php:8.2-cli
RUN docker-php-ext-install mysqli pdo pdo_mysql

# انسخ محتويات المجلد إلى المسار الأساسي للسيرفر
COPY fleet-manager
/ /var/www/html/

WORKDIR /var/www/html
CMD ["php", "-S", "0.0.0.0:80"]
