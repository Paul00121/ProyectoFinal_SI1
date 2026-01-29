FROM php:8.2-apache

# 1. Instalar dependencias del sistema (ESTO ES LO QUE FALTA)
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql

# 2. Activar mod_rewrite
RUN a2enmod rewrite

# 3. Copiar el proyecto
COPY . /var/www/html/

# 4. Permisos
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

EXPOSE 80
