<?php
declare(strict_types=1);

use App\Database\Conexion;
use Dotenv\Dotenv;


require dirname(__DIR__).'/vendor/autoload.php';

$dot =  Dotenv::createImmutable(dirname(__DIR__));
$dot->load();


Conexion::obtenerConexion();
echo("Conexion ok");
