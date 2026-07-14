FROM php:8.2-apache

# تعطيل أي وحدات MPM قد تكون مفعلة افتراضياً
RUN a2dismod mpm_event mpm_worker mpm_prefork || true

# تفعيل وحدة prefork بشكل صريح
RUN a2enmod mpm_prefork

# تثبيت الإضافات الضرورية
RUN docker-php-ext-install mysqli pdo pdo_mysql

# نسخ الملفات
COPY . /var/www/html/

# ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80

# تشغيل Apache في المقدمة
CMD ["apache2-foreground"]
