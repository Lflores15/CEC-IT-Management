FROM php:8.0.3-apache

# Install Required PHP Extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    && docker-php-ext-install mysqli pdo pdo_mysql gd

# Enable Apache mod_rewrite
RUN a2enmod rewrite

# Set Working Directory
WORKDIR /var/www/html

# Copy Everything (src, includes, Forms, etc.)
COPY . /var/www/html/

# Set File Permissions
RUN chown -R www-data:www-data /var/www/html

# Expose Port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]