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

        // Obtener todos los items del catálogo maestro
        $todosLosItems = $em->getRepository(TiendaItem::class)->findAll();

        // Obtener qué IDs de items ya ha comprado el piloto en esta partida
        $compras = $em->getRepository(TiendaCompra::class)->findBy([
            'partida' => $partida,
            'pilotoUsuario' => $piloto
        ]);
        
        $idsComprados = [];
        foreach ($compras as $compra) {
            $idsComprados[] = $compra->getItem()->getId();
        }

        // Clasificar los items por categorías para las pestañas de Twig
        $tiendaData = ['OBJETOS' => [], 'VEHICULOS' => [], 'PROPIEDADES' => []];
        foreach ($todosLosItems as $item) {
            $cat = $item->getCategoria();
            if (isset($tiendaData[$cat])) {
                $tiendaData[$cat][] = [
                    'entity' => $item,
                    'comprado' => in_array($item->getId(), $idsComprados)
                ];
            }
        }

        return $this->render('career/shop.html.twig', [
            'partida' => $partida,
            'piloto' => $piloto,
            'tienda' => $tiendaData
        ]);
    }

    #[Route('/api/career/shop/buy', name: 'api_career_shop_buy', methods: ['POST'])]
    public function buyItem(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = (int)$request->request->get('partidaId');
        $itemId = (int)$request->request->get('itemId');

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Acceso no autorizado.'], 403);
        }

        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        $item = $em->getRepository(TiendaItem::class)->find($itemId);

        if (!$item) {
            return new JsonResponse(['success' => false, 'message' => 'El artículo no existe en el catálogo maestro.'], 404);
        }

        // Verificar si ya está comprado
        $yaComprado = $em->getRepository(TiendaCompra::class)->findOneBy([
            'partida' => $partida,
            'pilotoUsuario' => $piloto,
            'item' => $item
        ]);

        if ($yaComprado) {
            return new JsonResponse(['success' => false, 'message' => 'Ya posees este artículo de lujo.'], 400);
        }

        // Verificar fondos líquidos
        if ($piloto->getDinero() < $item->getPrecio()) {
            return new JsonResponse(['success' => false, 'message' => 'Fondos insuficientes en tu cuenta corriente.'], 400);
        }

        try {
            // Deducción y bonificación transaccional
            $piloto->setDinero($piloto->getDinero() - $item->getPrecio());
            $piloto->setEstiloDeVida($piloto->getEstiloDeVida() + $item->getBonoEstiloDeVida());

            // Registrar propiedad del bien comprado
            $nuevaCompra = new TiendaCompra();
            $nuevaCompra->setPartida($partida);
            $nuevaCompra->setPilotoUsuario($piloto);
            $nuevaCompra->setItem($item);

            $em->persist($nuevaCompra);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'nuevoDinero' => number_format($piloto->getDinero(), 0, ',', '.') . ' €',
                'nuevoEstilo' => $piloto->getEstiloDeVida(),
                'message' => '¡Transacción completada! ' . $item->getNombre() . ' añadido a tu inventario personal.'
            ]);

        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Fallo bancario en el servidor.'], 500);
        }
    }
}