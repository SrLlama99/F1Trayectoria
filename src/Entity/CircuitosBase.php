<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'circuitos_base')]
class CircuitosBase
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombreCircuito = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)]
    private ?string $ciudad = null;

    #[ORM\ManyToOne(targetEntity: Pais::class)]
    #[ORM\JoinColumn(name: 'pais_id', referencedColumnName: 'id_pais', nullable: true)]
    private ?Pais $pais = null;

    #[ORM\Column(type: 'decimal', precision: 4, scale: 3)]
    private ?string $longitudKm = null; // En Doctrine los decimales mapean a strings para evitar pérdida de precisión numérica

    #[ORM\Column(type: 'integer')]
    private ?int $curvas = null;

    #[ORM\Column(type: 'string', length: 20)]
    private ?string $tipoCircuito = null; // 'URBANO' o 'PERMANENTE'

    #[ORM\Column(type: 'string', length: 10)]
    private ?string $dificultadDesgaste = null; // 'ALTO', 'MEDIO', 'BAJO'

    public function getId(): ?int { return $this->id; }

    public function getNombreCircuito(): ?string { return $this->nombreCircuito; }
    public function setNombreCircuito(string $nombre): self { $this->nombreCircuito = $nombre; return $this; }

    public function getCiudad(): ?string { return $this->ciudad; }
    public function setCiudad(?string $ciudad): self { $this->ciudad = $ciudad; return $this; }

    public function getPais(): ?Pais { return $this->pais; }
    public function setPais(?Pais $pais): self { $this->pais = $pais; return $this; }

    public function getLongitudKm(): ?string { return $this->longitudKm; }
    public function setLongitudKm(string $longitudKm): self { $this->longitudKm = $longitudKm; return $this; }

    public function getCurvas(): ?int { return $this->curvas; }
    public function setCurvas(int $curvas): self { $this->curvas = $curvas; return $this; }

    public function getTipoCircuito(): ?string { return $this->tipoCircuito; }
    public function setTipoCircuito(string $tipo): self { $this->tipoCircuito = $tipo; return $this; }

    public function getDificultadDesgaste(): ?string { return $this->dificultadDesgaste; }
    public function setDificultadDesgaste(string $dificultad): self { $this->dificultadDesgaste = $dificultad; return $this; }
}