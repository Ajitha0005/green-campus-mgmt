FROM php:8.1-apache

# Install PDO MySQL extension
RUN docker-php-ext-install pdo pdo_mysql

# Copy project files to the Apache document root
COPY . /var/www/html/

# Set working directory
WORKDIR /var/www/html

# Enable Apache mod_rewrite for better routing (if needed)
RUN a2enmod rewrite

# Give Apache ownership of the files
RUN chown -R www-data:www-data /var/www/html

# Use the PORT environment variable provided by Render
RUN sed -i 's/80/${PORT}/g' /etc/apache2/sites-available/000-default.conf /etc/apache2/ports.conf

EXPOSE 80
