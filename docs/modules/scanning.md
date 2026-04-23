# Módulo: Escaneo

## Clasificación por longitud de código

| Dígitos | Tipo | Acción en UI |
|---------|------|-------------|
| 5 | RF (radiofrecuencia) | Formulario RF automático |
| 10 | Bulto o Cubeta | Tipo = valor del toggle activo |
| 11 | Controlado o Refrigerado | Modal de selección |
| Otro | Inválido | Toast de error + audit_log |

## Toggle Bulto/Cubeta

- Solo aplica a códigos de 10 dígitos
- Estado por defecto: 'bulto'
- El operario cambia con un botón toggle
- Códigos de 5 y 11 dígitos ignoran el toggle

## Validaciones

1. Solo caracteres numéricos
2. Longitud debe ser 5, 10 u 11
3. Debounce: ignorar mismo código si escaneado hace < 2 segundos
4. Operación activa requerida
5. Scan duplicado en misma operación: advertir y pedir confirmación

## Modo offline

- Scans se guardan en SQLite local con syncStatus = 'pending'
- Al recuperar conectividad: batch sync (max 50 por request)
- POST /api/scans/batch → respuesta con status individual
- Estados: pending → syncing → synced | failed
- Si retryCount >= 3 → failed permanente → notificar operario

## Escaneo de facturas

- Formato: Code 128 alfanumérico
- Validación: factura debe existir en pedidos del cliente de la ruta
- Si no coincide: error "Factura no corresponde a esta ruta"
