# ADR-004: Estrategia offline con cola local SQLite y batch sync

## Estado
Aceptado — 2025-04-03

## Contexto
Los operarios trabajan en bodegas con conectividad intermitente. La app móvil debe permitir escaneo continuo sin depender de conexión en tiempo real.

## Decisión
Los escaneos se guardan localmente en SQLite con estado pending/syncing/synced/failed. Cuando se detecta conectividad (NetInfo), se envía un batch de hasta 50 scans a POST /api/scans/batch. El servidor valida cada scan y responde con status individual. Duplicados (mismo barcode + operación) se marcan como 'duplicate' sin error. Si retryCount >= 3, el scan se marca como 'failed' permanente.

## Consecuencias
- ✅ Operación continua sin conectividad.
- ✅ Sync automático al recuperar red.
- ✅ Resolución de conflictos clara (server wins).
- ⚠️ Datos de catálogo (rutas/clientes) pueden quedar stale después de 4 horas offline.
- ⚠️ Solicitudes NO se pueden crear offline (requieren confirmación en tiempo real).

## Alternativas consideradas
- Sync en tiempo real por WebSocket: descartada (no funciona offline).
- Sync manual por botón: descartada (mala UX, operarios olvidan sincronizar).
