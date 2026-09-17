-- B2 · Traducción entre motores (8 pts)
--
-- Original (MySQL):
-- SELECT `id`, `nombre` FROM `clientes`
-- ORDER BY `nombre`
-- LIMIT 20 OFFSET 40;
--
-- Equivalente en T-SQL (SQL Server), página 3 de 20 registros:

SELECT [id], [nombre]
FROM [clientes]
ORDER BY [nombre]
OFFSET 40 ROWS
FETCH NEXT 20 ROWS ONLY;

-- Notas:
-- * MySQL usa backticks (`) para identificadores; T-SQL usa corchetes ([]).
--   (En ambos casos son opcionales si el nombre no es palabra reservada,
--   pero se mantienen para respetar el estilo original.)
-- * LIMIT 20 OFFSET 40  =>  OFFSET 40 ROWS FETCH NEXT 20 ROWS ONLY.
--   OFFSET/FETCH en T-SQL REQUIERE un ORDER BY (a diferencia de MySQL,
--   donde LIMIT/OFFSET funcionan incluso sin ORDER BY, aunque el orden
--   de resultado no estaría garantizado ahí tampoco).
-- * "Página 3 de 20 registros" = registros 41 al 60 => OFFSET 40 ROWS
--   (se saltan las 2 páginas anteriores, 20 y 20) FETCH NEXT 20 ROWS ONLY.
