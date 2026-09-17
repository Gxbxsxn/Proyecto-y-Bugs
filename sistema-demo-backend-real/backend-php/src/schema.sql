-- Esquema de la base de datos — Torre de Cobros
-- Motor: SQLite (PDO), elegido para que el backend corra en cualquier
-- máquina sin instalar un servidor de base de datos aparte. La lógica
-- de acceso a datos (PDO + prepared statements) es la misma que se
-- usaría contra MySQL o SQL Server; solo cambiaría el DSN de conexión.

CREATE TABLE IF NOT EXISTS usuarios (
    id            INTEGER PRIMARY KEY AUTOINCREMENT,
    usuario       TEXT NOT NULL UNIQUE,
    clave_hash    TEXT NOT NULL
);

CREATE TABLE IF NOT EXISTS clientes (
    id      INTEGER PRIMARY KEY AUTOINCREMENT,
    nombre  TEXT NOT NULL,
    nit     TEXT NOT NULL UNIQUE
);

CREATE TABLE IF NOT EXISTS pagos (
    id                  INTEGER PRIMARY KEY AUTOINCREMENT,
    cliente_id          INTEGER NOT NULL REFERENCES clientes(id),
    monto               REAL NOT NULL,
    forma_pago          TEXT NOT NULL,
    fecha_vencimiento    TEXT NOT NULL,
    fecha_pago          TEXT NOT NULL
);

CREATE INDEX IF NOT EXISTS ix_pagos_fecha_pago ON pagos (fecha_pago);
CREATE INDEX IF NOT EXISTS ix_pagos_cliente_id ON pagos (cliente_id);
