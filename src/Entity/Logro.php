<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

#[ORM\Entity]
#[ORM\Table(name: 'logros')]
class Logro
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(type: 'string', length: 50, options: ['default' => '🏆'])]
    private string $icono = '🏆'; // Guardará un emoji o un string de FontAwesome

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $recompensaExp = 0; // Experiencia extra que otorgará al piloto al ganarlo

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $codigoIdentificador = null; // Ej: 'PRIMERA_VICTORIA', 'CAMPEON_F3' para buscarlo fácil por código

    public function getId(): ?int { return $this->id; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): self { $this->nombre = $nombre; return $this; }

    public function getDescripcion(): ?string { return $this->descripcion; }
    public function setDescripcion(string $descripcion): self { $this->descripcion = $descripcion; return $this; }

    public function getIcono(): string { return $this->icono; }
    public function setIcono(string $icono): self { $this->icono = $icono; return $this; }

    public function getRecompensaExp(): int { return $this->recompensaExp; }
    public function setRecompensaExp(int $exp): self { $this->recompensaExp = $exp; return $this; }

    public function getCodigoIdentificador(): ?string { return $this->codigoIdentificador; }
    public function setCodigoIdentificador(string $codigo): self { $this->codigoIdentificador = $codigo; return $this; }
}