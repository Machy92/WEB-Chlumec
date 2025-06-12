FROM php:8.1-apache

# Povolení modulu rewrite
RUN a2enmod rewrite

# Nainstaluj PostgreSQL PDO a další závislosti
RUN apt-get update && apt-get install -y \
    libpq-dev \
    && docker-php-ext-install pdo pdo_pgsql pgsql

# Nastavení pracovní složky
WORKDIR /var/www/html

# Zkopírování souborů
COPY . /var/www/html

# Nastavení oprávnění
RUN chown -R www-data:www-data /var/www/html && \
    chmod -R 755 /var/www/html

# Výchozí index.php
RUN echo "DirectoryIndex index.php" >> /etc/apache2/apache2.conf

# Otevření portu
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
