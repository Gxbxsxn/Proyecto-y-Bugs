# Reto Técnico OCA — Entrega

Esta entrega tiene dos partes principales:

1. Las soluciones a los 9 ejercicios pedidos en el reto.
2. Un sistema demo de un solo archivo, listo para abrir directamente en el navegador.

```text
entrega/
├── A-php/                          Ejercicios de PHP (login, pagos, mora)
├── B-sql/                          Ejercicios de SQL (ranking, paginación, rendimiento)
├── C-frontend/                     Ejercicios de React / Redux Toolkit / Axios
├── D-nodejs/                       Bonus de Socket.IO
├── sistema-demo/
│   └── torre-cobros-oca.html       Demo funcional en una sola página
├── sistema-demo-backend-real/      Versión real con backend PHP + SQLite + Socket.IO
├── README.md
└── .gitignore
```

---

## 1. Soluciones a los ejercicios

| Archivo | Qué resuelve |
|---|---|
| `A-php/A1_login_seguro.php` | Corrige la inyección SQL y la contraseña en texto plano del login original. |
| `A-php/A2_registrar_pago.php` | Inserta un pago dentro de una transacción PDO (begin/commit/rollback). |
| `A-php/A3_dias_mora.php` | Corrige los dos defectos de `diasMora()`: mora negativa y decimales por hora. |
| `B-sql/B1_top5_clientes.sql` | T-SQL: top 5 clientes que más pagaron en los últimos 90 días. |
| `B-sql/B2_traduccion_tsql.sql` | Traducción de la paginación de MySQL (`LIMIT/OFFSET`) a T-SQL (`OFFSET/FETCH`). |
| `B-sql/B3_rendimiento.md` | Plan de diagnóstico paso a paso ante una consulta lenta sobre una tabla grande. |
| `C-frontend/C1_ListaClientes.jsx` | Corrige el `useEffect` que no reaccionaba al cambio de `filtro`. |
| `C-frontend/C2_pagosSlice.js` | Slice de Redux Toolkit con thunk y estados idle/loading/succeeded/failed. |
| `C-frontend/C3_axiosInterceptors.js` | Interceptores de Axios: adjuntar JWT y manejar 401. |
| `D-nodejs/D1_socketio_salas.js` | Notificación por sala en Socket.IO (bonus). |

---

## 2. Demostración rápida del sistema

La versión recomendada para presentar es la demo de un solo archivo:

- `sistema-demo/torre-cobros-oca.html`
- se abre directamente en el navegador,
- no requiere backend ni instalación,
- permite mostrar login, pagos, mora, top 5 y notificaciones.

Acceso:
- Usuario: `analista`
- Contraseña: `cobros2026`

Abrir en el navegador en:
- `http://localhost:8080/torre-cobros-oca.html`

---

## 3. Versión real del proyecto

También quedó incluida la carpeta `sistema-demo-backend-real/` con:

- backend PHP + SQLite
- servidor Node + Socket.IO
- frontend en HTML para probar el flujo real

Esta versión está pensada para demostrar que la lógica puede correr contra datos reales y notificaciones en vivo.

---

## Estado final

Proyecto preparado para entrega con la demo simple como versión principal y la versión real como complemento técnico.
