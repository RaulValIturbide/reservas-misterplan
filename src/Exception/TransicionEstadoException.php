<?php

declare(strict_types = 1);
namespace App\Exception;

class TransicionEstadoException extends \Exception
{
    public function __construct(string $mensaje = "Transición de estado erronea.")
    {
        parent::__construct($mensaje);
    }
}