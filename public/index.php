<?php
declare(strict_types=1);

use App\Controller\ReservaController;
use App\Database\Conexion;
use App\Http\Respuesta;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Validation\FiltroReservaValidation;
use App\Validation\ReservaValidation;
use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

try {
    $dot = Dotenv::createImmutable(dirname(__DIR__));
    $dot->load();

    // Composición de dependencias (inyección manual)
    $pdo = Conexion::obtenerConexion();
    $reservaService = new ReservaService(
        $pdo,
        new ReservaRepository($pdo),
        new ReservaEventoRepository($pdo)
    );
    $controller = new ReservaController(
        $reservaService,
        new ReservaValidation(),
        new FiltroReservaValidation()
    );

    // Enrutado
    $metodo = $_SERVER['REQUEST_METHOD'];
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $fragmentos = explode('/', $ruta);
    $id = null;
    if (count($fragmentos) === 4
        && $fragmentos[1] === 'api'
        && $fragmentos[2] === 'reservas'
        && ctype_digit($fragmentos[3])) {
        $id = (int)$fragmentos[3];
        $ruta = '/api/reservas/id';
    }

    // Rutas de la API y los métodos que acepta cada una (única fuente de verdad para 404/405)
    $metodosPermitidos = [
        '/api/reservas'    => ['GET', 'POST'],
        '/api/reservas/id' => ['GET', 'PATCH'],
    ];

    if (!isset($metodosPermitidos[$ruta])) {
        Respuesta::json(['mensaje' => 'Ruta no encontrada.'], 404);
    } elseif (!in_array($metodo, $metodosPermitidos[$ruta], true)) {
        header('Allow: ' . implode(', ', $metodosPermitidos[$ruta]));
        Respuesta::json(['mensaje' => 'Método no permitido.'], 405);
    } else {
        match ($metodo . $ruta) {
            'GET/api/reservas'      => $controller->listar(),
            'GET/api/reservas/id'   => $controller->mostrar($id),
            'POST/api/reservas'     => $controller->crear(),
            'PATCH/api/reservas/id' => $controller->cambiarEstado($id),
        };
    }
} catch (Throwable $ex) {
    error_log((string) $ex);
    Respuesta::json(['mensaje' => 'Error interno del servidor.'], 500);
}
