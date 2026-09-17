<?php
/**
 * Front controller de la API — Torre de Cobros (backend real).
 *
 * Rutas:
 *   POST /api/login              { usuario, clave }              -> { token, usuario }
 *   GET  /api/clientes?filtro=   (requiere token)                 -> [ clientes ]
 *   GET  /api/pagos?cliente_id=  (requiere token)                 -> [ pagos ]
 *   POST /api/pagos              (requiere token)                 -> pago creado
 *   GET  /api/top5               (requiere token)                 -> ranking
 *
 * Arrancar con:  php -S localhost:8000 -t public
 */

require_once __DIR__ . '/../src/Database.php';
require_once __DIR__ . '/../src/Jwt.php';
require_once __DIR__ . '/../src/AuthService.php';
require_once __DIR__ . '/../src/PagosService.php';
require_once __DIR__ . '/../src/NotificadorTiempoReal.php';

header('Content-Type: application/json; charset=utf-8');

// --- CORS: para que el frontend (abierto como archivo local o en otro
// puerto) pueda llamar a esta API durante el desarrollo. ---
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Headers: Content-Type, Authorization');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(204);
    exit;
}

function responder(int $status, $data): void
{
    http_response_code($status);
    echo json_encode($data);
    exit;
}

function cuerpoJson(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true);
    return is_array($data) ? $data : [];
}

/**
 * C3 (real) · Middleware de autenticación: exige un Bearer token válido.
 * Si el token falta, está mal firmado o expiró, responde 401 de verdad
 * — el mismo 401 real que el interceptor de Axios del frontend detecta
 * para cerrar la sesión.
 */
function requireAuth(): array
{
    $header = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
    if (!preg_match('/Bearer\s+(.+)/', $header, $m)) {
        responder(401, ['error' => 'No autenticado.']);
    }

    $payload = Jwt::verificar($m[1]);
    if (!$payload) {
        responder(401, ['error' => 'Token inválido o expirado.']);
    }

    return $payload;
}

$path   = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];

try {
    if ($path === '/api/login' && $method === 'POST') {
        $body = cuerpoJson();
        $usuario = $body['usuario'] ?? '';
        $clave   = $body['clave'] ?? '';

        $resultado = AuthService::login($usuario, $clave);
        if (!$resultado) {
            responder(401, ['error' => 'Usuario o clave incorrectos.']);
        }

        // Token de vida corta (3 minutos) a propósito, para poder ver
        // en vivo cómo el interceptor reacciona a un 401 real cuando expira.
        $token = Jwt::emitir([
            'sub' => $resultado['usuario'],
            'exp' => time() + 180,
        ]);

        responder(200, ['token' => $token, 'usuario' => $resultado['usuario'], 'expiraEn' => 180]);
    }

    if ($path === '/api/clientes' && $method === 'GET') {
        requireAuth();
        $filtro = $_GET['filtro'] ?? '';
        responder(200, PagosService::listarClientes($filtro));
    }

    if ($path === '/api/pagos' && $method === 'GET') {
        requireAuth();
        $clienteId = (int) ($_GET['cliente_id'] ?? 0);
        responder(200, PagosService::listarPagos($clienteId));
    }

    if ($path === '/api/pagos' && $method === 'POST') {
        requireAuth();
        $body = cuerpoJson();

        $pago = PagosService::registrarPago(
            (int) ($body['clienteId'] ?? 0),
            (float) ($body['monto'] ?? 0),
            (string) ($body['formaPago'] ?? ''),
            (string) ($body['fechaVencimiento'] ?? (new DateTime())->format('Y-m-d H:i:s'))
        );

        // D1 (real): avisa al servidor de Socket.IO para que notifique
        // solo a la sala de este cliente.
        NotificadorTiempoReal::notificarPago($pago['clienteId'], $pago);

        responder(201, $pago);
    }

    if ($path === '/api/top5' && $method === 'GET') {
        requireAuth();
        responder(200, PagosService::top5UltimosNoventaDias());
    }

    responder(404, ['error' => 'Ruta no encontrada.']);

} catch (InvalidArgumentException $e) {
    // Errores de validación de negocio (ej. monto <= 0): 400, con mensaje claro.
    responder(400, ['error' => $e->getMessage()]);
} catch (Throwable $e) {
    // Cualquier otro error: nunca se expone el detalle interno al cliente
    // (ver A1 — no filtrar información de errores), solo se registra.
    error_log($e->getMessage());
    responder(500, ['error' => 'Error interno del servidor.']);
}
