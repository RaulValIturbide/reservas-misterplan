<?php
declare(strict_types=1);

namespace App\Database;

use PDO;
use PDOException;
use RuntimeException;

class Conexion
{
    private static ?PDO $pdo = null;

    public static function obtenerConexion(): PDO
    {
        if (self::$pdo !== null) {
            return self::$pdo;
        }
        $host = $_ENV['DB_HOST'] ?? 'localhost';
        $puerto = $_ENV['DB_PORT'] ?? '3306';
        $nombreBase = $_ENV['DB_NAME'] ?? '';
        $usuario = $_ENV['DB_USER'] ?? '';
        $contrasenya = $_ENV['DB_PASS'] ?? '';

        $dsn = "mysql:host=$host;port=$puerto;dbname=$nombreBase;charset=utf8mb4";

        $opciones = [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false
        ];

        try {
            self::$pdo = new PDO($dsn, $usuario, $contrasenya, $opciones);
        } catch (PDOException $e) {
            throw new RuntimeException('Error conectando a la base de datos.', 0, $e);
        }
        return self::$pdo;
    }


}
