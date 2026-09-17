<?php
/**
 * Conexión PDO a la base de datos.
 *
 * Usa SQLite (un archivo .sqlite en disco) para que el backend corra
 * sin instalar MySQL/SQL Server. Toda la capa de acceso a datos usa
 * PDO + prepared statements, exactamente igual que se haría contra
 * MySQL o SQL Server — para migrar solo cambiaría esta conexión:
 *
 *   MySQL:      new PDO('mysql:host=localhost;dbname=cobros;charset=utf8mb4', $user, $pass)
 *   SQL Server: new PDO('sqlsrv:Server=localhost;Database=cobros', $user, $pass)
 */

class Database
{
    private static ?PDO $instance = null;

    public static function get(): PDO
    {
        if (self::$instance === null) {
            $dbPath = __DIR__ . '/../data/cobros.sqlite';

            $nuevaBD = !file_exists($dbPath);

            $pdo = new PDO('sqlite:' . $dbPath);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
            $pdo->exec('PRAGMA foreign_keys = ON');

            if ($nuevaBD) {
                $schema = file_get_contents(__DIR__ . '/schema.sql');
                $pdo->exec($schema);
                self::seed($pdo);
            }

            self::$instance = $pdo;
        }

        return self::$instance;
    }

    /**
     * Carga datos de ejemplo la primera vez que se crea la base de datos,
     * para que el sistema no arranque vacío.
     */
    private static function seed(PDO $pdo): void
    {
        $stmtUsuario = $pdo->prepare(
            'INSERT INTO usuarios (usuario, clave_hash) VALUES (:usuario, :hash)'
        );
        $stmtUsuario->execute([
            ':usuario' => 'analista',
            ':hash'    => password_hash('cobros2026', PASSWORD_DEFAULT),
        ]);

        $clientes = [
            ['Distribuidora Maíz Real', 'NIT-0012'],
            ['Ferretería El Tornillo', 'NIT-0044'],
            ['Panadería Doña Elsa', 'NIT-0091'],
            ['Transportes Quiché', 'NIT-0133'],
            ['Textiles San Marcos', 'NIT-0207'],
            ['Farmacia Vida Plena', 'NIT-0258'],
            ['Constructora Alto Real', 'NIT-0301'],
            ['Comercial Petén Norte', 'NIT-0355'],
        ];

        $stmtCliente = $pdo->prepare(
            'INSERT INTO clientes (nombre, nit) VALUES (:nombre, :nit)'
        );
        foreach ($clientes as $c) {
            $stmtCliente->execute([':nombre' => $c[0], ':nit' => $c[1]]);
        }

        $clienteIds = $pdo->query('SELECT id FROM clientes')->fetchAll(PDO::FETCH_COLUMN);
        $formas = ['Transferencia', 'Efectivo', 'Tarjeta', 'Cheque'];

        $stmtPago = $pdo->prepare(
            'INSERT INTO pagos (cliente_id, monto, forma_pago, fecha_vencimiento, fecha_pago)
             VALUES (:cliente_id, :monto, :forma_pago, :fecha_vencimiento, :fecha_pago)'
        );

        foreach ($clienteIds as $clienteId) {
            $nPagos = random_int(3, 6);
            for ($i = 0; $i < $nPagos; $i++) {
                $diasAtras = random_int(0, 140);
                $vencimiento = (new DateTime())->modify("-{$diasAtras} days")->modify('-' . random_int(0, 10) . ' days');
                $desfase = random_int(-3, 14); // negativo = pagó antes, positivo = pagó con mora
                $fechaPago = (clone $vencimiento)->modify(($desfase >= 0 ? '+' : '') . "{$desfase} days");

                $stmtPago->execute([
                    ':cliente_id'         => $clienteId,
                    ':monto'              => round(random_int(30000, 420000) / 100, 2),
                    ':forma_pago'         => $formas[array_rand($formas)],
                    ':fecha_vencimiento'  => $vencimiento->format('Y-m-d H:i:s'),
                    ':fecha_pago'         => $fechaPago->format('Y-m-d H:i:s'),
                ]);
            }
        }
    }
}
