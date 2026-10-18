<?php
declare(strict_types=1);

namespace App\Service;

use App\Dto\NuevaReservaDTO;
use App\Repository\ReservaEventoRepository;
use App\Repository\ReservaRepository;
use PDO;
use Throwable;

class ReservaService
{
    private PDO $pdo;

    private ReservaRepository $reservaRepository;
    private ReservaEventoRepository $reservaEventoRepository;

    public function __construct(PDO $pdo, ReservaRepository $reservaRepository, ReservaEventoRepository $reservaEventoRepository)
    {
        $this->pdo = $pdo;
        $this->reservaRepository = $reservaRepository;
        $this->reservaEventoRepository = $reservaEventoRepository;
    }


    public function crear(NuevaReservaDTO $nuevaReservaDTO): int
    {
        try {
            $this->pdo->beginTransaction();
            $ultimoID = $this->reservaRepository->insertar($nuevaReservaDTO);
            $this->reservaEventoRepository->insertar($ultimoID, 'creada', 'Reserva creada');
            $this->pdo->commit();

        } catch (Throwable $ex) {
            $this->pdo->rollBack();
            throw $ex;
        }
        return $ultimoID;
    }

    public function buscarPorID(int $id):?array{
        return $this->reservaRepository->buscarPorID($id);
    }


}