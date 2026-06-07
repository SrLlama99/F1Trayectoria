<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'partidas_guardadas')]
#[ORM\UniqueConstraint(columns: ['id_usuario', 'slot_numero'])]
class PartidaGuardada
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'id_partida', type: 'integer')]
    private ?int $idPartida = null; // Renombrado internamente para coincidir con el getter semántico

    // Relación ManyToOne: Muchas partidas pertenecen a un Usuario
    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Usuario $usuario = null;

    #[ORM\Column(name: 'slot_numero', type: 'integer')]
    private ?int $slotNumero = null;

    #[ORM\Column(name: 'fecha_guardado', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $fechaGuardado = null;

    #[ORM\Column(name: 'ingame_ano', type: 'integer', options: ['default' => 2026])]
    private int $ingameAno = 2026;

    #[ORM\Column(name: 'ingame_mes', type: 'integer', options: ['default' => 1])]
    private int $ingameMes = 1;

    #[ORM\Column(name: 'ingame_dia', type: 'integer', options: ['default' => 1])]
    private int $ingameDia = 1;

    // Sincronizado el nombre con el ENUM de tu base de datos física
    #[ORM\Column(name: 'ingame_estado', type: 'string', length: 20, options: ['default' => 'Menu'])]
    private string $ingameEstado = 'Menu'; 


    // ==========================================
    //          GETTERS Y SETTERS REALES
    // ==========================================

    public function getIdPartida(): ?int
    {
        return $this->idPartida;
    }

    public function getUsuario(): ?Usuario
    {
        return $this->usuario;
    }

    public function setUsuario(?Usuario $usuario): self
    {
        $this->usuario = $usuario;
        return $this;
    }

    public function getSlotNumero(): ?int
    {
        return $this->slotNumero;
    }

    public function setSlotNumero(int $slotNumero): self
    {
        $this->slotNumero = $slotNumero;
        return $this;
    }

    public function getFechaGuardado(): ?\DateTimeInterface
    {
        return $this->fechaGuardado;
    }

    public function setFechaGuardado(\DateTimeInterface $fechaGuardado): self
    {
        $this->fechaGuardado = $fechaGuardado;
        return $this;
    }

    public function getIngameAno(): int
    {
        return $this->ingameAno;
    }

    public function setIngameAno(int $ingameAno): self
    {
        $this->ingameAno = $ingameAno;
        return $this;
    }

    public function getIngameMes(): int
    {
        return $this->ingameMes;
    }

    public function setIngameMes(int $ingameMes): self
    {
        $this->ingameMes = $ingameMes;
        return $this;
    }

    public function getIngameDia(): int
    {
        return $this->ingameDia;
    }

    public function setIngameDia(int $ingameDia): self
    {
        $this->ingameDia = $ingameDia;
        return $this;
    }

    public function getIngameEstado(): string
    {
        return $this->ingameEstado;
    }

    // Asegura que solo se inserten los estados válidos de tu ENUM de MySQL
    public function setIngameEstado(string $ingameEstado): self
    {
        $estadosValidos = ['Menu', 'Clasificacion', 'Carrera'];
        if (in_array($ingameEstado, $estadosValidos)) {
            $this->ingameEstado = $ingameEstado;
        } else {
            $this->ingameEstado = 'Menu'; // Valor de seguridad por defecto
        }
        return $this;
    }
}