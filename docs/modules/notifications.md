# Modulo: Notificaciones Push

## Descripcion general

Notificaciones push a la app movil via Expo Push API. Los tokens de dispositivo se registran por usuario y plataforma, y se usan para enviar notificaciones sobre eventos del sistema.

## Modelo DeviceToken

| Campo | Tipo | Descripcion |
|-------|------|-------------|
| `id` | bigint | PK |
| `user_id` | bigint | FK a users |
| `token` | string | Expo push token |
| `platform` | string | `ios` o `android` |
| `is_active` | boolean | Token activo/inactivo |
| `created_at` | timestamp | Fecha de registro |
| `updated_at` | timestamp | Ultima actualizacion |

## Registro de tokens

- `POST /api/device-tokens`
- Body: `{ token, platform }`
- Si el token ya existe para el usuario, se reactiva
- Si el token pertenece a otro usuario, se reasigna al usuario actual
- Requiere autenticacion (Sanctum)

## SendPushNotificationJob

- Cola: `notifications`
- Envia notificaciones usando Expo Push API (`https://exp.host/--/api/v2/push/send`)
- Agrupa tokens por lote (max 100 por request a Expo)
- Registra resultado en logs

## Eventos que generan notificaciones

### Solicitud creada

- **Trigger**: operario crea una solicitud (request)
- **Destinatarios**: supervisores del centro + ti_admin
- **Contenido**: "Nueva solicitud de {operario}: {tipo}"

### Solicitud resuelta

- **Trigger**: supervisor o ti_admin resuelve una solicitud
- **Destinatarios**: operario que creo la solicitud
- **Contenido**: "Tu solicitud fue {aprobada/rechazada}: {comentario}"

## Tokens invalidos

- Si Expo responde con `DeviceNotRegistered`, el token se desactiva (`is_active = false`)
- Los tokens inactivos se excluyen de futuros envios
- No se eliminan para mantener historial

## Flujo

1. Al iniciar la app, se solicita permiso de notificaciones
2. Se obtiene el Expo push token
3. Se registra via `POST /api/device-tokens`
4. Cuando ocurre un evento, se despacha `SendPushNotificationJob`
5. El job consulta tokens activos de los destinatarios
6. Envia la notificacion a Expo Push API
7. Tokens invalidos se desactivan automaticamente
