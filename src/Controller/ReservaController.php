<?php
declare(strict_types=1);

namespace App\Controller;

use App\Enum\EstadoReserva;
use App\Exception\ReservaNoEncontradaException;
use App\Exception\TransicionEstadoException;
use App\Exception\ValidacionException;
use App\Http\Respuesta;
use App\Service\ReservaService;
use App\Validation\FiltroReservaValidation;
use App\Validation\ReservaValidation;

/**
 * Traduce peticiones HTTP de la API de reservas a llamadas al servicio y genera la respuesta JSON.
 * Solo captura las excepciones esperadas; las inesperadas suben al catch global del index.php.
 */
class ReservaController
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly ReservaValidation $reservaValidation,
        private readonly FiltroReservaValidation $filtroValidation,
    )
    {
    }

    /** GET /api/reservas */
    public function listar(): void
    {
        try {
            $filtro = $this->filtroValidation->validarFiltro($_GET);
            Respuesta::json($this->reservaService->listarReservas($filtro), 200);
        } catch (ValidacionException $e) {
            Respuesta::json(['errores' => $e->errores], 400);
        }
    }

    /** GET /api/reservas/{id} */
    public function mostrar(int $id): void
    {
        $reserva = $this->reservaService->obtenerDetalle($id);
        if ($reserva === null) {
            Respuesta::json(['mensaje' => 'Reserva no encontrada.'], 404);
            return;
        }
        Respuesta::json($reserva, 200);
    }

    /** POST /api/reservas */
    public function crear(): void
    {
        $datos = $this->leerCuerpoJson();
        if ($datos === null) {
            return;
        }
        try {
            $reserva = $this->reservaValidation->validarReserva($datos);
            $idCreado = $this->reservaService->crear($reserva);
            Respuesta::json($this->reservaService->obtenerDetalle($idCreado), 201);
        } catch (ValidacionException $e) {
            Respuesta::json(['errores' => $e->errores], 400);
        }
    }

    /** PATCH /api/reservas/{id} */
    public function cambiarEstado(int $id): void
    {
        $datos = $this->leerCuerpoJson();
        if ($datos === null) {
            return;
        }
        $valor = $datos['estado'] ?? null;
        $nuevoEstado = is_string($valor) ? EstadoReserva::tryFrom($valor) : null;
        if ($nuevoEstado === null) {
            $validos = implode(', ', array_map(fn(EstadoReserva $e) => $e->value, EstadoReserva::cases()));
            Respuesta::json(['errores' => ['estado' => "El estado debe ser uno de: $validos."]], 400);
            return;
        }
        try {
            $this->reservaService->cambiarEstado($id, $nuevoEstado);
            Respuesta::json($this->reservaService->obtenerDetalle($id), 200);
        } catch (ReservaNoEncontradaException $e) {
            Respuesta::json(['mensaje' => $e->getMessage()], 404);
        } catch (TransicionEstadoException $e) {
            Respuesta::json(['mensaje' => $e->getMessage()], 409);
        }
    }

    /**
     * Devuelve el cuerpo JSON como array, o responde 400 y devuelve null si no es un JSON válido.
     */
    private function leerCuerpoJson(): ?array
    {
        $datos = json_decode((string) file_get_contents('php://input'), true);
        if (!is_array($datos)) {
            Respuesta::json(['mensaje' => 'El cuerpo de la petición debe ser un JSON válido'], 400);
            return null;
        }
        return $datos;
    }
}
