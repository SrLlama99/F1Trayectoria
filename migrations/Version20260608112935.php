<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20260608112935 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logros CHANGE icono icono VARCHAR(50) DEFAULT \'🏆\' NOT NULL');
        $this->addSql('ALTER TABLE pilotos_ia DROP stat_consistencia');
        $this->addSql('ALTER TABLE pilotos_usuario DROP stat_consistencia');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'📦\' NOT NULL');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE logros CHANGE icono icono VARCHAR(50) DEFAULT \'?\' NOT NULL');
        $this->addSql('ALTER TABLE pilotos_ia ADD stat_consistencia INT NOT NULL');
        $this->addSql('ALTER TABLE pilotos_usuario ADD stat_consistencia INT DEFAULT 50 NOT NULL');
        $this->addSql('ALTER TABLE tienda_items CHANGE emoji emoji VARCHAR(10) DEFAULT \'?\' NOT NULL');
    }
}
