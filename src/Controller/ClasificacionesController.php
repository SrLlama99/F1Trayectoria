<?php

namespace App\Controller;

use App\Entity\ResultadosCarreras;
use App\Entity\CalendarioTemporada;
use App\Entity\PartidaGuardada;
use App\Entity\PilotoIa;
use App\Entity\PilotoUsuario;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class ClasificacionesController extends AbstractController
{
    #[Route('/career/standings/{idPartida}', name: 'app_clasificaciones')]
    public function index(
        int $idPartida,
        Request $request,
        EntityManagerInterface $em
    ): Response {
        // 1. Validar la existencia de la partida guardada
        $partida = $em->getRepository(PartidaGuardada::class)->find($idPartida);
        if (!$partida) {
            throw $this->createNotFoundException('La partida guardada solicitada no existe.');
        }

        // 2. Localizar el piloto humano asociado de forma unívoca a esta partida guardada
        /** @var PilotoUsuario|null $pilotoHumano */
        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        // 3. Determinar el año de simulación actual en curso para esta partida
        $anoActual = $em->createQueryBuilder()
            ->select('MAX(c.anoSimulacion)')
            ->from(CalendarioTemporada::class, 'c')
            ->where('c.partida = :partida')
            ->setParameter('partida', $partida)
            ->getQuery()
            ->getSingleScalarResult();

        // Si no hay eventos cargados aún en el calendario, fijamos un año por defecto
        $anoActual = $anoActual !== null ? (int)$anoActual : 2026;

        // 4. Procesar filtros GET de navegación de la UI
        $temporadaFiltro = $request->query->get('temporada', 'actual'); // 'actual' o el año numérico directo
        $categoriaFiltro = $request->query->get('categoria', 'ALL');     // 'ALL', 'KARTING', 'F3', 'F2', 'F1'

        $anoObjetivo = ($temporadaFiltro === 'actual') ? $anoActual : (int)$temporadaFiltro;

        // 5. Histórico de años anteriores para alimentar el selector de temporadas pasadas
        $temporadasPasadasRaw = $em->createQueryBuilder()
            ->select('DISTINCT c.anoSimulacion')
            ->from(CalendarioTemporada::class, 'c')
            ->where('c.partida = :partida')
            ->andWhere('c.anoSimulacion < :anoActual')
            ->setParameter('partida', $partida)
            ->setParameter('anoActual', $anoActual)
            ->orderBy('c.anoSimulacion', 'DESC')
            ->getQuery()
            ->getResult();

        $temporadasPasadas = [];
        foreach ($temporadasPasadasRaw as $t) {
            $temporadasPasadas[] = [
                'id' => $t['anoSimulacion'],
                'anyo' => $t['anoSimulacion']
            ];
        }

        // 6. CONSULTA DEL MUNDIAL DE PILOTOS (Agregación sobre ResultadosCarreras)
        $qbPilotos = $em->createQueryBuilder()
            ->select(
                'IDENTITY(r.pilotoIa) as pilotoIaId',
                'r.esJugadorHumano',
                'e.nombreOficial as nombreEscuderia',
                'SUM(r.puntosObtenidos) as puntos',
                'SUM(CASE WHEN r.posicionFinal = 1 THEN 1 ELSE 0 END) as victorias',
                'SUM(CASE WHEN r.posicionFinal <= 3 THEN 1 ELSE 0 END) as podios'
            )
            ->from(ResultadosCarreras::class, 'r')
            ->leftJoin('r.escuderia', 'e')
            ->join('r.evento', 'ev')
            ->where('ev.partida = :partida')
            ->andWhere('ev.anoSimulacion = :anoObjetivo')
            ->setParameter('partida', $partida)
            ->setParameter('anoObjetivo', $anoObjetivo);

        if ($categoriaFiltro !== 'ALL') {
            $qbPilotos->andWhere('ev.categoria = :categoria')
                      ->setParameter('categoria', $categoriaFiltro);
        }

        $qbPilotos->groupBy('r.esJugadorHumano', 'r.pilotoIa', 'e.nombreOficial')
                  ->orderBy('puntos', 'DESC');

        $resultadosPilotosRaw = $qbPilotos->getQuery()->getResult();

        // Rehidratamos los datos agregados para que la plantilla Twig pinte los nombres sin romper la abstracción
        $clasificacionPilotos = [];
        foreach ($resultadosPilotosRaw as $row) {
            $nombreCompleto = 'Piloto IA';
            $esTuRegistro = false;

            if ($row['esJugadorHumano']) {
                $esTuRegistro = true;
                if ($pilotoHumano) {
                    $nombreCompleto = $pilotoHumano->getNombre() . ' ' . $pilotoHumano->getApellido();
                } else {
                    $nombreCompleto = 'TÚ (Jugador)';
                }
            } elseif ($row['pilotoIaId']) {
                $pilotoIaObj = $em->getRepository(PilotoIa::class)->find($row['pilotoIaId']);
                if ($pilotoIaObj) {
                    $nombreCompleto = $pilotoIaObj->getNombre() . ' ' . $pilotoIaObj->getApellido();
                }
            }

            $clasificacionPilotos[] = [
                'isHumano'   => $esTuRegistro, // Flag utilísimo para aplicar clases CSS doradas o destacar filas en Twig
                'piloto'     => [
                    'nombre'   => $nombreCompleto,
                    'apellido' => ''
                ],
                'escuderia'  => [
                    'nombre' => $row['nombreEscuderia'] ?? 'Agente Libre'
                ],
                'victorias'  => (int)$row['victorias'],
                'podios'     => (int)$row['podios'],
                'puntos'     => (int)$row['puntos']
            ];
        }

        // 7. CONSULTA DEL CAMPEONATO DE ESCUDERÍAS (Constructores)
        $qbEscuderias = $em->createQueryBuilder()
            ->select(
                'e.nombreOficial',
                'SUM(r.puntosObtenidos) as puntos'
            )
            ->from(ResultadosCarreras::class, 'r')
            ->join('r.escuderia', 'e')
            ->join('r.evento', 'ev')
            ->where('ev.partida = :partida')
            ->andWhere('ev.anoSimulacion = :anoObjetivo')
            ->setParameter('partida', $partida)
            ->setParameter('anoObjetivo', $anoObjetivo);

        if ($categoriaFiltro !== 'ALL') {
            $qbEscuderias->andWhere('ev.categoria = :categoria')
                         ->setParameter('categoria', $categoriaFiltro);
        }

        $qbEscuderias->groupBy('e.id', 'e.nombreOficial')
                     ->orderBy('puntos', 'DESC');

        $resultadosEscuderiasRaw = $qbEscuderias->getQuery()->getResult();

        $clasificacionEscuderias = [];
        foreach ($resultadosEscuderiasRaw as $row) {
            $clasificacionEscuderias[] = [
                'escuderia' => [
                    'nombre'     => $row['nombreOficial'],
                ],
                'puntos'    => (int)$row['puntos']
            ];
        }

        // 8. Carga de la vista enviando las variables de contexto optimizadas
        return $this->render('career/clasificaciones.html.twig', [
            'partida'                 => $partida,
            'anoActual'               => $anoActual,
            'temporadasPasadas'       => $temporadasPasadas,
            'clasificacionPilotos'    => $clasificacionPilotos,
            'clasificacionEscuderias' => $clasificacionEscuderias,
        ]);
    }
}