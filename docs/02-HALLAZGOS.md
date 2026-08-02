# 02 — Inventario de hallazgos

Auditoría del 2026-08-01 sobre `routes/`, 8 controladores de dominio, 10 modelos, 15 migraciones, `AppServiceProvider` y 45 vistas.

**Severidad:** 🔴 Crítico (rompe o expone) · 🟠 Alto (arquitectura) · 🟡 Medio (consistencia) · 🟢 Bajo (calidad)

| ID | Sev | Área | Hallazgo | Fase |
|---|---|---|---|---|
| H-01 | 🔴 | Seguridad | Panel de administración completamente público | 1 |
| H-02 | 🔴 | Datos | Borrar un producto destruye el historial de ventas | 1 |
| H-03 | 🔴 | Bug | Recepción de órdenes falla: no existe la columna `stock` | 1 |
| H-04 | 🔴 | Bug | Precio del pivot proveedor-producto: tres nombres distintos | 1 |
| H-05 | 🔴 | Bug | Contacto del proveedor se descarta en silencio | 1 |
| H-06 | 🔴 | Datos | Transacción sin rollback en creación de orden de compra | 1 |
| H-07 | 🔴 | Bug | `proveedores.show` devuelve error 500 | 1 |
| H-08 | 🟠 | Bug | El contador del carrito siempre muestra 0 | 2 |
| H-09 | 🟠 | Dominio | Modelos duplicados inglés/español (`Order` vs `OrdenCompra`) | 2 |
| H-10 | 🟠 | Dominio | Las ventas no registran `user_id` | 2 |
| H-11 | 🟠 | Dominio | El carrito vive solo en sesión; `user_id` nunca se usa | 2 |
| H-12 | 🟠 | Capas | Sin Form Requests: validación duplicada en cada `store`/`update` | 4 |
| H-13 | 🟠 | Capas | Sin capa de servicios: lógica de negocio en controladores y vistas | 4 |
| H-14 | 🔴 | Seguridad | Sin roles: **cualquier registrado es administrador** | 3 |
| H-15 | 🟡 | Vistas | Cuatro sistemas de layout coexistiendo | 5 |
| H-16 | 🟡 | Vistas | Se usan clases Bootstrap pero Bootstrap nunca se carga | 5 |
| H-17 | 🟡 | Build | Tailwind por CDN; Vite configurado pero sin usar | 5 |
| H-18 | 🟡 | Vistas | Marca inconsistente: "PlazaKing" vs "Tattos Market" | 5 |
| H-19 | 🟡 | Rutas | Rutas duplicadas y sin agrupar en `web.php` | 4 |
| H-20 | 🟡 | Capas | Route model binding inconsistente | 4 |
| H-21 | 🟡 | Arquitectura | Google Sheets: tercera fuente de proveedores, se retira | 3 |
| H-22 | 🟢 | Rendimiento | Sin paginación en productos ni proveedores | 6 |
| H-23 | 🟢 | Datos | `DatabaseSeeder` vacío; sin factories de dominio | 6 |
| H-24 | 🟢 | Calidad | Sin tests de dominio | 6 |
| H-25 | 🟡 | Esquema | Inconsistencias de tipos, casts, índices y enums | 2 |
| H-26 | 🟠 | Gestión | Sin control de versiones | 0 |
| H-27 | 🟢 | Gestión | Carpeta anidada y `package-lock.json` huérfano | 0 |
| H-28 | 🔴 | Bug | Falta la relación `Producto::proveedores()` que ya se usaba | 1 |
| H-29 | 🔴 | Bug | `Proveedor` apunta a la tabla `proveedors`, que no existe | 1 |
| H-30 | 🔴 | Bug | `OrdenCompraItem` escribe timestamps que su tabla no tiene | 1 |
| H-31 | 🟠 | Bug | Las rutas de perfil de Breeze nunca se registraron | 1 |
| H-32 | 🔴 | Bug | Un producto retirado rompía órdenes y falseaba el carrito | 1 |
| H-33 | 🔴 | Datos | `php artisan test` borraba la base de datos de desarrollo | 1 |
| H-34 | 🟠 | Gestión | Sin copia de seguridad de la base antes de cada fase | 6 |
| H-35 | 🔴 | Dominio | Las ventas no descuentan stock | 3 |

---

## 🔴 Críticos

### H-01 — Panel de administración completamente público
**Dónde:** `routes/web.php` (archivo completo)
No hay un solo `middleware('auth')` sobre las rutas de dominio. Un visitante anónimo puede:
- `DELETE /productos/{id}` → borrar el catálogo (y con él el historial de ventas, ver H-02)
- `GET|POST /proveedores/create` → crear proveedores
- `POST /ordenes/{orden}/recibir` → inyectar stock arbitrario

**Arreglo:** agrupar rutas de escritura bajo `middleware(['auth'])`, dejando públicas solo `home`, `productos.index`, `productos.show`, categoría y carrito.

### H-02 — Borrar un producto destruye el historial de ventas
**Dónde:** `database/migrations/2025_12_12_020206_create_order_items_table.php:16`
`producto_id` está declarado con `onDelete('cascade')`. Al eliminar un producto se borran sus líneas en ventas ya cerradas y los totales dejan de cuadrar. La tabla hermana `orden_compra_items` usa `restrict` — criterios opuestos para el mismo problema.

**Arreglo:** `restrictOnDelete()` en ambas + `SoftDeletes` en `Producto`.

### H-03 — Recepción de órdenes falla: no existe la columna `stock`
**Dónde:** `app/Http/Controllers/OrdenCompraController.php:103`
```php
$producto->stock += $item->cantidad;
```
Ninguna migración de `productos` crea `stock`. El `save()` posterior lanza `Column not found`. **El flujo de reposición de inventario no funciona en absoluto** — y un supermercado sin inventario no es un supermercado.

**Arreglo:** migración `add_stock_to_productos_table` + `stock` en `$fillable` + `$casts`.

### H-04 — Precio del pivot proveedor-producto: tres nombres distintos
**Dónde:** cuatro archivos en desacuerdo

| Archivo | Nombre usado | Resultado |
|---|---|---|
| `create_proveedor_producto_table.php:15` | `stock_proveedor` | no hay columna de precio |
| `app/Models/Proveedor.php:23` | `withPivot('stock_proveedor')` | no expone precio |
| `ProveedorProductoController.php:38` | `attach(..., 'precio_compra')` | SQL error: columna inexistente |
| `OrdenCompraController.php:64` | `->pivot->precio` | `null` |
| `resources/views/ordenes/create.blade.php:88` | `prod.pivot.precio_compra` | `undefined` en JS |

Asignar un producto a un proveedor falla; si se saltara ese error, las órdenes se guardarían con total 0.

**Arreglo:** añadir `precio_compra` al pivot y unificar el nombre en los cuatro puntos.

### H-05 — Contacto del proveedor se descarta en silencio
**Dónde:** `proveedores/form.blade.php:33,39` · `ProveedorController.php:28-29` · `create_proveedores_table.php`
El formulario envía `contacto_nombre` y `contacto_telefono`, el controlador los declara `required`… pero la tabla solo tiene `contacto`. Como no están en `$fillable`, `create($request->all())` **los descarta sin lanzar error**. Por eso la columna "Contacto" de `proveedores/index.blade.php:41` sale siempre vacía.

Es el peor tipo de bug: valida, guarda, redirige con "creado correctamente" y pierde los datos.

**Arreglo:** migración que reemplace `contacto` por `contacto_nombre` + `contacto_telefono`, y actualizar `$fillable`.

### H-06 — Transacción sin rollback
**Dónde:** `app/Http/Controllers/OrdenCompraController.php:44`
`DB::beginTransaction()` sin `try/catch`. Si el `foreach` falla a mitad (producto inexistente, pivot nulo — muy probable dado H-04), queda una orden huérfana con total 0 y la transacción sin cerrar.

**Arreglo:** `DB::transaction(function () { ... })` con closure.

### H-35 — Las ventas no descuentan stock
**Descubierto al replantear el roadmap (2026-08-01).** *(Asignado a la Fase 3.)*

**Dónde:** `app/Http/Controllers/CarritoController.php::procesarPago`

El método registra la venta, crea sus líneas y vacía el carrito, pero **no toca `producto.stock` en ningún momento**. `grep -n "stock" CarritoController.php` no devuelve nada.

El inventario solo cuenta media historia:

| Movimiento | Estado |
|---|---|
| **Entrada** — recepción de orden de compra | ✅ suma stock (arreglado en la Fase 1, H-03) |
| **Salida** — venta al cliente | ❌ **no descuenta nada** |

Se pueden vender 50 unidades y el stock sigue intacto. Tampoco se comprueba que haya existencias antes de confirmar la venta: se puede comprar lo que no hay.

**Arreglo (Fase 3):** la venta descuenta stock dentro de la misma transacción, valida disponibilidad antes de confirmar, y registra el movimiento en `movimientos_inventario`.

### H-34 — Sin copia de seguridad de la base antes de cada fase
**Abierto.** *(Asignado a la Fase 6.)*

Git protege el **código**, pero los **datos** de desarrollo no están versionados ni respaldados. H-33 lo dejó claro del peor modo posible: los productos se perdieron sin posibilidad de recuperación.

Dos medidas pendientes:

1. **Volcado previo a cada fase**, como paso 0 del procedimiento:
   ```
   C:/xampp/mysql/bin/mysqldump.exe -u root laravel > backup_pre_fase_N.sql
   ```
2. **Seeders que reconstruyan un catálogo de trabajo** (`ProductoSeeder`, `ProveedorSeeder`) para que perder la base de desarrollo sea una molestia, no un desastre. Ya estaba previsto en H-23.

Hasta entonces, el volcado manual es obligatorio antes de empezar cualquier fase.

### H-33 — `php artisan test` borraba la base de datos de desarrollo
**Detectado en la revisión del PR de la Fase 1, después de haber destruido datos.**

**Dónde:** `phpunit.xml:24-25`

Las dos líneas que aíslan la base de tests venían **comentadas** desde la instalación de Laravel:

```xml
<!-- <env name="DB_CONNECTION" value="sqlite"/> -->
<!-- <env name="DB_DATABASE" value=":memory:"/> -->
```

Sin ellas, PHPUnit hereda el `.env` y apunta a la base de **desarrollo**. Todos los tests de Breeze usan el trait `RefreshDatabase`, que ejecuta `migrate:fresh` — es decir, **DROP de todas las tablas** — al arrancar la suite.

Resultado: **cada `php artisan test` destruía todos los datos de desarrollo**, en silencio y sin aviso.

**Daño real:** durante la verificación de esta fase se ejecutó la suite varias veces. Se perdieron los ~43 productos y los usuarios de la base `laravel`. No había binlogs (XAMPP los trae desactivados) ni copias de seguridad, así que **no fue posible recuperarlos**. Las 8 categorías sí, porque `CategoriaSeeder` las regenera.

**Arreglo:** `phpunit.xml` apunta a una base MySQL aparte, `laravel_testing`.

Se descartó SQLite en memoria (más rápido) porque la migración `restrict_producto_deletion_on_item_tables` usa `dropForeign()`, que el driver de SQLite no soporta.

Requiere crear la base una vez:
```sql
CREATE DATABASE laravel_testing CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

**Verificado:** tras el arreglo, `php artisan test` crea 15 tablas en `laravel_testing` y la base `laravel` conserva sus datos intactos.

**Prevención:** antes de ejecutar una suite por primera vez en un proyecto, comprobar a qué base apunta. Añadido a `04-CONVENCIONES.md`. La copia de seguridad previa a cualquier fase debería ser parte del procedimiento — se recoge en H-34.

### H-32 — Un producto retirado rompía órdenes y falseaba el carrito
**Detectado en la revisión del PR de la Fase 1.** *(Regresión introducida por el propio H-02; resuelta antes de fusionar.)*

**Dónde:** `app/Models/OrdenCompraItem.php` · `app/Http/Controllers/CarritoController.php`

Al añadir `SoftDeletes` a `Producto` (H-02), las relaciones `belongsTo(Producto::class)` dejaron de resolver los productos retirados, porque el *global scope* de SoftDeletes los excluye. Consecuencias en dos sitios:

| Dónde | Síntoma |
|---|---|
| `OrdenCompraController::recibir()` y `ordenes/show` | **Error fatal:** `Call to a member function increment() on null` |
| `carrito/index`, `mostrarPago`, `procesarPago` | **No fallaba:** calculaba el subtotal como 0 y permitía pagar S/ 0 por un producto retirado |

El segundo es más peligroso justamente porque no rompe nada visible.

**Arreglo, distinto en cada caso porque la semántica es distinta:**
- **Documento histórico** — `OrdenCompraItem::producto()` usa `->withTrashed()`: una orden ya emitida debe seguir resolviendo su producto pase lo que pase.
- **Dato accesorio** — `CarritoController` filtra con `whereHas('producto')`: un producto retirado no debe poder comprarse, así que su línea desaparece del carrito.

**Lección:** activar `SoftDeletes` no es un cambio local. Obliga a revisar **cada** relación que apunte al modelo y decidir, una por una, si el registro histórico debe sobrevivir al borrado. Añadido a `04-CONVENCIONES.md`.

### H-31 — Las rutas de perfil de Breeze nunca se registraron
**Descubierto durante la Fase 1.** *(Resuelto en la misma fase, ver justificación abajo.)*

**Dónde:** `routes/web.php`

`ProfileController` y las vistas `profile/*` existían desde la instalación de Breeze, pero las tres rutas nunca se añadieron a `web.php`. `GET /profile` devolvía **404**, y las vistas que llaman a `route('profile.update')` y `route('profile.destroy')` habrían lanzado `RouteNotFoundException`.

Es lo que hacía fallar los **5 tests de `ProfileTest`**, rojos desde el commit inicial.

**Arreglo:** registrar `profile.edit`, `profile.update` y `profile.destroy` dentro del grupo `auth`.

**Por qué se arregló fuera de su alcance natural:** `04-CONVENCIONES.md` §7 exige suite en verde para cerrar cualquier fase. Con estos 5 tests rojos, ninguna fase podría cerrarse nunca. Son 4 líneas de Breeze estándar, en el mismo archivo que esta fase ya reestructuraba.

### H-30 — `OrdenCompraItem` escribe timestamps que su tabla no tiene
**Descubierto durante la Fase 1.** *(Resuelto en la misma fase: bloquea el criterio de aceptación nº 4.)*

**Dónde:** `app/Models/OrdenCompraItem.php` · `database/migrations/2025_12_12_035503_create_orden_compra_items_table.php`

La migración creó la tabla **sin** `created_at`/`updated_at` (igual que el pivot), pero el modelo mantenía el comportamiento por defecto de Eloquent. Cada `OrdenCompraItem::create()` fallaba con:

```
SQLSTATE[42S22]: Column not found: 1054 Unknown column 'updated_at' in 'field list'
```

Junto con H-29, confirma que **la creación de órdenes de compra nunca llegó a completarse**.

**Arreglo:** `public $timestamps = false;` para alinear el modelo con el esquema existente.
Se comprobaron los nueve modelos de dominio: es el único con este desajuste. Si en la Fase 2 se decide añadir timestamps a los documentos históricos (H-25), se revierte esta línea.

### H-29 — `Proveedor` apunta a la tabla `proveedors`, que no existe
**Descubierto durante la Fase 1.** *(Resuelto en la misma fase: bloquea los criterios de aceptación 2, 3 y 4.)*

**Dónde:** `app/Models/Proveedor.php`

El modelo no declaraba `$table`. Eloquent pluraliza en inglés, así que `Proveedor` se convierte en **`proveedors`** — pero la tabla se llama `proveedores`. Toda consulta al modelo fallaba con:

```
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'laravel.proveedors' doesn't exist
```

`OrdenCompra` sí lo declaraba (`protected $table = "ordenes_compra"`); en `Proveedor` se olvidó. Es el único modelo del proyecto con este problema — se verificaron los diez.

**Alcance real:** el CRUD de proveedores **nunca llegó a funcionar**. Esto explica por qué ese flujo acumulaba tantos desajustes sin detectar (H-04, H-05, H-28): nadie pudo ejecutarlo jamás, así que los errores no salieron a la luz.

**Arreglo:** `protected $table = 'proveedores';`

**Prevención:** en español la pluralización automática de Eloquent no es fiable. Toda entidad del dominio debe declarar `$table` explícitamente — añadido a `04-CONVENCIONES.md`.

### H-28 — Falta la relación `Producto::proveedores()` que ya se usaba
**Descubierto durante la Fase 1.** *(Resuelto en la misma fase: bloquea el criterio de aceptación nº 3.)*

**Dónde:** `app/Http/Controllers/ProveedorProductoController.php:22` · `app/Models/Producto.php`
```php
$productos = Producto::whereDoesntHave('proveedores', ...)
```
El modelo `Producto` solo declaraba `categoria()`. La relación `proveedores()` **nunca existió**, así que el formulario de asignar un producto a un proveedor lanzaba `BadMethodCallException: Call to undefined relationship`.

Es un segundo punto de rotura del mismo flujo que H-04, pero de causa independiente: aunque el pivot hubiera tenido la columna correcta, el formulario habría fallado igual.

**Arreglo:** declarar `Producto::proveedores()` como inversa de `Proveedor::productos()`, con el mismo `withPivot()`.

### H-07 — `proveedores.show` devuelve 500
**Dónde:** `routes/web.php:43` · `app/Http/Controllers/ProveedorController.php`
`Route::resource` registra `proveedores.show`, pero el controlador no implementa `show()`.

**Arreglo:** implementar `show()` o excluir la ruta con `->except(['show'])`.

---

## 🟠 Altos

### H-08 — El contador del carrito siempre muestra 0
**Dónde:** `app/Providers/AppServiceProvider.php:31`
```php
if (Auth::check() && Auth::user()->carrito) {
```
`User` no define la relación `carrito()`, así que Eloquent devuelve `null` y la condición nunca se cumple. Además el carrito real vive en sesión (`CarritoController:15`), no en `user_id`. El badge no funciona ni para invitados ni para usuarios autenticados. Depende de H-11.

### H-09 — Modelos duplicados inglés/español
`Order`/`OrderItem` (venta al cliente) conviven con `OrdenCompra`/`OrdenCompraItem` (compra al proveedor). Conceptos **opuestos**, nombres casi idénticos, idiomas distintos, sin relación entre sí. Es la mayor fuente de confusión al leer el código.

**Arreglo:** renombrar a `Venta`/`VentaItem`, dejando `OrdenCompra` para el abastecimiento.

### H-10 — Las ventas no registran `user_id`
**Dónde:** `create_orders_table.php` · `CarritoController.php:106`
`orders` solo tiene `total` y timestamps. No hay forma de saber quién compró, ni de mostrar "Mis pedidos". Bloquea cualquier funcionalidad de cliente.

### H-11 — El carrito vive solo en sesión
**Dónde:** `CarritoController.php:13-21`
`user_id` existe en la tabla (y se hizo nullable en una migración de parche) pero **nunca se escribe**. Al iniciar sesión el carrito de invitado no se fusiona: el usuario pierde lo que había añadido.

### H-12 — Sin Form Requests
Las mismas reglas de validación están copiadas en `store` y `update` de `ProductoController` (5 reglas × 2) y `ProveedorController` (7 reglas × 2). Cambiar una regla obliga a tocar dos sitios y es fácil olvidarse de uno.

### H-13 — Sin capa de servicios
El total del carrito se calcula **tres veces**, en tres lenguajes distintos:
- `CarritoController.php:89` (PHP, `mostrarPago`)
- `CarritoController.php:103` (PHP, `procesarPago`)
- `carrito/index.blade.php:40` (Blade, dentro del bucle)

Si cambia la regla (IVA, descuento, `unidad_medida` por kg) hay que acordarse de los tres.

### H-14 — Sin roles: cualquier registrado es administrador
**Severidad elevada a 🔴 y adelantado a la Fase 3 el 2026-08-01.**

No existe distinción entre cliente y administrador: `User` no tiene campo de rol.

Antes de la Fase 1 esto quedaba tapado por un problema mayor (todo era público). Ahora que el acceso exige sesión, la consecuencia es concreta y comprobable: **cualquiera que se registre en `/register` obtiene acceso completo al panel de gestión** — crear y borrar productos, gestionar proveedores, recibir órdenes de compra.

Un cliente del supermercado no debería poder tocar nada de eso.

**Arreglo (Fase 3):** campo `rol` en `users`, middleware `admin`, Policies por recurso, y registro público que siempre crea clientes.

---

## 🟡 Medios

### H-15 — Cuatro sistemas de layout
`layouts/layout.blade.php` (el usado), `layouts/layoutCenter.blade.php`, `layouts/super.blade.php`, `components/super-layout.blade.php`, más `components/navbar.blade.php` que no se incluye en ninguna vista. Restos de los intentos abandonados descritos en `01-CONTEXTO.md`.

### H-16 — Bootstrap usado pero nunca cargado
`layouts/layout.blade.php:10` carga **Tailwind** por CDN y solo los **iconos** de Bootstrap — no su CSS. Sin embargo estas vistas están escritas íntegramente con clases Bootstrap (`container`, `card`, `btn`, `table`, `form-control`, `row`/`col-md-6`):
- `proveedores/index`, `create`, `edit`, `form`
- `ordenes/index`, `create`, `show`

**Esas siete pantallas se muestran sin estilos.**

### H-17 — Tailwind por CDN, Vite sin usar
`vite.config.js`, `tailwind.config.js`, `postcss.config.js` y `resources/css/app.css` están configurados, pero solo `layouts/super.blade.php` llama a `@vite`. El sitio real usa el CDN, que no purga clases, no permite personalizar el tema y no debe usarse en producción.

### H-18 — Marca inconsistente
`components/navbar.blade.php:7` dice **"PlazaKing"**; `layouts/navigation.blade.php:15` dice **"Tattos Market"**; los `<title>` dicen **"Supermercado"**.

### H-19 — Rutas duplicadas y sin agrupar
- `productos.index` definida dos veces: `web.php:21` (resource) y `web.php:92`
- FQN innecesario en `web.php:43` pese al `use` de la línea 7
- La ruta AJAX `web.php:82` cuelga de `OrdenCompraController` sin prefijo ni nombre
- Órdenes de compra escritas a mano en lugar de `Route::resource`

### H-20 — Route model binding inconsistente
`ProductoController::show($id)` usa ID crudo mientras `edit(Producto $producto)` usa binding. Igual en `ProveedorProductoController` (`$producto_id` suelto).

### H-25 — Inconsistencias de esquema
- `productos.precio` es `decimal(8,2)` pero `order_items.precio` y `orden_compra_items.precio` son `decimal(10,2)` → un producto de más de 999 999.99 se truncaría de forma distinta según la tabla
- Ningún modelo declara `$casts` para importes → se comparan y suman como strings/floats
- `proveedor_producto` sin índice único en `(proveedor_id, producto_id)` → se puede asignar el mismo producto dos veces al mismo proveedor
- `carrito_items` sin índice único en `(carrito_id, producto_id)`
- `ordenes_compra.estado` es string libre (`'pendiente'`, `'enviado'`, `'recibido'` solo en un comentario)
- `unidad_medida` existe en la BD pero **no está en el `$fillable` de `Producto`** → no se puede asignar
- `proveedor_producto` sin timestamps
- `add_categoria_id_to_productos_table.php` tiene el `down()` vacío → migración no reversible

---

## 🟢 Bajos

### H-21 — Google Sheets: tercera fuente de proveedores
**Replanteado el 2026-08-01: se retira en lugar de arreglarse.** *(Movido de la Fase 6 a la Fase 3.)*

Había **tres** fuentes de datos de proveedores compitiendo entre sí:

| Fuente | Integrada con órdenes y stock |
|---|---|
| Tabla `proveedores` + CRUD | ✅ sí — la única |
| Google Sheets A (`ProveedorSheetService`) | ❌ no |
| Google Sheets B (`ProveedorSheetController`, **otra URL**) | ❌ no |

Un proveedor que vive en una hoja de cálculo no tiene id en la base, así que **no puede recibir una orden de compra** ni relacionarse con productos o stock. La BD ya hace todo lo que hacen las hojas, y además funciona.

**Decisión:** eliminar ambas integraciones. Los proveedores se gestionan desde el panel de administración, con la BD como única fuente.

Problemas técnicos que esto elimina de paso:
**Dónde:** `app/Services/ProveedorSheetService.php` · `app/Http/Controllers/ProveedorSheetController.php`
- `ProductoController::show:118` hace una **petición HTTP a Google en cada carga de ficha de producto**
- Sin `Cache::remember()`, sin `timeout()` → si Google tarda, la tienda tarda
- Dos URLs distintas hardcodeadas en dos archivos, con **lógica de parseo CSV duplicada**
- El controlador hace `abort(500)` si Google falla: una dependencia externa tumba una página propia

### H-22 — Sin paginación
`ProductoController::index` y `ProveedorController::index` usan `->get()`. Con catálogo real (miles de SKU) la página se cae — el log ya registra dos `Allowed memory size exhausted`.

### H-23 — Seeders y factories ausentes
`DatabaseSeeder::run()` está vacío y **no invoca a `CategoriaSeeder`**, que sí existe y funciona. `php artisan migrate --seed` deja la base sin categorías, y sin categorías el filtro lateral y el alta de productos quedan inutilizables. No hay factories de `Producto`, `Proveedor` ni `Categoria`.

### H-24 — Sin tests de dominio
Solo están los de Breeze (auth y perfil). Cero cobertura de carrito, checkout, órdenes de compra y recepción de stock — justamente donde están H-03, H-04 y H-06.

### H-27 — Restos de andamiaje
- Carpeta anidada: la app real está en `supermercado_laravel/supermercado_laravel/`
- `package-lock.json` huérfano en la raíz externa, con `"packages": {}` vacío
- `storage/logs/laravel.log` versionado con trazas antiguas (ya cubierto por `.gitignore`)
