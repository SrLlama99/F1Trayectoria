<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\CalendarioTemporada;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class WeekendController extends AbstractController
{
    #[Route('/career/weekend/{partidaId}', name: 'app_career_weekend', methods: ['GET'])]
    public function menuWeekend(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        /** @var CalendarioTemporada|null $carrera */
        $carrera = $em->getRepository(CalendarioTemporada::class)->findOneBy([
            'partida' => $partida,
            'categoria' => $piloto->getCategoria(),
            'anoSimulacion' => $partida->getIngameAno(),
            'semanaCarrera' => $partida->getIngameSemana()
        ]);

        if (!$carrera) {
            return $this->redirectToRoute('app_career_main', ['partidaId' => $partidaId]);
        }


        $sesiones = [];

        if ($carrera->getFormato() == 'SPRINT') {
            // Formato F1 Sprint (Viernes: FP1 + Clasificación / Sábado: Sprint Shootout + Sprint / Domingo: Race)
            // Simplificado para tu motor en 4 o 5 sesiones:
            $sesiones = [
                0 => ['codigo' => 'FP1', 'nombre' => 'Entrenamientos Libres 1', 'icono' => '🔧'],
                1 => ['codigo' => 'SQ', 'nombre' => 'Sesión de Clasificación Sprint', 'icono' => '⏱️'],
                2 => ['codigo' => 'SPRINT', 'nombre' => 'Carrera Sprint', 'icono' => '⚡'],
                3 => ['codigo' => 'Q', 'nombre' => 'Sesión de Clasificación', 'icono' => '⏱️'],
                4 => ['codigo' => 'RACE', 'nombre' => 'GRAN PREMIO (Carrera)', 'icono' => '🏆'],
            ];
        } elseif ($carrera->getFormato() == 'DEFAULT') {
            // Formato Tradicional Completo
            $sesiones = [
                0 => ['codigo' => 'FP1', 'nombre' => 'Entrenamientos Libres 1', 'icono' => '🔧'],
                1 => ['codigo' => 'FP2', 'nombre' => 'Entrenamientos Libres 2', 'icono' => '🧪'],
                2 => ['codigo' => 'FP3', 'nombre' => 'Entrenamientos Libres 3', 'icono' => '🛞'],
                3 => ['codigo' => 'Q', 'nombre' => 'Sesión de Clasificación', 'icono' => '⏱️'],
                4 => ['codigo' => 'RACE', 'nombre' => 'GRAN PREMIO (Carrera)', 'icono' => '🏆'],
            ];
        } elseif ($carrera->getFormato() == 'FORMACION'){
            $sesiones = [
                0 => ['codigo' => 'FP1', 'nombre' => 'Entrenamientos Libres 1', 'icono' => '🔧'],
                1 => ['codigo' => 'FP2', 'nombre' => 'Entrenamientos Libres 2', 'icono' => '🧪'],
                2 => ['codigo' => 'Q', 'nombre' => 'Sesión de Clasificación', 'icono' => '⏱️'],
                3 => ['codigo' => 'SPRINT', 'nombre' => 'Carrera Sprint', 'icono' => '⚡'],
                4 => ['codigo' => 'RACE', 'nombre' => 'GRAN PREMIO (Carrera)', 'icono' => '🏆'],
            ];
        }

        // Control de índice del estado de la partida
        $sesionActualIndex = method_exists($partida, 'getSesionActual') ? $partida->getSesionActual() : 0;

        // Coto de seguridad: Si por transiciones la sesión guardada supera el número de sesiones del formato actual
        if ($sesionActualIndex >= count($sesiones)) {
            // Significa que ha terminado el GP, deberíamos forzar el cierre del fin de semana
            // De momento lo capamos al límite máximo ejecutable
            $sesionActualIndex = count($sesiones) - 1;
        }

        return $this->render('career/weekend.html.twig', [
            'partida' => $partida,
            'piloto' => $piloto,
            'carrera' => $carrera,
            'sesiones' => $sesiones,
            'sesionActualIndex' => $sesionActualIndex
        ]);
    }

    #[Route('/career/weekend/{partidaId}/simulate/{sesionCodigo}', name: 'app_career_weekend_simulate', methods: ['GET'])]
    public function simulateSession(int $partidaId, string $sesionCodigo, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Aquí procesaremos los algoritmos del simulador según el $sesionCodigo ('FP1', 'Q', 'RACE'...)
        return $this->render('career/simulate_placeholder.html.twig', [
            'partida' => $partida,
            'sesion' => $sesionCodigo
        ]);
    }
}