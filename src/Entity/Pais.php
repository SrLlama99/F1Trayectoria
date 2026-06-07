<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'paises')]
class Pais
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_pais', type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 50, unique: true)]
    private ?string $nombrePais = null;

    #[ORM\Column(type: 'string', length: 3, unique: true)]
    private ?string $codigoIso = null;

    #[ORM\Column(type: 'string', length: 255, nullable: true)]
    private ?string $banderaUrl = null;

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $probabilidadTalento = 1;

    // Getters y Setters...
    public function getId(): ?int { return $this->id; }
    public function getNombrePais(): ?string { return $this->nombrePais; }
    public function setNombrePais(string $nombrePais): self { $this->nombrePais = $nombrePais; return $this; }
    public function getCodigoIso(): ?string { return $this->codigoIso; }
    public function setCodigoIso(string $codigoIso): self { $this->codigoIso = $codigoIso; return $this; }
    public function getBanderaUrl(): ?string { return $this->banderaUrl; }
    public function setBanderaUrl(?string $banderaUrl): self { $this->banderaUrl = $banderaUrl; return $this; }
    public function getProbabilidadTalento(): int { return $this->probabilidadTalento; }
    public function setProbabilidadTalento(int $probabilidadTalento): self { $this->probabilidadTalento = $probabilidadTalento; return $this; }
}