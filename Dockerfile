FROM php:8.2-apache

# Install SQLite3 & PostgreSQL PDO extensions
RUN apt-get update && apt-get install -y \
    libsqlite3-dev \
    sqlite3 \
    libpq-dev \
    && docker-php-ext-install pdo pdo_sqlite pdo_pgsql \
    && a2enmod rewrite \
    && rm -rf /var/lib/apt/lists/*

# Configure PHP for large APK uploads and downloads
RUN echo "upload_max_filesize = 128M" > /usr/local/etc/php/conf.d/uploads.ini \
    && echo "post_max_size = 128M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "memory_limit = 256M" >> /usr/local/etc/php/conf.d/uploads.ini \
    && echo "max_execution_time = 300" >> /usr/local/etc/php/conf.d/uploads.ini

# Set working directory
WORKDIR /var/www/html

# Copy application files
COPY . /var/www/html/

# Create data directory and set permissions
RUN mkdir -p /var/www/html/data \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
