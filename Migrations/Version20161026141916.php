<?php

namespace Ibtikar\ShareEconomyUMSBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161026141916 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phone_verification_code CHANGE created_at created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('ALTER TABLE user CHANGE created_at created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP, CHANGE updated_at updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP');
        $this->addSql('UPDATE phone_verification_code SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL;');
        $this->addSql('UPDATE phone_verification_code SET updated_at = CURRENT_TIMESTAMP WHERE updated_at IS NULL;');
        $this->addSql('UPDATE user SET created_at = CURRENT_TIMESTAMP WHERE created_at IS NULL;');
        $this->addSql('UPDATE user SET updated_at = CURRENT_TIMESTAMP WHERE updated_at IS NULL;');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

    }
}
