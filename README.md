# Proyecto y Bugs — Entrega final OCA

Este repositorio reúne la solución de los ejercicios técnicos y una demo funcional integrada que muestra el comportamiento esperado del sistema final.

## Objetivo

La entrega tiene dos partes:

- Soluciones a los ejercicios del reto técnico.
- Una demostración visual y navegable del flujo real de negocio: login, pagos, mora, ranking y notificaciones.

## Estructura del proyecto

```text
entrega_final/
├── A-php/
│   ├── A1_login_seguro.php
│   ├── A2_registrar_pago.php
│   └── A3_dias_mora.php
├── B-sql/
│   ├── B1_top5_clientes.sql
│   ├── B2_traduccion_tsql.sql
│   └── B3_rendimiento.md
├── C-frontend/
│   ├── C1_ListaClientes.jsx
│   ├── C2_pagosSlice.js
│   └── C3_axiosInterceptors.js
├── D-nodejs/
│   └── D1_socketio_salas.js
├── sistema-demo/
│   └── torre-cobros-oca.html
├── README.md
├── .gitignore
└── .DS_Store (ignorado/local)
```

## Cómo ejecutar la demo

La demo es un archivo HTML autocontenido y no requiere servidor ni dependencias.

1. Abrir el archivo:
   `sistema-demo/torre-cobros-oca.html`
2. Iniciar sesión con:
   - Usuario: `analista`
   - Contraseña: `cobros2026`

## Qué incluye la demo

- Login simulado con validación de contraseña segura.
- Sesión con expiración de 3 minutos.
- Listado de clientes filtrable.
- Historial de pagos con estados de carga y error.
- Registro de pago con bitácora transaccional.
- Cálculo de días de mora corregido.
- Top 5 clientes en los últimos 90 días.
- Notificaciones por sala usando BroadcastChannel como simulación de Socket.IO.

## Archivos clave

- A-php/A1_login_seguro.php: arreglo del login y protección frente a inyección SQL.
- A-php/A2_registrar_pago.php: transacción con begin/commit/rollback.
- A-php/A3_dias_mora.php: limpieza de mora negativa y cálculo sin decimales por hora.
- B-sql/B1_top5_clientes.sql: ranking de clientes por pagos en los últimos 90 días.
- B-sql/B2_traduccion_tsql.sql: equivalencia de paginación MySQL a T-SQL.
- B-sql/B3_rendimiento.md: guía para diagnosticar consultas lentas.
- C-frontend/C1_ListaClientes.jsx: corrección de re-render por filtro.
- C-frontend/C2_pagosSlice.js: slice con estados async y manejo de errores.
- C-frontend/C3_axiosInterceptors.js: JWT y 401 interceptor.
- D-nodejs/D1_socketio_salas.js: notificaciones y salas en Socket.IO.

## Nota

La demo está pensada para mostrar el comportamiento real del reto en una interfaz amigable, sin backend productivo. Los hashes, JWT y notificaciones en tiempo real están simulados en el navegador para fines educativos.

## Estado

Proyecto finalizado y preparado para entrega como evidencia técnica de la solución planteada.

