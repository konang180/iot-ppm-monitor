# Use the official PHP image from Docker Hub
FROM php:8.1-apache

# Enable mod_rewrite for clean URLs
RUN a2enmod rewrite

# Copy the current directory content to the Apache server
COPY . /var/www/html/

# Expose port 80 (default for Apache)
EXPOSE 80
