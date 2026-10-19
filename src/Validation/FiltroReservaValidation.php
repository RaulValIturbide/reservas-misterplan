<?php
declare(strict_types=1);

namespace App\Validation;

use App\Dto\FiltroReservaDTO;
use App\Enum\EstadoReserva;
use App\Exception\ValidacionException;
use DateTimeImmutable;

/**
 * Valida los filtros del listado (query string) y los convierte en un FiltroReservaDTO tipado.
 * Un filtro ausente o vacío se interpreta como "no filtrar por ese campo".
 */
class FiltroReservaValidation
{
    private const NOMBRE_MAX_LENGTH = 150;

    /**
     * @param array $filtro Normalmente $_GET. Los valores pueden ser string, array o no existir.
     * @throws ValidacionException Si algún filtro no es válido (se traduce a 400).
     */
    public function validarFiltro(array $filtro): FiltroReservaDTO
    {
        $errores = [];

        $valorEstado = $this->normalizar($filtro['estado'] ?? null);
        $valorDesde = $this->normalizar($filtro['desde'] ?? null);
        $valorHasta = $this->normalizar($filtro['hasta'] ?? null);
        $valorNombre = $this->normalizar($filtro['nombre'] ?? null);

        // Estado
        $estado = null;
        if ($valorEstado !== null) {
            $estado = is_string($valorEstado) ? EstadoReserva::tryFrom($valorEstado) : null;
            if ($estado === null) {
                $validos = implode(', ', array_map(fn(EstadoReserva $e) => $e->value, EstadoReserva::cases()));
                $errores['estado'] = "El estado debe ser uno de: $validos.";
            }
        }

        // Fechas
        $desde = null;
        if ($valorDesde !== null) {
            $desde = $this->convertirFecha($valorDesde);
            if ($desde === null) {
                $errores['desde'] = 'La fecha "desde" debe tener el formato AAAA-MM-DD.';
            }
        }

        $hasta = null;
        if ($valorHasta !== null) {
            $hasta = $this->convertirFecha($valorHasta);
            if ($hasta === null) {
                $errores['hasta'] = 'La fecha "hasta" debe tener el formato AAAA-MM-DD.';
            }
        }

        if ($desde !== null && $hasta !== null && $desde > $hasta) {
            $errores['hasta'] = 'La fecha "hasta" no puede ser anterior a "desde".';
        }

        // Nombre
        $nombre = null;
        if ($valorNombre !== null) {
            if (!is_string($valorNombre)) {
                $errores['nombre'] = 'El nombre debe ser un texto.';
            } elseif (mb_strlen($valorNombre) > self::NOMBRE_MAX_LENGTH) {
                $errores['nombre'] = 'El nombre no puede superar ' . self::NOMBRE_MAX_LENGTH . ' caracteres.';
            } else {
                $nombre = $valorNombre;
            }
        }

        if ($errores !== []) {
            throw new ValidacionException($errores);
        }

        return new FiltroReservaDTO($estado, $desde, $hasta, $nombre);
    }

    /**
     * Convierte "no enviado" y "enviado vacío" en null. Los strings se recortan.
     * Lo que no sea string (p. ej. un array por ?estado[]=x) se devuelve tal cual para que falle la validación.
     */
    private function normalizar(mixed $valor): mixed
    {
        if (is_string($valor)) {
            $valor = trim($valor);
            return $valor === '' ? null : $valor;
        }
        return $valor;
    }

    /**
     * Devuelve la fecha si es un string con formato Y-m-d y una fecha real; si no, null.
     */
    private function convertirFecha(mixed $valor): ?DateTimeImmutable
    {
        if (!is_string($valor)) {
            return null;
        }
        $fecha = DateTimeImmutable::createFromFormat('!Y-m-d', $valor);
        if ($fecha === false || $fecha->format('Y-m-d') !== $valor) {
            return null;
        }
        return $fecha;
    }
}
