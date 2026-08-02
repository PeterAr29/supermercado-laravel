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
| **Vista** | Mostrar datos ya preparados | **Calcular** (ningún total se calcula en Blade) |

**Límite duro:** un método de controlador no supera 15 líneas. Si lo hace, la lógica pertenece a un servicio.

## 4. Base de datos

- Importes: **siempre** `decimal(10,2)` + `$casts = ['precio' => 'decimal:2']`
- Toda FK declara explícitamente su `onDelete`: `restrictOnDelete()` por defecto; `cascadeOnDelete()` solo en datos accesorios (líneas de carrito), **nunca** en datos históricos (líneas de venta)
- Toda relación N:N lleva índice único en el par de claves
- Estados y unidades: **enum de PHP 8.1**, nunca strings sueltos con el catálogo en un comentario
- Toda migración implementa `down()` de verdad (una migración sin `down()` no es reversible)
- Los datos históricos no se borran: `SoftDeletes` en las entidades referenciadas por ventas
- **Al activar `SoftDeletes` hay que revisar todas las relaciones que apuntan al modelo**, una por una:
  - **Documento histórico** (línea de venta, línea de orden) → `->withTrashed()`: debe resolver siempre
  - **Dato accesorio** (línea de carrito) → filtrar con `whereHas()`: debe desaparecer

  Omitirlo no siempre revienta; a veces solo calcula mal en silencio (H-32)

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
git switch -c fase-1-seguridad     # 1. rama de la fase
# ...trabajar, commit por cada hallazgo cerrado...
git push -u origin fase-1-seguridad # 2. subir la rama
gh pr create --fill                 # 3. Pull Request (deja constancia del alcance)
gh pr merge --squash --delete-branch # 4. fusionar al cerrar la fase
git switch main && git pull          # 5. actualizar local
```

**Nunca se commitea directamente en `main`** salvo correcciones de documentación.

**Nunca se sube el `.env`.** Si se añade una variable nueva, se documenta en `.env.example` (ese sí se versiona) sin su valor real.

## 7. Antes de dar una fase por terminada

1. Se cumple el **criterio de aceptación** escrito en `03-ROADMAP.md` — verificado, no supuesto.
2. `php artisan test` pasa.
3. `php artisan pint` ejecutado.
4. Los hallazgos cerrados se marcan en `02-HALLAZGOS.md`.
5. Hallazgos nuevos descubiertos por el camino: **se anotan, no se arreglan** en la fase en curso.
