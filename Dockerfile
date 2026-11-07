FROM php:8.2-apache

# Set working directory
WORKDIR /var/www/html

# Install system dependencies for Composer (zip, unzip) and Composer itself
RUN apt-get update \
    && apt-get install -y unzip git \
    && rm -rf /var/lib/apt/lists/*

# Install Composer globally
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

# Copy only composer files first for caching layers
COPY composer.json .

# Install PHP dependencies (none now, but keep step for future)
RUN composer install --no-dev --optimize-autoloader

# Copy the rest of the application source code
COPY . .
# Rename capitalized index file for Linux case‑sensitivity
RUN mv /var/www/html/Index.php /var/www/html/index.php

# Expose port 80 (default for Apache)
EXPOSE 80

# Use the default Apache command
CMD ["apache2-foreground"]

