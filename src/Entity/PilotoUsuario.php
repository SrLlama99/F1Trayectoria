<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\Table(name: 'pilotos_usuario')]
class PilotoUsuario
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

    #[ORM\Column(type: 'integer', options: ['default' => 1])]
    private int $nivelActual = 1;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $expActual = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $puntosHabilidadDisponibles = 0;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statClasificacion = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statRitmo = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statAdelantamiento = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statDefensa = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statGestionNeumaticos = 50;

    #[ORM\Column(type: 'integer', options: ['default' => 50])]
    private int $statMojado = 50;

    #[ORM\Column(type: 'string', length: 3, nullable: true)] // 🌟 Añadido nullable
    private ?string $abreviatura = null;

    #[ORM\Column(type: 'integer', nullable: true)]           // 🌟 Añadido nullable
    private ?int $numeroDorsal = null;

    #[ORM\Column(type: 'string', length: 50, nullable: true)] // 🌟 Añadido nullable
    private ?string $categoria = null;

    #[ORM\Column(type: 'bigint', nullable: true)]
    private string|int $dinero = 0; // Iniciamos con un presupuesto modesto de karting/F4

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $estiloDeVida = 0; // Escala 1-100 (10 = Viviendo en el motorhome / piso compartido)

    #[ORM\Column(type: 'integer', nullable: true)]
    private int $felicidad = 0; // Escala 1-100 (75 = Motivado para debutar)

    #[ORM\Column(type: 'integer', options: ['default' => 0])]
    private int $jugadasCasino = 0; // Controla el número de apuestas/manos jugadas en la semana/día

    #[ORM\Column(type: 'string', length: 30, options: ['default' => 'MASCULINO'])]
    private string $genero = 'MASCULINO'; // MASCULINO, FEMENINO, NO_BINARIO

    #[ORM\Column(type: 'string', length: 30, options: ['default' => 'HETEROSEXUAL'])]
    private string $preferenciaSexual = 'HETEROSEXUAL'; // HETEROSEXUAL, HOMOSEXUAL, BISEXUAL, PANSEXUAL

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

    public function getNivelActual(): int
    {
        return $this->nivelActual;
    }
    public function setNivelActual(int $nivelActual): self
    {
        $this->nivelActual = $nivelActual;
        return $this;
    }

    public function getExpActual(): int
    {
        return $this->expActual;
    }
    public function setExpActual(int $expActual): self
    {
        $this->expActual = $expActual;
        return $this;
    }

    public function getPuntosHabilidadDisponibles(): int
    {
        return $this->puntosHabilidadDisponibles;
    }
    public function setPuntosHabilidadDisponibles(int $puntos): self
    {
        $this->puntosHabilidadDisponibles = $puntos;
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

    public function getDinero(): int
    {
        return (int) $this->dinero;
    }

    public function setDinero(int $dinero): self
    {
        $this->dinero = $dinero;
        return $this;
    }

    public function getEstiloDeVida(): int
    {
        return $this->estiloDeVida;
    }

    public function setEstiloDeVida(int $estiloDeVida): self
    {
        // Forzamos límites reglamentarios entre 1 y 100
        $this->estiloDeVida = max(1, min(100, $estiloDeVida));
        return $this;
    }

    public function getFelicidad(): int
    {
        return $this->felicidad;
    }

    public function setFelicidad(int $felicidad): self
    {
        // Forzamos límites reglamentarios entre 1 y 100
        $this->felicidad = max(1, min(100, $felicidad));
        return $this;
    }

    public function getJugadasCasino(): int
    {
        return $this->jugadasCasino;
    }

    public function setJugadasCasino(int $jugadasCasino): self
    {
        // Evitamos que el contador baje de 0 de forma accidental
        $this->jugadasCasino = max(0, $jugadasCasino);
        return $this;
    }

    /**
     * Incrementa en 1 el contador tras finalizar una jugada en el casino.
     */
    public function incrementarJugadaCasino(): self
    {
        $this->jugadasCasino++;
        return $this;
    }

    /**
     * Resetea el contador al cambiar de semana o jornada en el juego.
     */
    public function resetearJugadasCasino(): self
    {
        $this->jugadasCasino = 0;
        return $this;
    }

    public function getGenero(): string { return $this->genero; }
    public function setGenero(string $genero): self { $this->genero = strtoupper($genero); return $this; }

    public function getPreferenciaSexual(): string { return $this->preferenciaSexual; }
    public function setPreferenciaSexual(string $preferencia): self { $this->preferenciaSexual = strtoupper($preferencia); return $this; }
}