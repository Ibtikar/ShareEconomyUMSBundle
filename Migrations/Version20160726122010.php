<?php

namespace Ibtikar\ShareEconomyUMSBundle\Migrations;

use Doctrine\DBAL\Migrations\AbstractMigration;
use Doctrine\DBAL\Schema\Schema;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
class Version20160726122010 extends AbstractMigration
{

    /**
     * @param Schema $schema
     */
    public function up(Schema $schema)
    {
        $initialSchema = <<<EOF
                DROP TABLE IF EXISTS user;

                CREATE TABLE `user` (
                    `id` int(11) NOT NULL,
                    `email` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `phone` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `password` varchar(255) COLLATE utf8_unicode_ci NOT NULL,
                    `salt` varchar(32) COLLATE utf8_unicode_ci NOT NULL,
                    `roles` longtext COLLATE utf8_unicode_ci COMMENT '(DC2Type:simple_array)',
                    `enabled` tinyint(1) NOT NULL,
                    `emailVerified` tinyint(1) NOT NULL,
                    `emailVerificationToken` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `emailVerificationTokenExpiryTime` datetime DEFAULT NULL,
                    `changePasswordToken` varchar(32) COLLATE utf8_unicode_ci DEFAULT NULL,
                    `changePasswordTokenExpiryTime` datetime DEFAULT NULL,
                    `systemUser` tinyint(1) NOT NULL,
                    `created` datetime NOT NULL,
                    `updated` datetime NOT NULL,
                    `fullName` varchar(255) COLLATE utf8_unicode_ci NOT NULL
                  ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;

                ALTER TABLE `user`
                  ADD PRIMARY KEY (`id`),
                  ADD UNIQUE KEY `UNIQ_8D93D649E7927C74` (`email`),
                  ADD UNIQUE KEY `UNIQ_8D93D648E7927C73` (`phone`);

                ALTER TABLE `user`
                    MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
EOF;

        $initialData = <<<EOF
                --
                -- Dumping data for table `user`
                --

                INSERT INTO `user` (`email`, `phone`, `password`, `salt`, `roles`, `enabled`, `emailVerified`, `emailVerificationToken`, `emailVerificationTokenExpiryTime`, `changePasswordToken`, `changePasswordTokenExpiryTime`, `systemUser`, `created`, `updated`, `fullName`) VALUES
                    ('admin@sofra.com', '0123456789', 'S2CVsPxiIXtZp5409nvRz6B6RVptkryAhV94UqwvuamEyD56NEwMu23sC0lq2n//68+FovLT6XjGteMbFl54Tw==', '32bbba04283e1ada5776eaf3edc98a0b', 'ROLE_SUPER_ADMIN', 1, 1, NULL, NULL, NULL, NULL, 1, '2016-07-26 10:45:12', '2016-07-26 10:45:12', 'Sofra Admin');

EOF;

        $this->addSql($initialSchema);
        $this->addSql($initialData);
    }

    /**
     * @param Schema $schema
     */
    public function down(Schema $schema)
    {
        $this->addSql('SET foreign_key_checks = 0;');
        $this->addSql('DROP TABLE `user`;');
        $this->addSql('SET foreign_key_checks = 1;');
    }
}