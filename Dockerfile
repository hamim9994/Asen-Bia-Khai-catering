FROM php:8.2-apache

# Install PostgreSQL extension
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pdo_pgsql

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Copy your project files
COPY . /var/www/html/

# Set permissions
RUN chown -R www-data:www-data /var/www/html/

# Configure Apache to serve index.php and index.html
RUN echo "DirectoryIndex index.php index.html" > /etc/apache2/conf-available/directory-index.conf

# Create .htaccess for better URL handling
RUN echo "Options -Indexes\nRewriteEngine On\nRewriteRule ^$ index.php [L]" > /var/www/html/.htaccess

# Expose port 80
EXPOSE 80