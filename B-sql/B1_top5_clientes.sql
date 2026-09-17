-- B1 · Consulta en SQL Server (10 pts)
-- Top 5 clientes que más han pagado en los últimos 90 días, mayor a menor.

SELECT TOP 5
    c.id,
    c.nombre,
    SUM(p.monto) AS total_pagado
FROM clientes c
INNER JOIN pagos p
    ON p.cliente_id = c.id
WHERE p.fecha_pago >= DATEADD(DAY, -90, CAST(GETDATE() AS DATE))
GROUP BY c.id, c.nombre
ORDER BY total_pagado DESC;

-- Notas:
-- * TOP 5 es la forma idiomática en T-SQL (equivalente a LIMIT en MySQL).
-- * DATEADD(DAY, -90, ...) sobre GETDATE() calcula la fecha límite de la
--   ventana de 90 días. Se castea GETDATE() a DATE para que la comparación
--   no dependa de la hora exacta del momento en que corre la consulta.
-- * INNER JOIN porque solo interesan clientes que SÍ tienen pagos en el
--   rango (si se quisiera incluir clientes con 0 pagos en el ranking,
--   sería LEFT JOIN + COALESCE(SUM(...), 0), pero no tendría sentido en
--   un "top pagadores").
-- * Si hubiera empates y se necesitara un desempate estable, se podría
--   agregar un criterio secundario, ej: ORDER BY total_pagado DESC, c.nombre ASC.
