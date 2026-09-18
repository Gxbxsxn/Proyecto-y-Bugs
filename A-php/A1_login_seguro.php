<?php
/**
 * A1 · Corrige la vulnerabilidad (10 pts)
 *
 * PROBLEMAS ENCONTRADOS en la función original:
 *
 * 1. SQL Injection: el usuario y la clave se concatenan directo en el
 *    string SQL. Un usuario como  admin' -- convierte la consulta en
 *    "SELECT ... WHERE usuario = 'admin' -- ' AND clave = '...'" y
 *    comenta la validación de clave. Con  ' OR '1'='1  se puede lograr
 *    lo mismo sin conocer ninguna credencial.
 *
 * 2. Contraseñas en texto plano: se compara la clave directo contra la
 *    columna `clave` en la BD. Si la BD se filtra, todas las contraseñas
 *    quedan expuestas. Deben guardarse con password_hash() y verificarse
 *    con password_verify().
 *
 * 3. Fuga de información en el error: el die() expone el mensaje de
 *    "usuario o clave incorrectos" junto con la query SQL completa.
 *    Eso le da a un atacante la estructura exacta de la tabla/consulta
 *    (nombres de columnas, sintaxis) y además revela si el usuario
 *    existe o no dependiendo del mensaje, facilitando enumeración de
 *    usuarios.
 *
 * 4. Uso de global $conn y die(): acopla la función al scope global y
 *    corta la ejecución del script completo ante un fallo de login,
 *    lo cual es un mal manejo de errores para una API/aplicación real.
 *
 * 5. query() en vez de prepare()/execute(): además de habilitar la
 *    inyección, query() no separa datos de código SQL.
 *
 * SOLUCIÓN: prepared statements + hash de contraseña + manejo de
 * errores sin fugar información + misma firma función login($usuario, $clave).
 */

function login($usuario, $clave)
{
    global $conn; // conexión PDO

    // 1. Prepared statement: los parámetros nunca se concatenan al SQL.
    $sql = "SELECT id, nombre, clave FROM usuarios WHERE usuario = :usuario";
    $stmt = $conn->prepare($sql);
    $stmt->bindParam(':usuario', $usuario, PDO::PARAM_STR);
    $stmt->execute();

    $row = $stmt->fetch(PDO::FETCH_ASSOC);

    // 2. Mensaje genérico si el usuario no existe (no revela si el
    //    usuario existe o no).
    if (!$row) {
        return false;
    }

    // 3. Verificación de contraseña con hash (nunca comparar en claro).
    if (!password_verify($clave, $row['clave'])) {
        return false;
    }

    // 4. Nunca devolver el hash de la contraseña al llamador.
    unset($row['clave']);

    return $row;
}
