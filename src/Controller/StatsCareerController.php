<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StatsCareerController extends AbstractController
{
    #[Route('/career/stats/{partidaId}', name: 'app_career_stats', methods: ['GET'])]
    public function index(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);

        if (!$partida) {
            throw $this->createNotFoundException('La partida guardada no existe.');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy([
            'partida' => $partida
        ]);

        if (!$pilotoHumano) {
            throw $this->createNotFoundException('No se encontró un piloto asociado a esta partida.');
        }

        $nivel = $pilotoHumano->getNivelActual();
        $expActual = $pilotoHumano->getExpActual();
        $expNecesariaSiguienteNivel = $nivel * 100;
        
        $porcentajeExp = $expNecesariaSiguienteNivel > 0 
            ? min(100, round(($expActual / $expNecesariaSiguienteNivel) * 100)) 
            : 0;

        $sumaStats = $pilotoHumano->getStatClasificacion()
            + $pilotoHumano->getStatRitmo()
            + $pilotoHumano->getStatAdelantamiento()
            + $pilotoHumano->getStatDefensa()
            + $pilotoHumano->getStatGestionNeumaticos()
            + $pilotoHumano->getStatMojado();
            
        $mediaStats = round($sumaStats / 6);

        return $this->render('career/stats.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
            'expNecesaria' => $expNecesariaSiguienteNivel,
            'porcentajeExp' => $porcentajeExp,
            'mediaStats' => $mediaStats
        ]);
    }

    #[Route('/career/stats/{partidaId}/upgrade', name: 'app_career_upgrade_stats', methods: ['POST'])]
    public function upgradeStat(int $partidaId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);

        if (!$partida) {
            return new JsonResponse(['error' => 'La partida no existe.'], Response::HTTP_NOT_FOUND);
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy([
            'partida' => $partida
        ]);

        if (!$pilotoHumano) {
            return new JsonResponse(['error' => 'No se encontró el piloto.'], Response::HTTP_NOT_FOUND);
        }
        
        // Obtenemos la estadística (Soporta JSON o formulario tradicional)
        $data = json_decode($request->getContent(), true);
        $statToUpgrade = $data['stat'] ?? $request->request->get('stat');

        if ($pilotoHumano->getPuntosHabilidadDisponibles() > 0) {
            $subido = false;

            // 🟢 CORREGIDO: Casos del switch alineados con los nombres reales de las stats
            switch ($statToUpgrade) {
                case 'clasificacion':
                    if ($pilotoHumano->getStatClasificacion() < 100) {
                        $pilotoHumano->setStatClasificacion($pilotoHumano->getStatClasificacion() + 1);
                        $subido = true;
                    }
                    break;
                case 'ritmo':
                    if ($pilotoHumano->getStatRitmo() < 100) {
                        $pilotoHumano->setStatRitmo($pilotoHumano->getStatRitmo() + 1);
                        $subido = true;
                    }
                    break;
                case 'adelantamiento':
                    if ($pilotoHumano->getStatAdelantamiento() < 100) {
                        $pilotoHumano->setStatAdelantamiento($pilotoHumano->getStatAdelantamiento() + 1);
                        $subido = true;
                    }
                    break;
                case 'defensa':
                    if ($pilotoHumano->getStatDefensa() < 100) {
                        $pilotoHumano->setStatDefensa($pilotoHumano->getStatDefensa() + 1);
                        $subido = true;
                    }
                    break;
                case 'gestionNeumaticos':
                    if ($pilotoHumano->getStatGestionNeumaticos() < 100) {
                        $pilotoHumano->setStatGestionNeumaticos($pilotoHumano->getStatGestionNeumaticos() + 1);
                        $subido = true;
                    }
                    break;
                case 'mojado':
                    if ($pilotoHumano->getStatMojado() < 100) {
                        $pilotoHumano->setStatMojado($pilotoHumano->getStatMojado() + 1);
                        $subido = true;
                    }
                    break;
            }

            if ($subido) {
                $pilotoHumano->setPuntosHabilidadDisponibles($pilotoHumano->getPuntosHabilidadDisponibles() - 1);
                $em->flush();

                // Recalculamos la media global para refrescarla en vivo
                $sumaStats = $pilotoHumano->getStatClasificacion()
                    + $pilotoHumano->getStatRitmo()
                    + $pilotoHumano->getStatAdelantamiento()
                    + $pilotoHumano->getStatDefensa()
                    + $pilotoHumano->getStatGestionNeumaticos()
                    + $pilotoHumano->getStatMojado();

                return new JsonResponse([
                    'success' => true,
                    'puntosDisponibles' => $pilotoHumano->getPuntosHabilidadDisponibles(),
                    'mediaStats' => round($sumaStats / 6),
                    'stat' => $statToUpgrade,
                    // Devolvemos el valor dinámico de la stat que acaba de subir
                    'nuevoValor' => match($statToUpgrade) {
                        'clasificacion' => $pilotoHumano->getStatClasificacion(),
                        'ritmo' => $pilotoHumano->getStatRitmo(),
                        'adelantamiento' => $pilotoHumano->getStatAdelantamiento(),
                        'defensa' => $pilotoHumano->getStatDefensa(),
                        'gestionNeumaticos' => $pilotoHumano->getStatGestionNeumaticos(),
                        'mojado' => $pilotoHumano->getStatMojado(),
                    }
                ]);
            }

            return new JsonResponse(['success' => false, 'error' => 'Atributo ya al nivel máximo.'], Response::HTTP_BAD_REQUEST);
        }

        return new JsonResponse(['success' => false, 'error' => 'Sin puntos de habilidad.'], Response::HTTP_BAD_REQUEST);
    }
}