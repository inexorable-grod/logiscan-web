# Modulo: Manifiesto PDF

## Descripcion general

Generacion de manifiestos PDF por ruta usando DomPDF. Los operarios pueden solicitar la descarga del manifiesto de su ruta asignada a traves de la API.

## Endpoint

- `GET /api/manifest/{routeId}` - Descarga el PDF del manifiesto para la ruta indicada
- Requiere autenticacion (Sanctum)
- Solo el operario asignado a la ruta o supervisores pueden acceder

## Contenido del PDF

El manifiesto incluye tres secciones principales:

1. **Informacion de ruta**: numero de ruta, fecha, centro de distribucion, operario asignado
2. **Listado de clientes**: clientes de la ruta con direccion, orden de visita y cantidad de bultos esperados
3. **Resumen de escaneo**: total de bultos escaneados vs esperados, cubetas, controlados, refrigerados

## Jobs

### GenerateManifestPdfJob

- Cola: `default`
- Renderiza la vista Blade a PDF con DomPDF
- Almacena el PDF en `storage/app/manifests/{routeId}_{date}.pdf`
- Si el PDF ya existe para la misma fecha, lo sobreescribe

### SendManifestEmailJob

- Cola: `emails`
- Envia el manifiesto por email al supervisor del centro
- Se despacha opcionalmente despues de GenerateManifestPdfJob
- Adjunta el PDF generado al correo

## Flujo

1. Operario solicita `GET /api/manifest/{routeId}`
2. Si el PDF ya existe y esta actualizado, se retorna directamente
3. Si no existe o los datos cambiaron, se despacha `GenerateManifestPdfJob` de forma sincrona
4. Se retorna el PDF como response con `Content-Type: application/pdf`

## Configuracion DomPDF

- Tamano de papel: Letter
- Orientacion: vertical
- Encoding: UTF-8
- Fuente: DejaVu Sans (soporte de caracteres especiales)
