# Use the official PHP Apache image
FROM php:7.4-apache

# Install the PostgreSQL development libraries and PHP extension for PostgreSQL
RUN apt-get update && apt-get install -y libpq-dev \
    && docker-php-ext-install pgsql pdo_pgsql

# Enable Apache mod_rewrite (if needed for URL rewriting)
RUN a2enmod rewrite

# Copy your project files into the container
COPY . /var/www/html/
