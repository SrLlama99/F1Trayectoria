<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260607193520 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE logros (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, icono VARCHAR(50) DEFAULT \'🏆\' NOT NULL, recompensa_exp INT DEFAULT 0 NOT NULL, codigo_identificador VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE logros_partidas (id INT AUTO_INCREMENT NOT NULL, desbloqueado_en DATETIME NOT NULL, partida_id INT NOT NULL, logro_id INT NOT NULL, INDEX IDX_B52A5881F15A1987 (partida_id), INDEX IDX_B52A5881F33E21AB (logro_id), UNIQUE INDEX UNIQ_B52A5881F15A1987F33E21AB (partida_id, logro_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE trofeos (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, icono VARCHAR(50) NOT NULL, tipo_metal VARCHAR(30) NOT NULL, puntos_prestigio INT DEFAULT 0 NOT NULL, codigo_identificador VARCHAR(50) NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE trofeos_partidas (id INT AUTO_INCREMENT NOT NULL, conseguido_en DATETIME NOT NULL, detalles_contexto VARCHAR(100) DEFAULT NULL, partida_id INT NOT NULL, trofeo_id INT NOT NULL, INDEX IDX_8912B561F15A1987 (partida_id), INDEX IDX_8912B561ADF7C39B (trofeo_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE logros_partidas ADD CONSTRAINT FK_B52A5881F15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE logros_partidas ADD CONSTRAINT FK_B52A5881F33E21AB FOREIGN KEY (logro_id) REFERENCES logros (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trofeos_partidas ADD CONSTRAINT FK_8912B561F15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE trofeos_partidas ADD CONSTRAINT FK_8912B561ADF7C39B FOREIGN KEY (trofeo_id) REFERENCES trofeos (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calendario_temporada ADD semana_carrera INT NOT NULL');
        $this->addSql('ALTER TABLE partidas_guardadas ADD ingame_semana INT DEFAULT 1 NOT NULL, DROP ingame_mes, DROP ingame_dia, CHANGE ingame_estado ingame_estado VARCHAR(50) DEFAULT \'LIBRE\' NOT NULL');
        $this->addSql('ALTER TABLE resultados_carreras ADD numero_sesion INT NOT NULL, ADD tipo_sesion VARCHAR(30) NOT NULL, ADD mejor_tiempo_vuelta VARCHAR(20) DEFAULT NULL, DROP tiempo_clasificacion, DROP estado_carrera, CHANGE posicion_salida posicion_salida INT DEFAULT NULL');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'📦\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logros_partidas DROP FOREIGN KEY FK_B52A5881F15A1987');
        $this->addSql('ALTER TABLE logros_partidas DROP FOREIGN KEY FK_B52A5881F33E21AB');
        $this->addSql('ALTER TABLE trofeos_partidas DROP FOREIGN KEY FK_8912B561F15A1987');
        $this->addSql('ALTER TABLE trofeos_partidas DROP FOREIGN KEY FK_8912B561ADF7C39B');
        $this->addSql('DROP TABLE logros');
        $this->addSql('DROP TABLE logros_partidas');
        $this->addSql('DROP TABLE trofeos');
        $this->addSql('DROP TABLE trofeos_partidas');
        $this->addSql('ALTER TABLE calendario_temporada DROP semana_carrera');
        $this->addSql('ALTER TABLE partidas_guardadas ADD ingame_dia INT DEFAULT 1 NOT NULL, CHANGE ingame_estado ingame_estado VARCHAR(20) DEFAULT \'Menu\' NOT NULL, CHANGE ingame_semana ingame_mes INT DEFAULT 1 NOT NULL');
        $this->addSql('ALTER TABLE resultados_carreras ADD tiempo_clasificacion VARCHAR(15) DEFAULT NULL, ADD estado_carrera VARCHAR(20) DEFAULT \'TERMINO\' NOT NULL, DROP numero_sesion, DROP tipo_sesion, DROP mejor_tiempo_vuelta, CHANGE posicion_salida posicion_salida INT NOT NULL');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'?\' NOT NULL');
    }
}
