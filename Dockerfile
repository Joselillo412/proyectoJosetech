# 1. Usamos la imagen oficial de PHP con Apache preconfigurado
FROM php:8.2-apache

# 2. Instalamos las dependencias del sistema y extensiones necesarias para PostgreSQL y ZIP
RUN apt-get update && apt-get install -y \
    libpq-dev \
    libzip-dev \
    zip \
    unzip \
    git \
    && docker-php-ext-install pdo pdo_pgsql zip

# 3. Activamos el módulo rewrite de Apache (esencial para las rutas de Laravel)
RUN a2enmod rewrite

# 4. Cambiamos la ruta pública de Apache para que apunte a la carpeta /public de Laravel
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e 's!/var/www/html!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/sites-available/*.conf
RUN sed -ri -e 's!/var/www/!${APACHE_DOCUMENT_ROOT}!g' /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf

# 5. Descargamos e instalamos Composer de forma oficial
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# 6. Definimos el directorio de trabajo y copiamos todo el código del proyecto
WORKDIR /var/www/html
COPY . .

# 7. Ejecutamos la instalación de Composer optimizada para producción
RUN composer install --optimize-autoloader --no-dev

# 8. Otorgamos los permisos correctos a las carpetas de almacenamiento y caché de Laravel
RUN chown -R www-data:www-data /var/www/html/storage /var/www/html/bootstrap/cache
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# 9. Exponemos el puerto estándar web
EXPOSE 80