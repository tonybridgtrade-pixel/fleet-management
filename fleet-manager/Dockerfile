# استخدام نسخة PHP CLI خفيفة بدلاً من Apache
FROM php:8.2-cli

# تثبيت الإضافات المطلوبة
RUN docker-php-ext-install mysqli pdo pdo_mysql

# تحديد مكان الملفات
WORKDIR /var/www/html
COPY . .

# تشغيل سيرفر PHP المدمج بدل Apache
CMD ["php", "-S", "0.0.0.0:80"]
