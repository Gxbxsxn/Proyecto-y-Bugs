# B3 · Rendimiento (7 pts)

**Pregunta:** una consulta que filtra `pagos` por `fecha_pago` se volvió
lenta al crecer la tabla a varios millones de filas. ¿Qué revisarías y
qué harías, en orden?

## Orden de diagnóstico y acción

1. **Ver el plan de ejecución real** (`EXPLAIN` en MySQL / `SET SHOWPLAN_ALL ON`
   o el *Actual Execution Plan* en SQL Server) para confirmar qué está
   pasando de verdad, en vez de asumir. Lo que busco específicamente:
   si está haciendo un **table scan / clustered index scan** completo en
   vez de un **index seek**.

2. **Confirmar si existe un índice sobre `fecha_pago`** (o sobre las
   columnas que realmente usa el `WHERE`/`JOIN`/`ORDER BY` de esa
   consulta). Si no existe, esa suele ser la causa #1 de que una tabla
   de millones de filas se vuelva lenta al filtrar por fecha: sin índice,
   cada consulta recorre toda la tabla.

   ```sql
   CREATE INDEX ix_pagos_fecha_pago ON pagos (fecha_pago);
   ```

   Si la consulta también filtra por `cliente_id` u otra columna junto
   con la fecha, evalúo un **índice compuesto** en el orden que más
   selectividad aporte (ej. `(cliente_id, fecha_pago)` si casi siempre
   se filtra por cliente primero).

3. **Revisar si el índice existe pero no se usa** — causas típicas:
   - Se está aplicando una función sobre la columna en el WHERE
     (ej. `WHERE YEAR(fecha_pago) = 2026` o `CONVERT(...)`), lo cual
     vuelve el índice "no sargable" (no se puede usar para buscar).
     Corrección: reescribir como rango
     (`WHERE fecha_pago >= '2026-01-01' AND fecha_pago < '2027-01-01'`).
   - Estadísticas desactualizadas → el optimizador elige mal el plan.
     Corrección: `UPDATE STATISTICS` (SQL Server) / `ANALYZE TABLE` (MySQL).
   - Tipo de dato distinto entre la columna y el parámetro (ej. comparar
     `datetime` contra `varchar`), que fuerza una conversión implícita
     y descarta el índice.

4. **Revisar la selectividad del filtro**: si el rango de fechas
   consultado devuelve un porcentaje muy alto de la tabla (ej. "últimos
   2 años" sobre una tabla de 3 años), el optimizador puede preferir
   igual un scan completo porque es más barato que millones de *lookups*
   al índice. En ese caso el problema no es falta de índice sino que la
   consulta pide demasiados datos; ahí evaluaría paginación, agregar
   filtros adicionales, o un índice *covering* que incluya las columnas
   del SELECT para evitar el *key lookup*.

5. **Si el patrón de acceso es casi siempre por rango de fecha reciente**
   (ej. reportes del último mes/trimestre), considerar **particionar la
   tabla por fecha_pago** para que el motor descarte particiones enteras
   sin tener que tocarlas.

6. **Revisar el hardware/config como último recurso** (memoria disponible
   para buffer pool/cache, si hay locks/bloqueos concurrentes en esa
   tabla) — pero solo después de confirmar que el problema no es de
   índices o de la propia consulta, que es la causa más común y más
   barata de arreglar.

En resumen: primero *mido* (plan de ejecución), luego reviso si *existe*
el índice correcto, luego si *se está usando* correctamente, y solo
después considero soluciones estructurales más grandes (particionamiento,
hardware).
