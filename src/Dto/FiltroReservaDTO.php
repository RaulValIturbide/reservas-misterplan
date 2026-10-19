<?php
declare(strict_types=1);

namespace App\Dto;
use DateTimeImmutable;
use App\Enum\EstadoReserva;

class FiltroReservaDTO
{
    public function __construct(
        public readonly ?EstadoReserva $estado = null,
        public readonly ?DateTimeImmutable $desde = null,
        public readonly ?DateTimeImmutable $hasta = null,
        public readonly ?string $nombre = null,
    )
    {
    }
}