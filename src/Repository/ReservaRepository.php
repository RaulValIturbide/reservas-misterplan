<?php
declare(strict_types=1);

namespace App\Repository;

use App\Dto\FiltroReservaDTO;
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

    public function listar(FiltroReservaDTO $filtro): array
    {
        $condiciones = [];
        $parametros = [];
        if($filtro->estado !== null){
            $condiciones[] = "estado = :estado";
            $parametros["estado"] = $filtro->estado->value;
        }
        if($filtro->desde !== null){
            $parametros["desde"] = $filtro->desde->format('Y-m-d');
            $condiciones[] = "fecha_entrada >= :desde";
        }
        if($filtro->hasta !== null){
            $parametros["hasta"] = $filtro->hasta->format("Y-m-d");
            $condiciones[] = "fecha_entrada <= :hasta";
        }
        if($filtro->nombre !== null){
            $parametros["nombre"] ='%' . $filtro->nombre . '%';
            $condiciones[] = "nombre_huesped LIKE :nombre";
        }

        $sql = " SELECT id,nombre_huesped,nombre_alojamiento,notas,
                  importe,fecha_salida,fecha_entrada,fecha_creacion,
                  estado,email_huesped
                  FROM reserva";

        if(count($condiciones) > 0){
            $sql .= " WHERE ".implode(" AND ", $condiciones);
        }
        $sql .= " ORDER BY fecha_entrada ASC,id ASC";
        $ejecucion = $this->pdo->prepare($sql);
        $ejecucion->execute($parametros);
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