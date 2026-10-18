<?php
declare(strict_types=1);

namespace App\Dto;
use DateTimeImmutable;

/**
 * Esta clase tiene como objetivo servir de puente entre los datos introducidos por el usuario y la inserción en la tabla Sql
 */
class  NuevaReservaDTO
{
    public function  __construct(
            public readonly string $nombreHuesped,
            public readonly string $emailHuesped,
            public readonly string $nombreAlojamiento,
            public readonly DateTimeImmutable $fechaEntrada,
            public readonly DateTimeImmutable $fechaSalida,
            public readonly string $importe,
            public readonly ?string $notas = null
    )
    {}

}