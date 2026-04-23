# ADR-005: Notificaciones push simultáneas con Expo Push

## Estado
Aceptado — 2025-04-03

## Contexto
Cuando un operario envía una solicitud, tanto el Supervisor como TI deben ser notificados. Se debe decidir entre notificación simultánea o escalamiento temporal.

## Decisión
Notificación simultánea a ambos roles vía Expo Push Notifications. lockForUpdate() previene doble resolución. resolved_by_role registra quién actuó. Se usa Expo Push como transporte (simplifica FCM/APNs). Tokens se almacenan en device_tokens. Jobs en cola 'notifications' con 3 retries.

## Consecuencias
- ✅ Resolución más rápida — el primero disponible actúa.
- ✅ Sin lógica de timers o escalamiento que pueda fallar.
- ✅ Trazabilidad completa del patrón de resolución.
- ⚠️ TI recibe notificaciones que el Supervisor probablemente resolverá primero.
- ⚠️ Dependencia de Expo Push Service (SLA no garantizado).

## Alternativas consideradas
- Escalamiento con timer (5 min): descartada (agrega complejidad y latencia).
- Solo notificar al Supervisor: descartada (bloquea si no está disponible).
- FCM directo sin Expo: descartada (más complejo de implementar y mantener).
