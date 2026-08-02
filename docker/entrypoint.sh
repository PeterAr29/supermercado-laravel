#!/bin/sh
set -e

# Arranque del contenedor. Lo que pasa aquí, en este orden:
#   1. nginx aprende en qué puerto escuchar (lo decide la plataforma)
#   2. se comprueba que hay APP_KEY, porque sin ella no arranca nada
#   3. se espera a la base de datos y se migra
#   4. se cachea la configuración y arrancan los procesos

echo "==> Configurando nginx en el puerto ${PORT}"
# sed y no envsubst: envsubst viene en el paquete gettext, que Alpine no trae.
# Una sustitución no justifica una dependencia más en la imagen.
sed "s|\${PORT}|${PORT}|g" /etc/nginx/nginx.conf.template > /etc/nginx/nginx.conf

# Sin APP_KEY, Laravel no puede descifrar la sesión y responde 500 en cada
# petición. Es el fallo número uno al estrenar un despliegue, y el mensaje por
# defecto no dice qué hacer, así que se dice aquí.
if [ -z "${APP_KEY}" ]; then
    echo "!!! Falta APP_KEY."
    echo "!!! Genérala en local con:  php artisan key:generate --show"
    echo "!!! y ponla como variable de entorno del servicio."
    exit 1
fi

# La base de datos suele tardar más que la aplicación en estar lista. Sin esta
# espera, el primer despliegue falla, se reinicia y acaba funcionando: ruido
# que parece un fallo intermitente y no lo es.
#
# Se reintenta la propia migración en vez de hacer ping con un cliente de
# MySQL. Así se prueba la conexión que va a usar Laravel de verdad —driver,
# credenciales y nombre de la base incluidos— y no hace falta meter
# mariadb-client en la imagen solo para esperar.
echo "==> Migrando (se reintenta mientras la base arranca)"
intento=1
until php artisan migrate --force --no-interaction; do
    if [ "${intento}" -ge 20 ]; then
        echo "!!! La base de datos no respondió tras 20 intentos."
        echo "!!! Revisa DB_HOST, DB_PORT, DB_DATABASE, DB_USERNAME y DB_PASSWORD."
        exit 1
    fi
    echo "    intento ${intento}/20 fallido; reintento en 3s"
    intento=$((intento + 1))
    sleep 3
done

# Solo cuando se pide: si ya hay catálogo de verdad, sembrar encima duplica.
if [ "${SEED_AL_ARRANCAR}" = "true" ]; then
    echo "==> Sembrando datos de trabajo (SEED_AL_ARRANCAR=true)"
    php artisan db:seed --force --no-interaction
fi

# Cachear en el arranque y no en la construcción: la configuración depende de
# variables de entorno que solo existen aquí. Cachearla en el build congelaría
# los valores vacíos de entonces.
echo "==> Cacheando configuración, rutas y vistas"
php artisan config:cache
php artisan route:cache
php artisan view:cache

echo "==> Listo. Arrancando nginx y php-fpm"
exec supervisord -c /etc/supervisord.conf
