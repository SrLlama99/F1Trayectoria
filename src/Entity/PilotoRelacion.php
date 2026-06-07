<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pilotos_relaciones')]
class PilotoRelacion
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

    /**
     * Define el actor con el que se tiene la relación:
     * ESCUDERIA, TEAM_PRINCIPAL, COMPANERO, NOVIA, PATROCINADOR_1, PATROCINADOR_2...
     */
    #[ORM\Column(type: 'string', length: 30)]
    private ?string $tipoActor = null;

    /**
     * Nombre personalizado del actor si aplica (ej: el nombre de la novia,
     * el nombre del jefe de equipo de la IA, o la marca del patrocinador).
     */
    #[ORM\Column(type: 'string', length: 100, nullable: true)]
    private ?string $nombreActor = null;

    /**
     * Nivel de afinidad / relación (Escala 1 a 100)
     * 1-20: Enemistad/Tensión — 50: Neutral — 80-100: Excelente/Lealtad
     */
    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $afinidad = 50;

    public function getId(): ?int { return $this->id; }

    public function getPartida(): ?PartidaGuardada { return $this->partida; }
    public function setPartida(?PartidaGuardada $partida): self { $this->partida = $partida; return $this; }

    public function getPilotoUsuario(): ?PilotoUsuario { return $this->pilotoUsuario; }
    public function setPilotoUsuario(?PilotoUsuario $pilotoUsuario): self { $this->pilotoUsuario = $pilotoUsuario; return $this; }

    public function getTipoActor(): ?string { return $this->tipoActor; }
    public function setTipoActor(string $tipoActor): self 
    { 
        $this->tipoActor = strtoupper($tipoActor); 
        return $this; 
    }

    public function getNombreActor(): ?string { return $this->nombreActor; }
    public function setNombreActor(?string $nombreActor): self { $this->nombreActor = $nombreActor; return $this; }

    public function getAfinidad(): int { return $this->afinidad; }
    public function setAfinidad(int $afinidad): self 
    { 
        $this->afinidad = max(1, min(100, $afinidad)); 
        return $this; 
    }
}