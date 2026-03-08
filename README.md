# ParkSys - Sistema Web de Parqueadero (Extendido)

Implementacion basada en la guia del proyecto con PHP + MySQL + HTML/CSS/JS.

## Modulos implementados
- Login / Logout con sesion.
- Roles: `SUPERADMIN`, `ADMIN`, `OPERADOR`.
- Entrada de vehiculo con:
  - categoria, marca, modelo, color,
  - asignacion de ubicacion (mapa por espacios),
  - ticket aleatorio de ingreso.
- Salida con verificacion obligatoria de ticket.
- Factura con codigo aleatorio e impresion.
- Reportes diario, semanal y mensual mas completos.
- Dashboard ampliado: ocupacion por zona, activos por categoria, ingresos.
- Admin:
  - CRUD usuarios (solo SUPERADMIN),
  - CRUD categorias/tarifas,
  - CRUD marcas y modelos,
  - CRUD ubicaciones.

## Estructura
- `frontend/` interfaz web
- `backend/` endpoints JSON y logica de negocio
- `database/parksys.sql` esquema completo actualizado

## Instalacion y ejecucion
1. Copiar `sistema_parksys` en `C:\xampp\htdocs\`.
2. En phpMyAdmin, importar `database/parksys.sql` (esto recrea tablas).
3. Verificar `backend/conexion.php` (`sistema_parksys`, usuario `root`).
4. Abrir `http://localhost/sistema_parksys/frontend/login.php`.

## Usuarios iniciales
- `superadmin@parksys.local` / `admin123`
- `admin@parksys.local` / `admin123`
- `operador@parksys.local` / `admin123`

## Nota tecnica
Para usuarios creados desde Admin, la password queda hasheada (bcrypt).

## Nuevas mejoras
- Recuperacion de contrasena con token temporal:
  - rontend/forgot_password.php
  - rontend/reset_password.php
- Auditoria de acciones:
  - tabla uditoria
  - visor en Admin
- Exportacion de reportes:
  - Excel (CSV)
  - Vista imprimible para PDF

## Importante
Vuelve a importar database/parksys.sql para crear las nuevas tablas password_resets y uditoria.

- Integracion CarQuery API (consulta de marcas y modelos): backend/carquery.php + formulario de entrada.
- Gestion de logo global del sistema desde Admin (subida de imagen).


