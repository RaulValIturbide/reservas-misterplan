<?php
declare(strict_types=1);

use Dotenv\Dotenv;


require dirname(__DIR__) . '/vendor/autoload.php';

$dot = Dotenv::createImmutable(dirname(__DIR__));
$dot->load();


$metodo = $_SERVER['REQUEST_METHOD'];

$ruta = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$peticion = $metodo . $ruta;

function responderJson(array $datos,int $codigo = 200):void{
    http_response_code($codigo);
    header('Content-Type: application/json');
    echo json_encode($datos);
}

match ($peticion) {
    'GET/api/reservas' => responderJson(['mensaje' => 'listar'],200),
    'POST/api/reservas' => responderJson(['mensaje' => 'crear'],201),
    default => responderJson(['mensaje' => 'error'],404),
};

