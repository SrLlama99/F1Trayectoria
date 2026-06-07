<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CasinoController extends AbstractController
{
    private const MAX_JUGADAS = 3; // 👑 Límite centralizado estricto

    #[Route('/career/casino/{partidaId}', name: 'app_career_casino_menu', methods: ['GET'])]
    public function menu(int $partidaId, EntityManagerInterface $em): Response
    {
        // 1. Validaciones de Seguridad
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano) {
            throw $this->createNotFoundException('Piloto no encontrado.');
        }

        // 2. Control de Energía / Intentos semanales
        $jugadasConsumidas = method_exists($pilotoHumano, 'getJugadasCasino') ? $pilotoHumano->getJugadasCasino() : 0;
        $jugadasRestantes = max(0, self::MAX_JUGADAS - $jugadasConsumidas);

        return $this->render('career/casino_menu.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
            'totalJugadasPermitidas' => self::MAX_JUGADAS,
            'jugadasRestantes' => $jugadasRestantes,
        ]);
    }

    #[Route('/career/casino/{partidaId}/blackjack', name: 'app_career_casino_blackjack', methods: ['GET'])]
    public function blackjack(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano) {
            throw $this->createNotFoundException('Piloto no localizado.');
        }

        // 🛑 Capa de Seguridad: Bloqueo de entrada si ya superó el límite
        if ($pilotoHumano->getJugadasCasino() >= self::MAX_JUGADAS) {
            return $this->redirectToRoute('app_career_casino_menu', ['partidaId' => $partidaId]);
        }

        return $this->render('career/casino_blackjack.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
        ]);
    }

    // 🌟 CORREGIDO: Ahora recibe {partidaId} para actuar sobre la partida real en juego
    #[Route('/career/casino/api/save-blackjack/{partidaId}/{resultado}/{cantidad}', name: 'app_career_casino_api_blackjack', methods: ['GET'])]
    public function apiSaveBlackjack(int $partidaId, string $resultado, int $cantidad, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new Response('Acceso no autorizado', Response::HTTP_FORBIDDEN);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if ($piloto) {
            // 🛑 BLOQUEO INMEDIATO BACKEND
            if ($piloto->getJugadasCasino() >= self::MAX_JUGADAS) {
                return new Response('Límite semanal excedido', Response::HTTP_FORBIDDEN);
            }

            if ($resultado === 'JUGADOR_GANA') {
                $piloto->setDinero($piloto->getDinero() + $cantidad);
            } elseif ($resultado === 'BANCA_GANA') {
                $piloto->setDinero(max(0, $piloto->getDinero() - $cantidad));
            }
            
            $piloto->incrementarJugadaCasino();
            $em->flush();
            
            return new Response('OK', Response::HTTP_OK);
        }

        return new Response('Piloto no encontrado', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/career/casino/{partidaId}/dados', name: 'app_career_casino_dados', methods: ['GET'])]
    public function dados(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano) {
            throw $this->createNotFoundException('Piloto no localizado.');
        }

        // 🛑 Capa de Seguridad: Bloqueo de entrada si ya superó el límite
        if ($pilotoHumano->getJugadasCasino() >= self::MAX_JUGADAS) {
            return $this->redirectToRoute('app_career_casino_menu', ['partidaId' => $partidaId]);
        }

        return $this->render('career/casino_dados.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
        ]);
    }

    // 🌟 CORREGIDO: Ahora recibe {partidaId} para actuar sobre la partida real en juego
    #[Route('/career/casino/api/save-dados/{partidaId}/{resultado}/{cantidad}', name: 'app_career_casino_api_dados', methods: ['GET'])]
    public function apiSaveDados(int $partidaId, string $resultado, int $cantidad, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new Response('Acceso no autorizado', Response::HTTP_FORBIDDEN);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if ($piloto) {
            // 🛑 BLOQUEO INMEDIATO BACKEND
            if ($piloto->getJugadasCasino() >= self::MAX_JUGADAS) {
                return new Response('Límite semanal excedido', Response::HTTP_FORBIDDEN);
            }

            if ($resultado === 'JUGADOR_GANA') {
                $piloto->setDinero($piloto->getDinero() + $cantidad);
            } elseif ($resultado === 'BANCA_GANA') {
                $piloto->setDinero(max(0, $piloto->getDinero() - $cantidad));
            }

            $piloto->incrementarJugadaCasino();
            $em->flush();

            return new Response('OK', Response::HTTP_OK);
        }

        return new Response('Piloto no encontrado', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/career/casino/{partidaId}/ruleta', name: 'app_career_casino_ruleta', methods: ['GET'])]
    public function ruleta(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano) {
            throw $this->createNotFoundException('Piloto no localizado.');
        }

        // 🛑 Capa de Seguridad: Bloqueo de entrada si ya superó el límite
        if ($pilotoHumano->getJugadasCasino() >= self::MAX_JUGADAS) {
            return $this->redirectToRoute('app_career_casino_menu', ['partidaId' => $partidaId]);
        }

        return $this->render('career/casino_ruleta.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
        ]);
    }

    // 🌟 CORREGIDO: Ahora recibe {partidaId}
    #[Route('/career/casino/api/save-ruleta/{partidaId}/{resultado}/{cantidad}', name: 'app_career_casino_api_ruleta', methods: ['GET'])]
    public function apiSaveRuleta(int $partidaId, string $resultado, int $cantidad, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new Response('Acceso no autorizado', Response::HTTP_FORBIDDEN);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if ($piloto) {
            // 🛑 BLOQUEO INMEDIATO BACKEND
            if ($piloto->getJugadasCasino() >= self::MAX_JUGADAS) {
                return new Response('Límite semanal excedido', Response::HTTP_FORBIDDEN);
            }

            if ($resultado === 'JUGADOR_GANA') {
                $piloto->setDinero($piloto->getDinero() + $cantidad);
            } elseif ($resultado === 'BANCA_GANA') {
                $piloto->setDinero(max(0, $piloto->getDinero() - $cantidad));
            }

            $piloto->incrementarJugadaCasino();
            $em->flush();

            return new Response('OK', Response::HTTP_OK);
        }

        return new Response('Piloto no encontrado', Response::HTTP_BAD_REQUEST);
    }

    #[Route('/career/casino/{partidaId}/tragaperras', name: 'app_career_casino_tragaperras', methods: ['GET'])]
    public function tragaperras(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano) {
            throw $this->createNotFoundException('Piloto no localizado.');
        }

        // 🛑 Capa de Seguridad: Bloqueo de entrada si ya superó el límite
        if ($pilotoHumano->getJugadasCasino() >= self::MAX_JUGADAS) {
            return $this->redirectToRoute('app_career_casino_menu', ['partidaId' => $partidaId]);
        }

        return $this->render('career/casino_tragaperras.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
        ]);
    }

    // 🌟 CORREGIDO: Ahora recibe {partidaId}
    #[Route('/career/casino/api/save-tragaperras/{partidaId}/{resultado}/{cantidad}', name: 'app_career_casino_api_tragaperras', methods: ['GET'])]
    public function apiSaveTragaperras(int $partidaId, string $resultado, int $cantidad, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new Response('Acceso no autorizado', Response::HTTP_FORBIDDEN);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if ($piloto) {
            // 🛑 BLOQUEO INMEDIATO BACKEND
            if ($piloto->getJugadasCasino() >= self::MAX_JUGADAS) {
                return new Response('Límite semanal excedido', Response::HTTP_FORBIDDEN);
            }

            if ($resultado === 'JUGADOR_GANA') {
                $piloto->setDinero($piloto->getDinero() + $cantidad);
            } elseif ($resultado === 'BANCA_GANA') {
                $piloto->setDinero(max(0, $piloto->getDinero() - $cantidad));
            }

            $piloto->incrementarJugadaCasino();
            $em->flush();

            return new Response('OK', Response::HTTP_OK);
        }

        return new Response('Piloto no encontrado', Response::HTTP_BAD_REQUEST);
    }
}