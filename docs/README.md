# Gestión del proyecto — Supermercado Online

Documentación de gestión, no de código. Aquí se decide **qué se hace, en qué orden y con qué criterio**.

## Índice

| Documento | Para qué sirve | Cuándo se lee |
|---|---|---|
| [01-CONTEXTO.md](01-CONTEXTO.md) | Qué es el proyecto, cómo llegó a ser lo que es, arquitectura actual | Al entrar al proyecto |
| [02-HALLAZGOS.md](02-HALLAZGOS.md) | Inventario de deuda técnica con ID y severidad (`H-01`…`H-27`) | Antes de planificar |
| [03-ROADMAP.md](03-ROADMAP.md) | Fases de trabajo, alcance y criterio de aceptación de cada una | Antes de programar |
| [04-CONVENCIONES.md](04-CONVENCIONES.md) | Reglas para que el proyecto no se vuelva a desordenar | Durante cada cambio |

## Cómo se trabaja

1. **Una fase a la vez.** No se empieza la siguiente hasta que la actual cumple su criterio de aceptación.
2. **Cada cambio referencia un hallazgo.** El mensaje de commit lleva el ID: `fix(seguridad): protege rutas admin (H-01)`.
3. **Cada fase cierra con un commit y una entrada en [CHANGELOG.md](../CHANGELOG.md).**
4. **Nada se arregla "de paso".** Si aparece algo nuevo, se anota en `02-HALLAZGOS.md` y se asigna a una fase.

## Estado actual

- **Última fase cerrada:** 0 — Control de versiones y gestión ✅
- **Siguiente:** 1 — Seguridad e integridad de datos (cierra `H-01`…`H-07`)
- **Ver progreso:** [03-ROADMAP.md](03-ROADMAP.md)

> El roadmap es la **única** fuente de verdad del progreso. No duplicar el seguimiento en otros sitios.
