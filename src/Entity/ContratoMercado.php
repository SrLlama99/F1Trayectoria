<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'contratos_mercado')]
#[ORM\UniqueConstraint(columns: ['partida_id', 'escuderia_id', 'numero_asiento'])]
class ContratoMercado
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\ManyToOne(targetEntity: Escuderia::class)]
    #[ORM\JoinColumn(name: 'escuderia_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')] // 🟢 Añade onDelete: 'CASCADE' aquí
    private ?Escuderia $escuderia = null;

    #[ORM\Column(type: 'boolean', options: ['default' => false])]
    private bool $esJugadorHumano = false;

    #[ORM\ManyToOne(targetEntity: PilotoIa::class)]
    #[ORM\JoinColumn(nullable: true, onDelete: 'SET NULL')]
    private ?PilotoIa $pilotoIa = null;

    #[ORM\Column(type: 'integer')]
    private ?int $numeroAsiento = null; // Asiento 1 o 2

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $sueldoAnual = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $duracionContratoAnos = 1;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartida(): ?PartidaGuardada
    {
        return $this->partida;
    }
    public function setPartida(?PartidaGuardada $partida): self
    {
        $this->partida = $partida;
        return $this;
    }

    public function getEscuderia(): ?Escuderia
    {
        return $this->escuderia;
    }
    public function setEscuderia(?Escuderia $escuderia): self
    {
        $this->escuderia = $escuderia;
        return $this;
    }

    public function isEsJugadorHumano(): bool
    {
        return $this->esJugadorHumano;
    }
    public function setEsJugadorHumano(bool $esHumano): self
    {
        $this->esJugadorHumano = $esHumano;
        return $this;
    }

    public function getPilotoIa(): ?PilotoIa
    {
        return $this->pilotoIa;
    }
    public function setPilotoIa(?PilotoIa $pilotoIa): self
    {
        $this->pilotoIa = $pilotoIa;
        return $this;
    }

    public function getNumeroAsiento(): ?int
    {
        return $this->numeroAsiento;
    }
    public function setNumeroAsiento(int $numero): self
    {
        $this->numeroAsiento = $numero;
        return $this;
    }

    public function getSueldoAnual(): int
    {
        return $this->sueldoAnual;
    }
    public function setSueldoAnual(int $sueldo): self
    {
        $this->sueldoAnual = $sueldo;
        return $this;
    }

    public function getDuracionContratoAnos(): int
    {
        return $this->duracionContratoAnos;
    }
    public function setDuracionContratoAnos(int $anos): self
    {
        $this->duracionContratoAnos = $anos;
        return $this;
    }
}