FROM php:8.2-apache

# Install system dependencies and PHP extensions required by Acadtrack
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    && docker-php-ext-install pdo_mysql mbstring zip gd \
    && a2enmod rewrite headers \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Apache VirtualHost & Directory configuration allowing .htaccess rewrites
RUN echo '<Directory /var/www/html>\n\
    Options -Indexes +FollowSymLinks\n\
    AllowOverride All\n\
    Require all granted\n\
</Directory>' > /etc/apache2/conf-available/acadtrack.conf \
    && a2enconf acadtrack

WORKDIR /var/www/html

EXPOSE 80

CMD ["apache2-foreground"]
