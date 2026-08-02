# 03 — Roadmap por fases

Cada fase tiene **objetivo**, **alcance cerrado**, **checklist** y **criterio de aceptación**. No se pasa a la siguiente hasta cumplir el criterio.

| Fase | Nombre | Estado | Hallazgos que cierra |
|---|---|---|---|
| 0 | Control de versiones y gestión | 🟡 En curso | H-26, H-27 |
| 1 | Seguridad e integridad de datos | ⬜ Pendiente | H-01…H-07 |
| 2 | Dominio unificado | ⬜ Pendiente | H-08…H-11, H-25 |
| 3 | Separación de capas (MVC real) | ⬜ Pendiente | H-12, H-13, H-14, H-19, H-20 |
| 4 | Capa de presentación | ⬜ Pendiente | H-15…H-18 |
| 5 | Robustez y calidad | ⬜ Pendiente | H-21…H-24 |

Leyenda: ⬜ Pendiente · 🟡 En curso · ✅ Completada

---

## Fase 0 — Control de versiones y gestión 🟡

**Objetivo:** poder trabajar sin miedo. Ningún refactor es seguro sin historial.

**Por qué primero:** las fases 1-5 modifican migraciones, renombran modelos y borran vistas. Sin Git, un error obliga a rehacer el trabajo a mano.

### Checklist
- [x] Auditoría completa del proyecto → `02-HALLAZGOS.md`
- [x] Documentación de contexto → `01-CONTEXTO.md`
- [x] Roadmap por fases → este documento
- [x] Convenciones de trabajo → `04-CONVENCIONES.md`
- [ ] `git init` + commit inicial con el estado actual íntegro
- [ ] Verificar que `.gitignore` excluye `.env`, `/vendor`, `/node_modules`, `/storage/logs`
- [ ] `CHANGELOG.md` en la raíz
- [ ] Decidir qué hacer con la carpeta anidada y el `package-lock.json` huérfano (H-27)

### Criterio de aceptación
`git log` muestra el commit inicial, `git status` sale limpio y `git check-ignore .env` confirma que el `.env` no está versionado.

---

## Fase 1 — Seguridad e integridad de datos ⬜

**Objetivo:** que el proyecto deje de ser peligroso y que el flujo de abastecimiento funcione.

**Por qué ahora:** hoy un visitante anónimo puede borrar el catálogo y llevarse el historial de ventas por delante (H-01 + H-02). Y el inventario, que es el corazón de un supermercado, no existe (H-03).

### Checklist

**Bloque A — Cerrar el acceso** *(H-01)*
- [ ] Agrupar en `web.php` las rutas de escritura bajo `middleware('auth')`
- [ ] Dejar públicas solo: `home`, `productos.index`, `productos.show`, `productos.categoria` y el carrito
- [ ] Verificar manualmente que `DELETE /productos/1` como invitado redirige a login

**Bloque B — Proteger el historial** *(H-02)*
- [ ] Migración: `order_items.producto_id` y `orden_compra_items.producto_id` → `restrictOnDelete()`
- [ ] `SoftDeletes` en `Producto` + `use SoftDeletes` en el modelo
- [ ] `ProductoController::index` filtra los borrados (comportamiento por defecto de Eloquent)

**Bloque C — Inventario** *(H-03)*
- [ ] Migración `add_stock_to_productos_table`: `integer('stock')->default(0)`
- [ ] `stock` en `$fillable` y `$casts` de `Producto`
- [ ] Verificar que `OrdenCompraController::recibir()` completa sin excepción

**Bloque D — Precio del proveedor** *(H-04)*
- [ ] Migración: añadir `precio_compra` decimal(10,2) al pivot `proveedor_producto`
- [ ] `Proveedor::productos()` → `withPivot('stock_proveedor', 'precio_compra')`
- [ ] `OrdenCompraController:64` → `->pivot->precio_compra`
- [ ] Confirmar que `ordenes/create.blade.php:88` ya recibe el campo

**Bloque E — Contacto del proveedor** *(H-05)*
- [ ] Migración: `contacto` → `contacto_nombre` + `contacto_telefono`
- [ ] Actualizar `$fillable` de `Proveedor`
- [ ] Verificar que `proveedores/index` muestra el contacto

**Bloque F — Transacción y ruta rota** *(H-06, H-07)*
- [ ] `OrdenCompraController::store` → `DB::transaction(fn () => ...)`
- [ ] `ProveedorController::show()` implementado, o `->except(['show'])` en la ruta

### Criterio de aceptación
1. Como invitado, ninguna ruta de escritura responde 200.
2. Se crea un proveedor con contacto y **el contacto aparece** en el listado.
3. Se asigna un producto al proveedor con `precio_compra` y se guarda.
4. Se crea una orden de compra con **total distinto de 0**.
5. Se marca como recibida y **el stock del producto aumenta**.
6. Se borra un producto y las ventas anteriores conservan sus líneas y totales.

---

## Fase 2 — Dominio unificado ⬜

**Objetivo:** un solo vocabulario y un `User` conectado a sus compras.

### Checklist

**Bloque A — Renombrado** *(H-09)*
- [ ] `Order` → `Venta`, `OrderItem` → `VentaItem`
- [ ] Migración de renombrado de tablas: `orders` → `ventas`, `order_items` → `venta_items`
- [ ] Actualizar `CarritoController::procesarPago`, vistas `pago/*` y rutas
- [ ] Añadir `VentaItem::producto()` y `OrdenCompraItem::orden()` (relaciones que faltan)

**Bloque B — Usuario conectado** *(H-10, H-11, H-08)*
- [ ] `ventas.user_id` nullable + `Venta::user()` + `User::ventas()`
- [ ] `User::carrito()` (hasOne)
- [ ] `CarritoController::obtenerCarrito()` usa `user_id` cuando hay sesión iniciada
- [ ] Fusionar carrito de invitado al iniciar sesión (listener del evento `Login`)
- [ ] Corregir el `View::composer` de `AppServiceProvider` para contar también el carrito de invitado

**Bloque C — Esquema consistente** *(H-25)*
- [ ] Unificar todos los `precio`/`total` a `decimal(10,2)`
- [ ] `$casts` de importes (`decimal:2`) en todos los modelos
- [ ] Índices únicos en `proveedor_producto(proveedor_id, producto_id)` y `carrito_items(carrito_id, producto_id)`
- [ ] Enums PHP 8.1: `EstadoOrdenCompra`, `UnidadMedida` + `$casts`
- [ ] `unidad_medida` al `$fillable` de `Producto`
- [ ] Timestamps en el pivot

### Criterio de aceptación
No queda ninguna referencia a `Order`/`OrderItem` (`grep -ri "OrderItem" app/ resources/ routes/` sin resultados). Un usuario autenticado compra, cierra sesión, vuelve a entrar y **su venta sigue asociada a él**. El badge del carrito muestra la cantidad correcta como invitado y como usuario.

---

## Fase 3 — Separación de capas (MVC real) ⬜

**Objetivo:** que el controlador solo coordine. Es la fase que da el "orden" estructural.

### Checklist

**Bloque A — Validación** *(H-12)*
- [ ] `StoreProductoRequest` / `UpdateProductoRequest`
- [ ] `StoreProveedorRequest` / `UpdateProveedorRequest`
- [ ] `StoreOrdenCompraRequest`, `AgregarAlCarritoRequest`
- [ ] Eliminar todo `$request->validate()` de los controladores

**Bloque B — Servicios** *(H-13)*
- [ ] `CarritoService`: obtener, agregar, eliminar, vaciar, **calcular total (fuente única)**
- [ ] `CheckoutService`: crear venta + descontar stock, dentro de una transacción
- [ ] `OrdenCompraService`: crear orden y recepcionar con reposición
- [ ] Vistas y controladores consumen el total del servicio, nunca lo recalculan

**Bloque C — Autorización** *(H-14)*
- [ ] Campo `rol` en `users` (o paquete de roles si crece)
- [ ] `ProductoPolicy`, `ProveedorPolicy`, `OrdenCompraPolicy`
- [ ] `authorize()` en los controladores; middleware `auth` pasa a ser el suelo, no el techo

**Bloque D — Rutas y binding** *(H-19, H-20)*
- [ ] Eliminar la ruta duplicada `productos.index`
- [ ] Reorganizar `web.php`: bloque público / bloque cliente autenticado / bloque `admin`
- [ ] `Route::resource` para órdenes de compra
- [ ] La ruta AJAX pasa a `routes/api.php` o a un prefijo `ajax/` con nombre
- [ ] Route model binding en **todos** los métodos (`show(Producto $producto)`)
- [ ] Namespaces `Controllers/Admin/` y `Controllers/Tienda/`

### Criterio de aceptación
Ningún controlador supera **15 líneas por método**. `grep -rn "validate(" app/Http/Controllers/` no devuelve nada. El total del carrito se calcula en **un solo lugar** del código.

---

## Fase 4 — Capa de presentación ⬜

**Objetivo:** un solo sistema visual. Hoy hay siete pantallas literalmente sin estilos.

### Checklist
- [ ] **Decidir framework: Tailwind** (ya configurado con Vite) y documentarlo en `04-CONVENCIONES.md`
- [ ] Migrar a Tailwind las 7 vistas escritas en Bootstrap *(H-16)*: `proveedores/{index,create,edit,form}`, `ordenes/{index,create,show}`
- [ ] Sustituir el CDN de Tailwind por `@vite` *(H-17)*
- [ ] Un único layout base; eliminar `layoutCenter`, `super`, `super-layout`, `navbar` *(H-15)*
- [ ] Unificar la marca y el `<title>` *(H-18)*
- [ ] Componentes Blade: `<x-producto-card>`, `<x-alerta-flash>`, `<x-tabla>` (hoy duplicados en 6 vistas)
- [ ] Sacar el cálculo de totales de `carrito/index.blade.php`

### Criterio de aceptación
`npm run build` genera los assets, ninguna vista referencia un CDN, `resources/views/layouts/` contiene un solo layout de tienda (más el `guest` de Breeze) y las siete pantallas de proveedores/órdenes se ven con estilos.

---

## Fase 5 — Robustez y calidad ⬜

**Objetivo:** que aguante datos y uso reales.

### Checklist
- [ ] Unificar `ProveedorSheetService` + `ProveedorSheetController` en un solo servicio *(H-21)*
- [ ] URLs de Sheets a `config/services.php` + `.env`
- [ ] `Cache::remember(..., 600)` y `->timeout(5)`; degradar a lista vacía si Google falla (nunca `abort(500)`)
- [ ] Paginación en productos y proveedores *(H-22)*
- [ ] `DatabaseSeeder` invoca `CategoriaSeeder`, `ProductoSeeder`, `ProveedorSeeder` *(H-23)*
- [ ] Factories de `Producto`, `Categoria`, `Proveedor`
- [ ] Tests de feature *(H-24)*: agregar al carrito · checkout descuenta stock · recibir orden repone stock · invitado no puede borrar productos
- [ ] `php artisan pint` sobre todo el código (ya está en `require-dev`)

### Criterio de aceptación
`php artisan migrate:fresh --seed` deja una base usable. `php artisan test` pasa en verde con al menos 4 tests de dominio nuevos. La ficha de producto carga sin esperar a Google.

---

## Fuera de alcance (backlog)

Ideas válidas que **no** entran en estas fases, para no dispersar el trabajo:

- Pasarela de pago real (hoy el pago es simulado)
- Subida de imágenes a `storage` (hoy `imagen` es una URL de texto)
- Búsqueda en tiempo real (el comentario ya está en `layouts/navigation.blade.php:22`)
- Panel de reportes / dashboard con métricas de venta
- API REST para app móvil (`routes/api.php` está vacío)
- Gestión de ofertas y promociones (el carrusel del home es una imagen fija)
