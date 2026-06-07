<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260607171947 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE calendario_temporada (id INT AUTO_INCREMENT NOT NULL, ano_simulacion INT NOT NULL, categoria VARCHAR(10) NOT NULL, orden_carrera INT NOT NULL, clima_previsto VARCHAR(20) DEFAULT \'SOLEADO\' NOT NULL, estado_evento VARCHAR(20) DEFAULT \'PENDIENTE\' NOT NULL, partida_id INT NOT NULL, circuito_id INT NOT NULL, INDEX IDX_173EE96BF15A1987 (partida_id), INDEX IDX_173EE96B68CE3E02 (circuito_id), UNIQUE INDEX UNIQ_173EE96BF15A198760625AC64E10122D7390D51E (partida_id, ano_simulacion, categoria, orden_carrera), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE circuitos_base (id INT AUTO_INCREMENT NOT NULL, nombre_circuito VARCHAR(100) NOT NULL, ciudad VARCHAR(50) DEFAULT NULL, longitud_km NUMERIC(4, 3) NOT NULL, curvas INT NOT NULL, tipo_circuito VARCHAR(20) NOT NULL, dificultad_desgaste VARCHAR(10) NOT NULL, pais_id INT DEFAULT NULL, INDEX IDX_CA67309CC604D5C6 (pais_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE contratos_mercado (id INT AUTO_INCREMENT NOT NULL, es_jugador_humano TINYINT DEFAULT 0 NOT NULL, numero_asiento INT NOT NULL, sueldo_anual INT DEFAULT 0 NOT NULL, duracion_contrato_anos INT DEFAULT 1 NOT NULL, partida_id INT NOT NULL, escuderia_id INT NOT NULL, piloto_ia_id INT DEFAULT NULL, INDEX IDX_CD85D4EFF15A1987 (partida_id), INDEX IDX_CD85D4EF697D28DD (escuderia_id), INDEX IDX_CD85D4EF1A83C591 (piloto_ia_id), UNIQUE INDEX UNIQ_CD85D4EFF15A1987697D28DDB782CDFD (partida_id, escuderia_id, numero_asiento), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE escuderias (id INT AUTO_INCREMENT NOT NULL, nombre_oficial VARCHAR(100) NOT NULL, nombre_corto VARCHAR(10) NOT NULL, rendimiento_motor INT DEFAULT 50 NOT NULL, rendimiento_chasis INT DEFAULT 50 NOT NULL, rendimiento_aerodinamica INT DEFAULT 50 NOT NULL, presupuesto_disponible INT DEFAULT 10000000 NOT NULL, categoria VARCHAR(30) DEFAULT \'F1\' NOT NULL, partida_id INT NOT NULL, pais_id INT DEFAULT NULL, INDEX IDX_C33572FF15A1987 (partida_id), INDEX IDX_C33572FC604D5C6 (pais_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE paises (id_pais INT AUTO_INCREMENT NOT NULL, nombre_pais VARCHAR(50) NOT NULL, codigo_iso VARCHAR(3) NOT NULL, bandera_url VARCHAR(255) DEFAULT NULL, probabilidad_talento INT DEFAULT 1 NOT NULL, UNIQUE INDEX UNIQ_DFAD237BB0BF6408 (nombre_pais), UNIQUE INDEX UNIQ_DFAD237B13EB58DE (codigo_iso), PRIMARY KEY (id_pais)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE partidas_guardadas (id_partida INT AUTO_INCREMENT NOT NULL, slot_numero INT NOT NULL, fecha_guardado DATETIME DEFAULT CURRENT_TIMESTAMP NOT NULL, ingame_ano INT DEFAULT 2026 NOT NULL, ingame_mes INT DEFAULT 1 NOT NULL, ingame_dia INT DEFAULT 1 NOT NULL, ingame_estado VARCHAR(20) DEFAULT \'Menu\' NOT NULL, id_usuario INT NOT NULL, INDEX IDX_5CBA2779FCF8192D (id_usuario), UNIQUE INDEX UNIQ_5CBA2779FCF8192DFD59699C (id_usuario, slot_numero), PRIMARY KEY (id_partida)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pilotos_ia (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, edad INT NOT NULL, estado_actividad VARCHAR(20) DEFAULT \'ACTIVO\' NOT NULL, abreviatura VARCHAR(3) DEFAULT NULL, numero_dorsal INT DEFAULT NULL, categoria VARCHAR(50) DEFAULT NULL, stat_clasificacion INT NOT NULL, stat_ritmo INT NOT NULL, stat_consistencia INT NOT NULL, stat_adelantamiento INT NOT NULL, stat_defensa INT NOT NULL, stat_gestion_neumaticos INT NOT NULL, stat_mojado INT NOT NULL, stat_potencial INT NOT NULL, partida_id INT NOT NULL, pais_id INT NOT NULL, INDEX IDX_4881B7DF15A1987 (partida_id), INDEX IDX_4881B7DC604D5C6 (pais_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pilotos_relaciones (id INT AUTO_INCREMENT NOT NULL, tipo_actor VARCHAR(30) NOT NULL, nombre_actor VARCHAR(100) DEFAULT NULL, afinidad INT DEFAULT 50 NOT NULL, partida_id INT NOT NULL, piloto_usuario_id INT NOT NULL, INDEX IDX_F222AC0CF15A1987 (partida_id), INDEX IDX_F222AC0C679C8C2E (piloto_usuario_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE pilotos_usuario (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(50) NOT NULL, apellido VARCHAR(50) NOT NULL, nivel_actual INT DEFAULT 1 NOT NULL, exp_actual INT DEFAULT 0 NOT NULL, puntos_habilidad_disponibles INT DEFAULT 0 NOT NULL, stat_clasificacion INT DEFAULT 50 NOT NULL, stat_ritmo INT DEFAULT 50 NOT NULL, stat_consistencia INT DEFAULT 50 NOT NULL, stat_adelantamiento INT DEFAULT 50 NOT NULL, stat_defensa INT DEFAULT 50 NOT NULL, stat_gestion_neumaticos INT DEFAULT 50 NOT NULL, stat_mojado INT DEFAULT 50 NOT NULL, abreviatura VARCHAR(3) DEFAULT NULL, numero_dorsal INT DEFAULT NULL, categoria VARCHAR(50) DEFAULT NULL, dinero BIGINT DEFAULT NULL, estilo_de_vida INT DEFAULT NULL, felicidad INT DEFAULT NULL, jugadas_casino INT DEFAULT 0 NOT NULL, partida_id INT NOT NULL, pais_id INT NOT NULL, INDEX IDX_A9BD362BF15A1987 (partida_id), INDEX IDX_A9BD362BC604D5C6 (pais_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE resultados_carreras (id INT AUTO_INCREMENT NOT NULL, es_jugador_humano TINYINT DEFAULT 0 NOT NULL, posicion_salida INT NOT NULL, tiempo_clasificacion VARCHAR(15) DEFAULT NULL, posicion_final INT DEFAULT NULL, puntos_obtenidos INT DEFAULT 0 NOT NULL, vuelta_rapida TINYINT DEFAULT 0 NOT NULL, estado_carrera VARCHAR(20) DEFAULT \'TERMINO\' NOT NULL, evento_id INT NOT NULL, piloto_ia_id INT DEFAULT NULL, escuderia_id INT NOT NULL, INDEX IDX_60E854D387A5F842 (evento_id), INDEX IDX_60E854D31A83C591 (piloto_ia_id), INDEX IDX_60E854D3697D28DD (escuderia_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tienda_compras (id INT AUTO_INCREMENT NOT NULL, fecha_compra DATETIME NOT NULL, partida_id INT NOT NULL, piloto_usuario_id INT NOT NULL, item_id INT NOT NULL, INDEX IDX_ADA1DD47F15A1987 (partida_id), INDEX IDX_ADA1DD47679C8C2E (piloto_usuario_id), INDEX IDX_ADA1DD47126F525E (item_id), UNIQUE INDEX UNIQ_ADA1DD47F15A1987679C8C2E126F525E (partida_id, piloto_usuario_id, item_id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE tienda_items (id INT AUTO_INCREMENT NOT NULL, nombre VARCHAR(100) NOT NULL, descripcion VARCHAR(255) NOT NULL, categoria VARCHAR(30) NOT NULL, precio INT NOT NULL, bono_estilo_de_vida INT NOT NULL, emoji VARCHAR(10) DEFAULT \'📦\' NOT NULL, PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE usuarios (id INT AUTO_INCREMENT NOT NULL, username VARCHAR(50) NOT NULL, email VARCHAR(180) NOT NULL, password VARCHAR(255) NOT NULL, roles JSON NOT NULL, UNIQUE INDEX UNIQ_EF687F2F85E0677 (username), UNIQUE INDEX UNIQ_EF687F2E7927C74 (email), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0E3BD61CE16BA31DBBF396750 (queue_name, available_at, delivered_at, id), PRIMARY KEY (id)) DEFAULT CHARACTER SET utf8mb4');
        $this->addSql('ALTER TABLE calendario_temporada ADD CONSTRAINT FK_173EE96BF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE calendario_temporada ADD CONSTRAINT FK_173EE96B68CE3E02 FOREIGN KEY (circuito_id) REFERENCES circuitos_base (id)');
        $this->addSql('ALTER TABLE circuitos_base ADD CONSTRAINT FK_CA67309CC604D5C6 FOREIGN KEY (pais_id) REFERENCES paises (id_pais)');
        $this->addSql('ALTER TABLE contratos_mercado ADD CONSTRAINT FK_CD85D4EFF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contratos_mercado ADD CONSTRAINT FK_CD85D4EF697D28DD FOREIGN KEY (escuderia_id) REFERENCES escuderias (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE contratos_mercado ADD CONSTRAINT FK_CD85D4EF1A83C591 FOREIGN KEY (piloto_ia_id) REFERENCES pilotos_ia (id) ON DELETE SET NULL');
        $this->addSql('ALTER TABLE escuderias ADD CONSTRAINT FK_C33572FF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE escuderias ADD CONSTRAINT FK_C33572FC604D5C6 FOREIGN KEY (pais_id) REFERENCES paises (id_pais)');
        $this->addSql('ALTER TABLE partidas_guardadas ADD CONSTRAINT FK_5CBA2779FCF8192D FOREIGN KEY (id_usuario) REFERENCES usuarios (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pilotos_ia ADD CONSTRAINT FK_4881B7DF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pilotos_ia ADD CONSTRAINT FK_4881B7DC604D5C6 FOREIGN KEY (pais_id) REFERENCES paises (id_pais)');
        $this->addSql('ALTER TABLE pilotos_relaciones ADD CONSTRAINT FK_F222AC0CF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pilotos_relaciones ADD CONSTRAINT FK_F222AC0C679C8C2E FOREIGN KEY (piloto_usuario_id) REFERENCES pilotos_usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pilotos_usuario ADD CONSTRAINT FK_A9BD362BF15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE pilotos_usuario ADD CONSTRAINT FK_A9BD362BC604D5C6 FOREIGN KEY (pais_id) REFERENCES paises (id_pais)');
        $this->addSql('ALTER TABLE resultados_carreras ADD CONSTRAINT FK_60E854D387A5F842 FOREIGN KEY (evento_id) REFERENCES calendario_temporada (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE resultados_carreras ADD CONSTRAINT FK_60E854D31A83C591 FOREIGN KEY (piloto_ia_id) REFERENCES pilotos_ia (id)');
        $this->addSql('ALTER TABLE resultados_carreras ADD CONSTRAINT FK_60E854D3697D28DD FOREIGN KEY (escuderia_id) REFERENCES escuderias (id)');
        $this->addSql('ALTER TABLE tienda_compras ADD CONSTRAINT FK_ADA1DD47F15A1987 FOREIGN KEY (partida_id) REFERENCES partidas_guardadas (id_partida) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tienda_compras ADD CONSTRAINT FK_ADA1DD47679C8C2E FOREIGN KEY (piloto_usuario_id) REFERENCES pilotos_usuario (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE tienda_compras ADD CONSTRAINT FK_ADA1DD47126F525E FOREIGN KEY (item_id) REFERENCES tienda_items (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE calendario_temporada DROP FOREIGN KEY FK_173EE96BF15A1987');
        $this->addSql('ALTER TABLE calendario_temporada DROP FOREIGN KEY FK_173EE96B68CE3E02');
        $this->addSql('ALTER TABLE circuitos_base DROP FOREIGN KEY FK_CA67309CC604D5C6');
        $this->addSql('ALTER TABLE contratos_mercado DROP FOREIGN KEY FK_CD85D4EFF15A1987');
        $this->addSql('ALTER TABLE contratos_mercado DROP FOREIGN KEY FK_CD85D4EF697D28DD');
        $this->addSql('ALTER TABLE contratos_mercado DROP FOREIGN KEY FK_CD85D4EF1A83C591');
        $this->addSql('ALTER TABLE escuderias DROP FOREIGN KEY FK_C33572FF15A1987');
        $this->addSql('ALTER TABLE escuderias DROP FOREIGN KEY FK_C33572FC604D5C6');
        $this->addSql('ALTER TABLE partidas_guardadas DROP FOREIGN KEY FK_5CBA2779FCF8192D');
        $this->addSql('ALTER TABLE pilotos_ia DROP FOREIGN KEY FK_4881B7DF15A1987');
        $this->addSql('ALTER TABLE pilotos_ia DROP FOREIGN KEY FK_4881B7DC604D5C6');
        $this->addSql('ALTER TABLE pilotos_relaciones DROP FOREIGN KEY FK_F222AC0CF15A1987');
        $this->addSql('ALTER TABLE pilotos_relaciones DROP FOREIGN KEY FK_F222AC0C679C8C2E');
        $this->addSql('ALTER TABLE pilotos_usuario DROP FOREIGN KEY FK_A9BD362BF15A1987');
        $this->addSql('ALTER TABLE pilotos_usuario DROP FOREIGN KEY FK_A9BD362BC604D5C6');
        $this->addSql('ALTER TABLE resultados_carreras DROP FOREIGN KEY FK_60E854D387A5F842');
        $this->addSql('ALTER TABLE resultados_carreras DROP FOREIGN KEY FK_60E854D31A83C591');
        $this->addSql('ALTER TABLE resultados_carreras DROP FOREIGN KEY FK_60E854D3697D28DD');
        $this->addSql('ALTER TABLE tienda_compras DROP FOREIGN KEY FK_ADA1DD47F15A1987');
        $this->addSql('ALTER TABLE tienda_compras DROP FOREIGN KEY FK_ADA1DD47679C8C2E');
        $this->addSql('ALTER TABLE tienda_compras DROP FOREIGN KEY FK_ADA1DD47126F525E');
        $this->addSql('DROP TABLE calendario_temporada');
        $this->addSql('DROP TABLE circuitos_base');
        $this->addSql('DROP TABLE contratos_mercado');
        $this->addSql('DROP TABLE escuderias');
        $this->addSql('DROP TABLE paises');
        $this->addSql('DROP TABLE partidas_guardadas');
        $this->addSql('DROP TABLE pilotos_ia');
        $this->addSql('DROP TABLE pilotos_relaciones');
        $this->addSql('DROP TABLE pilotos_usuario');
        $this->addSql('DROP TABLE resultados_carreras');
        $this->addSql('DROP TABLE tienda_compras');
        $this->addSql('DROP TABLE tienda_items');
        $this->addSql('DROP TABLE usuarios');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
