<?php
declare(strict_types=1);

use App\Controller\ReservaController;
use App\Controller\VistaController;
use App\Database\Conexion;
use App\Http\Respuesta;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Validation\FiltroReservaValidation;
use App\Validation\ReservaValidation;
use Twig\Environment;
use Twig\Loader\FilesystemLoader;
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

    // Carga de Twig
    $loader = new FilesystemLoader(dirname(__DIR__) . '/templates');
    $twig = new Environment($loader, [
        'autoescape' => 'html',
        'strict_variables' => true,
    ]);
    $vistaController = new VistaController($reservaService, $twig);

    // Enrutado
    $metodo = $_SERVER['REQUEST_METHOD'];
    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $fragmentos = explode('/', $ruta);
    $id = null;
    if (count($fragmentos) === 4
        && $fragmentos[1] === 'api'
        && $fragmentos[2] === 'reservas'
        && ctype_digit($fragmentos[3])) {
        // /api/reservas/{id}
        $id = (int)$fragmentos[3];
        $ruta = '/api/reservas/id';
    } elseif (count($fragmentos) === 3
        && $fragmentos[1] === 'reservas'
        && ctype_digit($fragmentos[2])) {
        // /reservas/{id} (vista HTML)
        $id = (int)$fragmentos[2];
        $ruta = '/reservas/id';
    }

    // Rutas (vistas y API) y los métodos que acepta cada una (única fuente de verdad para 404/405)
    $metodosPermitidos = [
        '/' => ['GET'],
        '/reservas/id' => ['GET'],
        '/api/reservas' => ['GET', 'POST'],
        '/api/reservas/id' => ['GET', 'PATCH'],
    ];

    if (!isset($metodosPermitidos[$ruta])) {
        Respuesta::json(['mensaje' => 'Ruta no encontrada.'], 404);
    } elseif (!in_array($metodo, $metodosPermitidos[$ruta], true)) {
        header('Allow: ' . implode(', ', $metodosPermitidos[$ruta]));
        Respuesta::json(['mensaje' => 'Método no permitido.'], 405);
    } else {
        match ($metodo . $ruta) {
            'GET/' => $vistaController->listado(),
            'GET/reservas/id' => $vistaController->detalle($id),
            'GET/api/reservas' => $controller->listar(),
            'GET/api/reservas/id' => $controller->mostrar($id),
            'POST/api/reservas' => $controller->crear(),
            'PATCH/api/reservas/id' => $controller->cambiarEstado($id),
        };
    }
} catch (Throwable $ex) {
    error_log((string)$ex);
    Respuesta::json(['mensaje' => 'Error interno del servidor.'], 500);
}
