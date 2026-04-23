# Modulo: Sincronizacion Offline

## Descripcion general

Estrategia de sincronizacion offline para la app movil (Expo). Los escaneos se almacenan localmente en SQLite y se sincronizan por lotes cuando hay conectividad.

## Almacenamiento local

- Base de datos SQLite en el dispositivo
- Cada scan se guarda con su `syncStatus` y `retryCount`
- Los datos persisten entre reinicios de la app

## Estados de sincronizacion

| Estado | Descripcion |
|--------|-------------|
| `pending` | Scan guardado localmente, pendiente de envio |
| `syncing` | Envio en progreso |
| `synced` | Confirmado por el servidor |
| `failed` | Fallo permanente despues de agotar reintentos |

## Endpoint de sincronizacion

- `POST /api/scans/batch`
- Maximo **50 scans por lote**
- Respuesta: array con status individual por cada scan
- Autenticacion requerida (Sanctum)

## Debounce entre escaneos

- Intervalo minimo: **1500ms** entre escaneos del mismo codigo
- Escaneos dentro del intervalo se ignoran silenciosamente
- Previene duplicados por lecturas multiples del escaner

## Auto-sync por conectividad

1. Listener de `NetInfo` detecta cambio de estado de red
2. Al recuperar conectividad, se dispara sincronizacion automatica
3. Se agrupan scans con estado `pending` en lotes de 50
4. Los lotes se envian secuencialmente

## Logica de reintentos

- Maximo **3 reintentos** por scan
- Incremento de `retryCount` en cada fallo
- Si `retryCount >= 3`: estado cambia a `failed`
- Se notifica al operario sobre scans fallidos via alerta local

## Resolucion de conflictos

- Estrategia: **server wins** (el servidor tiene prioridad)
- Si el servidor detecta un scan duplicado (mismo codigo + misma operacion), lo marca como `duplicate`
- El cliente actualiza el estado local segun la respuesta del servidor
- Scans marcados como `duplicate` no se reenvian

## Flujo completo

1. Operario escanea un codigo
2. Scan se guarda en SQLite con `syncStatus = 'pending'`
3. Si hay conectividad, se intenta sync inmediato
4. Si no hay conectividad, queda en cola local
5. Al reconectar (NetInfo), auto-sync en lotes de 50
6. Servidor responde con status individual
7. Cliente actualiza `syncStatus` por cada scan
