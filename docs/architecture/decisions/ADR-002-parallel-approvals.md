# ADR-002: Aprobaciones en paralelo con lockForUpdate

## Estado
Aceptado — 2025-04-03

## Contexto
Los operarios necesitan aprobaciones rápidas de solicitudes. Si solo el Supervisor puede aprobar y está ocupado, los operarios se bloquean. Se necesita un mecanismo de respaldo que no introduzca doble resolución.

## Decisión
Supervisor (primera instancia) y TI (respaldo) reciben la notificación push simultáneamente. El primero en actuar gana. Se usa `lockForUpdate()` en MariaDB (InnoDB) para prevenir race conditions: la fila se bloquea durante la transacción y el segundo usuario recibe "Ya resuelta por [nombre]". El campo `resolved_by_role` registra quién resolvió para trazabilidad.

## Consecuencias
- ✅ Ninguna solicitud queda bloqueada si el Supervisor está ocupado.
- ✅ Trazabilidad completa: se sabe si resolvió Supervisor o TI.
- ✅ Atomicidad garantizada por InnoDB row-level locking.
- ⚠️ Requiere InnoDB engine obligatorio (no MyISAM).
- ⚠️ Tests de race condition son necesarios para validar el comportamiento.

## Alternativas consideradas
- Cola con escalamiento manual (5 min timeout): descartada (agrega latencia).
- Solo TI puede aprobar: descartada (rompe jerarquía operacional).
- Solo Supervisor: descartada (bloquea operarios cuando no está disponible).
