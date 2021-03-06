<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20201022181616 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Set memory table to save games.';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE memory (id INTEGER PRIMARY KEY AUTOINCREMENT NOT NULL, name VARCHAR(3) NOT NULL, score INTEGER NOT NULL, date DATETIME NOT NULL, time INTEGER NOT NULL)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('DROP TABLE memory');
    }
}
