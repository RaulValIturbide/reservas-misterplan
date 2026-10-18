<?php
declare(strict_types=1);

namespace App\Repository;

use PDO;

class ReservaEventoRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }
    public function insertar(int $reservaId,string $tipo,?string $descripcion):void{
        $sql = "INSERT INTO reserva_evento (reserva_id,tipo,descripcion)
                VALUES (:reservaId,:tipo,:descripcion)";

        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":reservaId",$reservaId,PDO::PARAM_INT);
        $ejecucion->bindValue(":tipo",$tipo,PDO::PARAM_STR);
        $ejecucion->bindValue(":descripcion",$descripcion,$descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $ejecucion->execute();
    }
}