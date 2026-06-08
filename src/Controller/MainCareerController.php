<?php

namespace App\Controller;

use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario;
use App\Entity\PilotoIa;
use App\Entity\CalendarioTemporada;
use App\Entity\ResultadosCarreras;
use App\Entity\ContratoMercado;
use App\Entity\Escuderia;
use App\Entity\Pais;
use App\Entity\Logro;
use App\Entity\LogroPartida;
use App\Entity\TiendaCompra;
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

            $agendaFormateada[] = [
                'ordenCarrera'   => $carreraEntidad->getOrdenCarrera(),
                'nombre'         => $nombreGP,
                'circuitoNombre' => $nombreCircuito,
                'semana'            => $carreraEntidad->getSemanaCarrera(),
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
        // ─── COMPROBACIÓN DE CARRERA ESTA SEMANA ───
        // Buscamos si existe un emparejamiento en CalendarioTemporada para la semana, año y categoría actuales
        $carreraEstaSemana = $em->getRepository(CalendarioTemporada::class)->findOneBy([
            'partida' => $partida,
            'categoria' => $pilotoHumano->getCategoria(),
            'anoSimulacion' => $partida->getIngameAno(),
            'semanaCarrera' => $partida->getIngameSemana()
        ]);
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
            'clasificacionEquipos' => $recorteClasificacionEquipos,
            'tieneCarrera' => ($carreraEstaSemana !== null) // Booleano para el botón del Paddock
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
                    $p->getStatAdelantamiento() +
                    $p->getStatDefensa() +
                    $p->getStatGestionNeumaticos() +
                    $p->getStatMojado()) / 6;

                $pilotosJson[] = [
                    'id' => $p->getId(),
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
                    'id' => $e->getId(),
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

    #[Route('/career/profile/{partidaId}/{idPilotoIa}', name: 'app_career_profile', defaults: ['idPilotoIa' => null], methods: ['GET'])]
    public function profile(int $partidaId, ?int $idPilotoIa, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        if ($idPilotoIa) {
            $piloto = $em->getRepository(PilotoIa::class)->find($idPilotoIa);
            $esHumano = false;
            if (!$piloto || $piloto->getPartida() !== $partida) {
                return $this->redirectToRoute('app_career_main', ['partidaId' => $partidaId]);
            }
        } else {
            $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
            $esHumano = true;
        }

        // 1. Media Global de Atributos (Suma de los 6 atributos principales / 6)
        $sumaStats = $piloto->getStatClasificacion()
            + $piloto->getStatRitmo()
            + $piloto->getStatAdelantamiento()
            + $piloto->getStatDefensa()
            + $piloto->getStatGestionNeumaticos()
            + $piloto->getStatMojado();

        $mediaStats = round($sumaStats / 6);

        // 2. Consulta dinámica de Estadísticas por Categoría
        $qb = $em->getRepository(ResultadosCarreras::class)->createQueryBuilder('r')
            ->join('r.evento', 'e')
            ->select('e.categoria AS categoria')
            ->addSelect('COUNT(r.id) AS carrerasTotales')
            ->addSelect('SUM(CASE WHEN r.posicionFinal = 1 THEN 1 ELSE 0 END) AS victorias')
            ->addSelect('SUM(CASE WHEN r.posicionFinal <= 3 THEN 1 ELSE 0 END) AS podios')
            ->addSelect('SUM(CASE WHEN r.posicionSalida = 1 THEN 1 ELSE 0 END) AS poles')
            ->addSelect('SUM(CASE WHEN r.vueltaRapida = true OR r.vueltaRapida = 1 THEN 1 ELSE 0 END) AS vueltasRapidas')
            ->addSelect('SUM(r.puntosObtenidos) AS puntosTotales')
            ->where('e.partida = :partida')
            ->setParameter('partida', $partida)
            ->groupBy('e.categoria');

        if ($esHumano) {
            $qb->andWhere('r.esJugadorHumano = true');
        } else {
            $qb->andWhere('r.esJugadorHumano = false')->andWhere('r.pilotoIa = :pilotoIa')->setParameter('pilotoIa', $piloto);
        }
        $resultadosDb = $qb->getQuery()->getResult();

        $estadisticasPorCategoria = [];
        foreach ($resultadosDb as $row) {
            $estadisticasPorCategoria[strtoupper($row['categoria'])] = [
                'carrerasTotales' => (int)$row['carrerasTotales'],
                'victorias'       => (int)$row['victorias'],
                'podios'          => (int)$row['podios'],
                'poles'           => (int)$row['poles'],
                'vueltasRapidas'  => (int)$row['vueltasRapidas'],
                'puntosTotales'   => (int)$row['puntosTotales'],
            ];
        }

        if (empty($estadisticasPorCategoria)) {
            $catActual = strtoupper($idPilotoIa ? $piloto->getCategoria() : 'KARTING');
            $estadisticasPorCategoria[$catActual] = [
                'carrerasTotales' => 0,
                'victorias' => 0,
                'podios' => 0,
                'poles' => 0,
                'vueltasRapidas' => 0,
                'puntosTotales' => 0
            ];
        }

        // 3. 🟢 NUEVO: OBTENER CONTRATO ACTIVO DESDE LA BASE DE DATOS
        // Buscamos el contrato vigente en la tabla ContratoMercado
        $contratoActivo = $em->getRepository(ContratoMercado::class)->findOneBy([
            'partida' => $partida,
            'pilotoIa' => $idPilotoIa ? $piloto : null,
            // Si es humano, normalmente se vincula dejando pilotoIa en null o usando un campo esHumano en tu entidad
        ]);

        // Mapeo de respaldo (Fallback) en caso de que esté en pretemporada o sin contrato activo asignado aún
        if (!$contratoActivo) {
            $contratoActivo = [
                'nombreEscuderia' => $idPilotoIa ? 'Agente Libre' : 'Escudería de Desarrollo',
                'salarioPorCarrera' => $idPilotoIa ? 0 : 2500,
                'carrerasRestantes' => 8,
                'rol' => 'Primer Piloto',
                'clausulaRescision' => $idPilotoIa ? 0 : 15000
            ];
        }

        $trofeos = [];

        $catalogoLogros = $em->getRepository(Logro::class)->findAll();

        $logrosObtenidosRaw = $em->getRepository(LogroPartida::class)->findBy([
            'partida' => $partida
        ]);

        // 3. Extraemos solo los códigos o identificadores de los logros que YA se han conseguido
        $logrosCompradosIds = [];
        foreach ($logrosObtenidosRaw as $logroPartida) {
            // Reemplaza 'getIdentificador' o 'getCodigo' por el método real de tu entidad LogroPartida
            $logrosCompradosIds[] = $logroPartida->getId();
        }

        $paises = $em->getRepository(Pais::class)->findAll();

        return $this->render('career/profile.html.twig', [
            'partida' => $partida,
            'piloto' => $piloto,
            'esHumano' => $esHumano,
            'mediaStats' => $mediaStats,
            'estadisticasCategorias' => $estadisticasPorCategoria,
            'contrato' => $contratoActivo, // 🟢 Enviamos el contrato a la vista
            'trofeos' => $trofeos,
            'catalogoLogros' => $catalogoLogros,
            'logrosCompradosIds' => $logrosCompradosIds,
            'paises' => $paises
        ]);
    }

    #[Route('/career/profile/update/{partidaId}/{idPilotoIa}', name: 'app_career_profile_update', requirements: ['partidaId' => '\d+', 'idPilotoIa' => '\d+'], methods: ['POST'])]
    public function updateProfile(int $partidaId, int $idPilotoIa, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Acceso denegado o partida inválida.'], Response::HTTP_FORBIDDEN);
        }

        // El coste se descuenta siempre del capital del jugador humano de la partida
        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano || $pilotoHumano->getDinero() < 200) {
            return new JsonResponse(['success' => false, 'message' => 'Fondos insuficientes en el Paddock. Necesitas 200 €.']);
        }

        // Determinar qué entidad estamos editando realmente
        if ($idPilotoIa > 0) {
            $piloto = $em->getRepository(PilotoIa::class)->find($idPilotoIa);
            if (!$piloto || $piloto->getPartida() !== $partida) {
                return new JsonResponse(['success' => false, 'message' => 'Piloto de IA no encontrado en esta escudería/partida.']);
            }

            // Solo modificamos el dorsal competitivo si es piloto controlado por la IA
            $nuevoDorsal = (int)$request->request->get('numeroDorsal');
            if ($nuevoDorsal > 0) {
                $piloto->setNumeroDorsal($nuevoDorsal);
            }
        } else {
            $piloto = $pilotoHumano;
        }

        // Validar e inyectar campos comunes
        $nombre = trim($request->request->get('nombre'));
        $apellido = trim($request->request->get('apellido'));
        $abreviatura = strtoupper(trim($request->request->get('abreviatura')));

        if (empty($nombre) || empty($apellido) || strlen($abreviatura) !== 3) {
            return new JsonResponse(['success' => false, 'message' => 'Datos inválidos. La abreviatura debe constar exactamente de 3 caracteres.']);
        }

        $piloto->setNombre($nombre);
        $piloto->setApellido($apellido);
        $piloto->setAbreviatura($abreviatura);

        $paisId = $request->request->get('paisId');
        if ($paisId) {
            $pais = $em->getRepository(Pais::class)->find($paisId);
            if ($pais) {
                $piloto->setPais($pais);
            }
        }

        // 💶 Cobro reglamentario de la transacción
        $pilotoHumano->setDinero($pilotoHumano->getDinero() - 200);

        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/career/escuderia/profile/{partidaId}/{escuderiaId}', name: 'app_career_escuderia_profile', requirements: ['partidaId' => '\d+', 'escuderiaId' => '\d+'], methods: ['GET'])]
    public function escuderiaProfile(int $partidaId, int $escuderiaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return $this->redirectToRoute('app_dashboard');
        }

        $escuderia = $em->getRepository(Escuderia::class)->find($escuderiaId);
        if (!$escuderia || $escuderia->getPartida() !== $partida) {
            return $this->redirectToRoute('app_career_main', ['partidaId' => $partidaId]);
        }

        // Obtener los pilotos actuales que tienen contrato con esta escudería en la partida
        $contratos = $em->getRepository(ContratoMercado::class)->findBy([
            'partida' => $partida,
            'escuderia' => $escuderia
        ]);

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        return $this->render('career/escuderia_profile.html.twig', [
            'partida' => $partida,
            'escuderia' => $escuderia,
            'contratos' => $contratos,
            'pilotoHumano' => $pilotoHumano,
        ]);
    }

    #[Route('/career/escuderia/update/{partidaId}/{escuderiaId}', name: 'app_career_escuderia_update', requirements: ['partidaId' => '\d+', 'escuderiaId' => '\d+'], methods: ['POST'])]
    public function updateEscuderia(int $partidaId, int $escuderiaId, Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Acceso denegado.'], Response::HTTP_FORBIDDEN);
        }

        $pilotoHumano = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
        if (!$pilotoHumano || $pilotoHumano->getDinero() < 200) {
            return new JsonResponse(['success' => false, 'message' => 'Fondos insuficientes en el Paddock. Necesitas 200 €.']);
        }

        $escuderia = $em->getRepository(Escuderia::class)->find($escuderiaId);
        if (!$escuderia || $escuderia->getPartida() !== $partida) {
            return new JsonResponse(['success' => false, 'message' => 'Escudería no encontrada.']);
        }

        $nombreOficial = trim($request->request->get('nombreOficial'));
        $nombreCorto = strtoupper(trim($request->request->get('nombreCorto')));

        if (empty($nombreOficial) || empty($nombreCorto)) {
            return new JsonResponse(['success' => false, 'message' => 'Los campos no pueden estar vacíos.']);
        }

        if (strlen($nombreCorto) > 10) {
            return new JsonResponse(['success' => false, 'message' => 'La abreviatura no puede superar los 10 caracteres.']);
        }

        $escuderia->setNombreOficial($nombreOficial);
        $escuderia->setNombreCorto($nombreCorto);

        // Cobro de los 200€ al piloto humano
        $pilotoHumano->setDinero($pilotoHumano->getDinero() - 200);
        $em->flush();

        return new JsonResponse(['success' => true]);
    }

    #[Route('/api/career/next-week', name: 'api_career_next_week', methods: ['POST'])]
    public function nextWeek(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = $request->request->get('partidaId');
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);

        if (!$partida || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Partida inválida.'], 403);
        }

        // 1. Avanzar la semana del simulador
        $nuevaSemana = $partida->getIngameSemana() + 1;
        if ($nuevaSemana > 52) { // Asumiendo un año de 52 semanas estándar fia
            $nuevaSemana = 1;
            $partida->setIngameAno($partida->getIngameAno() + 1);
        }
        $partida->setIngameSemana($nuevaSemana);
        $em->flush();

        // 2. Tirada del 50% para ver si salta un evento aleatorio
        $lanzarDado = rand(1, 100);
        if ($lanzarDado <= 50) {
            // Obtenemos todas las plantillas disponibles
            $plantillas = $em->getRepository(\App\Entity\EventoPlantilla::class)->findAll();

            if (!empty($plantillas)) {
            // Selección aleatoria simple (se puede mejorar usando el peso de probabilidad si se desea)
                /** @var \App\Entity\EventoPlantilla $eventoElegido */
                $eventoElegido = $plantillas[array_rand($plantillas)];

                // Estructuramos las opciones para enviarlas al JavaScript de la vista
                $opcionesPayload = [];
                foreach ($eventoElegido->getOpciones() as $opcion) {
                    $opcionesPayload[] = [
                        'id' => $opcion->getId(),
                        'texto' => $opcion->getTextoBoton()
                    ];
                }

                return new JsonResponse([
                    'success' => true,
                    'triggerEvento' => true,
                    'evento' => [
                        'titulo' => $eventoElegido->getTitulo(),
                        'descripcion' => $eventoElegido->getDescripcion(),
                        'opciones' => $opcionesPayload
                    ]
                ]);
            }
        }

        // Si no hubo evento, refrescamos directamente el paddock de forma nativa
        return new JsonResponse([
            'success' => true,
            'triggerEvento' => false
        ]);
    }

    #[Route('/api/career/event/decision', name: 'api_career_event_decision', methods: ['POST'])]
    public function processDecision(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = $request->request->get('partidaId');
        $opcionId = $request->request->get('opcionId');

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        $piloto = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);

        if (!$partida || !$piloto || $partida->getUsuario() !== $this->getUser()) {
            return new JsonResponse(['success' => false, 'message' => 'Acceso denegado.'], 403);
        }

        $opcion = $em->getRepository(\App\Entity\EventoOpcion::class)->find($opcionId);
        if (!$opcion) {
            return new JsonResponse(['success' => false, 'message' => 'Opción no localizada.'], 404);
        }

        // Procesar cada consecuencia asociada a la respuesta elegida por el piloto
        foreach ($opcion->getConsecuencias() as $consecuencia) {
            switch ($consecuencia->getTipoEfecto()) {
                case 'MODIFICAR_DINERO':
                    $piloto->setDinero($piloto->getDinero() + $consecuencia->getCantidad());
                    break;

                case 'MODIFICAR_AFINIDAD':
                    // 1. Buscamos la relación específica del piloto para esta partida.
                    // Para hacerlo flexible, asumiremos que puedes definir el objetivo en el evento.
                    // Si el evento no especifica actor, por defecto buscaremos 'NOVIA' (tu pareja).
                    $tipoActorAfectado = 'NOVIA'; 
            
            // Opcional: Si en un futuro añades un campo 'actor_afectado' en la consecuencia, lo lees aquí:
            // $tipoActorAfectado = $consecuencia->getActorAfectado() ?: 'NOVIA';

                    /** @var PilotoRelacion|null $relacion */
                    $relacion = $em->getRepository(\App\Entity\PilotoRelacion::class)->findOneBy([
                        'partida' => $partida,
                        'pilotoUsuario' => $piloto,
                        'tipoActor' => $tipoActorAfectado
                    ]);

                    // 2. Si existe la relación (ej: tiene novia en esta partida), alteramos su afinidad
                    if ($relacion) {
                        $nuevaAfinidad = $relacion->getAfinidad() + $consecuencia->getCantidad();

                        // Cotos lógicos según tu escala de 1 a 100 de la entidad
                        $nuevaAfinidad = max(1, min(100, $nuevaAfinidad));

                        $relacion->setAfinidad($nuevaAfinidad);
                    }
                    break;

                case 'AUMENTAR_DESGASTE':
                    // Aplica desgaste a un bien aleatorio comprado en esta partida si existe
                    $compras = $em->getRepository(TiendaCompra::class)->findBy(['partida' => $partida, 'pilotoUsuario' => $piloto]);
                    if (!empty($compras)) {
                        /** @var TiendaCompra $compraElegida */
                        $compraElegida = $compras[array_rand($compras)];
                        $nuevoDesgaste = min(100, $compraElegida->getDesgaste() + $consecuencia->getCantidad());
                        $compraElegida->setDesgaste($nuevoDesgaste);
                    }
                    break;
            }
        }

        $em->flush();

        return new JsonResponse([
            'success' => true,
            'message' => 'Decisión registrada en el Paddock oficial. Simulando impactos...'
        ]);
    }
}
