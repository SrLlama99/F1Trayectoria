<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'trofeos')]
class Trofeo
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $descripcion = null;

    #[ORM\Column(type: 'string', length: 50)]
    private string $icono = '🏆';

    #[ORM\Column(type: 'string', length: 30)]
    private string $tipoMetal = 'ORO'; // ORO, PLATA, BRONCE, PLATINO

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $puntosPrestigio = 0; // Puntos de reputación que otorga al piloto

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $codigoIdentificador = null; // Ej: 'TROFEO_MONACO_1', 'WORLD_CHAMP_F1'

    public function getId(): ?int { return $this->id; }

    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): self { $this->nombre = $nombre; return $this; }

    public function getDescripcion(): ?string { return $this->descripcion; }
    public function setDescripcion(string $descripcion): self { $this->descripcion = $descripcion; return $this; }

    public function getIcono(): string { return $this->icono; }
    public function setIcono(string $icono): self { $this->icono = $icono; return $this; }

    public function getTipoMetal(): string { return $this->tipoMetal; }
    public function setTipoMetal(string $tipoMetal): self { $this->tipoMetal = strtoupper($tipoMetal); return $this; }

    public function getPuntosPrestigio(): int { return $this->puntosPrestigio; }
    public function setPuntosPrestigio(int $puntos): self { $this->puntosPrestigio = $puntos; return $this; }

    public function getCodigoIdentificador(): ?string { return $this->codigoIdentificador; }
    public function setCodigoIdentificador(string $codigo): self { $this->codigoIdentificador = $codigo; return $this; }
}