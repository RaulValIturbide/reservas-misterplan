<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\NuevaReservaDTO;
use App\Enum\EstadoReserva;
use PDO;

class ReservaRepository
{
    private PDO $pdo;

    public function __construct(PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function listarTodasReservas(): array
    {
        $sql = " SELECT id,nombre_huesped,nombre_alojamiento,notas,
                  importe,fecha_salida,fecha_entrada,fecha_creacion,
                  estado,email_huesped
                  FROM reserva
                  ORDER BY fecha_entrada ASC";

        $ejecucion = $this->pdo->query($sql);
        return $ejecucion->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * @param int $idReserva El id de la reserva que se está buscando
     * @return array|null Devolverá la array de datos si encuentra la reserva o null si no lo encuentra
     */
    public function buscarPorID(int $idReserva): ?array
    {
        $sql = " SELECT id,nombre_huesped,nombre_alojamiento,notas,
                  importe,fecha_salida,fecha_entrada,fecha_creacion,
                  estado,email_huesped
                  FROM reserva 
                  WHERE id = :id";

        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":id", $idReserva, PDO::PARAM_INT);
        $ejecucion->execute();

        return $ejecucion->fetch(PDO::FETCH_ASSOC) ?: null;
    }

    public function insertar(NuevaReservaDTO $dto): int
    {

        $sql = " INSERT INTO reserva(nombre_huesped,email_huesped,nombre_alojamiento,fecha_entrada,fecha_salida,notas,importe)
                 VALUES(:nombreHuesped,:emailHuesped,:nombreAlojamiento,:fechaEntrada,:fechaSalida,:notas,:importe)";

        $fechaEntradaString = $dto->fechaEntrada->format('Y-m-d');
        $fechaSalidaString = $dto->fechaSalida->format('Y-m-d');

        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":nombreHuesped", $dto->nombreHuesped, PDO::PARAM_STR);
        $ejecucion->bindValue(":emailHuesped", $dto->emailHuesped, PDO::PARAM_STR);
        $ejecucion->bindValue(":nombreAlojamiento", $dto->nombreAlojamiento, PDO::PARAM_STR);
        $ejecucion->bindValue(":fechaEntrada", $fechaEntradaString);
        $ejecucion->bindValue(":fechaSalida", $fechaSalidaString);
        $ejecucion->bindValue(":notas", $dto->notas, $dto->notas === null ? PDO::PARAM_NULL : PDO::PARAM_STR);
        $ejecucion->bindValue(":importe", $dto->importe);
        $ejecucion->execute();
        $ultimoId = (int)$this->pdo->lastInsertId();

        return $ultimoId;
    }

    public function actualizarEstado(int $reservaID, EstadoReserva $nuevoEstado): void
    {
        $sql = " UPDATE reserva 
                 SET estado = :estado
                 WHERE id = :id";
        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->bindValue(":estado", $nuevoEstado->value, PDO::PARAM_STR);
        $ejecucion->bindValue(":id", $reservaID, PDO::PARAM_INT);
        $ejecucion->execute();
    }


}