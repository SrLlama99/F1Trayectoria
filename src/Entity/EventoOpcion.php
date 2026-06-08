<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'evento_opciones')]
class EventoOpcion
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * La opción está vinculada obligatoriamente a una plantilla de evento dilema.
     */
    #[ORM\ManyToOne(targetEntity: EventoPlantilla::class, inversedBy: 'opciones')]
    #[ORM\JoinColumn(name: 'evento_plantilla_id', nullable: false, onDelete: 'CASCADE')]
    private ?EventoPlantilla $eventoPlantilla = null;

    /**
     * El texto descriptivo de la acción que se mostrará en el botón del frontend.
     * Ejemplo: "Contratar un bufete de abogados especializado (Gasto de 5.000 €)"
     */
    #[ORM\Column(type: 'string', length: 255)]
    private ?string $textoBoton = null;

    /**
     * Una opción de respuesta puede desencadenar múltiples impactos y consecuencias a la vez.
     */
    #[ORM\OneToMany(mappedBy: 'eventoOpcion', targetEntity: EventoConsecuencia::class, cascade: ['all'], orphanRemoval: true)]
    private Collection $consecuencias;

    public function __construct()
    {
        $this->consecuencias = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventoPlantilla(): ?EventoPlantilla
    {
        return $this->eventoPlantilla;
    }

    public function setEventoPlantilla(?EventoPlantilla $eventoPlantilla): self
    {
        $this->eventoPlantilla = $eventoPlantilla;
        return $this;
    }

    public function getTextoBoton(): ?string
    {
        return $this->textoBoton;
    }

    public function setTextoBoton(string $textoBoton): self
    {
        $this->textoBoton = $textoBoton;
        return $this;
    }

    /**
     * @return Collection<int, EventoConsecuencia>
     */
    public function getConsecuencias(): Collection
    {
        return $this->consecuencias;
    }

    public function addConsecuencia(EventoConsecuencia $consecuencia): self
    {
        if (!$this->consecuencias->contains($consecuencia)) {
            $this->consecuencias->add($consecuencia);
            $consecuencia->setEventoOpcion($this);
        }
        return $this;
    }

    public function removeConsecuencia(EventoConsecuencia $consecuencia): self
    {
        if ($this->consecuencias->removeElement($consecuencia)) {
            // Setea el lado propietario a null para evitar inconsistencias si se desvincula
            if ($consecuencia->getEventoOpcion() === $this) {
                $consecuencia->setEventoOpcion(null);
            }
        }
        return $this;
    }
}