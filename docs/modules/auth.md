# Módulo: Autenticación

## Flujo de Login

1. `POST /api/login` con email + password
2. Validar credenciales contra bcrypt r12
3. Verificar `is_active = true`
4. Regenerar sesión (`session()->regenerate()`)
5. Crear token Sanctum
6. Registrar en audit_log (LOGIN)
7. Responder con: token, user data, centers (si supervisor), force_password_change

## Cambio obligatorio de contraseña

- Si `force_password_change = true`, la app redirige a pantalla de cambio
- `POST /api/password/change` con current_password + new_password
- Se actualiza la contraseña y `force_password_change = false`

## Reset de contraseña (por admin)

- Solo TI puede resetear cualquier usuario
- Supervisor puede resetear operarios
- Se genera contraseña temporal y `force_password_change = true`
- No hay self-service por email (operarios no tienen email corporativo)

## Logout

- Revoca el token Sanctum actual
- Desactiva todos los device_tokens del usuario
- Registra en audit_log (LOGOUT)

## Seguridad

- Rate limiting: 5 intentos/minuto por IP en login
- Session lifetime: 8 horas (turno laboral)
- Session regeneration en cada login
- bcrypt con 12 rounds
