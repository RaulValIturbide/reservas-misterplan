<?php
declare(strict_types=1);

namespace App\Http;

/**
 * Punto único para enviar respuestas JSON (código HTTP + cabecera + cuerpo).
 * Lo usan el controlador y el index.php (404, 405 y 500 globales).
 */
final class Respuesta
{
    public static function json(array $datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
    }
}
