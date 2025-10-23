FROM php:8.3-fpm-alpine
WORKDIR /var/www/html
RUN apk add --no-cache \
    $PHPIZE_DEPS \
    libpng-dev \
    libjpeg-turbo-dev \
    freetype-dev \
    postgresql-dev \
    libzip-dev \
    icu-dev \
    oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) \
        gd \
        pdo \
        pdo_pgsql \
        zip \
        intl \
        opcache \
    && apk del --no-cache \
        $PHPIZE_DEPS \
        libpng-dev \
        libjpeg-turbo-dev \
        freetype-dev \
        postgresql-dev \
        libzip-dev \
        icu-dev \
        oniguruma-dev

RUN apk add --no-cache \
    libpng \
    libjpeg-turbo \
    freetype \
    postgresql-libs \
    libzip \
    icu

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

ARG UID=1000
ARG GID=1000
RUN addgroup -g ${GID} appuser \
&& adduser -u ${UID} -G appuser -h /home/appuser -s /bin/sh -D appuser

COPY ./backend .

RUN chown -R appuser:appuser /var/www/html
RUN chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache
USER appuser
CMD ["php-fpm"]