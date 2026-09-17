<?php
/**
 * Notifica al servidor de tiempo real (Node + Socket.IO, D1) cuando se
 * registra un pago, para que avise solo a la "sala" del cliente
 * correspondiente. Es una llamada HTTP simple entre los dos backends —
 * un patrón común cuando el servidor de negocio (PHP) y el servidor de
 * tiempo real (Node) son procesos separados.
 *
 * Si el servidor de notificaciones no está corriendo, el pago se sigue
 * registrando con normalidad (la notificación en tiempo real es un
 * complemento, no debe tumbar el registro del pago si falla).
 */
class NotificadorTiempoReal
{
    private const URL = 'http://localhost:4000/notify';
    private const SECRETO = 'oca-reto-tecnico-demo-secret-2026'; // compartido con backend-socket

    public static function notificarPago(int $clienteId, array $pago): void
    {
        $payload = json_encode(['clienteId' => $clienteId, 'pago' => $pago]);

        $context = stream_context_create([
            'http' => [
                'method'  => 'POST',
                'header'  => "Content-Type: application/json\r\nX-Notify-Secret: " . self::SECRETO . "\r\n",
                'content' => $payload,
                'timeout' => 2, // no bloquear el registro del pago si el otro servidor está lento/caído
            ],
        ]);

        // @ para no interrumpir la respuesta al cliente si el servidor
        // de notificaciones no está levantado; en un sistema real esto
        // se registraría en un log en vez de silenciarse del todo.
        @file_get_contents(self::URL, false, $context);
    }
}
