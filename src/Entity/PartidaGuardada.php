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
    private ?int $idPartida = null;

    #[ORM\ManyToOne(targetEntity: Usuario::class)]
    #[ORM\JoinColumn(name: 'id_usuario', referencedColumnName: 'id', nullable: false, onDelete: 'CASCADE')]
    private ?Usuario $usuario = null;

    #[ORM\Column(name: 'slot_numero', type: 'integer')]
    private ?int $slotNumero = null;

    #[ORM\Column(name: 'fecha_guardado', type: 'datetime', options: ['default' => 'CURRENT_TIMESTAMP'])]
    private ?\DateTimeInterface $fechaGuardado = null;

    #[ORM\Column(name: 'ingame_ano', type: 'integer', options: ['default' => 2026])]
    private int $ingameAno = 2026;

    // ==========================================
    // 📅 NUEVO SISTEMA POR SEMANAS
    // ==========================================
    #[ORM\Column(name: 'ingame_semana', type: 'integer', options: ['default' => 1])]
    private int $ingameSemana = 1; // Rango típico: 1 a 52

    #[ORM\Column(name: 'ingame_estado', type: 'string', length: 50, options: ['default' => 'LIBRE'])]
    private string $ingameEstado = 'LIBRE'; 

    public function getIdPartida(): ?int { return $this->idPartida; }

    public function getUsuario(): ?Usuario { return $this->usuario; }
    public function setUsuario(?Usuario $usuario): self { $this->usuario = $usuario; return $this; }

    public function getSlotNumero(): ?int { return $this->slotNumero; }
    public function setSlotNumero(int $slotNumero): self { $this->slotNumero = $slotNumero; return $this; }

    public function getFechaGuardado(): ?\DateTimeInterface { return $this->fechaGuardado; }
    public function setFechaGuardado(\DateTimeInterface $fechaGuardado): self { $this->fechaGuardado = $fechaGuardado; return $this; }

    public function getIngameAno(): int { return $this->ingameAno; }
    public function setIngameAno(int $ingameAno): self { $this->ingameAno = $ingameAno; return $this; }

    // Métodos actualizados para la Semana
    public function getIngameSemana(): int { return $this->ingameSemana; }
    public function setIngameSemana(int $ingameSemana): self { $this->ingameSemana = $ingameSemana; return $this; }

    public function getIngameEstado(): string { return $this->ingameEstado; }
    public function setIngameEstado(string $ingameEstado): self { $this->ingameEstado = $ingameEstado; return $this; }
}