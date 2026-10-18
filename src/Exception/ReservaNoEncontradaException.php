<?php

declare(strict_types=1);

namespace App\Exception;

use Throwable;

class ReservaNoEncontradaException extends \Exception
{
    public function __construct(string $mensaje = "No se pudo encontrar la reserva.")
    {
        parent::__construct($mensaje);
    }
}