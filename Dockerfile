# Use PHP 8.2 with Apache
FROM php:8.2-apache

# Install PostgreSQL extension for database connection
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy your entire project to the web server
COPY . /var/www/html/

# Set proper permissions
RUN chown -R www-data:www-data /var/www/html/

# Configure Apache to serve index.php and index.html
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/directory-index.conf

# Expose port 80 (web server port)
EXPOSE 80