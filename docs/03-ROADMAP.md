# 03 — Roadmap por fases

Cada fase tiene **objetivo**, **alcance cerrado**, **checklist** y **criterio de aceptación**. No se pasa a la siguiente hasta cumplir el criterio.

> ⚠️ **El estado vivo de cada tarea está en el tablero, no aquí.**
> Tablero: https://github.com/users/PeterAr29/projects/1
> Este documento define **el porqué y el criterio de aceptación**; el tablero marca **qué está hecho**.
> Las casillas de más abajo son la referencia del alcance — se marcan en la issue, no en este archivo.

| Fase | Nombre | Issue | Hallazgos que cierra |
|---|---|---|---|
| 0 | Control de versiones y gestión | [#1](https://github.com/PeterAr29/supermercado-laravel/issues/1) ✅ | H-26 |
| 1 | Seguridad e integridad de datos | [#2](https://github.com/PeterAr29/supermercado-laravel/issues/2) ✅ | H-01…H-07, H-28…H-33 |
| 2 | Dominio unificado | [#3](https://github.com/PeterAr29/supermercado-laravel/issues/3) ✅ | H-08…H-11, H-25, H-36…H-38 |
| 3 | Paneles y roles | [#11](https://github.com/PeterAr29/supermercado-laravel/issues/11) ✅ | H-14, H-21, H-35, H-39…H-44 |
| 4 | Separación de capas (MVC real) | [#4](https://github.com/PeterAr29/supermercado-laravel/issues/4) | H-12, H-13, H-19, H-20 |
| 5 | Capa de presentación | [#5](https://github.com/PeterAr29/supermercado-laravel/issues/5) | H-15…H-18 |
| 6 | Robustez y calidad | [#6](https://github.com/PeterAr29/supermercado-laravel/issues/6) | H-22, H-23, H-24, H-34 |
| — | Decisión sobre carpeta anidada | [#7](https://github.com/PeterAr29/supermercado-laravel/issues/7) | H-27 |

### Cambio de plan — 2026-08-01

Tras repoblar el catálogo se replantea el roadmap por dos decisiones del responsable:

1. **Google Sheets se retira.** Había **tres** fuentes de proveedores compitiendo (la tabla `proveedores` y dos hojas publicadas distintas). Solo la BD está integrada con órdenes de compra y stock. H-21 pasa de "cachear Sheets" a "eliminar Sheets", y se mueve de la Fase 6 a la Fase 3.
2. **El supermercado necesita dos paneles**, uno de cliente y otro de administración. Se inserta la **Fase 3 — Paneles y roles**, y las fases 3-5 antiguas pasan a 4-6.

H-14 (roles) se adelanta de la antigua Fase 3 a la nueva, por urgencia: hoy cualquier registrado es administrador.

---

## Fase 0 — Control de versiones y gestión ✅

**Objetivo:** poder trabajar sin miedo. Ningún refactor es seguro sin historial.

**Por qué primero:** las fases 1-5 modifican migraciones, renombran modelos y borran vistas. Sin Git, un error obliga a rehacer el trabajo a mano.

### Checklist
- [x] Auditoría completa del proyecto → `02-HALLAZGOS.md`
- [x] Documentación de contexto → `01-CONTEXTO.md`
- [x] Roadmap por fases → este documento
- [x] Convenciones de trabajo → `04-CONVENCIONES.md`
- [x] `git init` + commit inicial con el estado actual íntegro (191 archivos, rama `main`)
- [x] Verificar que `.gitignore` excluye `.env`, `/vendor`, `/node_modules`, `/storage/logs`
- [x] `CHANGELOG.md` en la raíz
- [ ] Decidir qué hacer con la carpeta anidada y el `package-lock.json` huérfano (H-27) — *requiere decisión del responsable*

### Criterio de aceptación — ✅ cumplido
`git log` muestra el commit inicial, `git status` sale limpio y `git check-ignore .env` confirma que el `.env` no está versionado.

**Verificado el 2026-08-01:**
```
43259e4 docs(gestion): añade gestion del proyecto por fases (H-26)
08f2130 chore: commit inicial del estado actual del proyecto
```
`git status` limpio. `git check-ignore -v` confirma exclusión de `.env`, `vendor`, `node_modules` y `storage/logs/laravel.log`.

---

## Fase 1 — Seguridad e integridad de datos ✅

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

### Criterio de aceptación — ✅ cumplido
1. Como invitado, ninguna ruta de escritura responde 200.
2. Se crea un proveedor con contacto y **el contacto aparece** en el listado.
3. Se asigna un producto al proveedor con `precio_compra` y se guarda.
4. Se crea una orden de compra con **total distinto de 0**.
5. Se marca como recibida y **el stock del producto aumenta**.
6. Se borra un producto y las ventas anteriores conservan sus líneas y totales.

**Verificado el 2026-08-01 contra MySQL real: 22 comprobaciones, 0 fallos.**
Los datos de prueba se crearon dentro de una transacción revertida, sin dejar rastro.
`php artisan test`: **25 pasan, 0 fallan** (antes de la fase: 5 rojos por H-31).

### Hallazgos nuevos descubiertos durante la fase

Los seis bloqueaban criterios de aceptación, eran regresiones de esta misma fase o
impedían trabajar con seguridad, así que se resolvieron aquí. Detalle en `02-HALLAZGOS.md`.

| ID | Qué era | Impacto real |
|---|---|---|
| H-28 | Faltaba la relación `Producto::proveedores()` | Asignar productos a un proveedor lanzaba `BadMethodCallException` |
| H-29 | `Proveedor` apuntaba a la tabla `proveedors` | **Todo el CRUD de proveedores nunca funcionó** |
| H-30 | `OrdenCompraItem` escribía timestamps inexistentes | Crear órdenes de compra fallaba siempre |
| H-31 | Rutas de perfil de Breeze sin registrar | `/profile` daba 404; 5 tests en rojo |
| H-32 | Producto retirado rompía órdenes y falseaba el carrito | Regresión del propio H-02, detectada en la revisión del PR |
| H-33 | `php artisan test` apuntaba a la base de desarrollo | **Destruyó los datos de desarrollo antes de detectarse.** Ver H-34 |

**Lectura de fondo:** H-29 y H-30 demuestran que el módulo de proveedores y órdenes
de compra **jamás llegó a ejecutarse**. Los desajustes de H-04 y H-05 no se habían
detectado porque el flujo fallaba antes de llegar a ellos.

**Deriva de esquema detectada:** la columna `stock` existía en la base de desarrollo,
pero ninguna migración la creaba — se había añadido a mano. La migración de H-03 usa
`hasColumn()` para funcionar en ambos casos y dejar esquema y migraciones alineados.

---

## Fase 2 — Dominio unificado ✅

**Objetivo:** un solo vocabulario y un `User` conectado a sus compras.

**Por qué antes del panel:** el panel de cliente ("Mis pedidos") necesita que las ventas sepan a quién pertenecen. Hoy no lo saben.

### Checklist

**Bloque A — Renombrado** *(H-09)*
- [ ] `Order` → `Venta`, `OrderItem` → `VentaItem`
- [ ] Migración de renombrado de tablas: `orders` → `ventas`, `order_items` → `venta_items`
- [ ] Actualizar `CarritoController::procesarPago`, vistas `pago/*` y rutas
- [ ] Añadir `VentaItem::producto()` (con `withTrashed()`, ver H-32) y `OrdenCompraItem::orden()`

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
- [ ] `unidad_medida` al `$fillable` de `Producto` (hoy `ProductoSeeder` la asigna fuera de él)
- [ ] Timestamps en el pivot

### Criterio de aceptación — ✅ cumplido
No queda ninguna referencia a `Order`/`OrderItem`. Un usuario autenticado compra, cierra sesión, vuelve a entrar y **su venta sigue asociada a él**. El badge del carrito muestra la cantidad correcta como invitado y como usuario.

**Verificado el 2026-08-02 contra MySQL real:**
- Esquema: **11 comprobaciones, 0 fallos**
- Dominio: **23 comprobaciones, 0 fallos** (carrito de invitado, fusión al iniciar sesión, venta asociada, relaciones, enums)
- Vistas y enums: **11 comprobaciones, 0 fallos** (render HTTP 200 de las 5 vistas afectadas)
- `php artisan test`: **25 pasan, 0 fallan**

Los datos de prueba se crearon dentro de transacciones revertidas.

### Hallazgos nuevos descubiertos durante la fase

| ID | Qué era | Impacto real |
|---|---|---|
| H-36 | `eliminar()` no comprobaba de quién era la línea | Con solo conocer un id se le vaciaba el carrito a otra persona |
| H-37 | El cast a enum rompió comparaciones y vistas | **Una orden se podía recibir dos veces**, duplicando stock; y las vistas daban error fatal |
| H-38 | El select de unidad de medida nunca guardaba | Todo producto creado desde la app se quedaba en `und` aunque se eligiera `kg` |

**Lectura de fondo:** H-37 repite el patrón de H-32 en la Fase 1. Añadir un cast
(o un trait como `SoftDeletes`) **no es un cambio local**: obliga a revisar cada
comparación y cada impresión del campo afectado. Recogido en `04-CONVENCIONES.md`.

**Sobre H-36:** la Fase 1 miró *quién puede entrar* a cada ruta, no *sobre qué datos*
puede actuar quien ya entró. Esa segunda pregunta es la que responde la Fase 3 con
las Policies.

---

## Fase 3 — Paneles y roles ✅

**Objetivo:** que el supermercado tenga **dos caras**: la tienda para el cliente y el panel de gestión para el administrador. Y que el inventario refleje de verdad lo que entra y lo que sale.

**Por qué existe esta fase:** decidida el 2026-08-01. La Fase 1 cerró la puerta a los anónimos, pero dejó a **cualquier registrado con acceso total de administrador** (H-14). Además, el inventario solo cuenta la mitad de la historia: las órdenes de compra suman stock, pero **las ventas no lo descuentan** (H-35).

### Bloque A — Roles y separación *(H-14)* 🔴
- [ ] Campo `rol` en `users` (`cliente` | `admin`) + enum + `$casts`
- [ ] `UserSeeder` marca al administrador
- [ ] Middleware `admin` sobre la zona de gestión
- [ ] `ProductoPolicy`, `ProveedorPolicy`, `OrdenCompraPolicy`
- [ ] Registrarse en `/register` crea un **cliente**, nunca un administrador

### Bloque B — Panel de cliente
- [ ] `/mi-cuenta` — perfil, dirección, datos
- [ ] `/mis-pedidos` — historial de ventas del usuario (necesita `ventas.user_id` de la Fase 2)
- [ ] `/mis-pedidos/{venta}` — detalle de una compra
- [ ] El cliente **nunca** ve enlaces de gestión

### Bloque C — Panel de administrador
- [ ] Prefijo `/admin` con su propio layout y navegación
- [ ] Dashboard: ventas del día, productos bajo mínimo, órdenes pendientes
- [ ] Gestión de productos, categorías y proveedores (mover ahí lo que hoy cuelga de la raíz)
- [ ] Gestión de órdenes de compra
- [ ] Namespaces `Controllers/Admin/` y `Controllers/Tienda/`

### Bloque D — Entradas y salidas de inventario *(H-35)* 🔴
- [ ] **La venta descuenta stock** — hoy `procesarPago` no lo toca
- [ ] Validar stock disponible antes de confirmar la venta
- [ ] Tabla `movimientos_inventario`: producto, tipo (`entrada`/`salida`/`ajuste`), cantidad, motivo, documento de origen, usuario, fecha
- [ ] Registrar el movimiento en ambos flujos: recepción de orden (entrada) y venta (salida)
- [ ] Ajuste manual de stock desde el panel, con motivo obligatorio
- [ ] Vista de kardex por producto: de dónde viene y a dónde va cada unidad

### Bloque E — Retirar Google Sheets *(H-21)*
- [ ] Eliminar `ProveedorSheetService` y `ProveedorSheetController`
- [ ] Eliminar la vista `proveedores/sheet.blade.php` y su ruta
- [ ] Quitar el bloque de proveedores de `productos/show`, o servirlo desde la BD
- [ ] La BD queda como **única fuente** de proveedores

### Criterio de aceptación — ✅ cumplido
1. Un usuario recién registrado **no** puede entrar en `/admin` ni ver enlaces de gestión.
2. El administrador entra en `/admin` y gestiona productos, proveedores y órdenes.
3. El cliente ve **sus** pedidos en `/mis-pedidos`, y solo los suyos.
4. Una venta **descuenta stock** y deja un movimiento de tipo `salida`.
5. Recibir una orden **suma stock** y deja un movimiento de tipo `entrada`.
6. El kardex de un producto cuadra: `stock actual = entradas − salidas ± ajustes`.
7. No queda ninguna referencia a `docs.google.com` en el código.

**Verificado el 2026-08-02 contra MySQL real: 124 comprobaciones, 0 fallos.**
- Roles y policies: **27 comprobaciones** (enum, `$fillable`, 403 del cliente en cada zona)
- Inventario: **37 comprobaciones** (entrada, salida, ajuste, kardex cuadrado, venta sin stock que no se escribe a medias)
- Paneles: **60 comprobaciones** (13 pantallas de `/admin` en 200, panel de cliente, H-39, H-40, H-42)

Los datos de prueba se crearon dentro de transacciones revertidas.
`php artisan test`: **25 pasan, 0 fallan**.

### Lo que quedó fuera

El criterio de aceptación se cumple entero, pero **dos casillas del alcance no**.
Se dejan sin marcar en la issue #11 en vez de darlas por buenas:

| Sin hacer | Estado real |
|---|---|
| Gestión de **categorías** desde el panel | Hay CRUD de productos y proveedores, pero no existe `CategoriaController`: las categorías solo se crean con `CategoriaSeeder` |
| **Dirección** en `/mi-cuenta` | La pantalla muestra nombre, correo, tipo de cuenta, pedidos y total gastado; `users` no tiene columna de dirección |

Ninguna bloquea el criterio, y por eso la fase se cierra. La dirección del cliente
tendrá que decidirse junto con el envío —hoy el pago es simulado y no hay entrega
que direccionar—, así que va al backlog. La gestión de categorías es un CRUD más
del panel y **entra en la Fase 4**, que ya toca esa capa.

### Hallazgos nuevos descubiertos durante la fase

| ID | Qué era | Impacto real |
|---|---|---|
| H-42 | El formulario de producto nunca enviaba `stock` | **Todo producto creado desde la app nacía con 0 unidades** |
| H-43 | El botón "Agregar al carrito" de la ficha no enviaba `producto_id` | Ese botón **nunca funcionó**; el del listado sí, y por eso pasó desapercibido |
| H-44 | `Route::resource` generaba `{proveedore}` | **Editar un proveedor devolvía 500**, y `update()`/`destroy()` no hacían nada en silencio |

**Lectura de fondo:** H-44 es la tercera factura del mismo error — dejar que
Laravel singularice o pluralice en español (H-29 con la tabla, H-30 con los
timestamps, H-44 con el parámetro de ruta). Y los tres se descubrieron *al usar*
la pantalla, no al leer el código.

**Sobre el alcance:** `InventarioService` pertenecía a la Fase 4 (H-13). Se
adelanta porque H-35 lo necesita en dos flujos —venta y recepción— y escribir
el descuento de stock dos veces para unificarlo después habría sido trabajo
tirado. La Fase 4 construirá `CheckoutService` y `OrdenCompraService` encima.

---

## Fase 4 — Separación de capas (MVC real) ⬜

**Objetivo:** que el controlador solo coordine. Es la fase que da el "orden" estructural.

> Antes era la Fase 3. Se desplaza al insertarse "Paneles y roles".
> H-14 sale de aquí: se adelanta a la Fase 3 por urgencia.

### Checklist

**Bloque A — Validación** *(H-12)*
- [ ] `StoreProductoRequest` / `UpdateProductoRequest`
- [ ] `StoreProveedorRequest` / `UpdateProveedorRequest`
- [ ] `StoreOrdenCompraRequest`, `AgregarAlCarritoRequest`
- [ ] Eliminar todo `$request->validate()` de los controladores

**Bloque B — Servicios** *(H-13)*
- [ ] `CarritoService`: obtener, agregar, eliminar, vaciar, **calcular total (fuente única)**
- [ ] `CheckoutService`: crear venta + descontar stock + registrar movimiento, en transacción
- [ ] `OrdenCompraService`: crear orden y recepcionar con reposición
- [ ] `InventarioService`: único punto que modifica stock y escribe movimientos
- [ ] Vistas y controladores consumen el total del servicio, nunca lo recalculan

**Bloque C — Rutas y binding** *(H-19, H-20)*
- [ ] Eliminar la ruta duplicada `productos.index` *(ya hecho en la Fase 1)*
- [ ] `Route::resource` para órdenes de compra
- [ ] La ruta AJAX pasa a `routes/api.php` o a un prefijo `ajax/` con nombre
- [ ] Route model binding en **todos** los métodos (`show(Producto $producto)`)
- [ ] Todo `Route::resource` con nombre en español declara `parameters()` *(H-44)*

**Bloque D — Lo que arrastra la Fase 3**
- [ ] **Gestión de categorías** en `/admin`: era alcance de la Fase 3 y no se hizo.
      Hoy solo existen las que crea `CategoriaSeeder`

### Criterio de aceptación
Ningún controlador supera **15 líneas por método**. `grep -rn "validate(" app/Http/Controllers/` no devuelve nada. El total del carrito se calcula en **un solo lugar**, y el stock se modifica **solo** desde `InventarioService`.

---

## Fase 5 — Capa de presentación ⬜

**Objetivo:** un solo sistema visual. Hoy hay siete pantallas literalmente sin estilos.

> Antes era la Fase 4.

### Checklist
- [ ] **Decidir framework: Tailwind** (ya configurado con Vite) y documentarlo en `04-CONVENCIONES.md`
- [ ] Migrar a Tailwind las 7 vistas escritas en Bootstrap *(H-16)*: `proveedores/{index,create,edit,form}`, `ordenes/{index,create,show}`
- [ ] Sustituir el CDN de Tailwind por `@vite` *(H-17)*
- [ ] Layout de tienda y layout de panel, ambos sobre la misma base; eliminar `layoutCenter`, `super`, `super-layout`, `navbar` *(H-15)*
- [ ] Unificar la marca y el `<title>` *(H-18)*
- [ ] Componentes Blade: `<x-producto-card>`, `<x-alerta-flash>`, `<x-tabla>` (hoy duplicados en 6 vistas)
- [ ] Sacar el cálculo de totales de `carrito/index.blade.php`

### Criterio de aceptación
`npm run build` genera los assets, ninguna vista referencia un CDN, y las siete pantallas de proveedores/órdenes se ven con estilos.

---

## Fase 6 — Robustez y calidad ⬜

**Objetivo:** que aguante datos y uso reales.

> Antes era la Fase 5. H-21 sale de aquí: Google Sheets se retira en la Fase 3 en lugar de arreglarse.

### Checklist
- [ ] Paginación en productos y proveedores *(H-22)*
- [ ] Factories de `Producto`, `Categoria`, `Proveedor` *(H-23; los seeders ya están hechos)*
- [ ] Copia de seguridad previa a cada fase, automatizada *(H-34)*
- [ ] Tests de feature *(H-24)*:
  - [ ] agregar al carrito
  - [ ] el checkout descuenta stock y registra el movimiento
  - [ ] recibir una orden repone stock y registra el movimiento
  - [ ] un invitado no puede borrar productos
  - [ ] un cliente no puede entrar en `/admin`
- [ ] `php artisan pint` sobre todo el código

### Criterio de aceptación
`php artisan migrate:fresh --seed` deja una base usable. `php artisan test` pasa en verde con al menos 5 tests de dominio nuevos.

---

## Fuera de alcance (backlog)

Ideas válidas que **no** entran en estas fases, para no dispersar el trabajo:

- Pasarela de pago real (hoy el pago es simulado)
- Subida de imágenes a `storage` (hoy `imagen` es una URL de texto)
- Búsqueda en tiempo real (el comentario ya está en `layouts/navigation.blade.php:22`)
- Reportes avanzados y exportación a Excel/PDF
- API REST para app móvil (`routes/api.php` está vacío)
- Gestión de ofertas y promociones (el carrusel del home es una imagen fija)
- Alertas de stock mínimo por correo
- **Dirección del cliente en `/mi-cuenta`** — era alcance de la Fase 3 y no se hizo.
  Sale del roadmap porque no se decide sola: sin entrega ni pago real, una dirección
  es un campo que nadie lee. Vuelve cuando entre el envío
