# Tattos Market — Supermercado online

Tienda de supermercado con panel de gestión, sobre Laravel 10. Tiene **dos caras**: el catálogo y el carrito para el cliente, y `/admin` para quien gestiona el negocio —catálogo, proveedores, órdenes de compra e inventario con kardex—.

| | |
|---|---|
| **Stack** | PHP 8.1 · Laravel 10 · MySQL · Tailwind + Vite · Breeze |
| **Estado** | Fases 0-7 del roadmap cerradas · 63 tests en verde · sin hallazgos abiertos |
| **Gestión** | [`docs/`](docs/) — contexto, hallazgos, roadmap y convenciones |

## Dónde está el proyecto

La raíz del repositorio **es** la raíz del proyecto:

```
supermercado_laravel/        ← aquí están composer.json, artisan y .git
├── app/  config/  database/  public/  resources/  routes/  tests/
└── docs/                    ← documentación de gestión
```

> Hasta el 2026-08-02 la app vivía un nivel más abajo, en `supermercado_laravel/supermercado_laravel/`, con el repositorio por encima. Se aplanó al cerrar **H-27**. **Si vienes de una copia anterior, toda ruta absoluta pierde un nivel**: accesos directos, carpeta abierta en el IDE y tareas programadas hay que reapuntarlos.

## Puesta en marcha

```bash
composer install
npm install && npm run build     # sin esto, @vite lanza "Vite manifest not found"
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

`public/build` **no se versiona**: `npm run build` es obligatorio tras clonar y tras cambiar de rama.

### Base de datos

Crea la base de trabajo y **la de tests, que es otra**:

```sql
CREATE DATABASE laravel            CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE DATABASE laravel_testing    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

> ⚠️ La segunda no es opcional. `RefreshDatabase` ejecuta `migrate:fresh`, que hace **DROP de todas las tablas**. Cuando la suite apuntaba a la base de desarrollo, cada `php artisan test` la destruía en silencio — y así se perdieron los datos una vez (**H-33**). `phpunit.xml` la fija en `laravel_testing`; antes de estrenar la suite en una máquina nueva, comprueba a qué base apunta.

### Usuarios que deja el seeder

| Rol | Correo | Contraseña |
|---|---|---|
| Administrador | `admin@supermercado.test` | `password` |
| Cliente | `cliente@supermercado.test` | `password` |

`migrate:fresh --seed` deja además 8 categorías, 40 productos, 6 proveedores con su catálogo de compra, y el kardex de apertura de los 40.

## Cómo está organizado

Cada capa tiene una responsabilidad y no invade la siguiente — el detalle, en [`docs/04-CONVENCIONES.md`](docs/04-CONVENCIONES.md):

| Capa | Hace |
|---|---|
| **Form Request** | Valida y autoriza la entrada |
| **Controlador** | Recibe, delega en el servicio, responde. Nunca pasa de 15 líneas |
| **Servicio** | Reglas de negocio y transacciones. No conoce HTTP |
| **Policy** | «¿Puede este usuario, sobre este dato?» |
| **Vista** | Muestra. **No calcula** — ningún total se suma en Blade |

Los cinco servicios son `CarritoService`, `CheckoutService`, `OrdenCompraService`, `PanelService` e `InventarioService`.

**`InventarioService` es el único sitio que modifica `productos.stock`.** Ningún controlador, seeder ni comando hace `increment()` por su cuenta: todo movimiento deja su línea en el kardex —con tipo, cantidad con signo, stock resultante, motivo, documento de origen y usuario— o no ocurre.

## Comandos propios

```bash
php artisan db:respaldo --fase=7    # vuelca la base a backup_pre_fase_7.sql
php artisan db:respaldo             # nombre con fecha y hora
```

Paso 0 obligatorio antes de empezar cualquier fase: Git versiona el código, no los datos. No pisa un volcado existente salvo con `--forzar`. Los `backup_*.sql` están en `.gitignore`.

## Calidad

```bash
php artisan test        # 63 tests, 164 aserciones
./vendor/bin/pint       # NO es `php artisan pint`: Pint es un binario
```

Los tests de dominio viven en `tests/Feature/Tienda` (catálogo, carrito, checkout) y `tests/Feature/Admin` (órdenes de compra, acceso al panel). Cada uno está escrito sobre un fallo que el proyecto ya pagó una vez y lleva su id de hallazgo en un comentario.

**Un test afirma algo del resultado, no que la petición no reventó.** Una pantalla en blanco y una con la codificación destrozada responden `200` igual: pasó dos veces (H-45, H-47). Si la prueba mira una pantalla, mira **lo que tiene que aparecer en el HTML**.

**Y aun así no basta: si tocas vistas, abre la aplicación y míralas.** Un test comprueba el HTML *que se le dice que comprueba*. Con la suite entera en verde, el paginador seguía hablando inglés y un campo de la ficha pedía datos que nadie recogía (H-48…H-51). Eso solo se ve mirando.

## Documentación

| Documento | Para qué |
|---|---|
| [`docs/01-CONTEXTO.md`](docs/01-CONTEXTO.md) | Qué es el proyecto y cómo llegó a ser lo que es |
| [`docs/02-HALLAZGOS.md`](docs/02-HALLAZGOS.md) | Los 51 hallazgos registrados, con diagnóstico y arreglo |
| [`docs/03-ROADMAP.md`](docs/03-ROADMAP.md) | Las fases, su criterio de aceptación y el backlog |
| [`docs/04-CONVENCIONES.md`](docs/04-CONVENCIONES.md) | Reglas para que no se vuelva a desordenar |
| [`CHANGELOG.md`](CHANGELOG.md) | Qué cambió en cada fase |

Empieza por [`docs/README.md`](docs/README.md), que es el índice y dice en qué estado está todo.
