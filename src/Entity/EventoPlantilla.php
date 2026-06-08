<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'evento_plantillas')]
class EventoPlantilla
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\Column(type: 'string', length: 150)]
    private ?string $titulo = null;

    #[ORM\Column(type: 'text')]
    private ?string $descripcion = null;

    /**
     * Determina si el evento requiere que el jugador posea un bien de una categoría concreta para activarse.
     * Valores sugeridos: 'OBJETOS', 'VEHICULOS', 'PROPIEDADES' o null (si es un evento global).
     */
    #[ORM\Column(type: 'string', length: 30, nullable: true)]
    private ?string $categoriaRequerida = null;

    /**
     * Peso numérico para el motor de aleatoriedad del avance de semana (ej: 10 común, 1 muy raro).
     */
    #[ORM\Column(type: 'integer', options: ['default' => 5])]
    private int $probabilidad = 5;

    /**
     * Un evento plantilla ofrece múltiples opciones de decisión al piloto.
     */
    #[ORM\OneToMany(mappedBy: 'eventoPlantilla', targetEntity: EventoOpcion::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $opciones;

    public function __construct()
    {
        $this->opciones = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitulo(): ?string
    {
        return $this->titulo;
    }

    public function setTitulo(string $titulo): self
    {
        $this->titulo = $titulo;
        return $this;
    }

    public function getDescripcion(): ?string
    {
        return $this->descripcion;
    }

    public function setDescripcion(string $descripcion): self
    {
        $this->descripcion = $descripcion;
        return $this;
    }

    public function getCategoriaRequerida(): ?string
    {
        return $this->categoriaRequerida;
    }

    public function setCategoriaRequerida(?string $categoriaRequerida): self
    {
        $this->categoriaRequerida = $categoriaRequerida ? strtoupper($categoriaRequerida) : null;
        return $this;
    }

    public function getProbabilidad(): int
    {
        return $this->probabilidad;
    }

    public function setProbabilidad(int $probabilidad): self
    {
        $this->probabilidad = $probabilidad;
        return $this;
    }

    /**
     * @return Collection<int, EventoOpcion>
     */
    public function getOpciones(): Collection
    {
        return $this->opciones;
    }

    public function addOpcion(EventoOpcion $opcion): self
    {
        if (!$this->opciones->contains($opcion)) {
            $this->opciones->add($opcion);
            $opcion->setEventoPlantilla($this);
        }
        return $this;
    }

    public function removeOpcion(EventoOpcion $opcion): self
    {
        if ($this->opciones->removeElement($opcion)) {
            // Set the owning side to null (unless already changed)
            if ($opcion->getEventoPlantilla() === $this) {
                $opcion->setEventoPlantilla(null);
            }
        }
        return $this;
    }
}