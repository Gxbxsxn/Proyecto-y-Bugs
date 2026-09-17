<?php
/**
 * JWT mínimo, propio, firmado con HMAC-SHA256.
 *
 * No es una librería externa (para no requerir `composer install`), pero
 * implementa el mismo principio real que un JWT: header.payload.firma,
 * codificados en base64url, con la firma verificando que el token no fue
 * alterado y que fue emitido por este servidor. Esto es lo que hace que
 * el interceptor 401 del frontend (C3) reaccione a un 401 *real* del
 * servidor, no a un temporizador simulado en el navegador.
 */

class Jwt
{
    // En un proyecto real esto vendría de una variable de entorno,
    // nunca hardcodeado en el repositorio.
    private const SECRET = 'oca-reto-tecnico-demo-secret-2026';

    public static function emitir(array $payload): string
    {
        $header = self::base64UrlEncode(json_encode(['alg' => 'HS256', 'typ' => 'JWT']));
        $body   = self::base64UrlEncode(json_encode($payload));
        $firma  = self::firmar("$header.$body");

        return "$header.$body.$firma";
    }

    /**
     * @return array|null El payload decodificado si el token es válido y
     *                     no ha expirado; null en cualquier otro caso.
     */
    public static function verificar(?string $token): ?array
    {
        if (!$token) {
            return null;
        }

        $partes = explode('.', $token);
        if (count($partes) !== 3) {
            return null;
        }

        [$header, $body, $firma] = $partes;

        $firmaEsperada = self::firmar("$header.$body");
        if (!hash_equals($firmaEsperada, $firma)) {
            return null; // firma inválida: el token fue alterado o no lo emitimos nosotros
        }

        $payload = json_decode(self::base64UrlDecode($body), true);
        if (!$payload || !isset($payload['exp']) || time() >= $payload['exp']) {
            return null; // token expirado
        }

        return $payload;
    }

    private static function firmar(string $data): string
    {
        return self::base64UrlEncode(hash_hmac('sha256', $data, self::SECRET, true));
    }

    private static function base64UrlEncode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }

    private static function base64UrlDecode(string $data): string
    {
        return base64_decode(strtr($data, '-_', '+/'));
    }
}
