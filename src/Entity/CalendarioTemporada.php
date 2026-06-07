<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'calendario_temporada')]
#[ORM\UniqueConstraint(columns: ['partida_id', 'ano_simulacion', 'categoria', 'orden_carrera'])]
class CalendarioTemporada
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\ManyToOne(targetEntity: CircuitosBase::class)]
    #[ORM\JoinColumn(nullable: false)]
    private ?CircuitosBase $circuito = null;

    #[ORM\Column(type: 'integer')]
    private ?int $anoSimulacion = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $categoria = null; // 'KARTING', 'F3', 'F2', 'F1'

    #[ORM\Column(type: 'integer')]
    private ?int $ordenCarrera = null; // Posición en el calendario (1, 2, 3...)

    // ==========================================
    // 🏁 ASIGNACIÓN DE SEMANA DE GRAN PREMIO
    // ==========================================
    #[ORM\Column(type: 'integer')]
    private ?int $semanaCarrera = null; // Ej: Semana 12 (GP de Bahréin), Semana 21 (GP de Mónaco)

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'SOLEADO'])]
    private string $climaPrevisto = 'SOLEADO';

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'PENDIENTE'])]
    private string $estadoEvento = 'PENDIENTE'; // 'PENDIENTE' o 'COMPLETADO'

    public function getId(): ?int { return $this->id; }

    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $partida): self { $this->partida = $partida; return $this; }

    public function getCircuito(): ?CircuitosBase { return $this->circuito; }
    public function setCircuito(?CircuitosBase $circuito): self { $this->circuito = $circuito; return $this; }

    public function getAnoSimulacion(): ?int { return $this->anoSimulacion; }
    public function setAnoSimulacion(int $ano): self { $this->anoSimulacion = $ano; return $this; }

    public function getCategoria(): ?string { return $this->categoria; }
    public function setCategoria(string $categoria): self { $this->categoria = $categoria; return $this; }

    public function getOrdenCarrera(): ?int { return $this->ordenCarrera; }
    public function setOrdenCarrera(int $orden): self { $this->ordenCarrera = $orden; return $this; }

    // Getter y Setter para la nueva gestión de semanas
    public function getSemanaCarrera(): ?int { return $this->semanaCarrera; }
    public function setSemanaCarrera(int $semana): self { $this->semanaCarrera = $semana; return $this; }

    public function getClimaPrevisto(): string { return $this->climaPrevisto; }
    public function setClimaPrevisto(string $clima): self { $this->climaPrevisto = $clima; return $this; }

    public function getEstadoEvento(): string { return $this->estadoEvento; }
    public function setEstadoEvento(string $estado): self { $this->estadoEvento = $estado; return $this; }
}