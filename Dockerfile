FROM php:8.4-fpm

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    sqlite3 \
    libsqlite3-dev \
    && docker-php-ext-install pdo_mysql pdo_sqlite mbstring exif pcntl bcmath gd zip

# Install Node.js and npm
RUN curl -fsSL https://deb.nodesource.com/setup_22.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www

# Copy application files
COPY . .

# Create database directory
RUN mkdir -p /var/www/database && \
    touch /var/www/database/database.sqlite

# Install PHP dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# Create .env file and generate app key
RUN cp .env.example .env && \
    php artisan key:generate

# Generate Wayfinder actions
RUN php artisan wayfinder:generate

# Install Node dependencies and build assets
# Skip optional dependencies during install, then install the correct architecture-specific ones
RUN npm ci --omit=optional && \
    npm install --save-optional \
    @rollup/rollup-linux-$(dpkg --print-architecture)-gnu@4.9.5 \
    @tailwindcss/oxide-linux-$(dpkg --print-architecture)-gnu@^4.0.1 \
    lightningcss-linux-$(dpkg --print-architecture)-gnu@^1.29.1 && \
    npm run build

# Set permissions
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Expose port 9000
EXPOSE 9000

CMD ["php-fpm"]