# Changelog

Registro de cambios del proyecto. Cada fase cerrada del [roadmap](docs/03-ROADMAP.md) genera una entrada.

Formato basado en [Keep a Changelog](https://keepachangelog.com/es-ES/1.1.0/).

## [No publicado]

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

| Fase | Nombre | Estado |
|---|---|---|
| 3 | Paneles y roles | ⬜ Pendiente |
| 4 | Separación de capas (MVC real) | ⬜ Pendiente |
| 5 | Capa de presentación | ⬜ Pendiente |
| 6 | Robustez y calidad | ⬜ Pendiente |
