<?php
declare(strict_types=1);

namespace App\Exception;

class ValidacionException extends \Exception
{
    public function __construct(
        public readonly array  $errores,
        string $mensaje = "Los datos de la reserva no son correctos"
    )
    {
        parent::__construct($mensaje);
    }
}