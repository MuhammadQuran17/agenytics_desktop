# https://docs.docker.com/guides/frameworks/laravel/production-setup/

# ------------------------------------------    
# Stage 1: Image Builder environment
# ------------------------------------------
    FROM php:8.4-fpm-alpine AS builder

    # Install system dependencies and PHP extensions for Laravel with MySQL/PostgreSQL support.
    # Dependencies in this stage are only required for building the final image.
    RUN apk add --no-cache \
        curl \
        unzip \
        postgresql-dev \
        oniguruma-dev \
        openssl-dev \
        libxml2-dev \
        curl-dev \
        icu-dev \
        libzip-dev \
        autoconf \
        g++ \
        make \
        linux-headers \
        && docker-php-ext-install -j$(nproc) \
        pdo_mysql \
        pdo_pgsql \
        pgsql \
        opcache \
        intl \
        zip \
        bcmath \
        soap \
        && pecl install redis \
        && docker-php-ext-enable redis \
        && apk del autoconf g++ make linux-headers
    
    # ---------------------------------------------------
    # Install Node.js 24 (LTS)
    # ---------------------------------------------------
    RUN apk add --no-cache nodejs npm

    # Set the working directory inside the container
    WORKDIR /var/www
    
    # Copy the entire Laravel application code into the container
    # -----------------------------------------------------------
    # In Laravel, `composer install` may trigger scripts
    # needing access to application code.
    # For example, the `post-autoload-dump` event might execute
    # Artisan commands like `php artisan package:discover`. If the
    # application code (including the `artisan` file) is not
    # present, these commands will fail, leading to build errors.
    #
    # By copying the entire application code before running
    # `composer install`, we ensure that all necessary files are
    # available, allowing these scripts to run successfully.
    # In other cases, it would be possible to copy composer files
    # first, to leverage Docker's layer caching mechanism.
    # -----------------------------------------------------------
    COPY . /var/www
    
    # Install Composer and dependencies
    RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer \
        && composer install --no-dev --optimize-autoloader --no-interaction --no-progress --prefer-dist
    
    # Install Node.js dependencies and build assets
    RUN npm install && npm run build
    
    # ------------------------------------------    
    # Stage 2: base-php environment
    # ------------------------------------------
    FROM php:8.4-fpm-alpine AS base-php
    
    # Install only runtime libraries needed in production
    RUN apk add --no-cache \
        postgresql-libs \
        icu-libs \
        libzip \
        curl \
        fcgi \
        procps
    
    # Download and install php-fpm health check script
    RUN curl -o /usr/local/bin/php-fpm-healthcheck \
        https://raw.githubusercontent.com/renatomefi/php-fpm-healthcheck/master/php-fpm-healthcheck \
        && chmod +x /usr/local/bin/php-fpm-healthcheck
    
    # Copy the initial storage structure
    COPY ./storage /var/www/storage-init
    
    # Copy PHP extensions and libraries from the builder stage
    COPY --from=builder /usr/local/lib/php/extensions/ /usr/local/lib/php/extensions/
    COPY --from=builder /usr/local/etc/php/conf.d/ /usr/local/etc/php/conf.d/
    COPY --from=builder /usr/local/bin/docker-php-ext-* /usr/local/bin/
    
    # Use the recommended production PHP configuration
    # -----------------------------------------------------------
    # PHP provides development and production configurations.
    # Here, we replace the default php.ini with the production
    # version to apply settings optimized for performance and
    # security in a live environment.
    # -----------------------------------------------------------
    RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"
    
    # Enable PHP-FPM status page by modifying zz-docker.conf with sed
    RUN sed -i '/\[www\]/a pm.status_path = /status' /usr/local/etc/php-fpm.d/zz-docker.conf
    # Update the variables_order to include E (for ENV)
    RUN sed -i 's/variables_order = "GPCS"/variables_order = "EGPCS"/' "$PHP_INI_DIR/php.ini"
    
    # Copy the application code, assets and dependencies from the build stage
    COPY --from=builder /var/www /var/www
    COPY --from=builder /var/www/public /var/www/public
    
    # Set working directory
    WORKDIR /var/www
    
    # Copy the initialization script
    COPY ./docker_specific/php/entrypoint.sh /usr/local/bin/entrypoint.sh
    RUN chmod +x /usr/local/bin/entrypoint.sh
    
    # Ensure correct permissions and create necessary storage directories
    RUN mkdir -p /var/www/storage \
        && chown -R www-data:www-data /var/www
    
    # ------------------------------------------    
    #  fpm environment
    # ------------------------------------------
    FROM base-php AS fpm

    # Switch to the non-privileged user to run the application
    USER www-data
    
    COPY --from=base-php /usr/local/bin/entrypoint.sh /usr/local/bin/entrypoint.sh
    ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
    
    # Expose port 9000 and start php-fpm server
    EXPOSE 9000
    CMD ["php-fpm"]
    
    # ------------------------------------------    
    #  worker environment
    # ------------------------------------------
    FROM base-php AS worker

    # Switch to the non-privileged user to run the application
    USER www-data
    
    COPY --from=base-php /usr/local/bin/entrypoint.sh /usr/local/bin/entrypoint.sh
    ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
    
    # nice will gurantee that queue will give CPU priority to FPM or DB on the high load requests
    # It is important to set up --max-jobs (The number of jobs to process before stopping ) and --max-time . Because it will be restarted and freshed cache. Is is not good to run very long, because it can cause optimization issue, cache maximality, CPU load. 3 hours or 2000 jobs for now
    CMD ["/bin/sh", "-c", "nice -n 10 php /var/www/artisan queue:work --verbose --timeout=300 --rest=1 --memory=256 --max-jobs=2000 --max-time=10800"]

    # ------------------------------------------    
    #  migrator environment
    # ------------------------------------------
    FROM base-php AS migrator
    
    RUN mkdir -p /usr/local/bin/migrator

    # COPY the script into the image
    COPY ./docker_specific/migrator /usr/local/bin/migrator

    # Make it executable
    RUN chmod +x /usr/local/bin/migrator/run-migrator.sh

    # Switch to the non-privileged user to run the application
    USER www-data
