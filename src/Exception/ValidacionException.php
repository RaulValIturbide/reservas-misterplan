<?php
declare(strict_types=1);

namespace App\Exception;

class ValidacionException extends \Exception
{
    public function __construct(
        public readonly array  $errores,
        string $mensaje = "Los datos enviados no son válidos"
    )
    {
        parent::__construct($mensaje);
    }
}