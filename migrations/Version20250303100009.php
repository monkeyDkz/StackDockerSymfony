<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20250303100009 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // Ajouter d'abord la colonne en permettant les valeurs NULL
        $this->addSql('ALTER TABLE "user" ADD is_verified BOOLEAN DEFAULT FALSE');
        
        // Mettre à jour les enregistrements existants
        $this->addSql('UPDATE "user" SET is_verified = FALSE WHERE is_verified IS NULL');
        
        // Rendre la colonne NOT NULL
        $this->addSql('ALTER TABLE "user" ALTER COLUMN is_verified SET NOT NULL');
    }
    
    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE "user" DROP is_verified');
    }
}
