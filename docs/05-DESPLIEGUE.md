# 05 — Despliegue

El proyecto se despliega como **imagen Docker**, no con la detección automática de la plataforma. Así lo que se publica es exactamente lo que se puede construir y probar en local: `docker build` da el mismo contenedor que da Railway.

| Fichero | Qué hace |
|---|---|
| `Dockerfile` | Tres etapas: assets con Node, dependencias con Composer, imagen final con nginx + php-fpm |
| `.dockerignore` | Qué **no** entra. La línea que más importa es `.env` |
| `docker/entrypoint.sh` | Puerto, comprobación de `APP_KEY`, migración con reintentos y cacheo |
| `docker/nginx.conf.template` | Sirve `public/`, bloquea ficheros ocultos |
| `docker/supervisord.conf` | nginx y php-fpm bajo un solo PID 1 |
| `railway.json` | Le dice a Railway que use el Dockerfile, con health check en `/` |

## Probarlo en local antes de publicar

Esto es lo que se hizo antes del primer despliegue, y merece repetirse ante cualquier cambio del `Dockerfile`:

```bash
docker network create super-test
docker run -d --name super-db --network super-test \
  -e MYSQL_ROOT_PASSWORD=raiz -e MYSQL_DATABASE=laravel mysql:8

docker build -t supermercado:test .

docker run -d --name super-app --network super-test -p 8399:8080 \
  -e PORT=8080 -e APP_KEY="$(php artisan key:generate --show)" \
  -e APP_ENV=production -e APP_DEBUG=false \
  -e DB_HOST=super-db -e DB_DATABASE=laravel \
  -e DB_USERNAME=root -e DB_PASSWORD=raiz \
  -e SEED_AL_ARRANCAR=true -e LOG_CHANNEL=stderr \
  supermercado:test

curl -s http://localhost:8399/productos | grep Mostrando
```

Y al terminar: `docker rm -f super-app super-db && docker network rm super-test`.

## Variables de entorno del servicio

| Variable | Valor | Por qué |
|---|---|---|
| `APP_KEY` | `base64:…` | **Obligatoria.** Sin ella el contenedor se niega a arrancar. Se genera con `php artisan key:generate --show` |
| `APP_ENV` | `production` | |
| `APP_DEBUG` | `false` | En `true` una excepción publica la traza entera, con credenciales incluidas |
| `APP_URL` | la URL del servicio | La usan los enlaces de correo y los assets |
| `APP_LOCALE` | `es` | H-49 |
| `DB_CONNECTION` | `mysql` | |
| `DB_HOST` `DB_PORT` `DB_DATABASE` `DB_USERNAME` `DB_PASSWORD` | las del servicio MySQL | En Railway se referencian con `${{MySQL.MYSQLHOST}}` y equivalentes |
| `LOG_CHANNEL` | `stderr` | El contenedor es efímero: lo que se escriba en `storage/logs` se pierde en cada despliegue |
| `SESSION_DRIVER` | `cookie` | `file` también funciona, pero cierra la sesión de todo el mundo en cada despliegue |
| `SEED_AL_ARRANCAR` | `true` **solo la primera vez** | Siembra el catálogo de trabajo. Déjala en `false` después, o quítala |

> ⚠️ **`SEED_AL_ARRANCAR=true` no es idempotente.** `UserSeeder` y `CategoriaSeeder` sí, pero `ProductoSeeder` vuelve a insertar el catálogo entero: si se queda puesta, cada despliegue duplica los 40 productos. Se pone para el primer arranque y se quita.

## Desplegar en Railway

Los dos primeros pasos son interactivos —abren el navegador— y hay que hacerlos a mano:

```bash
npm install -g @railway/cli
railway login                       # abre el navegador
railway init                        # crea el proyecto
railway add --database mysql        # añade el servicio MySQL
railway up                          # construye y despliega
railway domain                      # genera la URL pública
```

Las variables se ponen desde el panel del proyecto o con `railway variables --set "CLAVE=valor"`. Las de MySQL se referencian a las del otro servicio en vez de copiarse a mano:

```
DB_HOST=${{MySQL.MYSQLHOST}}
DB_PORT=${{MySQL.MYSQLPORT}}
DB_DATABASE=${{MySQL.MYSQLDATABASE}}
DB_USERNAME=${{MySQL.MYSQLUSER}}
DB_PASSWORD=${{MySQL.MYSQLPASSWORD}}
```

Copiarlas a mano parece más rápido y deja de funcionar el día que Railway rote la contraseña.

**Railway necesita plan de pago** (Hobby) para mantener un servicio desplegado. La cuenta gratuita sirve para probar, pero el servicio se duerme.

## Después del primer despliegue

1. Entrar con `admin@supermercado.test` / `password` y **cambiar la contraseña**. Son credenciales de seeder, públicas en este repositorio.
2. Quitar `SEED_AL_ARRANCAR`.
3. Poner la URL en el campo *homepage* del repositorio: `gh repo edit --homepage "https://…"`.

## Lo que se aprendió montándolo

- **`bootstrap/cache/*.php` no puede viajar a la imagen.** El manifiesto de paquetes se genera en local, donde sí están las dependencias de desarrollo; dentro, con `--no-dev`, Laravel intenta registrar `CollisionServiceProvider`, no lo encuentra y no arranca. Se descubrió ejecutando el contenedor, no leyendo el `Dockerfile`.
- **La configuración se cachea en el arranque, no en la construcción.** Depende de variables de entorno que en el build todavía no existen.
- **`TrustProxies` necesita `$proxies = '*'`.** Con el TLS terminado por delante, la aplicación recibe HTTP y solo sabe que el cliente venía por HTTPS leyendo `X-Forwarded-Proto`. Sin eso, `url()` genera enlaces `http://` dentro de una página `https://` y el navegador bloquea los assets.
