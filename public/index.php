<?php
declare(strict_types=1);

use App\Repository\ReservaRepository;
use App\Database\Conexion;
use App\Exception\ValidacionException;
use App\Validation\ReservaValidation;
use Dotenv\Dotenv;


require dirname(__DIR__) . '/vendor/autoload.php';

$dot = Dotenv::createImmutable(dirname(__DIR__));
$dot->load();

$repoReserva = new ReservaRepository(Conexion::obtenerConexion());


$metodo = $_SERVER['REQUEST_METHOD'];

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


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

function responderBusquedaID(ReservaRepository $repo, int $numID): void
{
    $respuesta = $repo->buscarPorID($numID);
    if ($respuesta !== null) {
        responderJson($respuesta, 200);
    } else {
        responderJson(['mensaje' => 'Reserva no encontrada'], 404);
    }
}

function crearReserva():void
{
    $reservaValidation = new ReservaValidation();
    $texto = file_get_contents('php://input');

    $datos = json_decode($texto, true);
    if (!is_array($datos)) {
        responderJson(['mensaje' => 'El cuerpo de la petición debe ser un JSON válido'], 400);
    } else {
        try {
            $reserva = $reservaValidation->validarReserva($datos);
            responderJson(['mensaje'=>'Reserva validada'], 201);
        } catch (ValidacionException $e) {
            responderJson(['errores' => $e->errores], 400);
        }
    }

}

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
    'GET/api/reservas' => responderJson($repoReserva->listarTodasReservas(), 200),
    'GET/api/reservas/id' => responderBusquedaID($repoReserva, $numIdConsulta),
    'POST/api/reservas' => crearReserva(),
    'PATCH/api/reservas/id' => responderJson(['mensaje' => 'actualizar'], 200),
    'DELETE/api/reservas/id' => responderJson(['mensaje' => 'no permitido'], 405),

    default => responderJson(['mensaje' => 'error'], 404),
};



