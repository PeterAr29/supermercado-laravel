# 01 — Contexto del proyecto

## Qué es

Tienda de supermercado online construida con **Laravel 10 + Breeze**, patrón MVC. Cubre dos flujos de negocio distintos:

- **Venta al cliente:** catálogo → carrito → pago simulado → registro de venta.
- **Abastecimiento:** proveedores → catálogo por proveedor (N:N) → orden de compra → recepción y reposición de stock.

Complemento: consulta de proveedores publicados en **Google Sheets** (CSV público) como fuente externa.

## Stack

| Capa | Tecnología |
|---|---|
| Framework | Laravel 10 (`^10.10`) |
| PHP | 8.1.25 |
| Auth | Laravel Breeze `^1.29` (sesión + Blade) |
| BD | MySQL (XAMPP, `DB_DATABASE=laravel`) |
| Vistas | Blade |
| CSS | Tailwind vía CDN (+ `vite.config.js` y `tailwind.config.js` configurados **pero sin usar** en el sitio principal) |
| Build | Vite + npm |
| Tests | PHPUnit 10 (solo los de Breeze) |

## Cómo llegó a ser lo que es

El proyecto creció **por capas incrementales**, cada una añadida sin revisar la anterior. Reconstruido a partir de las fechas de las migraciones y del historial de `storage/logs/laravel.log`:

| Fase | Fecha | Qué se añadió |
|---|---|---|
| 0 | — | Laravel 10 + Breeze (auth, perfil, tests base) |
| 1 | 10 dic 2025 | `productos` — CRUD básico |
| 2 | 11 dic 2025 | `carritos` + `carrito_items` — carrito por **sesión**; al día siguiente se parchea `user_id` a nullable |
| 3 | 12 dic 2025 | `orders` + `order_items` — checkout simulado, **nombrado en inglés** |
| 4 | 12 dic 2025 | `proveedores`, pivot N:N, `ordenes_compra` + items — **nombrado en español** |
| 5 | 13 dic 2025 | `categorias` + filtro lateral |
| 6 | 14 dic 2025 | `unidad_medida` en productos |
| 7 | s/f | Integración Google Sheets |

**Lo que explica el desorden actual:** el log conserva rastros de al menos tres intentos de arquitectura abandonados a medias — `ProductoAdminController` (nunca creado), `layouts.app` (nunca creado), un sistema de componentes con `$slot` que se dejó a mitad. Los restos de esos intentos siguen en el repositorio y conviven con el código vivo.

## Arquitectura actual

```
app/
├── Http/Controllers/     8 controladores de dominio + 9 de Breeze
├── Models/              10 modelos
├── Services/             1 servicio (ProveedorSheetService)
├── Providers/            AppServiceProvider define un View::composer('*') global
└── View/Components/      AppLayout, GuestLayout (de Breeze)

resources/views/         45 vistas Blade
routes/web.php           mezcla Route::resource + rutas sueltas, sin middleware de auth
database/migrations/     15 migraciones (4 de Laravel/Breeze, 11 del dominio)
```

### Modelo de dominio (estado actual)

```
Categoria 1──N Producto
Producto  N──N Proveedor        (pivot: proveedor_producto)
Producto  1──N CarritoItem N──1 Carrito
Producto  1──N OrderItem   N──1 Order          ← venta al cliente  (INGLÉS)
Producto  1──N OrdenCompraItem N──1 OrdenCompra ← compra a proveedor (ESPAÑOL)
                                    N──1 Proveedor
User ──────────────────────────── (sin relación con Carrito ni Order)
```

**Problema central del modelo:** `Order` (venta) y `OrdenCompra` (compra) son conceptos opuestos con nombres casi idénticos en idiomas distintos, y `User` está desconectado de ambos flujos.

## Cómo levantar el proyecto

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan serve
```

> ⚠️ `php artisan db:seed` hoy **no siembra nada**: `DatabaseSeeder` está vacío y no invoca a `CategoriaSeeder` (ver `H-23`).

## Convenciones de idioma

El dominio se escribe **en español** (`Producto`, `Proveedor`, `Carrito`). Laravel y Breeze permanecen en inglés (`User`, `password_reset_tokens`). Las excepciones actuales (`Order`, `OrderItem`) se corrigen en la Fase 2.
