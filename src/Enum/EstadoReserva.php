<?php
declare(strict_types=1);
namespace App\Enum;

enum EstadoReserva : string
{
    case Pendiente = "pendiente";
    case Confirmada = "confirmada";
    case Cancelada = "cancelada";
}
