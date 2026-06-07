<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tienda_compras')]
#[ORM\UniqueConstraint(columns: ['partida_id', 'piloto_usuario_id', 'item_id'])]
class TiendaCompra
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\ManyToOne(targetEntity: PilotoUsuario::class)]
    #[ORM\JoinColumn(name: 'piloto_usuario_id', nullable: false, onDelete: 'CASCADE')]
    private ?PilotoUsuario $pilotoUsuario = null;

    #[ORM\ManyToOne(targetEntity: TiendaItem::class)]
    #[ORM\JoinColumn(name: 'item_id', nullable: false)]
    private ?TiendaItem $item = null;

    #[ORM\Column(type: 'datetime')]
    private ?\DateTimeInterface $fechaCompra = null;

    public function __construct() { $this->fechaCompra = new \DateTime(); }
    public function getId(): ?int { return $this->id; }
    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $p): self { $this->partida = $p; return $this; }
    public function getPilotoUsuario(): ?PilotoUsuario { return $this->pilotoUsuario; }
    public function setPilotoUsuario(?PilotoUsuario $u): self { $this->pilotoUsuario = $u; return $this; }
    public function getItem(): ?TiendaItem { return $this->item; }
    public function setItem(?TiendaItem $item): self { $this->item = $item; return $this; }
    public function getFechaCompra(): ?\DateTimeInterface { return $this->fechaCompra; }
}