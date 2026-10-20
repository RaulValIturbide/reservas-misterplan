<?php
declare(strict_types=1);

namespace App\Controller;

use App\Dto\FiltroReservaDTO;
use App\Service\ReservaService;
use Twig\Environment;

/**
 * Renderiza las vistas HTML con Twig. Usa el mismo ReservaService que la API.
 */
class VistaController
{
    public function __construct(
        private readonly ReservaService $reservaService,
        private readonly Environment $twig,
    ) {
    }

    /** GET / */
    public function listado(): void
    {
        $reservas = $this->reservaService->listarReservas(new FiltroReservaDTO());

        echo $this->twig->render('listado.html.twig', [
            'reservas' => $reservas,
        ]);
    }

    /** GET /reservas/{id} */
    public function detalle(int $id): void
    {
        $reserva = $this->reservaService->obtenerDetalle($id);

        if ($reserva === null) {
            http_response_code(404);
            echo $this->twig->render('no_encontrada.html.twig', [
                'id' => $id,
            ]);
            return;
        }

        echo $this->twig->render('detalle.html.twig', [
            'reserva' => $reserva,
        ]);
    }
}
