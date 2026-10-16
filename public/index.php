<?php
declare(strict_types=1);

use Dotenv\Dotenv;

require dirname(__DIR__) . '/vendor/autoload.php';

$dot = Dotenv::createImmutable(dirname(__DIR__));
$dot->load();


$metodo = $_SERVER['REQUEST_METHOD'];

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);


/**
 * @param array $datos
 * @param int $codigo
 * @return void
 */
function responderJson(array $datos,int $codigo = 200):void{
    http_response_code($codigo);
    header('Content-Type: application/json');
    echo json_encode($datos);
}

$fragmentos = explode('/', $ruta);
$numIdConsulta = null;

if(count($fragmentos) === 4
    && $fragmentos[1] === 'api'
    && $fragmentos[2] === 'reservas'
    && ctype_digit($fragmentos[3])){
    $numIdConsulta = (int) $fragmentos[3];
    $ruta = '/api/reservas/id';
}


$peticion = $metodo . $ruta;
match ($peticion) {
    'GET/api/reservas' => responderJson(['mensaje' => 'listar'],200),
    'GET/api/reservas/id' => responderJson(['mensaje' => 'mostrar id '.$numIdConsulta],200),
    'POST/api/reservas' => responderJson(['mensaje' => 'crear'],201),
    'PATCH/api/reservas/id' => responderJson(['mensaje' => 'editar id '.$numIdConsulta],200),
    'DELETE/api/reservas/id' => responderJson(['mensaje' => 'no permitido'],405),

    default => responderJson(['mensaje' => 'error'],404),
};

