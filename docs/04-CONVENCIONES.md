# 04 — Convenciones

Reglas para que el proyecto no vuelva al estado descrito en `02-HALLAZGOS.md`. El desorden actual no viene de errores puntuales, sino de **decisiones tomadas dos veces de forma distinta**.

## 1. Idioma

| Ámbito | Idioma | Ejemplo |
|---|---|---|
| Dominio (modelos, tablas, columnas, rutas) | **Español** | `Producto`, `ordenes_compra`, `precio_compra` |
| Laravel / Breeze / paquetes | Inglés (no tocar) | `User`, `password_reset_tokens` |
| Métodos de controlador de recurso | Inglés (convención Laravel) | `index`, `store`, `update`, `destroy` |
| Comentarios y documentación | Español | |

**Nunca** mezclar en el mismo concepto: `Order` + `OrdenCompra` fue exactamente ese error (H-09).

## 2. Nomenclatura

- **Modelos:** singular, `PascalCase` → `Producto`, `OrdenCompra`
- **Tablas:** plural, `snake_case` → `productos`, `ordenes_compra`
- **Todo modelo del dominio declara `$table` explícitamente.** Eloquent pluraliza en
  inglés: `Proveedor` → `proveedors`, no `proveedores`. Dejarlo al automatismo costó
  que el CRUD de proveedores nunca funcionara (H-29)
- **Pivots:** singular alfabético → `proveedor_producto`
- **Claves foráneas:** `<modelo_singular>_id` → `producto_id`, `proveedor_id`
- **Rutas nombradas:** `recurso.accion` → `productos.index`, `ordenes.recibir`
- **Todo `Route::resource` con nombre en español declara `parameters()`.** Laravel
  singulariza en inglés: `proveedores` → `{proveedore}`, que no casa con el
  argumento `Proveedor $proveedor` del controlador. El binding no falla: entrega
  un **modelo vacío**, y `update()`/`destroy()` dejan de hacer nada sin avisar (H-44)
- **Vistas:** `recurso/accion.blade.php` en plural → `productos/index.blade.php`

**Un concepto, un nombre, en toda la pila.** Si el pivot guarda `precio_compra`, entonces la migración, el `withPivot()`, el controlador y el JS de la vista dicen `precio_compra`. Sin excepciones (H-04).

## 3. Responsabilidad por capa

| Capa | Sí hace | No hace |
|---|---|---|
| **Ruta** | Mapear URL → controlador, aplicar middleware | Lógica |
| **Form Request** | Validar y autorizar la entrada | Consultar reglas de negocio |
| **Controlador** | Recibir, delegar en el servicio, devolver respuesta | Validar, calcular, consultar la BD directamente |
| **Servicio** | Reglas de negocio, transacciones | Conocer HTTP (`request()`, `redirect()`) |
| **Modelo** | Relaciones, scopes, casts, accessors | Reglas de negocio de varios modelos |
| **Policy** | Responder «¿puede este usuario, sobre este dato?» | Autenticar (de eso va el middleware) |
| **Vista** | Mostrar datos ya preparados | **Calcular** (ningún total se calcula en Blade) |

**Límite duro:** un método de controlador no supera 15 líneas. Si lo hace, la lógica pertenece a un servicio.

## 4. Base de datos

- Importes: **siempre** `decimal(10,2)` + `$casts = ['precio' => 'decimal:2']`
- Toda FK declara explícitamente su `onDelete`: `restrictOnDelete()` por defecto; `cascadeOnDelete()` solo en datos accesorios (líneas de carrito), **nunca** en datos históricos (líneas de venta)
- Toda relación N:N lleva índice único en el par de claves
- Estados y unidades: **enum de PHP 8.1**, nunca strings sueltos con el catálogo en un comentario
- Toda migración implementa `down()` de verdad (una migración sin `down()` no es reversible)
- Los datos históricos no se borran: `SoftDeletes` en las entidades referenciadas por ventas
- **Un cast nuevo no es un cambio local.** Al añadir un cast de enum, revisar *toda*
  comparación (`=== 'texto'` deja de funcionar) y *toda* impresión en Blade
  (`{{ $modelo->campo }}` sobre un enum es error fatal). Exponer el estado con
  métodos del modelo (`estaPendiente()`) en vez de comparar enums en las vistas (H-37)
- **Al activar `SoftDeletes` hay que revisar todas las relaciones que apuntan al modelo**, una por una:
  - **Documento histórico** (línea de venta, línea de orden) → `->withTrashed()`: debe resolver siempre
  - **Dato accesorio** (línea de carrito) → filtrar con `whereHas()`: debe desaparecer

  Omitirlo no siempre revienta; a veces solo calcula mal en silencio (H-32)

## 4 bis. Inventario

- **`productos.stock` solo se modifica desde `InventarioService`.** Ningún
  controlador, seeder ni comando hace `increment('stock')` por su cuenta: todo
  movimiento deja su línea en el kardex o no ocurre (H-35)
- Toda salida se comprueba con la fila del producto **bloqueada** dentro de la
  transacción. Comprobar el stock y descontarlo en dos pasos sueltos es vender
  dos veces la última unidad
- Un ajuste manual **exige motivo**. Un ajuste sin explicación es un descuadre
  con permiso
- Los movimientos no se editan ni se borran: un error se corrige con otro
  movimiento, que deja su propio rastro

## 5. Front-end

- **Tailwind, únicamente.** Cero clases de Bootstrap (`container`, `card`, `btn`, `form-control`, `row`, `col-md-*`)
- Assets por **Vite** (`@vite`), nunca por CDN
- Un solo layout base de tienda + el `guest` de Breeze
- Lo que se repite en 3 vistas o más se convierte en componente Blade

## 6. Git

- Rama `main` estable; una rama por fase: `fase-1-seguridad`, `fase-2-dominio`…
- Commits en español, formato convencional, **con el ID del hallazgo**:

```
tipo(ámbito): descripción (H-XX)

fix(seguridad): protege rutas de escritura con middleware auth (H-01)
feat(inventario): añade columna stock a productos (H-03)
refactor(dominio): renombra Order a Venta (H-09)
docs(gestion): añade roadmap por fases
```

Tipos: `feat`, `fix`, `refactor`, `docs`, `test`, `chore`.

- Un commit = un cambio coherente. No mezclar el arreglo de H-01 con el de H-16.
- Cada fase cerrada genera una entrada en `CHANGELOG.md`.

### Remoto

`origin` → https://github.com/PeterAr29/supermercado-laravel (**privado**)

Ciclo de una fase:

```bash
git switch -c fase-1-seguridad      # 1. rama de la fase
# ...trabajar, commit por cada hallazgo cerrado...
git push -u origin fase-1-seguridad # 2. subir la rama
gh pr create --body "... Closes #2"  # 3. PR con el alcance Y la palabra clave
gh pr merge --squash --delete-branch # 4. fusionar al cerrar la fase
git switch main && git pull          # 5. actualizar local
```

**El PR lleva `Closes #N` en el cuerpo.** Sin esa palabra clave —`Closes`, `Fixes`
o `Resolves`— fusionar **no cierra la issue**: mencionar «Issue #N» solo crea una
referencia. Las fases 2 y 3 se fusionaron sin ella y sus issues se quedaron
abiertas.

**Las casillas de la issue no se marcan solas.** Ningún merge las toca: son
markdown del cuerpo. Se repasan a mano al cerrar la fase, y **la que no se hizo
se deja sin marcar con una nota que diga por qué** — una casilla marcada de más
convierte el tablero en decoración.

**Nunca se commitea directamente en `main`** salvo correcciones de documentación.

**Nunca se sube el `.env`.** Si se añade una variable nueva, se documenta en `.env.example` (ese sí se versiona) sin su valor real.

## 7. Antes de EMPEZAR una fase

1. **Volcado de la base de desarrollo.** Git versiona el código, no los datos:
   ```
   C:/xampp/mysql/bin/mysqldump.exe -u root laravel > backup_pre_fase_N.sql
   ```
2. **Comprobar a qué base apunta la suite de tests** (`phpunit.xml`) antes de ejecutarla
   por primera vez. `RefreshDatabase` hace `migrate:fresh`: si apunta a la base de
   desarrollo, la borra entera sin avisar (H-33).

## 8. Antes de dar una fase por terminada

1. Se cumple el **criterio de aceptación** escrito en `03-ROADMAP.md` — verificado, no supuesto.
2. `php artisan test` pasa.
3. `php artisan pint` ejecutado.
4. Los hallazgos cerrados se marcan en `02-HALLAZGOS.md`.
5. Hallazgos nuevos descubiertos por el camino: **se anotan, no se arreglan** en la fase en curso.
6. **Repasar la checklist de la issue una por una** y marcar solo lo hecho. Lo que
   quede sin marcar se explica en un comentario de cierre: si no bloquea el criterio
   de aceptación la fase se cierra igual, pero el hueco queda escrito y con dueño.
7. La tarjeta del tablero se mueve a `Done`.
