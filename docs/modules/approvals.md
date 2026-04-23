# Módulo: Aprobaciones

## Tipos de solicitud

| Tipo | Descripción | Requiere route_id |
|------|-------------|-------------------|
| new_client | Alta de nuevo cliente | Sí |
| update_client | Cambio de datos de cliente | Sí |
| scan_reset | Reinicio de escaneo de operación | No |

## Flujo

1. Operario crea solicitud (`POST /api/requests`)
2. Se guarda con status = 'pending' en client_requests
3. NotificationService envía push simultáneo a Supervisor + todos los TI activos
4. El primero en actuar (Aprobar/Rechazar) gana:
   - `lockForUpdate()` bloquea la fila
   - Se actualiza status, resolved_by, resolved_by_role, resolved_at
   - Se notifica al operario con el nombre del resolutor
5. El segundo recibe: "Ya resuelta por [nombre]" (HTTP 409)

## Acciones post-aprobación

- **new_client aprobado**: Se crea automáticamente el Client con los datos de request_data
- **Rechazo**: Requiere admin_comment obligatorio

## Trazabilidad

- `resolved_by_role` = 'supervisor' o 'ti_admin'
- audit_log registra APPROVED_REQUEST o REJECTED_REQUEST
- Estadísticas: cuántas resolvió Supervisor vs TI como respaldo
