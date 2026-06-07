<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use DateTimeImmutable;

#[ORM\Entity]
#[ORM\Table(name: 'logros_partidas')]
// Evitamos que una misma partida pueda desbloquear duplicado el mismo logro
#[ORM\UniqueConstraint(columns: ['partida_id', 'logro_id'])]
class LogroPartida
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\ManyToOne(targetEntity: Logro::class)]
    #[ORM\JoinColumn(name: 'logro_id', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Logro $logro = null;

    #[ORM\Column(type: 'datetime_immutable')]
    private DateTimeImmutable $desbloqueadoEn;

    public function __construct()
    {
        $this->desbloqueadoEn = new DateTimeImmutable();
    }

    public function getId(): ?int { return $this->id; }

    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $partida): self { $this->partida = $partida; return $this; }

    public function getLogro(): ?Logro { return $this->logro; }
    public function setLogro(?Logro $logro): self { $this->logro = $logro; return $this; }

    public function getDesbloqueadoEn(): DateTimeImmutable { return $this->desbloqueadoEn; }
    public function setDesbloqueadoEn(DateTimeImmutable $fecha): self { $this->desbloqueadoEn = $fecha; return $this; }
}