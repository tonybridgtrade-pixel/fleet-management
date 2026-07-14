FROM php:8.2-apache

# 1. إيقاف أي وحدة MPM افتراضية لمنع أي تعارض
RUN a2dismod mpm_event mpm_worker mpm_prefork || true

# 2. تفعيل وحدة prefork فقط، وهي اللي بتحبها ملفات PHP
RUN a2enmod mpm_prefork

# 3. إعداد خادم Apache عشان يشتغل بالـ Port اللي بيحدده Railway
RUN sed -i 's/80/80/g' /etc/apache2/ports.conf /etc/apache2/sites-available/000-default.conf

# 4. تثبيت الإضافات المطلوبة لقاعدة البيانات
RUN docker-php-ext-install mysqli pdo pdo_mysql

# 5. نسخ ملفات المشروع
COPY . /var/www/html/

# 6. ضبط الصلاحيات للملفات
RUN chown -R www-data:www-data /var/www/html

# 7. الأمر النهائي للتشغيل
CMD ["apache2-foreground"]
