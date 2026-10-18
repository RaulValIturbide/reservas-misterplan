<?php
declare(strict_types=1);

namespace App\Validation;

use App\Dto\NuevaReservaDTO;
use App\Exception\ValidacionException;
use DateTimeImmutable;

class ReservaValidation
{

    public function validarReserva(array $datos): NuevaReservaDTO
    {
        $errores = [];

        //Leemos los campos; si alguno no llega, queda en null
        $nombreHuesped = $datos['nombre_huesped'] ?? null;
        $emailHuesped = $datos['email_huesped'] ?? null;
        $nombreAlojamiento = $datos['nombre_alojamiento'] ?? null;
        $fechaEntrada = $datos['fecha_entrada'] ?? null;
        $fechaSalida = $datos['fecha_salida'] ?? null;
        $importe = $datos['importe'] ?? null;
        $notas = $datos['notas'] ?? null;

        //Normalización a string
        $nombreHuesped = is_string($nombreHuesped) ? trim($nombreHuesped) : null;
        $emailHuesped = is_string($emailHuesped) ? trim($emailHuesped) : null;
        $nombreAlojamiento = is_string($nombreAlojamiento) ? trim($nombreAlojamiento) : null;
        $fechaEntrada = is_string($fechaEntrada) ? trim($fechaEntrada) : null;
        $fechaSalida = is_string($fechaSalida) ? trim($fechaSalida) : null;
        $this->normalizarImporte($importe);
        $this->normalizarNotas($notas);


        //Empezamos con las validaciones
        //NombreHuesped
        $nombreHuespedValidacion = $this->validarStringObligatorio(
            campo: $nombreHuesped,
            msgError: "Falta nombre huésped.",
            cantidadCaracter: 150,
            msgMaxLength: "El nombre es demasiado largo (150 max).");
        if ($nombreHuespedValidacion !== null) {
            $errores["nombre_huesped"] = $nombreHuespedValidacion;
        }

        //EmailHuesped
        $emailHuespedValidacion = $this->validarStringObligatorio(
            campo: $emailHuesped,
            msgError: "Falta email huésped.",
            cantidadCaracter: 300,
            msgMaxLength: "El email es demasiado largo (300 max).");

        if ($emailHuespedValidacion !== null) {
            $errores["email_huesped"] = $emailHuespedValidacion;

        }//Si el contenido está correcto pasamos a validar el formato
        else if (filter_var($emailHuesped, FILTER_VALIDATE_EMAIL) === false) {
            $errores["email_huesped"] = "Formato de email no válido.";
        }

        //Nombre Alojamiento
        $nombreAlojamientoValidacion = $this->validarStringObligatorio(
            campo: $nombreAlojamiento,
            msgError: "Falta nombre alojamiento.",
            cantidadCaracter: 300,
            msgMaxLength: "El nombre de alojamiento es demasiado largo (300 max).");

        if ($nombreAlojamientoValidacion !== null) {
            $errores["nombre_alojamiento"] = $nombreAlojamientoValidacion;
        }


        //Fecha Entrada
        $fechaEntradaValidacion = $this->validarFecha($fechaEntrada);
        if ($fechaEntradaValidacion !== null) {
            $errores["fecha_entrada"] = $fechaEntradaValidacion;
        }
        //Fecha Salida
        $fechaSalidaValidacion = $this->validarFecha($fechaSalida);
        if ($fechaSalidaValidacion !== null) {
            $errores["fecha_salida"] = $fechaSalidaValidacion;
        }

        //Comprobación entrada menor que salida si no ha habido errores en ellas
        if (!isset($errores["fecha_entrada"]) && !isset($errores["fecha_salida"])) {
            $fechaEntradaSalidaValidacion = $this->fechaEntradaMenorQueSalida($fechaEntrada, $fechaSalida);
            if ($fechaEntradaSalidaValidacion !== null) {
                $errores["fecha_salida"] = $fechaEntradaSalidaValidacion;
            }
        }

        //Importe
        $importeValidacion = $this->validarImporte($importe);
        if ($importeValidacion !== null) {
            $errores["importe"] = $importeValidacion;
        }

        //Notas
        if (is_string($notas) && mb_strlen($notas) > 1000) {
            $errores["notas"] = "Notas no puede superar los 1000 caracteres.";
        }

        //Comprobamos y validamos o lanzamos error
        if (!empty($errores)) {
            throw new ValidacionException($errores);
        }

        $fechaEntrada = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaEntrada);
        $fechaSalida = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaSalida);

        return new NuevaReservaDTO(
            nombreHuesped: $nombreHuesped,
            emailHuesped: $emailHuesped,
            nombreAlojamiento: $nombreAlojamiento,
            fechaEntrada: $fechaEntrada,
            fechaSalida: $fechaSalida,
            importe: $importe,
            notas: $notas
        );
    }

    /**
     * @param mixed $campo Contenido del vector, comprabaremos si es un string o si está vacío
     * @param string $msgError El mensaje que mandaremos de base si el $campo no reune los requisitos
     * @param int $cantidadCaracter La cantidad máxmima de caracteres que están estipulados en MySql
     * @param string $msgMaxLength El mensaje a mandar si se ha alcanzado el máximo numero de caracteres
     * @return string|null Devolverá un string con el mensaje de error si no se valida o null si la validación es correcta
     */
    private function validarStringObligatorio(mixed $campo, string $msgError, int $cantidadCaracter, string $msgMaxLength): ?string
    {
        $mensaje = null;
        if (!is_string($campo) || trim($campo) === "") {
            $mensaje = $msgError;
        } else if (mb_strlen($campo) > $cantidadCaracter) {
            $mensaje = $msgMaxLength;
        }
        return $mensaje;
    }

    private function validarFecha(mixed $fecha): ?string
    {
        $mensaje = null;
        if (is_string($fecha)) {
            if (trim($fecha) === "") {
                $mensaje = "Falta fecha.";
            } else {
                $fechaValidada = DateTimeImmutable::createFromFormat('!Y-m-d', $fecha);
                if ($fechaValidada === false) {
                    $mensaje = "Formato de fecha incorrecto.";
                } else if ($fechaValidada->format('Y-m-d') !== $fecha) {
                    $mensaje = "Fecha desconocida o incorrecta.";
                }
            }
        } else {
            $mensaje = "Falta fecha.";
        }
        return $mensaje;
    }

    private function fechaEntradaMenorQueSalida(string $fechaEntrada, string $fechaSalida): ?string
    {
        $mensaje = null;

        $fechaEntradaDate = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaEntrada);
        $fechaSalidaDate = DateTimeImmutable::createFromFormat('!Y-m-d', $fechaSalida);

        if ($fechaEntradaDate >= $fechaSalidaDate) {
            $mensaje = "La fecha de salida debe ser posterior a la de entrada";
        }
        return $mensaje;
    }

    private function validarImporte(mixed $numero): ?string
    {
        $mensaje = null;
        if (is_string($numero)) {
            if ($numero === "") {
                $mensaje = "Importe obligatorio.";
            } else {
                $expresionReg = "/^\d{1,8}(\.\d{1,2})?$/";
                if (preg_match($expresionReg, $numero) === 1) {
                    if ((float)$numero <= 0) {
                        $mensaje = "El importe debe ser superior a 0";
                    }
                } else {
                    $mensaje = "Formato de importe incorrecto.";
                }
            }

        } else {
            $mensaje = "Importe obligatorio.";
        }
        return $mensaje;
    }


    private function normalizarImporte(mixed &$numero): void
    {
        if (is_int($numero) || is_float($numero)) {
            $numero = (string)$numero;
        } else if (is_string($numero)) {
            $numero = trim($numero);
            $numero = str_replace(',', '.', $numero);
        } else {
            $numero = null;
        }
    }

    private function normalizarNotas(mixed &$notas): void
    {
        if (is_string($notas)) {
            $notas = trim($notas);
            if ($notas === "") {
                $notas = null;
            }
        } else if (is_int($notas) || is_float($notas)) {
            $notas = (string)$notas;
        } else {
            $notas = null;
        }
    }


}