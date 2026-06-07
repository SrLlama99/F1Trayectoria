<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'escuderias')]
class Escuderia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombreOficial = null;

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $nombreCorto = null;

    #[ORM\ManyToOne(targetEntity: Pais::class)]
    #[ORM\JoinColumn(name: 'pais_id', referencedColumnName: 'id_pais', nullable: true)]
    private ?Pais $pais = null;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $rendimientoMotor = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $rendimientoChasis = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $rendimientoAerodinamica = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 10000000])]
    private int $presupuestoDisponible = 10000000;

    #[ORM\Column(type: 'string', length: 30, options: ['default' => 'F1'])]
    private string $categoria = 'F1'; // Valores válidos: 'Karting', 'F3', 'F2', 'F1'

    public function getId(): ?int { return $this->id; }

    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $partida): self { $this->partida = $partida; return $this; }

    public function getNombreOficial(): ?string { return $this->nombreOficial; }
    public function setNombreOficial(string $nombre): self { $this->nombreOficial = $nombre; return $this; }

    public function getNombreCorto(): ?string { return $this->nombreCorto; }
    public function setNombreCorto(string $nombreCorto): self { $this->nombreCorto = $nombreCorto; return $this; }

    public function getPais(): ?Pais { return $this->pais; }
    public function setPais(?Pais $pais): self { $this->pais = $pais; return $this; }

    public function getRendimientoMotor(): int { return $this->rendimientoMotor; }
    public function setRendimientoMotor(int $valor): self { $this->rendimientoMotor = $valor; return $this; }

    public function getRendimientoChasis(): int { return $this->rendimientoChasis; }
    public function setRendimientoChasis(int $valor): self { $this->rendimientoChasis = $valor; return $this; }

    public function getRendimientoAerodinamica(): int { return $this->rendimientoAerodinamica; }
    public function setRendimientoAerodinamica(int $valor): self { $this->rendimientoAerodinamica = $valor; return $this; }

    public function getPresupuestoDisponible(): int { return $this->presupuestoDisponible; }
    public function setPresupuestoDisponible(int $monto): self { $this->presupuestoDisponible = $monto; return $this; }

    public function getCategoria(): string { return $this->categoria; }
    public function setCategoria(string $categoria): self { $this->categoria = $categoria; return $this; }
}