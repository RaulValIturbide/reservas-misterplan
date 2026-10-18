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

    public function insertar(int $reservaId, string $tipo, ?string $descripcion): void
    {
        $sql = "INSERT INTO reserva_evento (reserva_id,tipo,descripcion)
                VALUES (:reservaId,:tipo,:descripcion)";

        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":reservaId", $reservaId, PDO::PARAM_INT);
        $ejecucion->bindValue(":tipo", $tipo, PDO::PARAM_STR);
        $ejecucion->bindValue(":descripcion", $descripcion, $descripcion === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $ejecucion->execute();
    }

    public function buscarPorReserva(int $reservaId): array
    {
        $sql = " SELECT id,reserva_id,tipo,descripcion,fecha_creacion
                 FROM reserva_evento 
                 WHERE reserva_id = :reservaId
                 ORDER BY fecha_creacion ASC ";
        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":reservaId", $reservaId, PDO::PARAM_INT);
        $ejecucion->execute();

        return $ejecucion->fetchAll(PDO::FETCH_ASSOC);
    }
}