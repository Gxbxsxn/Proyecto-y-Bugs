<?php
/**
 * A2 · Registrar un pago con transacción (10 pts)
 *
 * Wrapper simple de PDO. Se asume que $this->pdo es la conexión PDO
 * (modo excepción: PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION).
 */

class PagosRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    /**
     * Inserta un pago dentro de una transacción.
     *
     * @param int    $clienteId
     * @param float  $monto
     * @param string $formaPago
     * @param string $fechaPago  formato 'Y-m-d H:i:s'
     * @return int   ID del pago generado
     * @throws InvalidArgumentException si el monto no es válido
     * @throws Throwable si falla la inserción (se relanza tras el rollback)
     */
    public function registrarPago(int $clienteId, float $monto, string $formaPago, string $fechaPago): int
    {
        // Validación de negocio antes de tocar la BD.
        if ($monto <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $this->pdo->beginTransaction();

        try {
            $sql = "INSERT INTO pagos (cliente_id, monto, forma_pago, fecha_pago)
                    VALUES (:cliente_id, :monto, :forma_pago, :fecha_pago)";

            $stmt = $this->pdo->prepare($sql);
            $stmt->execute([
                ':cliente_id' => $clienteId,
                ':monto'      => $monto,
                ':forma_pago' => $formaPago,
                ':fecha_pago' => $fechaPago,
            ]);

            $id = (int) $this->pdo->lastInsertId();

            // Aquí es donde, en un caso real, irían otras operaciones
            // relacionadas (actualizar saldo del cliente, etc.) que deben
            // ser atómicas junto con el insert del pago.

            $this->pdo->commit();

            return $id;
        } catch (Throwable $e) {
            // Si algo falla (constraint, conexión, lógica adicional),
            // se revierte todo y se relanza para que la capa superior
            // decida cómo responder (log, mensaje al usuario, etc.).
            $this->pdo->rollBack();
            throw $e;
        }
    }
}

/**
 * Notas para la entrevista:
 * - beginTransaction/commit/rollBack garantizan atomicidad: si el insert
 *   falla a medio camino (o si en el futuro se agregan más operaciones
 *   dentro del mismo método) no queda el sistema en un estado inconsistente.
 * - La validación de monto > 0 se hace ANTES de abrir la transacción para
 *   no pagar el costo de abrir/cerrar una transacción con datos inválidos.
 * - Uso parámetros nombrados (:cliente_id, etc.) en vez de posicionales
 *   para que la query sea legible y evitar errores de orden.
 * - lastInsertId() se lee dentro de la transacción, antes del commit,
 *   que es el uso estándar con PDO/MySQL/SQL Server (con SQL Server usar
 *   SCOPE_IDENTITY() vía OUTPUT si se prefiere evitar lastInsertId).
 */
