<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'evento_consecuencias')]
class EventoConsecuencia
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    /**
     * La consecuencia pertenece a una opción concreta de respuesta elegida por el usuario.
     */
    #[ORM\ManyToOne(targetEntity: EventoOpcion::class, inversedBy: 'consecuencias')]
    #[ORM\JoinColumn(name: 'evento_opcion_id', nullable: false, onDelete: 'CASCADE')]
    private ?EventoOpcion $eventoOpcion = null;

    /**
     * Tipo de alteración que se va a procesar en el Paddock.
     * Valores del motor: 'MODIFICAR_DINERO', 'AUMENTAR_DESGASTE', 'MODIFICAR_AFINIDAD'.
     */
    #[ORM\Column(type: 'string', length: 50)]
    private ?string $tipoEfecto = null;

    /**
     * El impacto numérico real. 
     * Puede ser negativo (ej: -2500 para dinero, -15 para afinidad) o positivo (ej: 30 de desgaste).
     */
    #[ORM\Column(type: 'integer')]
    private int $cantidad = 0;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEventoOpcion(): ?EventoOpcion
    {
        return $this->eventoOpcion;
    }

    public function setEventoOpcion(?EventoOpcion $eventoOpcion): self
    {
        $this->eventoOpcion = $eventoOpcion;
        return $this;
    }

    public function getTipoEfecto(): ?string
    {
        return $this->tipoEfecto;
    }

    public function setTipoEfecto(string $tipoEfecto): self
    {
        $this->tipoEfecto = strtoupper($tipoEfecto);
        return $this;
    }

    public function getCantidad(): int
    {
        return $this->cantidad;
    }

    public function setCantidad(int $cantidad): self
    {
        $this->cantidad = $cantidad;
        return $this;
    }
}