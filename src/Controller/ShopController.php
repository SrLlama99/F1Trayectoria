<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\TiendaItem;
use App\Entity\TiendaCompra;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ShopController extends AbstractController
{
    #[Route('/career/shop/{partidaId}', name: 'app_career_shop', methods: ['GET'])]
    public function index(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        $todosLosItems = $em->getRepository(TiendaItem::class)->findAll();

        // Obtener las compras activas mapeadas por el ID del item
        $comprasRaw = $em->getRepository(TiendaCompra::class)->findBy([
            'partida' => $partida,
            'pilotoUsuario' => $piloto
        ]);

        $comprasMapeadas = [];
        foreach ($comprasRaw as $compra) {
            $comprasMapeadas[$compra->getItem()->getId()] = [
                'idCompra' => $compra->getId(),
                'desgaste' => $compra->getDesgaste()
            ];
        }

        return $this->render('career/shop.html.twig', [
            'partida' => $partida,
            'piloto' => $piloto,
            'items' => $todosLosItems,
            'comprasMapeadas' => $comprasMapeadas
        ]);
    }

    #[Route('/api/career/shop/buy', name: 'api_career_shop_buy', methods: ['POST'])]
    public function buyItem(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = $request->request->get('partidaId');
        $itemId = $request->request->get('itemId');

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        $item = $em->getRepository(TiendaItem::class)->find($itemId);

        if (!$item) {
            return new JsonResponse(['success' => false, 'message' => 'El artículo solicitado no existe.'], 404);
        }

        // Verificar si ya se posee y NO está roto
        $yaComprado = $em->getRepository(TiendaCompra::class)->findOneBy([
            'partida' => $partida,
            'pilotoUsuario' => $piloto,
            'item' => $item
        ]);

        if ($yaComprado) {
            return new JsonResponse(['success' => false, 'message' => 'Ya posees este artículo en tu propiedad.'], 400);
        }

        if ($piloto->getDinero() < $item->getPrecio()) {
            return new JsonResponse(['success' => false, 'message' => 'Fondos insuficientes en tu cuenta corriente.'], 400);
        }

        try {
            $piloto->setDinero($piloto->getDinero() - $item->getPrecio());
            $piloto->setEstiloDeVida($piloto->getEstiloDeVida() + $item->getBonoEstiloDeVida());

            $nuevaCompra = new TiendaCompra();
            $nuevaCompra->setPartida($partida);
            $nuevaCompra->setPilotoUsuario($piloto);
            $nuevaCompra->setItem($item);
            $nuevaCompra->setDesgaste(0); // Forzar inicio sin desgaste

            $em->persist($nuevaCompra);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'nuevoDinero' => number_format($piloto->getDinero(), 0, ',', '.') . ' €',
                'nuevoEstilo' => $piloto->getEstiloDeVida(),
                'message' => '¡Transacción completada! ' . $item->getNombre() . ' adquirido.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error de procesamiento en la cámara acorazada.'], 500);
        }
    }

    #[Route('/api/career/shop/repair', name: 'api_career_shop_repair', methods: ['POST'])]
    public function repairItem(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = $request->request->get('partidaId');
        $itemId = $request->request->get('itemId');

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        $item = $em->getRepository(TiendaItem::class)->find($itemId);

        $compra = $em->getRepository(TiendaCompra::class)->findOneBy([
            'partida' => $partida,
            'pilotoUsuario' => $piloto,
            'item' => $item
        ]);

        if (!$compra) {
            return new JsonResponse(['success' => false, 'message' => 'No eres el propietario de este objeto.'], 400);
        }

        $desgaste = $compra->getDesgaste();
        if ($desgaste <= 0) {
            return new JsonResponse(['success' => false, 'message' => 'El objeto ya está en perfecto estado.'], 400);
        }

        // FÓRMULA DE COSTES CRECIENTES: A mayor desgaste, reparar cada punto es más caro.
        // Coste base de reparación total es el 50% del valor del item, escalado por el desgaste al cuadrado.
        $factorDesgaste = $desgaste / 100;
        $costeReparacion = (int) (($item->getPrecio() * 0.5) * ($factorDesgaste * $factorDesgaste) + ($desgaste * 5));
        $costeReparacion = max(10, $costeReparacion); // Mínimo 10€

        if ($piloto->getDinero() < $costeReparacion) {
            return new JsonResponse(['success' => false, 'message' => 'No tienes suficiente dinero para afrontar la reparación. Coste: ' . $costeReparacion . ' €'], 400);
        }

        try {
            $piloto->setDinero($piloto->getDinero() - $costeReparacion);
            $compra->setDesgaste(0); // Se restaura por completo al repararlo
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'nuevoDinero' => number_format($piloto->getDinero(), 0, ',', '.') . ' €',
                'message' => 'Objeto reparado por completo. Mantenimiento certificado.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error al procesar la orden del taller.'], 500);
        }
    }
}