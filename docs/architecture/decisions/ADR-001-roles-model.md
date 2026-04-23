# ADR-001: Modelo de 4 roles con separación de responsabilidades

## Estado
Aceptado — 2025-04-03

## Contexto
El sistema logístico necesita controlar acceso para personal de distintos niveles: gerencia (visibilidad), TI (administración), supervisión (operaciones) y operarios (ejecución en bodega). Se requiere que cada nivel tenga acceso estrictamente limitado a sus responsabilidades.

## Decisión
Se implementan 4 roles con ENUM en la tabla users:
- **gerente_ops**: Dashboard estadístico global, solo lectura. Sin acciones operacionales.
- **ti_admin**: Acceso total. CRUD de usuarios, centros, rutas. Aprueba solicitudes como respaldo.
- **supervisor**: Un único usuario que selecciona el centro activo por sesión. Aprueba solicitudes como primera instancia.
- **operario**: Accede solo al centro asignado vía center_users. Escanea y envía solicitudes.

## Consecuencias
- ✅ Separación clara de responsabilidades sin ambigüedad.
- ✅ El Supervisor puede gestionar todos los centros uno a la vez.
- ✅ TI tiene acceso global como respaldo operacional.
- ⚠️ Solo un supervisor — si crece la operación, se necesitará múltiples supervisores.
- ⚠️ El Gerente es estrictamente lectura — no puede tomar acciones de emergencia.

## Alternativas consideradas
- RBAC dinámico con permisos granulares: descartada (excesiva complejidad para 4 roles fijos).
- Solo 2 roles (admin/operario): descartada (no permite segregar supervisión de administración TI).
