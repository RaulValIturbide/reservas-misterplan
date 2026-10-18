<?php
declare(strict_types=1);

use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use App\Service\ReservaService;
use App\Database\Conexion;
use App\Exception\ValidacionException;
use App\Validation\ReservaValidation;
use Dotenv\Dotenv;

/**
 * @param array $datos
 * @param int $codigo
 * @return void
 */
function responderJson(array $datos, int $codigo = 200): void
{
    http_response_code($codigo);
    header('Content-Type: application/json; charset=utf-8');
    echo json_encode($datos, JSON_UNESCAPED_UNICODE);
}

function mostrarReserva(ReservaService $reservaService, int $numID): void
{
    $respuesta = $reservaService->obtenerDetalle($numID);
    if ($respuesta !== null) {
        responderJson($respuesta, 200);
    } else {
        responderJson(['mensaje' => 'Reserva no encontrada'], 404);
    }
}

function leerCuerpoJson(): ?array
{
    $texto = file_get_contents('php://input');

    $datos = json_decode($texto, true);
    if (is_array($datos)) {
        return $datos;
    } else {
        responderJson(['mensaje' => 'El cuerpo de la petición debe ser un JSON válido'], 400);
        return null;
    }
}

function crearReserva(ReservaService $reservaServicio): void
{
    $reservaValidation = new ReservaValidation();
    $datos = leerCuerpoJson();
    if (is_array($datos)) {
        try {
            $reserva = $reservaValidation->validarReserva($datos);
            $idCreado = $reservaServicio->crear($reserva);

            responderJson($reservaServicio->obtenerDetalle($idCreado), 201);

        } catch (ValidacionException $e) {
            responderJson(['errores' => $e->errores], 400);
        } catch (Throwable $e) {
            responderJson(['mensaje' => 'Error al crear la reserva'], 500);
        }
    }

}

try {

    require dirname(__DIR__) . '/vendor/autoload.php';

    $dot = Dotenv::createImmutable(dirname(__DIR__));
    $dot->load();

    $pdoSingleton = Conexion::obtenerConexion();
    $repoReserva = new ReservaRepository($pdoSingleton);
    $repoReservaEvento = new ReservaEventoRepository($pdoSingleton);
    $reservaService = new ReservaService($pdoSingleton, $repoReserva, $repoReservaEvento);


    $metodo = $_SERVER['REQUEST_METHOD'];

    $ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

    $fragmentos = explode('/', $ruta);
    $numIdConsulta = null;

    if (count($fragmentos) === 4
        && $fragmentos[1] === 'api'
        && $fragmentos[2] === 'reservas'
        && ctype_digit($fragmentos[3])) {
        $numIdConsulta = (int)$fragmentos[3];
        $ruta = '/api/reservas/id';
    }


    $peticion = $metodo . $ruta;
    match ($peticion) {
        'GET/api/reservas' => responderJson($reservaService->listarReservas(), 200),
        'GET/api/reservas/id' => mostrarReserva($reservaService, $numIdConsulta),
        'POST/api/reservas' => crearReserva($reservaService),
        'PATCH/api/reservas/id' => responderJson(['mensaje' => 'actualizar'], 200),
        'DELETE/api/reservas/id' => responderJson(['mensaje' => 'no permitido'], 405),

        default => responderJson(['mensaje' => 'ruta no encontrada'], 404),
    };
} catch (Throwable $ex) {
    error_log($ex->getMessage());
    responderJson(['mensaje' => "Error interno del servidor."], 500);
}



