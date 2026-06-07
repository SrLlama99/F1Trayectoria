<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'trofeos_partidas')]
class TrofeoPartida
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\ManyToOne(targetEntity: Trofeo::class)]
    #[ORM\JoinColumn(name: 'trofeo_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Trofeo $trofeo = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $conseguidoEn;

    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $detallesContexto = null; // Ej: "Temporada 1 - Circuito de Spa-Francorchamps"

    public function __construct()
    {
        $this->conseguidoEn = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $partida): self { $this->partida = $partida; return $this; }

    public function getTrofeo(): ?Trofeo { return $this->trofeo; }
    public function setTrofeo(?Trofeo $trofeo): self { $this->trofeo = $trofeo; return $this; }

    public function getConseguidoEn(): DateTimeImmutable { return $this->conseguidoEn; }
    public function setConseguidoEn(DateTimeImmutable $fecha): self { $this->conseguidoEn = $fecha; return $this; }

    public function getDetallesContexto(): ?string { return $this->detallesContexto; }
    public function setDetallesContexto(?string $detalles): self { $this->detallesContexto = $detalles; return $this; }
}