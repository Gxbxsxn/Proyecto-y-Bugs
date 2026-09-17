<?php
require_once __DIR__ . '/Database.php';

/**
 * A2, A3 y B1 (reales) · Registrar pagos con transacción, calcular mora
 * corregida y calcular el top 5 de clientes. Misma lógica que en
 * /A-php/A2_registrar_pago.php, /A-php/A3_dias_mora.php y
 * /B-sql/B1_top5_clientes.sql, corriendo aquí contra la base de datos real.
 */
class PagosService
{
    /** A3 · corregido: sin mora negativa, sin decimales por hora. */
    public static function diasMora(string $fechaVencimiento, string $fechaPago): int
    {
        $venc = new DateTime($fechaVencimiento);
        $pago = new DateTime($fechaPago);
        $venc->setTime(0, 0, 0);
        $pago->setTime(0, 0, 0);

        $dias = (int) $venc->diff($pago)->format('%r%a');
        return max(0, $dias);
    }

    public static function listarClientes(string $filtro = ''): array
    {
        $pdo = Database::get();

        $sql = 'SELECT id, nombre, nit FROM clientes
                WHERE nombre LIKE :filtro OR nit LIKE :filtro
                ORDER BY nombre';
        $stmt = $pdo->prepare($sql);
        $stmt->execute([':filtro' => '%' . $filtro . '%']);
        $clientes = $stmt->fetchAll();

        foreach ($clientes as &$c) {
            $pagos = self::listarPagos((int) $c['id']);
            $c['total'] = array_sum(array_column($pagos, 'monto'));
            $c['moraMax'] = 0;
            foreach ($pagos as $p) {
                $mora = self::diasMora($p['fechaVencimiento'], $p['fechaPago']);
                $c['moraMax'] = max($c['moraMax'], $mora);
            }
        }

        return $clientes;
    }

    public static function listarPagos(int $clienteId): array
    {
        $pdo = Database::get();
        $stmt = $pdo->prepare(
            'SELECT id, cliente_id, monto, forma_pago, fecha_vencimiento, fecha_pago
             FROM pagos WHERE cliente_id = :cliente_id ORDER BY fecha_pago DESC'
        );
        $stmt->execute([':cliente_id' => $clienteId]);

        return array_map(function ($p) {
            return [
                'id'                => (int) $p['id'],
                'clienteId'         => (int) $p['cliente_id'],
                'monto'             => (float) $p['monto'],
                'formaPago'         => $p['forma_pago'],
                'fechaVencimiento'  => $p['fecha_vencimiento'],
                'fechaPago'         => $p['fecha_pago'],
                'diasMora'          => self::diasMora($p['fecha_vencimiento'], $p['fecha_pago']),
            ];
        }, $stmt->fetchAll());
    }

    /** A2 · transacción real: begin / commit / rollback. */
    public static function registrarPago(int $clienteId, float $monto, string $formaPago, string $fechaVencimiento): array
    {
        if ($monto <= 0) {
            throw new InvalidArgumentException('El monto debe ser mayor a cero.');
        }

        $pdo = Database::get();
        $pdo->beginTransaction();

        try {
            $fechaPago = (new DateTime())->format('Y-m-d H:i:s');

            $stmt = $pdo->prepare(
                'INSERT INTO pagos (cliente_id, monto, forma_pago, fecha_vencimiento, fecha_pago)
                 VALUES (:cliente_id, :monto, :forma_pago, :fecha_vencimiento, :fecha_pago)'
            );
            $stmt->execute([
                ':cliente_id'        => $clienteId,
                ':monto'             => $monto,
                ':forma_pago'        => $formaPago,
                ':fecha_vencimiento' => $fechaVencimiento,
                ':fecha_pago'        => $fechaPago,
            ]);

            $id = (int) $pdo->lastInsertId();
            $pdo->commit();

            return [
                'id' => $id,
                'clienteId' => $clienteId,
                'monto' => $monto,
                'formaPago' => $formaPago,
                'fechaVencimiento' => $fechaVencimiento,
                'fechaPago' => $fechaPago,
                'diasMora' => self::diasMora($fechaVencimiento, $fechaPago),
            ];
        } catch (Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
    }

    /** B1 · top 5 clientes que más han pagado en los últimos 90 días. */
    public static function top5UltimosNoventaDias(): array
    {
        $pdo = Database::get();

        $sql = "SELECT c.id, c.nombre, SUM(p.monto) AS total_pagado
                FROM clientes c
                INNER JOIN pagos p ON p.cliente_id = c.id
                WHERE p.fecha_pago >= datetime('now', '-90 days')
                GROUP BY c.id, c.nombre
                ORDER BY total_pagado DESC
                LIMIT 5";
        // Nota: la sintaxis de fecha ('now', '-90 days') es de SQLite;
        // en T-SQL sería DATEADD(DAY, -90, GETDATE()) — ver B-sql/B1_top5_clientes.sql
        // para la versión SQL Server tal como la pide el reto original.

        $stmt = $pdo->query($sql);

        return array_map(function ($row) {
            return [
                'cliente' => ['id' => (int) $row['id'], 'nombre' => $row['nombre']],
                'total'   => (float) $row['total_pagado'],
            ];
        }, $stmt->fetchAll());
    }
}
