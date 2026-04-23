# ADR-003: Selector de centro por sesión para Supervisor

## Estado
Aceptado — 2025-04-03

## Contexto
El Supervisor gestiona múltiples centros pero opera en uno a la vez. Se necesita un mecanismo para seleccionar el centro activo sin requerir múltiples cuentas o re-login.

## Decisión
El `active_center_id` se almacena en la sesión del Supervisor. Al iniciar sesión, si no hay centro seleccionado, se redirige a `/select-center`. El cambio de centro se hace desde la navegación sin cerrar sesión. Cada cambio se registra en audit_log. TI y Gerente tienen acceso global implícito. Operarios acceden por asignación en center_users.

## Consecuencias
- ✅ UX fluida: cambiar centro sin re-login.
- ✅ Audit trail de cada cambio de centro.
- ✅ CenterAccessMiddleware aplica reglas uniformemente.
- ⚠️ Si la sesión expira, se pierde el centro activo.
- ⚠️ Requiere session regeneration en login para prevenir fixation.

## Alternativas consideradas
- Centro en JWT claim: descartada (no se puede cambiar sin re-emitir token).
- Centro en tabla de BD: descartada (overhead innecesario vs sesión).
