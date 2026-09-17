# Reto Técnico OCA — Entrega

Esta entrega tiene dos partes principales:

1. Las soluciones a los 9 ejercicios pedidos en el reto.
2. Un sistema demo funcional para mostrar el flujo del negocio en el navegador.

```text
entrega/
├── A-php/                          Ejercicios de PHP (login, pagos, mora)
├── B-sql/                          Ejercicios de SQL (ranking, paginación, rendimiento)
├── C-frontend/                     Ejercicios de React / Redux Toolkit / Axios
├── D-nodejs/                       Bonus de Socket.IO
├── sistema-demo/
│   └── torre-cobros-oca.html       Demo de un solo archivo, lista para abrir
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

## 2. Demo funcional recomendada para presentar

La opción más simple y fiable es esta:

- `sistema-demo/torre-cobros-oca.html`
- se abre directamente en el navegador,
- no requiere instalación ni backend,
- permite demostrar login, pagos, mora y top 5 con datos simulados.

Credenciales:
- Usuario: `analista`
- Contraseña: `cobros2026`

URL de acceso:
- `http://localhost:8080/torre-cobros-oca.html`

---

## 3. Versión real con backend

La carpeta `sistema-demo-backend-real/` contiene una versión real del mismo flujo con:

- backend PHP + SQLite
- servidor Node + Socket.IO
- frontend conectado a API y WebSockets reales

Esto sirve para demostrar que la lógica funciona contra datos reales y notificaciones en tiempo real.

### Arranque rápido

```bash
cd sistema-demo-backend-real/backend-php
php -S localhost:8000 -t public
```

```bash
cd sistema-demo-backend-real/backend-socket
npm install
npm start
```

```bash
cd sistema-demo-backend-real
python3 -m http.server 5173 -d frontend
```

Luego abre:
- `http://localhost:5173`

---

## Estado final

Este repositorio queda preparado para entrega con la demo simple como versión principal y la versión real como complemento técnico para demostrar la solución en un entorno real.

