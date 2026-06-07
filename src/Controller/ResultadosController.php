<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\CalendarioTemporada;
use App\Entity\ResultadosCarreras;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ResultadosController extends AbstractController
{
    #[Route('/career/results-history/{partidaId}', name: 'app_career_resultados', methods: ['GET'])]
    public function index(int $partidaId, Request $request, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        // 1. Obtener filtros de la URL o aplicar los valores por defecto del juego
        $categoriaSeleccionada = strtoupper($request->query->get('categoria', $pilotoHumano->getCategoria() ?? 'KARTING'));
        $anoSeleccionado = (int) $request->query->get('ano', $partida->getIngameAno());
        $eventoIdSeleccionado = $request->query->get('eventoId') ? (int) $request->query->get('eventoId') : null;
        $sesionSeleccionada = strtoupper($request->query->get('sesion', 'CARRERA')); // Por defecto muestra el domingo

        // 2. Cargar listas para los selectores de los filtros
        // Años disputados hasta el momento actual de la partida
        $añosDisponibles = range(2026, $partida->getIngameAno()); 
        $categoriasDisponibles = ['KARTING', 'F3', 'F2', 'F1'];

        // 3. Buscar todas las carreras ya disputadas que coincidan con el filtro
        $carrerasDisputadas = $em->getRepository(CalendarioTemporada::class)->findBy([
            'partida' => $partida,
            'anoSimulacion' => $anoSeleccionado,
            'categoria' => $categoriaSeleccionada,
            'estadoEvento' => 'COMPLETADO'
        ], ['ordenCarrera' => 'ASC']);

        // 4. Si no hay evento seleccionado en la URL pero hay carreras disputadas, auto-seleccionar la última
        if (!$eventoIdSeleccionado && !empty($carrerasDisputadas)) {
            $eventoIdSeleccionado = end($carrerasDisputadas)->getId();
        }

        // 5. Cargar los resultados del evento y sesión elegidos
        $resultados = [];
        $eventoActivo = null;
        
        if ($eventoIdSeleccionado) {
            $eventoActivo = $em->getRepository(CalendarioTemporada::class)->find($eventoIdSeleccionado);
            
            // Buscamos los registros de la tabla resultados_carreras
            // NOTA: Si en tu tabla 'resultados_carreras' guardas la sesión, añade el filtro aquí.
            // Ejemplo: ['evento' => $eventoActivo, 'sesion' => $sesionSeleccionada]
            $resultados = $em->getRepository(ResultadosCarreras::class)->findBy(
                ['evento' => $eventoActivo],
                ['posicionFinal' => 'ASC'] // O por mejor tiempo si es Libres/Clasificación
            );
        }

        return $this->render('career/resultados.html.twig', [
            'partida' => $partida,
            'pilotoUsuario' => $pilotoHumano,
            'carreras' => $carrerasDisputadas,
            'resultados' => $resultados,
            'eventoActivo' => $eventoActivo,
            
            // Valores de filtros activos para mantener los selectores en su sitio
            'filtros' => [
                'categoria' => $categoriaSeleccionada,
                'ano' => $anoSeleccionado,
                'eventoId' => $eventoIdSeleccionado,
                'sesion' => $sesionSeleccionada
            ],
            'añosDisponibles' => $añosDisponibles,
            'categoriasDisponibles' => $categoriasDisponibles
        ]);
    }
}