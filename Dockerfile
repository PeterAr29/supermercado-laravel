# Imagen de despliegue — nginx + php-fpm en un solo contenedor.
#
# Se usa un Dockerfile y no la detección automática de Railway (Nixpacks)
# porque así lo que se despliega es exactamente lo que se puede construir y
# probar en local: `docker build` + `docker run` aquí dan el mismo contenedor
# que allí. La detección automática es cómoda hasta que falla, y entonces se
# depura a ciegas contra un builder remoto.

# ---------- 1. Assets ----------
# Node solo hace falta para compilar el bundle; no viaja a la imagen final.
FROM node:20-alpine AS assets

WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY resources ./resources
COPY vite.config.js tailwind.config.js postcss.config.js ./
RUN npm run build

# ---------- 2. Dependencias de PHP ----------
FROM composer:2 AS vendor

WORKDIR /app
COPY composer.json composer.lock ./
# Sin scripts: 'artisan package:discover' necesita el código, que aún no está.
# --no-dev deja fuera Pint, PHPUnit y Breeze, que no pintan nada en producción.
RUN composer install \
        --no-dev \
        --prefer-dist \
        --no-scripts \
        --no-autoloader \
        --no-interaction

# El código entra después de instalar para que la capa cara —descargar las
# dependencias— solo se rehaga cuando cambie composer.lock, no en cada commit.
COPY . .

# El autoloader se genera aquí y no en la imagen final: así allí no hace falta
# composer, que no pinta nada en tiempo de ejecución.
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# ---------- 3. Imagen final ----------
FROM php:8.2-fpm-alpine

RUN apk add --no-cache nginx supervisor \
    && docker-php-ext-install pdo_mysql opcache bcmath \
    && rm -rf /var/cache/apk/*

# OPcache: sin esto cada petición recompila todo el framework.
RUN { \
        echo 'opcache.enable=1'; \
        echo 'opcache.memory_consumption=128'; \
        echo 'opcache.max_accelerated_files=10000'; \
        echo 'opcache.validate_timestamps=0'; \
    } > /usr/local/etc/php/conf.d/opcache.ini

# El contenedor es efímero: los logs van a la salida estándar, que es donde
# la plataforma los recoge. Escribirlos en storage/logs sería tirarlos.
RUN { \
        echo 'display_errors=Off'; \
        echo 'log_errors=On'; \
        echo 'error_log=/dev/stderr'; \
        echo 'upload_max_filesize=16M'; \
        echo 'post_max_size=16M'; \
    } > /usr/local/etc/php/conf.d/app.ini

WORKDIR /var/www/html

COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R 775 storage bootstrap/cache

COPY docker/nginx.conf.template /etc/nginx/nginx.conf.template
COPY docker/supervisord.conf /etc/supervisord.conf
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

# Railway inyecta $PORT en tiempo de ejecución y puede cambiarlo entre
# despliegues; por eso nginx se configura en el arranque, no aquí.
ENV PORT=8080
EXPOSE 8080

ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]
