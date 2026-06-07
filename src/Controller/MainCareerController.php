<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\PilotoIa;
use App\Entity\CalendarioTemporada;
use App\Entity\ResultadosCarreras;
use App\Entity\ContratoMercado;
use App\Entity\Escuderia;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;

class MainCareerController extends AbstractController
{
    #[Route('/career/main/{partidaId}', name: 'app_career_main', methods: ['GET'])]
    public function index(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Piloto del usuario conectado
        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        // Categoría actual del piloto
        $categoriaActual = 'Karting';

        // 1. OBTENER PRÓXIMAS CARRERAS FILTRADAS POR PARTIDA, ESTADO Y CATEGORÍA 🟢 (CORREGIDO)
        $agendaCompletaEntidades = $em->getRepository(CalendarioTemporada::class)->createQueryBuilder('c')
            ->where('c.partida = :partida')
            ->andWhere('c.estadoEvento = :estado')
            ->andWhere('c.categoria = :categoria') // 🟢 Filtramos para que solo aparezcan las carreras de Karting
            ->setParameter('partida', $partida)
            ->setParameter('estado', 'PENDIENTE')
            ->setParameter('categoria', $categoriaActual)
            ->orderBy('c.ordenCarrera', 'ASC')
            ->getQuery()
            ->getResult();

        $agendaFormateada = [];
        foreach ($agendaCompletaEntidades as $carreraEntidad) {
            $circuito = $carreraEntidad->getCircuito();

            $banderaUrl = null;
            if ($circuito) {
                $nombreGP = 'GP DE ' . ($circuito->getPais() ? strtoupper($circuito->getPais()->getNombrePais()) : strtoupper($circuito->getNombreCircuito()));
                $nombreCircuito = $circuito->getNombreCircuito();

                if ($circuito->getPais()) {
                    $banderaUrl = $circuito->getPais()->getBanderaUrl();
                }
            } else {
                $nombreGP = 'GRAN PREMIO OFICIAL';
                $nombreCircuito = 'Trazado Urbano';
            }

            $diaVigente = method_exists($carreraEntidad, 'getDia') ? $carreraEntidad->getDia() : $carreraEntidad->getOrdenCarrera();

            $agendaFormateada[] = [
                'ordenCarrera'   => $carreraEntidad->getOrdenCarrera(),
                'nombre'         => $nombreGP,
                'circuitoNombre' => $nombreCircuito,
                'dia'            => $diaVigente,
                'banderaUrl'     => $banderaUrl
            ];
        }

        // Separamos la primera del resto de la agenda
        $proximaCarrera = !empty($agendaFormateada) ? $agendaFormateada[0] : null;
        $proximasTres   = array_slice($agendaFormateada, 1, 3);

        // 2. OBTENER TODOS LOS PILOTOS DE LA CATEGORÍA PARA ESTA PARTIDA
        $pilotosIa = $em->getRepository(PilotoIa::class)->findBy([
            'partida' => $partida,
            'categoria' => $categoriaActual
        ]);

        // 3. MAPEAR ESCUDERÍAS ACTUALES DESDE EL MERCADO DE CONTRATOS
        $escuderiasCategoria = $em->getRepository(Escuderia::class)->findBy([
            'categoria' => $categoriaActual
        ]);

        $puntosEquipos = [];
        foreach ($escuderiasCategoria as $esc) {
            $puntosEquipos[$esc->getNombreOficial()] = [
                'nombre' => $esc->getNombreOficial(),
                'puntos' => 0,
                'esTuEquipo' => false,
                'posicionReal' => 1
            ];
        }

        $contratosActivos = $em->getRepository(ContratoMercado::class)->findBy(['partida' => $partida]);
        $mapeoEscuderiaPilotos = [];
        $escuderiaNombre = 'Agente Libre';

        foreach ($contratosActivos as $contrato) {
            if ($contrato->getEscuderia()) {
                $nombreOficialEsc = $contrato->getEscuderia()->getNombreOficial();

                if (isset($puntosEquipos[$nombreOficialEsc])) {
                    if ($contrato->isEsJugadorHumano()) {
                        $mapeoEscuderiaPilotos['humano_' . $pilotoHumano->getId()] = $nombreOficialEsc;
                        $escuderiaNombre = $nombreOficialEsc;
                    } else if ($contrato->getPilotoIa()) {
                        $mapeoEscuderiaPilotos['ia_' . $contrato->getPilotoIa()->getId()] = $nombreOficialEsc;
                    }
                }
            }
        }

        if (isset($puntosEquipos[$escuderiaNombre])) {
            $puntosEquipos[$escuderiaNombre]['esTuEquipo'] = true;
        }

        // 4. PROCESAMIENTO Y SUMA DE PUNTOS DESDE 'ResultadosCarreras'
        $resultados = $em->getRepository(ResultadosCarreras::class)->createQueryBuilder('r')
            ->join('r.evento', 'e')
            ->where('e.partida = :partida')
            ->setParameter('partida', $partida)
            ->getQuery()
            ->getResult();

        $puntosPilotosIa = [];
        $puntosHumano = 0;

        foreach ($resultados as $res) {
            $puntos = $res->getPuntosObtenidos();
            $escuderiaDelResultado = $res->getEscuderia();
            $nombreEscuderiaRes = $escuderiaDelResultado ? $escuderiaDelResultado->getNombreOficial() : 'Agente Libre';

            if (isset($puntosEquipos[$nombreEscuderiaRes])) {
                $puntosEquipos[$nombreEscuderiaRes]['puntos'] += $puntos;
            }

            if ($res->isEsJugadorHumano()) {
                $puntosHumano += $puntos;
            } else if ($res->getPilotoIa()) {
                $iaId = $res->getPilotoIa()->getId();
                if (!isset($puntosPilotosIa[$iaId])) {
                    $puntosPilotosIa[$iaId] = 0;
                }
                $puntosPilotosIa[$iaId] += $puntos;
            }
        }

        // 5. CONSTRUCCIÓN DE LA TABLA MUNDIAL DE PILOTOS
        $tablaMundialPilotos = [];
        $tablaMundialPilotos[] = [
            'esHumano' => true,
            'nombre' => $pilotoHumano->getNombre() . ' ' . $pilotoHumano->getApellido(),
            'dorsal' => $pilotoHumano->getNumeroDorsal(),
            'puntos' => $puntosHumano,
            'abreviatura' => $pilotoHumano->getAbreviatura(),
            'escuderia' => $escuderiaNombre
        ];

        foreach ($pilotosIa as $ia) {
            $escuderiaIaNombre = $mapeoEscuderiaPilotos['ia_' . $ia->getId()] ?? 'Agente Libre';
            $tablaMundialPilotos[] = [
                'esHumano' => false,
                'nombre' => $ia->getNombre() . ' ' . $ia->getApellido(),
                'dorsal' => $ia->getNumeroDorsal(),
                'puntos' => $puntosPilotosIa[$ia->getId()] ?? 0,
                'abreviatura' => $ia->getAbreviatura(),
                'escuderia' => $escuderiaIaNombre
            ];
        }

        usort($tablaMundialPilotos, function ($a, $b) {
            return $b['puntos'] <=> $a['puntos'];
        });

        $indiceHumano = 0;
        foreach ($tablaMundialPilotos as $key => $fila) {
            if ($fila['esHumano']) {
                $indiceHumano = $key;
                break;
            }
        }

        $totalPilotos = count($tablaMundialPilotos);
        if ($indiceHumano <= 1) {
            $recorteClasificacionPilotos = array_slice($tablaMundialPilotos, 0, 5);
        } elseif ($indiceHumano >= $totalPilotos - 2) {
            $recorteClasificacionPilotos = array_slice($tablaMundialPilotos, -5, 5);
        } else {
            $recorteClasificacionPilotos = array_slice($tablaMundialPilotos, $indiceHumano - 2, 5);
        }

        // 6. ORDENACIÓN Y ENFOQUE DINÁMICO DE EQUIPOS
        $listaEquipos = array_values($puntosEquipos);

        usort($listaEquipos, function ($a, $b) {
            return $b['puntos'] <=> $a['puntos'];
        });

        $indiceEquipoHumano = null;
        foreach ($listaEquipos as $key => &$eq) {
            $eq['posicionReal'] = $key + 1;
            if ($eq['esTuEquipo']) {
                $indiceEquipoHumano = $key;
            }
        }
        unset($eq);

        $recorteClasificacionEquipos = [];
        $totalEquipos = count($listaEquipos);

        if ($totalEquipos > 0) {
            if ($indiceEquipoHumano === null) {
                $recorteClasificacionEquipos = array_slice($listaEquipos, 0, min(3, $totalEquipos));
            } elseif ($indiceEquipoHumano === 0) {
                $recorteClasificacionEquipos = array_slice($listaEquipos, 0, min(3, $totalEquipos));
            } elseif ($indiceEquipoHumano === $totalEquipos - 1) {
                $recorteClasificacionEquipos = array_slice($listaEquipos, max(0, $totalEquipos - 3), 3);
            } else {
                $recorteClasificacionEquipos = array_slice($listaEquipos, $indiceEquipoHumano - 1, 3);
            }
        }

        $sumaStats = $pilotoHumano->getStatClasificacion()
            + $pilotoHumano->getStatRitmo()
            + $pilotoHumano->getStatAdelantamiento()
            + $pilotoHumano->getStatDefensa()
            + $pilotoHumano->getStatGestionNeumaticos()
            + $pilotoHumano->getStatMojado();

        $mediaStats = round($sumaStats / 6);

        $nivel = $pilotoHumano->getNivelActual();
        $expActual = $pilotoHumano->getExpActual();
        $expNecesariaSiguienteNivel = $nivel * 100; // Fórmula: nivel * 100

        // Calculamos el porcentaje para el ancho de la barra de Tailwind (máximo 100%)
        $porcentajeExp = $expNecesariaSiguienteNivel > 0
            ? min(100, round(($expActual / $expNecesariaSiguienteNivel) * 100))
            : 0;

        return $this->render('career/main.html.twig', [
            'partida' => $partida,
            'piloto' => $pilotoHumano,
            'mediaStats' => $mediaStats,
            'expNecesaria' => $expNecesariaSiguienteNivel, // 🟢 Enviamos el tope de EXP
            'porcentajeExp' => $porcentajeExp,             // 🟢 Enviamos el % de la barra
            'escuderiaNombre' => $escuderiaNombre,
            'proximaCarrera' => $proximaCarrera,
            'proximasTres' => $proximasTres,
            'clasificacionRecortada' => $recorteClasificacionPilotos,
            'indiceHumanoGlobal' => $indiceHumano + 1,
            'clasificacionEquipos' => $recorteClasificacionEquipos
        ]);
    }

    #[Route('/career/search/{partidaId}', name: 'app_career_search', methods: ['GET'])]
    public function search(int $partidaId, Request $request, EntityManagerInterface $em): Response
    {
        $query = $request->query->get('q', '');

        // 1. RECUPERAMOS LA PARTIDA REAL Y SUS COMPONENTES
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        // Recuperamos los datos reales de la base de datos para filtrar en memoria
        $todosLosPilotos = $em->getRepository(PilotoIa::class)->findBy(['partida' => $partida]);
        $todasLasEscuderias = $em->getRepository(Escuderia::class)->findAll(); 

        $pilotosFiltrados = [];
        $escuderiasFiltrados = [];

        // 2. Filtramos en memoria si hay búsqueda (Mínimo 2 caracteres)
        if (strlen(trim($query)) >= 2) {
            $pilotosFiltrados = array_filter($todosLosPilotos, function ($piloto) use ($query) {
                return stripos($piloto->getNombre(), $query) !== false ||
                    stripos($piloto->getApellido(), $query) !== false;
            });

            $escuderiasFiltrados = array_filter($todasLasEscuderias, function ($escuderia) use ($query) {
                return stripos($escuderia->getNombreOficial(), $query) !== false;
            });
        }

        // 3. RESPUESTA AJAX (JSON)
        if ($request->headers->get('X-Requested-With') === 'XMLHttpRequest') {
            $pilotosJson = [];
            foreach ($pilotosFiltrados as $p) {
                $media = ($p->getStatClasificacion() +
                    $p->getStatRitmo() +
                    $p->getStatConsistencia() +
                    $p->getStatAdelantamiento() +
                    $p->getStatDefensa() +
                    $p->getStatGestionNeumaticos() +
                    $p->getStatMojado()) / 7;

                $pilotosJson[] = [
                    'nombre' => $p->getNombre(),
                    'apellido' => $p->getApellido(),
                    'categoria' => $p->getCategoria(),
                    'media' => round($media)
                ];
            }

            // 🟢 CORREGIDO: Inicializar el array completamente limpio
            $escuderiasJson = []; 
            
            // 🟢 CORREGIDO: Recorrer el array de "$escuderiasFiltrados", NO "$escuderiasJson"
            foreach ($escuderiasFiltrados as $e) { 
                $escuderiasJson[] = [
                    'nombreOficial' => $e->getNombreOficial(),
                    'categoria' => $e->getCategoria()
                ];
            }

            return new JsonResponse([
                'pilotos' => $pilotosJson,
                'escuderias' => $escuderiasJson // 🟢 CORREGIDO: Enviamos el array limpio directamente
            ]);
        }

        // 4. CARGA INICIAL (HTML) - Enviamos el objeto $partida real
        return $this->render('career/search.html.twig', [
            'partida' => $partida,
            'texto' => $query,
            'pilotos' => $pilotosFiltrados,
            'escuderias' => $escuderiasFiltrados,
        ]);
    }
}
