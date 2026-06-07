<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'resultados_carreras')]
class ResultadosCarreras
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: CalendarioTemporada::class)]
    #[ORM\JoinColumn(name: 'evento_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?CalendarioTemporada $evento = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $esJugadorHumano = false;

    #[ORM\ManyToOne(targetEntity: PilotoIa::class)]
    #[ORM\JoinColumn(name: 'piloto_ia_id', referencedColumnName: 'id', nullable: true)]
    private ?PilotoIa $pilotoIa = null;

    #[ORM\ManyToOne(targetEntity: Escuderia::class)]
    #[ORM\JoinColumn(name: 'escuderia_id', referencedColumnName: 'id', nullable: false)]
    private ?Escuderia $escuderia = null;

    // ==========================================
    // 📊 NUEVOS CAMPOS DEL REWORK DE SESIONES
    // ==========================================

    #[ORM\Column(type: 'integer')]
    private ?int $numeroSesion = null; // Siempre del 1 al 5

    #[ORM\Column(type: 'string', length: 30)]
    private ?string $tipoSesion = null; // 'LIBRES', 'CLASIFICACION', 'CLASIFICACION_SPRINT', 'CARRERA_SPRINT', 'CARRERA'

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $posicionSalida = null; // Útil para parrilas de salida en Carreras y Sprints

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $posicionFinal = null; // Resultado final en esa sesión concreta (P1, P2...)

    #[ORM\Column(type: 'string', length: 20, nullable: true)]
    private ?string $mejorTiempoVuelta = null; // Ej: "1:11.543" o "DNF"

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $puntosObtenidos = 0; // Puntos repartidos en esa sesión (0 en libres, parciales en Sprint, completos en Carrera)

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $vueltaRapida = false; // Flag para bonificaciones de vuelta rápida en carreras

    // ==========================================
    // GETTERS Y SETTERS
    // ==========================================

    public function getId(): ?int { return $this->id; }

    public function getEvento(): ?CalendarioTemporada { return $this->evento; }
    public function setEvento(?CalendarioTemporada $evento): self { $this->evento = $evento; return $this; }

    public function isEsJugadorHumano(): bool { return $this->esJugadorHumano; }
    public function setEsJugadorHumano(bool $esJugadorHumano): self { $this->esJugadorHumano = $esJugadorHumano; return $this; }

    public function getPilotoIa(): ?PilotoIa { return $this->pilotoIa; }
    public function setPilotoIa(?PilotoIa $pilotoIa): self { $this->pilotoIa = $pilotoIa; return $this; }

    public function getEscuderia(): ?Escuderia { return $this->escuderia; }
    public function setEscuderia(?Escuderia $escuderia): self { $this->escuderia = $escuderia; return $this; }

    public function getNumeroSesion(): ?int { return $this->numeroSesion; }
    public function setNumeroSesion(int $numero): self { $this->numeroSesion = $numero; return $this; }

    public function getTipoSesion(): ?string { return $this->tipoSesion; }
    public function setTipoSesion(string $tipo): self { $this->tipoSesion = strtoupper($tipo); return $this; }

    public function getPosicionSalida(): ?int { return $this->posicionSalida; }
    public function setPosicionSalida(?int $posicion): self { $this->posicionSalida = $posicion; return $this; }

    public function getPosicionFinal(): ?int { return $this->posicionFinal; }
    public function setPosicionFinal(?int $posicion): self { $this->posicionFinal = $posicion; return $this; }

    public function getMejorTiempoVuelta(): ?string { return $this->mejorTiempoVuelta; }
    public function setMejorTiempoVuelta(?string $tiempo): self { $this->mejorTiempoVuelta = $tiempo; return $this; }

    public function getPuntosObtenidos(): int { return $this->puntosObtenidos; }
    public function setPuntosObtenidos(int $puntos): self { $this->puntosObtenidos = $puntos; return $this; }

    public function isVueltaRapida(): bool { return $this->vueltaRapida; }
    public function setVueltaRapida(bool $vueltaRapida): self { $this->vueltaRapida = $vueltaRapida; return $this; }
}