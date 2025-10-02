<?php
declare(strict_types=1);

/**
 * db(): devuelve una instancia única de PDO para el blog.
 * - Reusa app/config.php si existe y expone $pdo
 * - Si no, crea su propio PDO de respaldo
 * - Nunca imprime nada
 */
function db(): PDO
{
    static $inst = null;              // singleton local de este archivo
    if ($inst instanceof PDO) {
        return $inst;
    }

    // 1) ¿Existe el PDO global de todo tu proyecto?
    //    app/blog/app/db.php  ->  ../../config.php  (tu config raíz)
    $rootConfig = __DIR__ . '/../../config.php';
    if (is_file($rootConfig)) {
        // Incluye la config principal. Debe definir $pdo (PDO)
        /** @var PDO $pdo */
        require $rootConfig;

        if (isset($pdo) && $pdo instanceof PDO) {
            $inst = $pdo;
            return $inst;
        }
        // Si por alguna razón no definió $pdo, seguimos al fallback.
    }

    // 2) Fallback: crea un PDO propio del blog
    //    Puedes ajustar estas variables según tu docker-compose.
    //    Intentamos detectar host/credenciales de entorno primero.
    $host = getenv('MYSQL_HOST') ?: 'localhost';
    $db   = getenv('MYSQL_DATABASE') ?: 'blog';
    $user = getenv('MYSQL_USER') ?: 'root';
    $pass = getenv('MYSQL_PASSWORD') ?: '';

    $dsn = "mysql:host={$host};dbname={$db};charset=utf8mb4";

    $inst = new PDO($dsn, $user, $pass, [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ]);

    return $inst;
}
