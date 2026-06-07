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

    #[ORM\Column(type: 'integer')]
    private ?int $posicionSalida = null;

    #[ORM\Column(type: 'string', length: 15, nullable: true)]
    private ?string $tiempoClasificacion = null;

    #[ORM\Column(type: 'integer', nullable: true)]
    private ?int $posicionFinal = null;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $puntosObtenidos = 0;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $vueltaRapida = false;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'TERMINO'])]
    private string $estadoCarrera = 'TERMINO'; // 'TERMINO', 'ACCIDENTE', 'AVERIA_MECANICA'

    public function getId(): ?int { return $this->id; }

    public function getEvento(): ?CalendarioTemporada { return $this->evento; }
    public function setEvento(?CalendarioTemporada $evento): self { $this->evento = $evento; return $this; }

    public function isEsJugadorHumano(): bool { return $this->esJugadorHumano; }
    public function setEsJugadorHumano(bool $esHumano): self { $this->esJugadorHumano = $esHumano; return $this; }

    public function getPilotoIa(): ?PilotoIa { return $this->pilotoIa; }
    public function setPilotoIa(?PilotoIa $pilotoIa): self { $this->pilotoIa = $pilotoIa; return $this; }

    public function getEscuderia(): ?Escuderia { return $this->escuderia; }
    public function setEscuderia(?Escuderia $escuderia): self { $this->escuderia = $escuderia; return $this; }

    public function getPosicionSalida(): ?int { return $this->posicionSalida; }
    public function setPosicionSalida(int $posicion): self { $this->posicionSalida = $posicion; return $this; }

    public function getTiempoClasificacion(): ?string { return $this->tiempoClasificacion; }
    public function setTiempoClasificacion(?string $tiempo): self { $this->tiempoClasificacion = $tiempo; return $this; }

    public function getPosicionFinal(): ?int { return $this->posicionFinal; }
    public function setPosicionFinal(?int $posicion): self { $this->posicionFinal = $posicion; return $this; }

    public function getPuntosObtenidos(): int { return $this->puntosObtenidos; }
    public function setPuntosObtenidos(int $puntos): self { $this->puntosObtenidos = $puntos; return $this; }

    public function isVueltaRapida(): bool { return $this->vueltaRapida; }
    public function setVueltaRapida(bool $vueltaRapida): self { $this->vueltaRapida = $vueltaRapida; return $this; }

    public function getEstadoCarrera(): string { return $this->estadoCarrera; }
    public function setEstadoCarrera(string $estado): self { $this->estadoCarrera = $estado; return $this; }
}