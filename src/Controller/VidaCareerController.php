<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\PilotoRelacion;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class VidaCareerController extends AbstractController
{
    #[Route('/career/vida/{partidaId}', name: 'app_career_vida', methods: ['GET'])]
    public function index(int $partidaId, EntityManagerInterface $em): Response
    {
        // 1. Validar la partida guardada y comprobar seguridad del usuario
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // 2. Localizar el piloto activo del jugador
        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy([
            'partida' => $partida
        ]);

        if (!$pilotoHumano) {
            throw $this->createNotFoundException('No se encontró un piloto asociado a esta simulación.');
        }

        // 3. Obtener todas las relaciones del piloto para la partida actual
        $relaciones = $em->getRepository(PilotoRelacion::class)->findBy(
            ['partida' => $partida, 'pilotoUsuario' => $pilotoHumano],
            ['tipoActor' => 'ASC'] // Ordenar alfabéticamente por tipo de actor
        );

        // 4. Renderizar la vista
        return $this->render('career/vida.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
            'relaciones' => $relaciones
        ]);
    }
}