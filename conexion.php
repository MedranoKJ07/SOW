
<?php

/**
 * Creates the application's MySQL connection from the process environment.
 *
 * Keeping this function as the single connection boundary means pages can use
 * either `$db = conectarDB()` or the shared `$conn` created below without
 * embedding deployment credentials in source code.
 */
function conectarDB(): mysqli
{
    $required = [
        'DB_HOST' => getenv('DB_HOST') ?: '',
        'DB_USER' => getenv('DB_USER') ?: '',
        'DB_PASS' => getenv('DB_PASS') ?: '',
        'DB_NAME' => getenv('DB_NAME') ?: '',
    ];

    foreach ($required as $name => $value) {
        if ($value === '') {
            throw new RuntimeException("Falta la variable de entorno {$name}.");
        }
    }

    $port = (int) (getenv('DB_PORT') ?: 3306);
    if ($port < 1 || $port > 65535) {
        throw new RuntimeException('DB_PORT debe estar entre 1 y 65535.');
    }

    mysqli_report(MYSQLI_REPORT_OFF);
    $conn = new mysqli($required['DB_HOST'], $required['DB_USER'], $required['DB_PASS'], $required['DB_NAME'], $port);
    if ($conn->connect_errno) {
        error_log('Conexión MySQL fallida: código ' . $conn->connect_errno);
        throw new RuntimeException('No fue posible conectar con la base de datos.');
    }

    if (!$conn->set_charset('utf8mb4')) {
        $conn->close();
        throw new RuntimeException('No fue posible establecer el juego de caracteres de la base de datos.');
    }

    return $conn;
}

// Compatibilidad con los módulos existentes que esperan `$conn` después del include.
$conn = conectarDB();
