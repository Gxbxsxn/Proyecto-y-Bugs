<?php
require_once __DIR__ . '/Database.php';

/**
 * A1 (real) · Login sin inyección SQL y con password_hash/password_verify.
 * Misma lógica que /A-php/A1_login_seguro.php, corriendo aquí contra una
 * base de datos real vía PDO.
 */
class AuthService
{
    public static function login(string $usuario, string $clave): ?array
    {
        $pdo = Database::get();

        $stmt = $pdo->prepare('SELECT id, usuario, clave_hash FROM usuarios WHERE usuario = :usuario');
        $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
        $stmt->execute();

        $row = $stmt->fetch();
        if (!$row) {
            return null; // no revela si el usuario existe o no
        }

        if (!password_verify($clave, $row['clave_hash'])) {
            return null;
        }

        return ['id' => $row['id'], 'usuario' => $row['usuario']];
    }
}
