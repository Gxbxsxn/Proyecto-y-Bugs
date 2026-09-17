# Torre de Cobros — con backend real

Esta versión ya no simula nada en el navegador: tiene un **backend PHP real**
con base de datos, un **servidor Node/Socket.IO real** para las notificaciones,
y un frontend que les habla por HTTP y WebSocket de verdad.

```
sistema-demo-backend-real/
├── backend-php/        API en PHP + PDO + SQLite   (puerto 8000)
├── backend-socket/      Servidor Socket.IO (Node)    (puerto 4000)
└── frontend/           index.html (abrir en el navegador)
```

## Requisitos

- **PHP 8+** con la extensión `pdo_sqlite` (viene incluida en la mayoría
  de instalaciones; también funciona con XAMPP/MAMP/Laragon).
- **Node.js 18+** y `npm`.
- Un navegador moderno.

## Cómo levantarlo (3 pasos, 3 terminales)

**1. Backend PHP** (API + base de datos)
```bash
cd backend-php
php -S localhost:8000 -t public
```
La primera vez que arranca crea automáticamente `backend-php/data/cobros.sqlite`
con el esquema y datos de ejemplo (8 clientes, ~30 pagos, un usuario `analista`).

**2. Servidor de notificaciones** (Socket.IO)
```bash
cd backend-socket
npm install
npm start
```
Deja este proceso corriendo en su propia terminal; escucha en el puerto 4000.

**3. Frontend**

Simplemente abre `frontend/index.html` con doble clic en el navegador.
(Si tu navegador bloquea `fetch` desde `file://`, sirve la carpeta con
cualquier servidor estático, por ejemplo `npx serve frontend` o
`python3 -m http.server 5173 -d frontend`, y ábrelo desde `http://localhost:5173`.)

**Acceso:** usuario `analista`, clave `cobros2026`.

## Qué es real aquí (a diferencia de la versión de un solo archivo)

| Concepto | Antes (`sistema-demo/`) | Ahora (`sistema-demo-backend-real/`) |
|---|---|---|
| Login / contraseña | simulado con `btoa()` en el navegador | `password_hash()` / `password_verify()` real en PHP, contra SQLite |
| Sesión / JWT | temporizador simulado en JS | JWT propio firmado con HMAC-SHA256, verificado en cada petición |
| 401 al expirar | simulado con `setInterval` | 401 real devuelto por el servidor cuando el token vence o es inválido |
| Registrar pago | se guardaba en `localStorage` | `INSERT` real dentro de una transacción PDO (`beginTransaction`/`commit`/`rollBack`) |
| Mora, top 5 | calculado en JS sobre datos falsos | calculado en PHP sobre datos reales en SQLite |
| Notificación en tiempo real | `BroadcastChannel` entre pestañas del mismo navegador | Socket.IO real: el backend PHP llama a un servidor Node por HTTP, y ese servidor empuja el evento por WebSocket a la sala del cliente — funciona incluso entre navegadores distintos |

## Cómo probar que la notificación en tiempo real funciona de verdad

1. Levanta los tres procesos.
2. Abre el frontend en **dos ventanas o navegadores distintos**.
3. En ambas, entra al mismo cliente (ej. "Distribuidora Maíz Real").
4. Registra un pago desde una de las dos.
5. La otra debería actualizarse sola en un instante, sin refrescar — eso
   es el servidor Node avisando por la sala `cliente:<id>`, exactamente
   el mismo mecanismo de `D-nodejs/D1_socketio_salas.js`.

## Notas de seguridad (léelas antes de la entrevista)

- El secreto compartido entre PHP y Node (`NotificadorTiempoReal.php` /
  `server.js`) está hardcodeado para simplificar la demo; en un proyecto
  real iría en variables de entorno (`.env`), nunca en el repositorio.
- `Access-Control-Allow-Origin: *` y `cors: { origin: "*" }` están abiertos
  a propósito para que la demo funcione fácil en `localhost`; en
  producción se restringiría al dominio real del frontend.
- SQLite se usa por portabilidad (cero instalación); la lógica de acceso
  a datos (PDO + prepared statements) es la misma que usarías contra
  MySQL o SQL Server — solo cambia el DSN en `Database.php`.
- Este backend no reemplaza los archivos comentados de `A-php/`, `B-sql/`,
  etc. — son la referencia "de examen"; este backend es la prueba de que
  esa misma lógica corre en un sistema real.
