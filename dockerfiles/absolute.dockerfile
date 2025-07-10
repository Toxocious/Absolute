# --------------------------
# ----    Build Stage   ----
# --------------------------
FROM php:8.0-fpm AS builder

# Install build-time dependencies for PHP extensions
RUN apt-get update && apt-get install -y \
    libpng-dev \
    libwebp-dev \
    libjpeg62-turbo-dev \
    libxpm-dev \
    libfreetype6-dev \
    && rm -rf /var/lib/apt/lists/*

# Configure, install, and enable PHP extensions
# Using -j$(nproc) can speed up compilation on multi-core systems
RUN docker-php-ext-configure gd --with-jpeg --with-freetype \
    && docker-php-ext-install -j$(nproc) pdo pdo_mysql gd opcache \
    && pecl install xdebug-3.2.0 \
    && docker-php-ext-enable xdebug

# --------------------------
# ---- Production Stage ----
# --------------------------
FROM php:8.0-fpm

# Install only the necessary runtime libraries, not the -dev packages
RUN apt-get update && apt-get install -y --no-install-recommends \
    libpng16-16 \
    libwebp6 \
    libjpeg62-turbo \
    libxpm4 \
    libfreetype6 \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Copy the compiled extensions and their config files from the builder stage
COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
