<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200603232118 extends AbstractMigration
{
    public function getDescription() : string
    {
        return '';
    }

    public function up(Schema $schema) : void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('CREATE TABLE discord_role (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE corporation (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE corporation_discord_role (corporation_id INT NOT NULL, discord_role_id INT NOT NULL, INDEX IDX_31DF23DEB2685369 (corporation_id), INDEX IDX_31DF23DE929E06F7 (discord_role_id), PRIMARY KEY(corporation_id, discord_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE `character` (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE character_discord_role (character_id INT NOT NULL, discord_role_id INT NOT NULL, INDEX IDX_F1DB0C031136BE75 (character_id), INDEX IDX_F1DB0C03929E06F7 (discord_role_id), PRIMARY KEY(character_id, discord_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alliance (id INT AUTO_INCREMENT NOT NULL, uid VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE alliance_discord_role (alliance_id INT NOT NULL, discord_role_id INT NOT NULL, INDEX IDX_18653DD210A0EA3F (alliance_id), INDEX IDX_18653DD2929E06F7 (discord_role_id), PRIMARY KEY(alliance_id, discord_role_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE corporation_discord_role ADD CONSTRAINT FK_31DF23DEB2685369 FOREIGN KEY (corporation_id) REFERENCES corporation (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE corporation_discord_role ADD CONSTRAINT FK_31DF23DE929E06F7 FOREIGN KEY (discord_role_id) REFERENCES discord_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_discord_role ADD CONSTRAINT FK_F1DB0C031136BE75 FOREIGN KEY (character_id) REFERENCES `character` (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE character_discord_role ADD CONSTRAINT FK_F1DB0C03929E06F7 FOREIGN KEY (discord_role_id) REFERENCES discord_role (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alliance_discord_role ADD CONSTRAINT FK_18653DD210A0EA3F FOREIGN KEY (alliance_id) REFERENCES alliance (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE alliance_discord_role ADD CONSTRAINT FK_18653DD2929E06F7 FOREIGN KEY (discord_role_id) REFERENCES discord_role (id) ON DELETE CASCADE');
    }

    public function down(Schema $schema) : void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->abortIf($this->connection->getDatabasePlatform()->getName() !== 'mysql', 'Migration can only be executed safely on \'mysql\'.');

        $this->addSql('ALTER TABLE corporation_discord_role DROP FOREIGN KEY FK_31DF23DE929E06F7');
        $this->addSql('ALTER TABLE character_discord_role DROP FOREIGN KEY FK_F1DB0C03929E06F7');
        $this->addSql('ALTER TABLE alliance_discord_role DROP FOREIGN KEY FK_18653DD2929E06F7');
        $this->addSql('ALTER TABLE corporation_discord_role DROP FOREIGN KEY FK_31DF23DEB2685369');
        $this->addSql('ALTER TABLE character_discord_role DROP FOREIGN KEY FK_F1DB0C031136BE75');
        $this->addSql('ALTER TABLE alliance_discord_role DROP FOREIGN KEY FK_18653DD210A0EA3F');
        $this->addSql('DROP TABLE discord_role');
        $this->addSql('DROP TABLE corporation');
        $this->addSql('DROP TABLE corporation_discord_role');
        $this->addSql('DROP TABLE `character`');
        $this->addSql('DROP TABLE character_discord_role');
        $this->addSql('DROP TABLE alliance');
        $this->addSql('DROP TABLE alliance_discord_role');
    }
}
