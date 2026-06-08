<?php

namespace App\Controller;

use App\Entity\Pais;
use App\Entity\PartidaGuardada;
use App\Entity\PilotoUsuario; // Solo para el jugador real
use App\Entity\ContratoMercado;     // 🏎️ Asegúrate de que este sea el nombre exacto de tu entidad para la IA
use App\Entity\Usuario;
use App\Entity\Escuderia;
use App\Entity\PilotoIa;
use App\Entity\CircuitosBase;
use App\Entity\CalendarioTemporada;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CareerController extends AbstractController
{
    #[Route('/career/create/slot/{slotNumero}', name: 'app_career_create', methods: ['GET'])]
    public function showCreationForm(int $slotNumero, EntityManagerInterface $em): Response
    {
        if ($slotNumero < 1 || $slotNumero > 3) {
            return $this->redirectToRoute('app_dashboard');
        }
        $listaPaises = $em->getRepository(Pais::class)->findBy([], ['nombrePais' => 'ASC']);
        return $this->render('career/create.html.twig', [
            'slotNumero' => $slotNumero,
            'paises' => $listaPaises
        ]);
    }

    #[Route('/career/generation-loading/{partidaId}', name: 'app_career_loading', methods: ['GET'])]
    public function loadingScreen(int $partidaId): Response
    {
        return $this->render('career/loading.html.twig', [
            'partidaId' => $partidaId
        ]);
    }

    #[Route('/api/career/setup', name: 'api_career_setup', methods: ['POST'])]
    public function setupCareer(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $slotNumero = (int)$request->request->get('slotNumero');
        $nombre = trim($request->request->get('nombre'));
        $apellido = trim($request->request->get('apellido'));
        $codigoIso = $request->request->get('pais');
        $generoElegido = trim($request->request->get('genero', 'MASCULINO'));
        $preferenciaElegida = trim($request->request->get('preferenciaSexual', 'HETEROSEXUAL'));
        $abreviatura = strtoupper(substr(trim($request->request->get('abreviatura')), 0, 3));

        if (!$nombre || !$apellido || !$codigoIso || strlen($abreviatura) !== 3) {
            return new JsonResponse(['success' => false, 'message' => 'DATOS DE REGISTRO INCOMPLETOS'], Response::HTTP_BAD_REQUEST);
        }

        $usuario = $this->getUser() ?? $em->getRepository(Usuario::class)->find(1);
        $pais = $em->getRepository(Pais::class)->findOneBy(['codigoIso' => $codigoIso]);

        if (!$pais) {
            return new JsonResponse(['success' => false, 'message' => 'EL PAÍS SELECCIONADO NO EXISTE'], Response::HTTP_BAD_REQUEST);
        }

        try {
            $partidaExistente = $em->getRepository(PartidaGuardada::class)->findOneBy([
                'usuario' => $usuario,
                'slotNumero' => $slotNumero
            ]);

            if ($partidaExistente) {
                return new JsonResponse([
                    'success' => true,
                    'message' => 'PROCESANDO SOLICITUD EN CURSO...',
                    'partidaId' => $partidaExistente->getIdPartida()
                ]);
            }

            $partida = new PartidaGuardada();
            $partida->setUsuario($usuario);
            $partida->setSlotNumero($slotNumero);
            $partida->setIngameAno(2026);
            $partida->setIngameEstado('Karting');
            $partida->setFechaGuardado(new \DateTime());
            $partida->setIngameSemana(1);
            $em->persist($partida);

            $piloto = new PilotoUsuario();
            $piloto->setPartida($partida);
            $piloto->setPais($pais);
            $piloto->setNombre($nombre);
            $piloto->setApellido($apellido);
            $piloto->setAbreviatura($abreviatura);
            $piloto->setNumeroDorsal(rand(2, 98));
            $piloto->setNivelActual(1);
            $piloto->setExpActual(0);
            $piloto->setPuntosHabilidadDisponibles(0);
            $piloto->setCategoria('KARTING');
            $piloto->setGenero($generoElegido);
            $piloto->setPreferenciaSexual($preferenciaElegida);

            $piloto->setStatClasificacion(50);
            $piloto->setStatRitmo(50);
            $piloto->setStatAdelantamiento(50);
            $piloto->setStatDefensa(50);
            $piloto->setStatGestionNeumaticos(50);
            $piloto->setStatMojado(50);
            $em->persist($piloto);

            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'SUPERLICENCIA EMITIDA. CONFIGURANDO PARRILLAS FIA...',
                'partidaId' => $partida->getIdPartida()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error de consistencia: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/api/career/generate-world/{partidaId}', name: 'api_career_generate_world', methods: ['POST'])]
    public function generateWorld(int $partidaId, EntityManagerInterface $em): JsonResponse
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida) return new JsonResponse(['success' => false, 'message' => 'Partida no encontrada'], 404);

        try {
            $dataEcosistema = [
                'F1' => [
                    'escuderias' => [
                        [
                            'nombre' => 'Oracle Red Bull Racing',
                            'corto' => 'RBR',
                            'motor' => 93,
                            'chasis' => 95,
                            'aero' => 96,
                            'presupuesto' => 135000000,
                            'pilotos' => [['Max', 'Verstappen', 'VER', 1, 55000000], ['Sergio', 'Pérez', 'PER', 11, 14000000]]
                        ],
                        [
                            'nombre' => 'Mercedes-AMG PETRONAS F1 Team',
                            'corto' => 'MER',
                            'motor' => 96,
                            'chasis' => 92,
                            'aero' => 91,
                            'presupuesto' => 135000000,
                            'pilotos' => [['George', 'Russell', 'RUS', 63, 18000000], ['Andrea Kimi', 'Antonelli', 'ANT', 12, 6000000]]
                        ],
                        [
                            'nombre' => 'Scuderia Ferrari HP',
                            'corto' => 'FER',
                            'motor' => 95,
                            'chasis' => 93,
                            'aero' => 94,
                            'presupuesto' => 135000000,
                            'pilotos' => [['Charles', 'Leclerc', 'LEC', 16, 34000000], ['Lewis', 'Hamilton', 'HAM', 44, 50000000]]
                        ],
                        [
                            'nombre' => 'McLaren Formula 1 Team',
                            'corto' => 'MCL',
                            'motor' => 96,
                            'chasis' => 96,
                            'aero' => 95,
                            'presupuesto' => 135000000,
                            'pilotos' => [['Lando', 'Norris', 'NOR', 4, 20000000], ['Oscar', 'Piastri', 'PIA', 81, 8000000]]
                        ],
                        [
                            'nombre' => 'Aston Martin Aramco F1 Team',
                            'corto' => 'AST',
                            'motor' => 94,
                            'chasis' => 89,
                            'aero' => 91,
                            'presupuesto' => 130000000,
                            'pilotos' => [['Fernando', 'Alonso', 'ALO', 14, 25000000], ['Lance', 'Stroll', 'STR', 18, 3000000]]
                        ],
                        [
                            'nombre' => 'Audi F1 Team',
                            'corto' => 'AUD',
                            'motor' => 90,
                            'chasis' => 88,
                            'aero' => 87,
                            'presupuesto' => 135000000,
                            'pilotos' => [['Nico', 'Hülkenberg', 'HUL', 27, 7000000], ['Gabriel', 'Bortoleto', 'BOR', 5, 2000000]]
                        ],
                        [
                            'nombre' => 'Williams Racing',
                            'corto' => 'WIL',
                            'motor' => 96,
                            'chasis' => 84,
                            'aero' => 83,
                            'presupuesto' => 110000000,
                            'pilotos' => [['Carlos', 'Sainz', 'SAI', 55, 12000000], ['Alexander', 'Albon', 'ALB', 23, 6000000]]
                        ],
                        [
                            'nombre' => 'Alpine F1 Team',
                            'corto' => 'ALP',
                            'motor' => 95,
                            'chasis' => 82,
                            'aero' => 84,
                            'presupuesto' => 120000000,
                            'pilotos' => [['Pierre', 'Gasly', 'GAS', 10, 8000000], ['Jack', 'Doohan', 'DOO', 61, 1500000]]
                        ],
                        [
                            'nombre' => 'Visa Cash App RB F1 Team',
                            'corto' => 'VCARB',
                            'motor' => 93,
                            'chasis' => 85,
                            'aero' => 85,
                            'presupuesto' => 115000000,
                            'pilotos' => [['Yuki', 'Tsunoda', 'TSU', 22, 3500000], ['Liam', 'Lawson', 'LAW', 30, 2000000]]
                        ],
                        [
                            'nombre' => 'MoneyGram Haas F1 Team',
                            'corto' => 'HAA',
                            'motor' => 95,
                            'chasis' => 83,
                            'aero' => 82,
                            'presupuesto' => 105000000,
                            'pilotos' => [['Esteban', 'Ocon', 'OCO', 31, 6000000], ['Oliver', 'Bearman', 'BEA', 87, 1500000]]
                        ]
                    ],
                    'agentes_libres' => [
                        ['Valtteri', 'Bottas', 'BOT', 77, 82],
                        ['Kevin', 'Magnussen', 'MAG', 20, 79],
                        ['Zhou', 'Guanyu', 'ZHO', 24, 75],
                        ['Logan', 'Sargeant', 'SAR', 2, 70]
                    ]
                ],
                'F2' => [
                    'escuderias' => [
                        [
                            'nombre' => 'Prema Racing',
                            'corto' => 'PRE',
                            'motor' => 70,
                            'chasis' => 75,
                            'aero' => 74,
                            'presupuesto' => 4000000,
                            'pilotos' => [['Arvid', 'Lindblad', 'LIN', 3, 300000], ['Gabriele', 'Minì', 'MIN', 4, 300000]]
                        ],
                        [
                            'nombre' => 'ART Grand Prix',
                            'corto' => 'ART',
                            'motor' => 70,
                            'chasis' => 74,
                            'aero' => 73,
                            'presupuesto' => 3800000,
                            'pilotos' => [['James', 'Wharton', 'WHA', 5, 250000], ['Tuukka', 'Taponen', 'TAP', 6, 250000]]
                        ],
                        [
                            'nombre' => 'Campos Racing',
                            'corto' => 'CAM',
                            'motor' => 70,
                            'chasis' => 76,
                            'aero' => 75,
                            'presupuesto' => 3900000,
                            'pilotos' => [['Nikola', 'Tsolov', 'TSO', 9, 200000], ['Mari', 'Boya', 'BOY', 10, 200000]]
                        ],
                        [
                            'nombre' => 'Rodin Motorsport',
                            'corto' => 'ROD',
                            'motor' => 70,
                            'chasis' => 72,
                            'aero' => 72,
                            'presupuesto' => 3500000,
                            'pilotos' => [['Louis', 'Sharp', 'SHA', 14, 180000], ['Rodin', 'Rider', 'ROD', 15, 150000]]
                        ]
                    ],
                    'agentes_libres' => [
                        ['Zane', 'Maloney', 'MAL', 29, 73],
                        ['Jak', 'Crawford', 'CRA', 7, 72],
                        ['Isack', 'Hadjar', 'HAD', 16, 74],
                        ['Victor', 'Martins', 'MAR', 12, 72]
                    ]
                ],
                'F3' => [
                    'escuderias' => [
                        [
                            'nombre' => 'Trident',
                            'corto' => 'TRI',
                            'motor' => 50,
                            'chasis' => 55,
                            'aero' => 53,
                            'presupuesto' => 1500000,
                            'pilotos' => [['Noah', 'Strømsted', 'STR', 25, 90000], ['Rafael', 'Câmara', 'CAM', 26, 95000]]
                        ],
                        [
                            'nombre' => 'Hitech Pulse-Eight',
                            'corto' => 'HIT',
                            'motor' => 50,
                            'chasis' => 53,
                            'aero' => 54,
                            'presupuesto' => 1400000,
                            'pilotos' => [['Gerrit', 'Gerhard', 'GER', 28, 70000], ['Luke', 'Browning', 'BRO', 29, 85000]]
                        ]
                    ],
                    'agentes_libres' => [
                        ['Leonardo', 'Fornaroli', 'FOR', 31, 65],
                        ['Sami', 'Meguetounif', 'MEG', 32, 62],
                        ['Oliver', 'Goethe', 'GOE', 33, 64],
                        ['Tim', 'Tramnitz', 'TRA', 34, 61]
                    ]
                ],
                'Karting' => [
                    'escuderias' => [
                        [
                            'nombre' => 'CRG Racing Team',
                            'corto' => 'CRG',
                            'motor' => 25,
                            'chasis' => 30,
                            'aero' => 20,
                            'presupuesto' => 300000,
                            'pilotos' => [['Giovanni', 'Rossi', 'ROS', 41, 15000], ['Alessandro', 'Mancini', 'MAN', 42, 12000]]
                        ],
                        [
                            'nombre' => 'Tony Kart Racing Team',
                            'corto' => 'TON',
                            'motor' => 26,
                            'chasis' => 29,
                            'aero' => 22,
                            'presupuesto' => 320000,
                            'pilotos' => [['Lukas', 'Becker', 'BEC', 43, 16000], ['Marc', 'Dupasier', 'DUP', 44, 14000]]
                        ]
                    ],
                    'agentes_libres' => [
                        ['Piloto', 'Pruebas A', 'PPA', 88, 30],
                        ['Piloto', 'Pruebas B', 'PPB', 89, 30]
                    ]
                ]
            ];

            $paisBase = $em->getRepository(Pais::class)->findOneBy([]) ?? null;

            // =========================================================================
            // 2. GENERACIÓN PROCEDIMENTAL DE ESCUDERÍAS, PILOTOS IA Y CONTRATOS
            // =========================================================================
            foreach ($dataEcosistema as $categoria => $grupos) {

                // 2a. Procesar Escuderías y sus contratos de pilotos oficiales de la IA
                if (isset($grupos['escuderias'])) {
                    foreach ($grupos['escuderias'] as $item) {
                        // 1. Instanciar y persistir la Escudería
                        $escuderia = new \App\Entity\Escuderia();
                        $escuderia->setPartida($partida);
                        $escuderia->setNombreOficial($item['nombre']);
                        $escuderia->setNombreCorto($item['corto']);
                        $escuderia->setCategoria($categoria);
                        $escuderia->setRendimientoMotor($item['motor']);
                        $escuderia->setRendimientoChasis($item['chasis']);
                        $escuderia->setRendimientoAerodinamica($item['aero']);
                        $escuderia->setPresupuestoDisponible($item['presupuesto']);
                        if ($paisBase) {
                            $escuderia->setPais($paisBase);
                        }

                        $em->persist($escuderia);

                        // 2. Procesar los pilotos de esta escudería y generar sus Contratos de Mercado
                        foreach ($item['pilotos'] as $index => $pData) {
                            // Instanciar el Piloto IA
                            $pilotoIA = new \App\Entity\PilotoIa();
                            $pilotoIA->setPartida($partida);
                            $pilotoIA->setNombre($pData[0]);
                            $pilotoIA->setApellido($pData[1]);
                            $pilotoIA->setEdad(rand(19, 36)); // Evita el error "edad cannot be null"
                            $pilotoIA->setAbreviatura($pData[2]);
                            $pilotoIA->setNumeroDorsal($pData[3]);
                            $pilotoIA->setCategoria($categoria);
                            $pilotoIA->setPais($paisBase);

                            // Cálculo balanceado de stats según categoría
                            $potencia = ($categoria === 'F1') ? 83 : (($categoria === 'F2') ? 66 : 48);
                            $pilotoIA->setStatClasificacion(rand($potencia, $potencia + 12));
                            $pilotoIA->setStatRitmo(rand($potencia, $potencia + 13));
                            $pilotoIA->setStatAdelantamiento(rand($potencia, $potencia + 11));
                            $pilotoIA->setStatDefensa(rand($potencia, $potencia + 11));
                            $pilotoIA->setStatGestionNeumaticos(rand($potencia, $potencia + 12));
                            $pilotoIA->setStatMojado(rand($potencia, $potencia + 14));
                            $pilotoIA->setStatPotencial(rand($potencia + 5, 99));

                            $em->persist($pilotoIA);

                            // 🟢 LÓGICA DE CONTRATO ASOCIADA A LA ENTIDAD ContratoMercado
                            $contrato = new \App\Entity\ContratoMercado();
                            $contrato->setPartida($partida);
                            $contrato->setEscuderia($escuderia);
                            $contrato->setPilotoIa($pilotoIA);
                            $contrato->setEsJugadorHumano(false); // Es un piloto controlado por la IA
                            $contrato->setNumeroAsiento($index + 1); // El primer piloto del array será Asiento 1, el segundo Asiento 2
                            $contrato->setSueldoAnual($pData[4]); // Salario extraído directamente del mapa de datos
                            $contrato->setDuracionContratoAnos(rand(1, 3)); // Duración aleatoria de contrato simulado

                            $em->persist($contrato);
                        }
                    }
                }

                // 2b. Procesar Agentes Libres de la IA (Pilotos que se quedan en el paro, sin contrato)
                if (isset($grupos['agentes_libres'])) {
                    foreach ($grupos['agentes_libres'] as $alData) {
                        $pilotoLibre = new \App\Entity\PilotoIa();
                        $pilotoLibre->setPartida($partida);
                        $pilotoLibre->setNombre($alData[0]);
                        $pilotoLibre->setApellido($alData[1]);
                        $pilotoLibre->setEdad(rand(20, 38)); // Seteo de edad obligatorio
                        $pilotoLibre->setAbreviatura($alData[2]);
                        $pilotoLibre->setNumeroDorsal($alData[3]);
                        $pilotoLibre->setCategoria($categoria);
                        $pilotoLibre->setPais($paisBase);

                        $potenciaLibre = $alData[4];
                        $pilotoLibre->setStatClasificacion(rand($potenciaLibre - 3, $potenciaLibre + 4));
                        $pilotoLibre->setStatRitmo(rand($potenciaLibre - 2, $potenciaLibre + 5));
                        $pilotoLibre->setStatAdelantamiento(rand($potenciaLibre - 2, $potenciaLibre + 4));
                        $pilotoLibre->setStatDefensa(rand($potenciaLibre - 3, $potenciaLibre + 3));
                        $pilotoLibre->setStatGestionNeumaticos(rand($potenciaLibre - 1, $potenciaLibre + 5));
                        $pilotoLibre->setStatMojado(rand($potenciaLibre - 5, $potenciaLibre + 5));
                        $pilotoLibre->setStatPotencial(rand($potenciaLibre, 90));

                        $em->persist($pilotoLibre);

                        // 💡 Nota: Los agentes libres NO generan una instancia de ContratoMercado 
                        // ya que están desempleados al comenzar la partida.
                    }
                }
            }

            // =========================================================================
            // 3. GENERACIÓN DE CALENDARIO (SIN CREACIÓN DE CIRCUITOS)
            // =========================================================================
            $ordenCircuitos = [
                'F1' => ['Bahrein', 'Yeda', 'Melbourne', 'Suzuka', 'Shanghái', 'Miami', 'Imola', 'Mónaco', 'Montreal', 'Barcelona', 'Spielberg', 'Silverstone', 'Budapest', 'Spa', 'Zandvoort', 'Monza', 'Bakú', 'Marina Bay', 'Austin', 'Ciudad de México', 'Interlagos', 'Las Vegas', 'Lusail', 'Yas Marina'],
                'F2' => ['Bahrein', 'Yeda', 'Melbourne', 'Imola', 'Mónaco', 'Barcelona', 'Spielberg', 'Silverstone', 'Budapest', 'Spa', 'Monza', 'Bakú', 'Lusail', 'Yas Marina'],
                'F3' => ['Bahrein', 'Melbourne', 'Imola', 'Mónaco', 'Barcelona', 'Spielberg', 'Silverstone', 'Budapest', 'Spa', 'Monza'],
                'Karting' => ['Circuito Internacional de Zuera', 'Kartódromo de Campillos', 'Circuito de Alcañiz', 'Kartódromo de Valencia']
            ];

            foreach ($ordenCircuitos as $catKey => $nombresCircuitos) {
                foreach ($nombresCircuitos as $index => $nombreBusqueda) {

                    // Comprobación estricta en el catálogo general
                    $circuitoMaestro = $em->getRepository(CircuitosBase::class)->createQueryBuilder('c')
                        ->where('c.nombreCircuito LIKE :nombre')
                        ->setParameter('nombre', '%' . $nombreBusqueda . '%')
                        ->setMaxResults(1)
                        ->getQuery()
                        ->getOneOrNullResult();

                    if (!$circuitoMaestro) {
                        $circuitoMaestro = new \App\Entity\CircuitosBase();
                        $circuitoMaestro->setNombreCircuito($nombreBusqueda . ' International Circuit');
                        $circuitoMaestro->setCiudad($nombreBusqueda);
                        $circuitoMaestro->setLongitudKm((string)number_format(rand(4100, 5900) / 1000, 3, '.', '')); // Formato decimal string compatible
                        $circuitoMaestro->setCurvas(rand(12, 21));
                        $circuitoMaestro->setTipoCircuito('PERMANENTE');
                        $circuitoMaestro->setDificultadDesgaste(['BAJO', 'MEDIO', 'ALTO'][rand(0, 2)]);
                        if ($paisBase) {
                            $circuitoMaestro->setPais($paisBase);
                        }
                        $em->persist($circuitoMaestro);
                        $em->flush(); // Sincronización intermedia para dotar de ID al circuito maestro
                    }

                    // Si el circuito existe globalmente en el sistema, lo asignamos a la agenda de la partida
                    if ($circuitoMaestro) {
                        $calendarioCita = new CalendarioTemporada();
                        $calendarioCita->setPartida($partida);
                        $calendarioCita->setCircuito($circuitoMaestro);
                        $calendarioCita->setAnoSimulacion($partida->getIngameAno());
                        $calendarioCita->setCategoria(strtoupper($catKey));
                        $calendarioCita->setOrdenCarrera($index + 1);
                        $calendarioCita->setClimaPrevisto(['SOLEADO', 'NUBLADO', 'LLUVIA LIGERA', 'TORMENTA'][rand(0, 3)]);
                        $calendarioCita->setEstadoEvento('PENDIENTE');
                        $calendarioCita->setSemanaCarrera($index + 1);

                        $em->persist($calendarioCita);
                    }
                }
            }

            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'UNIVERSO FIA 2026 GENERADO CORRECTAMENTE.'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse([
                'success' => false,
                'message' => 'CRASH EN GENERADOR DE BOXES: ' . $e->getMessage()
            ], 500);
        }
    }

    #[Route('/career/offers/{partidaId}', name: 'app_career_offers', methods: ['GET'])]
    public function showOffers(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida) {
            return $this->redirectToRoute('app_dashboard');
        }

        // 1. Obtener todas las escuderías de la categoría KARTING creadas exclusivamente para esta partida
        $escuderiasKarting = $em->getRepository(Escuderia::class)->findBy([
            'partida' => $partida,
            'categoria' => 'Karting'
        ]);

        if (count($escuderiasKarting) === 0) {
            throw new \Exception("No se encontraron escuderías de Karting generadas para esta partida.");
        }

        // 2. Mezclar el array y extraer un máximo de 3 escuderías aleatorias
        shuffle($escuderiasKarting);
        $escuderiasOfertas = array_slice($escuderiasKarting, 0, 3);

        // 3. Empaquetar los datos generando el sueldo aleatorio por carrera
        $ofertas = [];
        foreach ($escuderiasOfertas as $escuderia) {
            $ofertas[] = [
                'id' => $escuderia->getId(),
                'nombre' => $escuderia->getNombreOficial(),
                'corto' => $escuderia->getNombreCorto(),
                'motor' => $escuderia->getRendimientoMotor(),
                'chasis' => $escuderia->getRendimientoChasis(),
                'aero' => $escuderia->getRendimientoAerodinamica(),
                'duracion' => '1 Temporada', // Requisito estricto
                'sueldo_carrera' => rand(850, 1500) // Sueldo simulado por GP en la base de Karting
            ];
        }

        return $this->render('career/offers.html.twig', [
            'partidaId' => $partidaId,
            'ofertas' => $ofertas
        ]);
    }

    #[Route('/api/career/sign-contract', name: 'api_career_sign_contract', methods: ['POST'])]
    public function signContract(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = (int)$request->request->get('partidaId');
        $escuderiaId = (int)$request->request->get('escuderiaId');
        $sueldoCarrera = (int)$request->request->get('sueldoCarrera');

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        $escuderia = $em->getRepository(Escuderia::class)->find($escuderiaId);

        if (!$partida || !$escuderia) {
            return new JsonResponse(['success' => false, 'message' => 'DATOS DE CONTRATO INVÁLIDOS'], 400);
        }

        try {
            // Verificar si el jugador ya tiene un contrato activo en esta partida para evitar duplicados
            $contratoExistente = $em->getRepository(ContratoMercado::class)->findOneBy([
                'partida' => $partida,
                'esJugadorHumano' => true
            ]);

            if ($contratoExistente) {
                return new JsonResponse(['success' => false, 'message' => 'Ya has firmado un contrato para esta partida.'], 400);
            }

            // Buscamos qué asientos de la escudería están libres. (Asiento 1 o Asiento 2)
            // Por diseño de la IA en el paso anterior, los asientos 1 y 2 están ocupados por bots.
            // Como el jugador ingresará a este equipo, vamos a rescindir o añadir un Asiento de Desarrollo (Ej: Asiento 3)
            // o si prefieres sobreescribir un asiento libre. Usaremos el Asiento 1 para el jugador en el mercado.
            // Para evitar romper los UniqueConstraint ('partida_id', 'escuderia_id', 'numero_asiento'), le asignaremos el número de asiento 3.

            $contratoHumano = new ContratoMercado();
            $contratoHumano->setPartida($partida);
            $contratoHumano->setEscuderia($escuderia);
            $contratoHumano->setPilotoIa(null); // Null porque es el jugador real, no un bot IA
            $contratoHumano->setEsJugadorHumano(true); // Flag fundamental de tu entidad
            $contratoHumano->setNumeroAsiento(3); // Asiento prioritario de jugador

            // Multiplicamos el sueldo por carrera por el número de carreras de Karting (4) para guardarlo en sueldoAnual
            $contratoHumano->setSueldoAnual($sueldoCarrera * 4);
            $contratoHumano->setDuracionContratoAnos(1); // 1 temporada

            $em->persist($contratoHumano);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => '¡Felicidades! Has firmado con ' . $escuderia->getNombreOficial()
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error al procesar la firma: ' . $e->getMessage()], 500);
        }
    }

    #[Route('/career/choose-number/{partidaId}', name: 'app_career_choose_number', methods: ['GET'])]
    public function chooseNumber(int $partidaId, EntityManagerInterface $em): Response
    {
        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida) {
            return $this->redirectToRoute('app_dashboard');
        }

        // 1. Obtener los dorsales ya ocupados por los pilotos de la IA en la categoría KARTING para esta partida
        $pilotosIaKarting = $em->getRepository(PilotoIa::class)->findBy([
            'partida' => $partida,
            'categoria' => 'Karting'
        ]);

        $numerosOcupados = [];
        foreach ($pilotosIaKarting as $pilotoIa) {
            $numerosOcupados[] = $pilotoIa->getNumeroDorsal();
        }

        // Ordenamos los números ocupados de menor a mayor
        sort($numerosOcupados);

        return $this->render('career/choose_number.html.twig', [
            'partidaId' => $partidaId,
            'numerosOcupados' => $numerosOcupados
        ]);
    }

    #[Route('/api/career/save-number', name: 'api_career_save_number', methods: ['POST'])]
    public function saveNumber(Request $request, EntityManagerInterface $em): JsonResponse
    {
        $partidaId = (int)$request->request->get('partidaId');
        $numeroElegido = (int)$request->request->get('numero');

        if ($numeroElegido < 1 || $numeroElegido > 99) {
            return new JsonResponse(['success' => false, 'message' => 'El número debe estar entre 1 y 99.'], 400);
        }

        $partida = $em->getRepository(PartidaGuardada::class)->find($partidaId);
        if (!$partida) {
            return new JsonResponse(['success' => false, 'message' => 'Partida no encontrada.'], 404);
        }

        // 2. Validar que el número no esté ocupado por la IA en Karting de esta partida
        $dorsalOcupado = $em->getRepository(PilotoIa::class)->findOneBy([
            'partida' => $partida,
            'categoria' => 'Karting',
            'numeroDorsal' => $numeroElegido
        ]);

        if ($dorsalOcupado) {
            return new JsonResponse(['success' => false, 'message' => 'Este dorsal ya está ocupado por otro piloto de la parrilla.'], 400);
        }

        try {
            // 3. Buscar el PilotoUsuario (jugador real) de esta partida y actualizar su dorsal
            $pilotoUsuario = $em->getRepository(PilotoUsuario::class)->findOneBy(['partida' => $partida]);
            if (!$pilotoUsuario) {
                return new JsonResponse(['success' => false, 'message' => 'No se encontró el piloto del jugador.'], 404);
            }

            $pilotoUsuario->setNumeroDorsal($numeroElegido);
            $em->flush();

            return new JsonResponse([
                'success' => true,
                'message' => 'Dorsal oficial asignado correctamente. ¡Bienvenido al mundial!'
            ]);
        } catch (\Exception $e) {
            return new JsonResponse(['success' => false, 'message' => 'Error al guardar el dorsal: ' . $e->getMessage()], 500);
        }
    }
}
