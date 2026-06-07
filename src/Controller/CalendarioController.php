<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\CalendarioTemporada;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CalendarioController extends AbstractController
{
    #[Route('/career/calendar/{partidaId}', name: 'app_career_calendario', methods: ['GET'])]
    public function index(int $partidaId, Request $request, EntityManagerInterface $em): Response
    {
        // 1. Validar la partida y el usuario
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // 2. Obtener los datos del piloto del jugador
        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        // 3. Determinar la categoría activa (por defecto, la del piloto; si no, por parámetro GET)
        $categoriaPorDefecto = $pilotoHumano ? strtoupper($pilotoHumano->getCategoria()) : 'KARTING';
        $categoriaSeleccionada = strtoupper($request->query->get('categoria', $categoriaPorDefecto));

        // Año actual dentro de la simulación de la partida
        $anoActual = $partida->getIngameAno();

        // 4. Recuperar los eventos del calendario que coincidan con el año y la categoría
        $eventos = $em->getRepository(CalendarioTemporada::class)->findBy(
            [
                'partida' => $partida,
                'anoSimulacion' => $anoActual,
                'categoria' => $categoriaSeleccionada
            ],
            ['ordenCarrera' => 'ASC']
        );

        $categoriasDisponibles = ['KARTING', 'F3', 'F2', 'F1'];

        return $this->render('career/calendario.html.twig', [
            'partida' => $partida,
            'pilotoUsuario' => $pilotoHumano,
            'eventos' => $eventos,
            'categoriaSeleccionada' => $categoriaSeleccionada,
            'categoriasDisponibles' => $categoriasDisponibles,
            'anoActual' => $anoActual
        ]);
    }
}