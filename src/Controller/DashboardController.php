<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\ContratoMercado;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    #[Route('/dashboard', name: 'app_dashboard', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $usuarioActual = $this->getUser();

        $slots = [
            1 => null,
            2 => null,
            3 => null
        ];

        if (!$usuarioActual) {
            return $this->redirectToRoute('app_login_screen');
        }

        $partidasExistentes = $em->getRepository(PartidaGuardada::class)->findBy([
            'usuario' => $usuarioActual
        ]);

        foreach ($partidasExistentes as $partida) {
            $slotNum = (int)$partida->getSlotNumero();

            if ($slotNum >= 1 && $slotNum <= 3) {
                $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

                if (!$piloto) {
                    continue;
                }

                // 🟢 NUEVO: Buscar si el piloto humano tiene un contrato firmado en esta partida
                $contrato = $em->getRepository(ContratoMercado::class)->findOneBy([
                    'partida' => $partida,
                    'esJugadorHumano' => true
                ]);

                // Si tiene contrato, extraemos el nombre de la escudería, si no, "Agente Libre"
                $escuderiaNombre = $contrato && $contrato->getEscuderia()
                    ? $contrato->getEscuderia()->getNombreOficial()
                    : 'Agente Libre';

                $slots[$slotNum] = [
                    'partida' => $partida,
                    'piloto' => $piloto,
                    'escuderia' => $escuderiaNombre // 🟢 Lo añadimos al array para Twig
                ];
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'slots' => $slots
        ]);
    }

    #[Route('/career/delete/{idPartida}', name: 'app_career_delete', methods: ['POST', 'GET'])]
    public function delete(int $idPartida, EntityManagerInterface $em): Response
    {
        $usuarioActual = $this->getUser();
        if (!$usuarioActual) {
            return $this->redirectToRoute('app_login_screen');
        }

        $partida = $em->getRepository(PartidaGuardada::class)->find($idPartida);

        if (!$partida) {
            $this->addFlash('error', 'La partida no existe.');
            return $this->redirectToRoute('app_dashboard');
        }

        if ($partida->getUsuario() !== $usuarioActual) {
            $this->addFlash('error', 'Acceso denegado. No eres el propietario.');
            return $this->redirectToRoute('app_dashboard');
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if ($piloto) {
            $em->remove($piloto);
        }

        $em->remove($partida);
        $em->flush();

        $this->addFlash('success', 'Historial de telemetría purgado correctamente.');
        return $this->redirectToRoute('app_dashboard');
    }
}