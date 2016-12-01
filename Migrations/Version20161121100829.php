<?php

namespace Ibtikar\ShareEconomyUMSBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20161121100829 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phone_verification_code CHANGE is_verified is_verified TINYINT(1) DEFAULT \'0\' NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE enabled enabled TINYINT(1) DEFAULT \'1\' NOT NULL, CHANGE emailVerified emailVerified TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE systemUser systemUser TINYINT(1) DEFAULT \'0\' NOT NULL, CHANGE isPhoneVerified isPhoneVerified TINYINT(1) DEFAULT \'0\' NOT NULL');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE phone_verification_code CHANGE is_verified is_verified TINYINT(1) NOT NULL');
        $this->addSql('ALTER TABLE user CHANGE enabled enabled TINYINT(1) NOT NULL, CHANGE emailVerified emailVerified TINYINT(1) NOT NULL, CHANGE isPhoneVerified isPhoneVerified TINYINT(1) NOT NULL, CHANGE systemUser systemUser TINYINT(1) NOT NULL');
    }
}
