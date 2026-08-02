# 01 — Contexto del proyecto

> **Estado descrito: tras la Fase 6 (2026-08-02).** Este documento dice cómo está el proyecto **hoy** y cómo llegó hasta aquí. Qué cambió en cada fase está en [`../CHANGELOG.md`](../CHANGELOG.md); por qué, en [`03-ROADMAP.md`](03-ROADMAP.md).

## Qué es

Tienda de supermercado online construida con **Laravel 10 + Breeze**, patrón MVC. Tiene **dos caras** y cubre dos flujos de negocio opuestos:

- **Tienda (cliente):** catálogo → carrito → pago simulado → venta, que **descuenta stock**.
- **Panel (`/admin`):** proveedores → catálogo por proveedor (N:N) → orden de compra → recepción, que **repone stock**.

Entre los dos está el **inventario**: un kardex donde toda entrada, salida y ajuste deja su línea.

## Stack

| Capa | Tecnología |
|---|---|
| Framework | Laravel 10 (`^10.10`) |
| PHP | 8.1.25 |
| Auth | Laravel Breeze `^1.29` (sesión + Blade), con dos roles: `cliente` y `admin` |
| BD | MySQL (XAMPP, `DB_DATABASE=laravel`; tests en `laravel_testing`) |
| Vistas | Blade, un solo `<head>` en `layouts/base` |
| CSS | **Tailwind compilado por Vite.** Ningún CDN, ninguna clase de Bootstrap |
| Build | Vite + npm (`public/build` no se versiona) |
| Tests | PHPUnit 10 — 49 tests, 128 aserciones |
| Estilo | Laravel Pint (`./vendor/bin/pint`) |

## Cómo llegó a ser lo que es

El proyecto creció **por capas incrementales**, cada una añadida sin revisar la anterior. Reconstruido a partir de las fechas de las migraciones y del historial de `storage/logs/laravel.log`:

| Etapa | Fecha | Qué se añadió |
|---|---|---|
| 0 | — | Laravel 10 + Breeze (auth, perfil, tests base) |
| 1 | 10 dic 2025 | `productos` — CRUD básico |
| 2 | 11 dic 2025 | `carritos` + `carrito_items` — carrito por **sesión**; al día siguiente se parchea `user_id` a nullable |
| 3 | 12 dic 2025 | `orders` + `order_items` — checkout simulado, **nombrado en inglés** |
| 4 | 12 dic 2025 | `proveedores`, pivot N:N, `ordenes_compra` + items — **nombrado en español** |
| 5 | 13 dic 2025 | `categorias` + filtro lateral |
| 6 | 14 dic 2025 | `unidad_medida` en productos |
| 7 | s/f | Integración Google Sheets |

**Lo que explicaba el desorden:** el log conserva rastros de al menos tres intentos de arquitectura abandonados a medias — `ProductoAdminController` (nunca creado), `layouts.app` (nunca creado), un sistema de componentes con `$slot` que se dejó a mitad. Cada intento dejó sus restos conviviendo con el código vivo.

### El reordenamiento — agosto 2026

Auditoría de 47 hallazgos y siete fases de trabajo, del 2026-08-01 al 2026-08-02:

| Fase | Qué dejó |
|---|---|
| 0 | Git, y esta documentación |
| 1 | Rutas de escritura cerradas, historial de ventas protegido, inventario que existe |
| 2 | Un solo vocabulario: `Order` → `Venta`. `User` conectado a sus compras y a su carrito |
| 3 | Dos roles y dos paneles. El kardex, y la venta que por fin descuenta |
| 4 | Form Requests, cinco servicios, y ningún controlador de más de 15 líneas |
| 5 | Un solo layout, Tailwind por Vite, adiós a Bootstrap sin cargar |
| 6 | Paginación, factories, 24 tests de dominio y el respaldo automatizado |

**Google Sheets se retiró** en la Fase 3: era la tercera fuente de proveedores en competencia y la única sin conexión con órdenes ni stock. La carpeta anidada —la app vivía un nivel por debajo del repositorio— se aplanó al cerrar H-27.

## Arquitectura actual

```
app/
├── Console/Commands/     1 comando propio (db:respaldo)
├── Enums/                4 enums de PHP 8.1
├── Exceptions/           3 excepciones de dominio + el Handler
├── Http/
│   ├── Controllers/      12 de dominio (Admin/ y Tienda/) + 10 de Breeze y perfil
│   ├── Middleware/       EsAdministrador, además de los de Laravel
│   └── Requests/         13 Form Requests: validan Y autorizan
├── Models/               11 modelos
├── Policies/             5 policies
├── Providers/            AppServiceProvider define un View::composer('*') global
├── Services/             5 servicios — aquí vive el negocio
└── View/Components/      AppLayout, GuestLayout (de Breeze)

resources/views/          63 vistas Blade; layouts/base es el único <head>
routes/web.php            /admin tras ['auth','admin'], zona de usuario tras 'auth', tienda pública
database/migrations/      29 migraciones (4 de Laravel/Breeze, 25 del dominio)
database/seeders/         6 seeders encadenados por DatabaseSeeder
database/factories/       4 factories
tests/                    14 archivos, 49 tests
```

### Las capas, y qué no hace cada una

| Capa | Sí hace | No hace |
|---|---|---|
| **Form Request** | Validar y autorizar la entrada | Consultar reglas de negocio |
| **Controlador** | Recibir, delegar, responder. Máximo 15 líneas | Validar, calcular, consultar la BD |
| **Servicio** | Reglas de negocio y transacciones | Conocer HTTP |
| **Policy** | «¿Puede este usuario, sobre este dato?» | Autenticar — de eso va el middleware |
| **Vista** | Mostrar lo ya preparado | **Calcular** |

Los cinco servicios son `CarritoService`, `CheckoutService`, `OrdenCompraService`, `PanelService` e **`InventarioService`**, sobre el que se apoyan los demás.

**`InventarioService` es el único sitio del proyecto que modifica `productos.stock`.** Bloquea la fila del producto dentro de la transacción, calcula el stock resultante y escribe producto y movimiento a la vez. No hay forma de mover stock sin dejar la línea que lo explica.

### Modelo de dominio

```
Categoria 1──N Producto
Producto  N──N Proveedor           (pivot: stock_proveedor, precio_compra)
Producto  1──N CarritoItem     N──1 Carrito     1──1 User   ← accesorio
Producto  1──N VentaItem       N──1 Venta       N──1 User   ← venta al cliente
Producto  1──N OrdenCompraItem N──1 OrdenCompra N──1 Proveedor
                                                            ← compra al proveedor
Producto  1──N MovimientoInventario ──N──1 User
                        └── origen (morphTo): Venta · OrdenCompra · nada si es ajuste
```

- **`Venta` y `OrdenCompra`** son los dos flujos opuestos, y ahora se llaman distinto. Antes eran `Order` y `OrdenCompra`: dos conceptos contrarios con nombres casi idénticos en idiomas distintos (H-09).
- **`User` está conectado a los dos lados**: sus ventas, y **un** carrito que sobrevive al cierre de sesión — uno solo, garantizado por índice único, porque llegó a poder tener dos a la vez (H-39). El de invitado es el mismo modelo con `user_id` a null, y se fusiona al iniciar sesión.
- **`MovimientoInventario` es el kardex.** Su `origen` polimórfico dice de dónde viene cada unidad; un ajuste manual no tiene origen, pero **exige motivo**.
- **Nada histórico se borra.** `Producto` usa `SoftDeletes`, y las líneas de venta y de orden lo resuelven con `withTrashed()`: son documentos y deben resolver siempre. Las líneas de carrito, que son accesorias, se filtran y desaparecen.

## Cómo levantar el proyecto

En el [`README.md`](../README.md) de la raíz, que incluye la creación de **`laravel_testing`** —obligatoria— y los usuarios que deja el seeder.

El resumen:

```bash
composer install
npm install && npm run build     # sin esto, @vite lanza "Vite manifest not found"
php artisan migrate --seed
```

`migrate:fresh --seed` deja una base de trabajo completa: 2 usuarios, 8 categorías, 40 productos, 6 proveedores con su catálogo de compra y el kardex de apertura de los 40.

## Convenciones de idioma

El dominio se escribe **en español** (`Producto`, `Proveedor`, `Carrito`, `Venta`). Laravel y Breeze permanecen en inglés (`User`, `password_reset_tokens`), igual que los métodos de controlador de recurso (`index`, `store`). Las excepciones que quedaban —`Order`, `OrderItem`— se corrigieron en la Fase 2.

**Un concepto, un nombre, en toda la pila.** El detalle y el resto de reglas, en [`04-CONVENCIONES.md`](04-CONVENCIONES.md).
