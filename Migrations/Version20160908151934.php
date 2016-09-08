<?php

namespace Ibtikar\ShareEconomyUMSBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160908151934 extends AbstractMigration
{
    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE users_verification_codes (user_id INT NOT NULL, phone_verification_code_id INT NOT NULL, INDEX IDX_38992541A76ED395 (user_id), INDEX IDX_3899254171D7FC04 (phone_verification_code_id), PRIMARY KEY(user_id, phone_verification_code_id)) DEFAULT CHARACTER SET utf8 COLLATE utf8_unicode_ci ENGINE = InnoDB');
        $this->addSql('ALTER TABLE users_verification_codes ADD CONSTRAINT FK_38992541A76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE users_verification_codes ADD CONSTRAINT FK_3899254171D7FC04 FOREIGN KEY (phone_verification_code_id) REFERENCES phone_verification_code (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE phone_verification_code DROP FOREIGN KEY FK_8036D16DA76ED395');

        $this->addSql('INSERT INTO users_verification_codes SELECT user_id, id FROM phone_verification_code');

        $this->addSql('DROP INDEX user_id ON phone_verification_code');
        $this->addSql('ALTER TABLE phone_verification_code DROP user_id');
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() != 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('DROP TABLE users_verification_codes');
        $this->addSql('ALTER TABLE phone_verification_code ADD user_id INT DEFAULT NULL');
        $this->addSql('ALTER TABLE phone_verification_code ADD CONSTRAINT FK_8036D16DA76ED395 FOREIGN KEY (user_id) REFERENCES user (id) ON DELETE CASCADE');
        $this->addSql('CREATE INDEX user_id ON phone_verification_code (user_id)');
    }
}
