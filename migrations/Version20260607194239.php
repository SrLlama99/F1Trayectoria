<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260607194239 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logros CHANGE icono icono VARCHAR(50) DEFAULT \'🏆\' NOT NULL');
        $this->addSql('ALTER TABLE pilotos_usuario ADD genero VARCHAR(30) DEFAULT \'MASCULINO\' NOT NULL, ADD preferencia_sexual VARCHAR(30) DEFAULT \'HETEROSEXUAL\' NOT NULL');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'📦\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logros CHANGE icono icono VARCHAR(50) DEFAULT \'?\' NOT NULL');
        $this->addSql('ALTER TABLE pilotos_usuario DROP genero, DROP preferencia_sexual');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'?\' NOT NULL');
    }
}
