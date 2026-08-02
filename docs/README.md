# Gestión del proyecto — Supermercado Online

Documentación de gestión, no de código. Aquí se decide **qué se hace, en qué orden y con qué criterio**.

## Índice

| Documento | Para qué sirve | Cuándo se lee |
|---|---|---|
| [01-CONTEXTO.md](01-CONTEXTO.md) | Qué es el proyecto, cómo llegó a ser lo que es, arquitectura actual | Al entrar al proyecto |
| [02-HALLAZGOS.md](02-HALLAZGOS.md) | Inventario de deuda técnica con ID y severidad (`H-01`…`H-27`) | Antes de planificar |
| [03-ROADMAP.md](03-ROADMAP.md) | Fases de trabajo, alcance y criterio de aceptación de cada una | Antes de programar |
| [04-CONVENCIONES.md](04-CONVENCIONES.md) | Reglas para que el proyecto no se vuelva a desordenar | Durante cada cambio |

## Dónde vive cada cosa

Para evitar dos listas que se contradigan, el reparto es estricto:

| Sitio | Responde a | Es la fuente de verdad de |
|---|---|---|
| [**Tablero (Projects)**](https://github.com/users/PeterAr29/projects/1) | ¿Qué está hecho y qué falta? | **El estado** |
| [**Issues #1–#7**](https://github.com/PeterAr29/supermercado-laravel/issues) | ¿Qué tareas concretas tiene esta fase? | **Las casillas marcadas** |
| `03-ROADMAP.md` | ¿Por qué esta fase y cuándo se da por buena? | **Alcance y criterio de aceptación** |
| `02-HALLAZGOS.md` | ¿Cuál es el problema exactamente y dónde está? | **El diagnóstico** (archivo:línea) |

## Cómo se trabaja

1. **Una fase a la vez.** No se empieza la siguiente hasta que la actual cumple su criterio de aceptación.
2. **Una rama por fase** (`fase-1-seguridad`), con Pull Request al terminar. Ver `04-CONVENCIONES.md`.
3. **Cada cambio referencia un hallazgo.** El commit lleva el ID: `fix(seguridad): protege rutas admin (H-01)`.
4. **Se marcan las casillas en la issue** a medida que se avanza, no en los documentos.
5. **Cada fase cierra con una entrada en [CHANGELOG.md](../CHANGELOG.md)** y la issue cerrada.
6. **Nada se arregla "de paso".** Si aparece algo nuevo, se anota en `02-HALLAZGOS.md` y se asigna a una fase.

## Estado actual

- **Última fase cerrada:** 2 — Dominio unificado ✅ ([#3](https://github.com/PeterAr29/supermercado-laravel/issues/3))
- **Siguiente:** 3 — Paneles y roles ([#11](https://github.com/PeterAr29/supermercado-laravel/issues/11))
- **Roadmap replanteado el 2026-08-01:** se retira Google Sheets y se inserta la Fase 3 — Paneles y roles ([#11](https://github.com/PeterAr29/supermercado-laravel/issues/11)). Las fases 3-5 antiguas pasan a 4-6.
