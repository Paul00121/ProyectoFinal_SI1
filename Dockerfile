FROM php:8.2-apache

# Instalar extensiones necesarias para PostgreSQL
RUN docker-php-ext-install pdo pdo_pgsql

# Habilitar mod_rewrite
RUN a2enmod rewrite

# Copiar el proyecto al servidor
COPY . /var/www/html/

# Permisos
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
