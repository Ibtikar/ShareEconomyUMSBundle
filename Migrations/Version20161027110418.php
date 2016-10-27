<?php

namespace Ibtikar\ShareEconomyUMSBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161027110418 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(190) NOT NULL, CHANGE phone phone VARCHAR(190) NOT NULL, CHANGE password password VARCHAR(190) NOT NULL, CHANGE fullName fullName VARCHAR(190) NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE user CHANGE email email VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, CHANGE password password VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, CHANGE fullName fullName VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci, CHANGE phone phone VARCHAR(255) NOT NULL COLLATE utf8mb4_general_ci');
    }
}
