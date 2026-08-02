# Changelog

Registro de cambios del proyecto. Cada fase cerrada del [roadmap](docs/03-ROADMAP.md) genera una entrada.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/).

## [No publicado]

_Nada todavía._

---

## [1.0.0] — 2026-08-02

**Primera versión publicada.** Cierra el reordenamiento completo: ocho fases (0→7) y los
51 hallazgos de la auditoría, ninguno abierto.

Lo que había el 2026-08-01 era un catálogo que cualquier visitante anónimo podía borrar,
con las ventas sin dueño, el stock que solo subía, cuatro marcas distintas según la
pantalla y un módulo de proveedores que **nunca había llegado a ejecutarse**. Lo que hay
ahora es una tienda con dos paneles, inventario con kardex, capas separadas y 63 tests.

El detalle, fase a fase, debajo.

### Fase 7 — Lo que solo se ve en pantalla — 2026-08-02

**Cambiado**
- **Cada rol aterriza donde trabaja** (`H-48`): el administrador en `/admin`, el cliente en la tienda. Los dos caían en `/dashboard`, el marcador de posición de Breeze —«Dashboard» y «You're logged in!»—, en inglés y sin nada detrás. La decisión vive en `User::rutaDeInicio()`
- **Todo lo que imprimía el framework pasa a español** (`H-49`): la paginación, los mensajes de validación, y las pantallas de acceso, registro, perfil, recuperación y verificación
- **El kardex distingue quién movió el stock** (`H-51`): «Sistema» para los asientos que escribe un seeder, «Invitado» solo para la compra de quien no tiene cuenta

**Eliminado**
- `resources/views/dashboard.blade.php` y la ruta `/dashboard`
- El campo «¿Qué debemos considerar al comprar este producto?» de la ficha (`H-50`), que quedaba **fuera** del formulario: el cliente escribía su indicación y se evaporaba sin avisar. Se retira por el mismo argumento con el que la dirección del cliente salió del roadmap — sin nadie que prepare el pedido, esa nota no la lee ningún humano

**Notas**
- Los cuatro salieron de **arrancar la aplicación y mirarla**, con el roadmap ya dado por terminado y 49 tests en verde. Las cuatro pantallas respondían `200` y hacían lo que su código decía
- **H-49 era el doble de grande, y lo dijo el test.** La paginación tiene sus cadenas en dos sitios: `pagination.php` y las claves JSON del propio paginador. Traducido solo el primero, el texto seguía en inglés
- Los mensajes empiezan por «El campo :attribute»: el sustituto se inserta sin artículo y en minúscula, y sin él salía «producto es obligatorio.»
- **H-51 no era un error siempre**: «Invitado» era correcto para la compra de un invitado y falso solo para el asiento de apertura
- `/dashboard` era la única ruta con el middleware `verified`. Ya no la usa nadie, pero no cambia nada: `User` no implementa `MustVerifyEmail`, así que ese middleware ya dejaba pasar a todo el mundo
- ⚠️ Si tenías `.env` propio, añade `APP_LOCALE=es`
- Verificado: `php artisan test` **63 pasan, 164 aserciones** (eran 49) · `./vendor/bin/pint --test` 170 archivos limpios · y **el recorrido en navegador de las 21 pantallas**, que es como salieron

---

### Estructura — Se aplana la carpeta anidada — 2026-08-02

**Cambiado**
- **La raíz del proyecto es ya la raíz del repositorio** (`H-27`). La app vivía en `supermercado_laravel/supermercado_laravel/`, un nivel por debajo de donde estaba el repositorio

**Eliminado**
- El `package-lock.json` huérfano de la raíz externa, con `"packages": {}` vacío. Chocaba de nombre con el real, que sí tiene dependencias

**Notas**
- ⚠️ **Toda ruta absoluta al proyecto pierde un nivel.** Accesos directos, configuración del IDE y cualquier tarea programada que apuntara a la carpeta interna hay que reapuntarlos
- Se comprobó **antes de mover nada** que los directorios se podían renombrar: el *language server* de PHP de VS Code mantiene ficheros abiertos y en Windows eso bloquea renombrados. Se movió entrada por entrada, no la carpeta entera, para que un fallo dejara cada cosa intacta en su sitio
- `php artisan optimize:clear` es obligatorio tras el traslado: las vistas compiladas guardan la ruta absoluta de su `.blade.php`
- Git no se ve afectado —`.git` viaja con el resto y el historial queda intacto—; lo que cambia es dónde está el repositorio
- Verificado: `php artisan test` **49 pasan** · `npm run build` reconstruye los assets · `/`, `/productos` y `/carrito` sirven HTML con productos reales y el enlace a la página 2

---

### Fase 6 — Robustez y calidad — 2026-08-02

**Añadido**
- **24 tests de feature del dominio** (`H-24`). Había 25, todos de Breeze: carrito, checkout, órdenes de compra y control de acceso no tenían ninguno. Ahora 49
- **Factories** de `Categoria`, `Producto` y `Proveedor` (`H-23`), con estados que nombran el caso: `sinStock()`, `conStock(3)`, `bajoMinimo()`, `retirado()`, `inactivo()`. `UserFactory` gana `admin()` y `cliente()`
- **`php artisan db:respaldo --fase=N`** (`H-34`). El paso 0 de cada fase era una línea de `mysqldump` copiada a mano de la documentación. Se niega a pisar un volcado existente sin `--forzar`, y la contraseña viaja por `MYSQL_PWD` en vez de por la línea de comandos

**Cambiado**
- **El catálogo de la tienda pagina** de 12 en 12 (`H-22`), conservando buscador y filtro al cambiar de página. El panel ya paginaba desde la Fase 4; la tienda, que es la parte con miles de SKU, seguía trayéndolo todo con `->get()`

**Corregido**
- `04-CONVENCIONES.md` pedía `php artisan pint` como paso de cierre de fase desde la Fase 0. Ese comando no existe —Pint es un binario, `./vendor/bin/pint`—, así que el paso llevaba seis fases sin poder ejecutarse tal y como estaba escrito

**Notas**
- Media checklist de la fase ya estaba hecha sin que el roadmap lo supiera: la paginación del panel llegó con la Fase 4 y `DatabaseSeeder` se completó al repoblar el catálogo tras H-33. Ambas cosas se arreglaron **de paso**, en fases de otro alcance, y nadie volvió a tocar el roadmap
- `migrate:fresh --seed` se verificó contra `laravel_testing`, no contra la base de desarrollo: mismas migraciones y mismos seeders, sin arriesgar los datos. Deja 2 usuarios, 8 categorías, 40 productos, 6 proveedores y el kardex de apertura
- Verificado: `php artisan test` **49 pasan, 128 aserciones, 0 fallos**. `./vendor/bin/pint --test`: 163 archivos limpios
- **Con esta fase el roadmap queda completo.** Lo siguiente sale del backlog, y eso es una decisión, no una tarea

---

### Fase 5 — Capa de presentación — 2026-08-02

**Cambiado**
- **Un solo layout base** (`H-15`). Convivían cinco cabeceras distintas: `layout`, `admin`, `layoutCenter`, `super` y `guest`
- **Todo el CSS y el JS salen de Vite** (`H-17`). Tailwind entraba por el CDN de play, que compila en el navegador en cada carga; Swiper y bootstrap-icons por jsdelivr; la fuente de las pantallas de acceso, por fonts.bunny.net
- **Las diez pantallas escritas en Bootstrap pasan a Tailwind** (`H-16`). Usaban `btn`, `card`, `form-control`, `table-striped`… pero Bootstrap no se cargaba en ninguna parte: se veían como HTML sin estilos
- **Una sola marca**, desde `config('app.name')` (`H-18`). Convivían "PlazaKing", "Tattos Market", "Supermercado" y "Laravel" según la pantalla

**Añadido**
- Componentes Blade: `x-alerta-flash`, `x-producto-card` y `x-campo`
- Swiper como dependencia de npm, arrancado desde `resources/js/app.js`
- El catálogo de cada proveedor muestra el **margen** por producto

**Eliminado**
- `layouts/layoutCenter`, `layouts/super`, `components/super-layout`, `components/navbar` y `welcome`, que ya no usaba nadie

**Corregido**
- **El perfil y el dashboard se pintaban vacíos** (`H-47`): `AppLayout` apuntaba a un layout que solo tenía `@yield('content')` y nunca imprimía `$slot`
- El botón "Agregar" del home tampoco enviaba `producto_id` (`H-43`, que la Fase 3 dio por cerrado arreglando solo la ficha)

**Notas**
- ⚠️ Arrancar el proyecto ahora exige `npm install && npm run build`: `public/build` no se versiona
- H-47 llevaba meses oculto detrás de un test en verde que solo comprobaba `assertOk()`. Una pantalla vacía responde `200`. Por eso la verificación de esta fase mira **el HTML de 26 pantallas**, no su código de estado
- Verificado: **215 comprobaciones, 0 fallos** — 51 de la fase y 164 de no-regresión. `php artisan test`: 25 pasan

---

### Fase 4 — Separación de capas (MVC real) — 2026-08-02

**Cambiado**
- La validación sale de los controladores a **nueve Form Requests**, que además autorizan (`H-12`)
- Las reglas de negocio salen a **cuatro servicios** (`H-13`): `CarritoService`, `CheckoutService`, `OrdenCompraService` y `PanelService`, todos sobre el `InventarioService` de la Fase 3
- El total del carrito se calcula en **un solo sitio**. Estaba en tres, uno de ellos dentro de la tabla de `carrito/index.blade.php`
- Ningún método de controlador de dominio supera las 15 líneas. El mayor tenía 62
- `Route::resource` para órdenes de compra, y la ruta AJAX pasa al prefijo `ajax/` con nombre (`H-19`)
- Route model binding en todos los métodos: `ProveedorProductoController` recibía `$producto_id` suelto (`H-20`)
- La vista genera la URL del AJAX desde la ruta con nombre, no escrita a mano

**Añadido**
- **Gestión de categorías** en `/admin`, con su política. Era alcance de la Fase 3 y se había quedado fuera
- `CarritoItem::subtotal`, para que ninguna vista multiplique
- Excepciones de dominio `CarritoVacioException` y `ProductoNoAsignadoException`

**Corregido**
- La codificación de 4 vistas, que rompió el refactor de la Fase 3 y se fusionó sin detectar (`H-45`)
- El RUC del proveedor pasa a ser único y de 11 dígitos: `numeric` aceptaba `5` y `-3` (`H-46`)
- Borrar un proveedor con órdenes, o una categoría con productos, avisa en vez de reventar contra la clave foránea

**Notas**
- H-45 pasó las 60 comprobaciones de la Fase 3 porque todas preguntaban por el código de estado HTTP, y una página con la codificación destrozada responde 200. Una verificación solo cubre lo que pregunta
- Verificado contra MySQL real: **164 comprobaciones, 0 fallos** — 40 de la fase y 124 de no-regresión de las fases 1-3. `php artisan test`: 25 pasan

---

### Fase 3 — Paneles y roles — 2026-08-02

**Seguridad**
- Hay dos roles, `cliente` y `admin`. Hasta hoy cualquiera que se registrara entraba con acceso total de gestión (`H-14`)
- La zona de gestión vive en `/admin` detrás del middleware `admin`; el cliente recibe 403
- Policies de `Producto`, `Proveedor`, `OrdenCompra` y `Venta`. La de `Venta` pregunta por la propiedad: un pedido ajeno devuelve 403
- Registrarse en la tienda crea **siempre** un cliente; `rol` no es asignable en masa

**Añadido**
- Panel de administración con layout propio: resumen del día, gestión de productos, inventario, proveedores y órdenes
- Panel de cliente: `/mi-cuenta`, `/mis-pedidos` y el detalle de cada pedido
- **Movimientos de inventario** (`H-35`): tabla `movimientos_inventario` con tipo, cantidad con signo, stock resultante, motivo, documento de origen y usuario
- `InventarioService`, único punto que modifica `productos.stock`
- Kardex por producto y ajuste manual con motivo obligatorio
- `productos.stock_minimo` y aviso de reposición en el panel
- Namespaces `Controllers/Admin/` y `Controllers/Tienda/`

**Cambiado**
- **La venta descuenta stock.** Antes el inventario solo subía (`H-35`)
- Se comprueba el stock al añadir al carrito y antes de la pantalla de pago
- La recepción de órdenes deja de hacer `increment()` directo
- Google Sheets se retira: la base de datos es la única fuente de proveedores (`H-21`)

**Corregido**
- Un usuario ya no puede acabar con dos carritos a la vez (`H-39`)
- Asignar dos veces el mismo producto a un proveedor devuelve un mensaje, no un 500 (`H-40`)
- El carrito ya no acepta productos retirados del catálogo (`H-41`)
- El formulario de producto por fin envía el stock: antes todo producto creado desde la app nacía con 0 unidades (`H-42`)
- El botón "Agregar al carrito" de la ficha de producto, que nunca funcionó (`H-43`)
- Editar un proveedor ya no devuelve 500: `Route::resource` generaba `{proveedore}`, que no casaba con el argumento del controlador (`H-44`)

**Notas**
- H-44 es la tercera vez que el proyecto paga por dejar que Laravel adivine plurales en español (H-29, H-30, H-44)
- `InventarioService` se adelanta desde la Fase 4: H-35 lo necesita en dos flujos
- Verificado contra MySQL real: 124 comprobaciones en tres bloques, 0 fallos. `php artisan test`: 25 pasan

---

### Fase 2 — Dominio unificado — 2026-08-02

**Cambiado**
- `Order`/`OrderItem` pasan a `Venta`/`VentaItem`; tablas `orders`→`ventas`, `order_items`→`venta_items` (`H-09`)
- Todos los importes son `decimal(10,2)` con casts `decimal:2` (`H-25`)
- `estado` y `unidad_medida` pasan a enums de PHP 8.1 (`H-25`)

**Añadido**
- `ventas.user_id`: las compras ya saben a quién pertenecen (`H-10`)
- El carrito persiste por usuario y el de invitado se fusiona al iniciar sesión (`H-11`)
- Índices únicos en `proveedor_producto` y `carrito_items`, y timestamps en el pivot (`H-25`)
- Relaciones que faltaban: `User::ventas()`, `User::carrito()`, `VentaItem::producto()`, `OrdenCompraItem::orden()`

**Corregido**
- El contador del carrito ya no muestra siempre 0 (`H-08`)
- Solo se pueden borrar líneas del propio carrito (`H-36`)
- Una orden ya no se puede recibir dos veces duplicando stock (`H-37`)
- El select de unidad de medida por fin guarda lo que se elige (`H-38`)

**Notas**
- H-37 repite el patrón de H-32: añadir un cast o un trait no es un cambio local
- Verificado contra MySQL real: 45 comprobaciones en tres bloques, 0 fallos. `php artisan test`: 25 pasan

---

### Fase 1 — Seguridad e integridad de datos — 2026-08-01

**Seguridad**
- Las rutas de escritura exigen sesión iniciada. Antes, un visitante anónimo podía borrar el catálogo, crear proveedores y reponer stock (`H-01`)

**Corregido**
- Borrar un producto ya no destruye el historial de ventas: `order_items` pasa de `CASCADE` a `RESTRICT` y `Producto` usa `SoftDeletes` (`H-02`)
- La recepción de órdenes ya no falla: se crea la columna `stock` que el código escribía (`H-03`)
- El precio de compra del proveedor se unifica como `precio_compra` en migración, modelos, controlador y vista (`H-04`)
- Los datos de contacto del proveedor dejan de perderse en silencio: `contacto` se divide en `contacto_nombre` y `contacto_telefono` (`H-05`)
- La creación de órdenes usa `DB::transaction()` con rollback automático; `recibir()` también (`H-06`)
- `proveedores.show` ya no devuelve 500: se excluye del resource (`H-07`)
- Añadida la relación `Producto::proveedores()`, que el controlador ya invocaba (`H-28`)
- `Proveedor` declara su tabla: apuntaba a `proveedors`, inexistente (`H-29`)
- `OrdenCompraItem` deja de escribir timestamps que su tabla no tiene (`H-30`)
- Registradas las rutas de perfil de Breeze: `/profile` devolvía 404 (`H-31`)
- Un producto retirado ya no rompe la recepción de órdenes ni deja pagar S/ 0 en el carrito (`H-32`)
- La suite de tests apunta a una base aparte (`laravel_testing`): antes borraba la base de desarrollo en cada ejecución (`H-33`)

**Notas**
- H-29 y H-30 revelan que el módulo de proveedores y órdenes de compra nunca llegó a ejecutarse
- La columna `stock` existía en la base de desarrollo sin migración que la creara; la migración de H-03 reconcilia ambos casos
- Verificado contra MySQL real: 22 comprobaciones, 0 fallos. `php artisan test`: 25 pasan (antes 5 rojos)
- ⚠️ H-33 se detectó tarde: la suite ya había destruido los ~43 productos y los usuarios de la base de desarrollo, sin copia de seguridad posible. Las categorías se regeneraron con `CategoriaSeeder`. Medidas preventivas en H-34

---

### Fase 0 — Control de versiones y gestión — 2026-08-01

**Añadido**
- Control de versiones con Git (`H-26`)
- Documentación de gestión en `docs/`:
  - `01-CONTEXTO.md` — arquitectura actual y reconstrucción de cómo se construyó el proyecto
  - `02-HALLAZGOS.md` — 27 hallazgos auditados con severidad e ID
  - `03-ROADMAP.md` — 6 fases con alcance y criterio de aceptación
  - `04-CONVENCIONES.md` — reglas de nomenclatura, capas, esquema, front-end y Git
- Este `CHANGELOG.md`

**Sin cambios de código.** La fase 0 documenta y versiona; no modifica el comportamiento de la aplicación.

---

## Fases pendientes

Ninguna. Las fases 0 a 7 del [roadmap](docs/03-ROADMAP.md) están cerradas, y con
H-27 y H-51 resueltos **no queda ningún hallazgo abierto** de los 51 registrados.

Lo que venga ahora sale del backlog, al final del roadmap.

---

[No publicado]: https://github.com/PeterAr29/supermercado-laravel/compare/v1.0.0...main
[1.0.0]: https://github.com/PeterAr29/supermercado-laravel/releases/tag/v1.0.0
