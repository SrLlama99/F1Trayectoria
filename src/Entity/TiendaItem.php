<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'tienda_items')]
class TiendaItem
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 100)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 255)]
    private ?string $descripcion = null;

    /**
     * Categorías válidas: OBJETOS, VEHICULOS, PROPIEDADES
     */
    #[ORM\Column(type: 'string', length: 30)]
    private ?string $categoria = null;

    #[ORM\Column(type: 'integer')]
    private int $precio = 0;

    /**
     * Cuántos puntos añade permanentemente al Estilo de Vida al comprarlo
     */
    #[ORM\Column(type: 'integer')]
    private int $bonoEstiloDeVida = 0;

    #[ORM\Column(type: 'string', length: 10, options: ['default' => '📦'])]
    private string $emoji = '📦';

    public function getId(): ?int { return $this->id; }
    public function getNombre(): ?string { return $this->nombre; }
    public function setNombre(string $nombre): self { $this->nombre = $nombre; return $this; }
    public function getDescripcion(): ?string { return $this->descripcion; }
    public function setDescripcion(string $desc): self { $this->descripcion = $desc; return $this; }
    public function getCategoria(): ?string { return $this->categoria; }
    public function setCategoria(string $cat): self { $this->categoria = strtoupper($cat); return $this; }
    public function getPrecio(): int { return $this->precio; }
    public function setPrecio(int $precio): self { $this->precio = $precio; return $this; }
    public function getBonoEstiloDeVida(): int { return $this->bonoEstiloDeVida; }
    public function setBonoEstiloDeVida(int $bono): self { $this->bonoEstiloDeVida = $bono; return $this; }
    public function getEmoji(): string { return $this->emoji; }
    public function setEmoji(string $emoji): self { $this->emoji = $emoji; return $this; }
}