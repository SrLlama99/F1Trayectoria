<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pilotos_ia')]
class PilotoIa
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[ORM\ManyToOne(targetEntity: PartidaGuardada::class)]
    #[ORM\JoinColumn(name: 'partida_id', referencedColumnName: 'id_partida', nullable: false, onDelete: 'CASCADE')]
    private ?PartidaGuardada $partida = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $nombre = null;

    #[ORM\Column(type: 'string', length: 50)]
    private ?string $apellido = null;

    #[ORM\ManyToOne(targetEntity: Pais::class)]
    #[ORM\JoinColumn(name: 'pais_id', referencedColumnName: 'id_pais', nullable: false)]
    private ?Pais $pais = null;

    #[ORM\Column(type: 'integer')]
    private ?int $edad = null;

    #[ORM\Column(type: 'string', length: 20, options: ['default' => 'ACTIVO'])]
    private string $estadoActividad = 'ACTIVO';

    #[ORM\Column(type: 'string', length: 3, nullable: true)] // 🌟 Añadido nullable
    private ?string $abreviatura = null;

    #[ORM\Column(type: 'integer', nullable: true)]           // 🌟 Añadido nullable
    private ?int $numeroDorsal = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)] // 🌟 Añadido nullable
    private ?string $categoria = null;

    #[ORM\Column(type: 'integer')] private int $statClasificacion = 50;
    #[ORM\Column(type: 'integer')] private int $statRitmo = 50;
    #[ORM\Column(type: 'integer')] private int $statConsistencia = 50;
    #[ORM\Column(type: 'integer')] private int $statAdelantamiento = 50;
    #[ORM\Column(type: 'integer')] private int $statDefensa = 50;
    #[ORM\Column(type: 'integer')] private int $statGestionNeumaticos = 50;
    #[ORM\Column(type: 'integer')] private int $statMojado = 50;
    #[ORM\Column(type: 'integer')] private int $statPotencial = 70;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPartida(): ?PartidaGuardada
    {
        return $this->partida;
    }
    public function setPartida(?PartidaGuardada $partida): self
    {
        $this->partida = $partida;
        return $this;
    }

    public function getNombre(): ?string
    {
        return $this->nombre;
    }
    public function setNombre(string $nombre): self
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getApellido(): ?string
    {
        return $this->apellido;
    }
    public function setApellido(string $apellido): self
    {
        $this->apellido = $apellido;
        return $this;
    }

    public function getPais(): ?Pais
    {
        return $this->pais;
    }
    public function setPais(?Pais $pais): self
    {
        $this->pais = $pais;
        return $this;
    }

    public function getEdad(): ?int
    {
        return $this->edad;
    }
    public function setEdad(int $edad): self
    {
        $this->edad = $edad;
        return $this;
    }

    public function getEstadoActividad(): string
    {
        return $this->estadoActividad;
    }
    public function setEstadoActividad(string $estado): self
    {
        $this->estadoActividad = $estado;
        return $this;
    }

    public function getStatClasificacion(): int
    {
        return $this->statClasificacion;
    }
    public function setStatClasificacion(int $stat): self
    {
        $this->statClasificacion = $stat;
        return $this;
    }

    public function getStatRitmo(): int
    {
        return $this->statRitmo;
    }
    public function setStatRitmo(int $stat): self
    {
        $this->statRitmo = $stat;
        return $this;
    }

    public function getStatConsistencia(): int
    {
        return $this->statConsistencia;
    }
    public function setStatConsistencia(int $stat): self
    {
        $this->statConsistencia = $stat;
        return $this;
    }

    public function getStatAdelantamiento(): int
    {
        return $this->statAdelantamiento;
    }
    public function setStatAdelantamiento(int $stat): self
    {
        $this->statAdelantamiento = $stat;
        return $this;
    }

    public function getStatDefensa(): int
    {
        return $this->statDefensa;
    }
    public function setStatDefensa(int $stat): self
    {
        $this->statDefensa = $stat;
        return $this;
    }

    public function getStatGestionNeumaticos(): int
    {
        return $this->statGestionNeumaticos;
    }
    public function setStatGestionNeumaticos(int $stat): self
    {
        $this->statGestionNeumaticos = $stat;
        return $this;
    }

    public function getStatMojado(): int
    {
        return $this->statMojado;
    }
    public function setStatMojado(int $stat): self
    {
        $this->statMojado = $stat;
        return $this;
    }

    public function getStatPotencial(): int
    {
        return $this->statPotencial;
    }
    public function setStatPotencial(int $stat): self
    {
        $this->statPotencial = $stat;
        return $this;
    }

    public function getAbreviatura(): ?string
    {
        return $this->abreviatura;
    }

    /**
     * Fuerza automáticamente que la abreviatura se guarde siempre en MAYÚSCULAS 
     * y recortada exactamente a 3 caracteres.
     */
    public function setAbreviatura(string $abreviatura): self
    {
        $this->abreviatura = strtoupper(substr(trim($abreviatura), 0, 3));
        return $this;
    }

    public function getNumeroDorsal(): ?int
    {
        return $this->numeroDorsal;
    }

    public function setNumeroDorsal(int $numeroDorsal): self
    {
        $this->numeroDorsal = $numeroDorsal;
        return $this;
    }

    public function getCategoria(): ?string
    {
        return $this->categoria;
    }

    public function setCategoria(string $categoria): self
    {
        $this->categoria = $categoria;
        return $this;
    }
}