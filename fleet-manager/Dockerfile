FROM php:8.2-apache

# 1. إزالة أي MPM مثبت حالياً
RUN rm -f /etc/apache2/mods-enabled/mpm_*.load

# 2. تفعيل وحدة prefork فقط (وهي الأكثر توافقاً مع PHP)
RUN a2enmod mpm_prefork

# 3. تثبيت الامتدادات الضرورية لـ PHP
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 4. نسخ ملفات المشروع
COPY . /var/www/html/

# 5. ضبط الصلاحيات
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
