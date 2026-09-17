<?php
/**
 * A3 · Encuentra el bug de negocio (15 pts)
 *
 * Función original:
 *
 * function diasMora($fechaVencimiento, $fechaPago) {
 *     $dias = (strtotime($fechaPago) - strtotime($fechaVencimiento)) / 86400;
 *     return $dias;
 * }
 *
 * DEFECTOS ENCONTRADOS:
 *
 * 1. Días de mora negativos: si el cliente paga ANTES o EL MISMO DÍA del
 *    vencimiento, la resta da 0 o negativo. Días de mora nunca debería
 *    ser negativo: si pagó a tiempo, la mora es 0, no "-3 días".
 *    Esto puede estar inflando bonificaciones o generando reportes
 *    incorrectos si algo río abajo asume que el valor siempre es >= 0.
 *
 * 2. Resultado con decimales por la hora: strtotime() incluye horas,
 *    minutos y segundos si vienen en el string de fecha. Si
 *    $fechaVencimiento es "2026-09-10 08:00:00" y $fechaPago es
 *    "2026-09-12 07:00:00", la resta da 1.958333 días en vez de 2 (o 1,
 *    según el criterio de negocio), porque no se completó el día 2 aún.
 *    El resultado no es un número entero de días, que es lo que "días
 *    de mora" debería representar.
 *
 * 3. (relacionado con el punto 2) No hay validación de que las fechas
 *    sean parseables: si strtotime() falla, devuelve `false`, y
 *    `false - false` da 0 silenciosamente, ocultando un error de datos.
 *
 * CORRECCIÓN: normalizar a medianoche antes de restar (para contar días
 * calendario completos, no horas), usar cero como piso, y validar el
 * parseo de las fechas.
 */

function diasMora(string $fechaVencimiento, string $fechaPago): int
{
    $vencimiento = strtotime($fechaVencimiento);
    $pago        = strtotime($fechaPago);

    if ($vencimiento === false || $pago === false) {
        throw new InvalidArgumentException('Fecha inválida recibida en diasMora().');
    }

    // Normalizamos a medianoche para comparar días calendario completos,
    // sin que la hora del día distorsione el cálculo.
    $vencimiento = strtotime('midnight', $vencimiento);
    $pago        = strtotime('midnight', $pago);

    $dias = (int) (($pago - $vencimiento) / 86400);

    // Un pago a tiempo o anticipado no genera mora negativa.
    return max(0, $dias);
}

/**
 * Alternativa usando DateTime (más explícita, evita depender de strtotime
 * para parseo ambiguo de formatos):
 *
 * function diasMora(string $fechaVencimiento, string $fechaPago): int
 * {
 *     $venc = new DateTime($fechaVencimiento);
 *     $pago = new DateTime($fechaPago);
 *     $venc->setTime(0, 0, 0);
 *     $pago->setTime(0, 0, 0);
 *     $dias = (int) $venc->diff($pago)->format('%r%a');
 *     return max(0, $dias);
 * }
 */
