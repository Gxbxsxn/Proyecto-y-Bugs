# Reto Técnico OCA — Soluciones

Estructura:

```
A-php/
  A1_login_seguro.php       -> corrección de inyección SQL + hash de contraseña
  A2_registrar_pago.php     -> transacción PDO (begin/commit/rollback)
  A3_dias_mora.php          -> corrección de los 2 defectos de negocio
B-sql/
  B1_top5_clientes.sql      -> T-SQL, top 5 clientes últimos 90 días
  B2_traduccion_tsql.sql    -> traducción MySQL -> T-SQL (paginación)
  B3_rendimiento.md         -> plan de diagnóstico de rendimiento
C-frontend/
  C1_ListaClientes.jsx      -> fix de useEffect con dependencias
  C2_pagosSlice.js          -> slice de Redux Toolkit con thunk
  C3_axiosInterceptors.js   -> interceptores request/response con JWT
D-nodejs/
  D1_socketio_salas.js      -> notificación por sala en Socket.IO
```

Cada archivo trae comentarios explicando **qué estaba mal** (cuando aplica)
y **por qué** se resolvió de esa forma — son los puntos que puedo defender
en la entrevista de seguimiento, tal como pide el enunciado.

## Antes de entregar

- [ ] Inicializar como repo Git: `git init && git add . && git commit -m "Reto técnico OCA"`
- [ ] Revisar cada comentario y asegurarte de poder explicarlo con tus
      propias palabras — el enunciado dice que la explicación pesa tanto
      como el código.
- [ ] Enviar a e.linares@ocacall.com con copia a ne.perez@ocacall.com
- [ ] Fecha límite: 18 de septiembre de 2026
