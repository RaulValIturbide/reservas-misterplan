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
}