<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20230717122637 extends AbstractMigration
{
    public function getDescription(): string
    {
        return '';
    }

    public function up(Schema $schema): void
    {
        // this up() migration is auto-generated, please modify it to your needs
        $this->addSql('CREATE TABLE creature (id INT AUTO_INCREMENT NOT NULL, user_id INT DEFAULT NULL, name VARCHAR(255) NOT NULL, resume LONGTEXT NOT NULL, description LONGTEXT NOT NULL, picture VARCHAR(255) DEFAULT NULL, size VARCHAR(255) NOT NULL, weight VARCHAR(255) NOT NULL, physical_percularities VARCHAR(255) DEFAULT NULL, diet VARCHAR(255) NOT NULL, origin VARCHAR(255) NOT NULL, localisation VARCHAR(255) NOT NULL, first_mention VARCHAR(255) DEFAULT NULL, created_at DATETIME NOT NULL, updated_at DATETIME DEFAULT NULL, is_valid TINYINT(1) NOT NULL, is_visible TINYINT(1) NOT NULL, related_creatures LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', other_names LONGTEXT DEFAULT NULL COMMENT \'(DC2Type:json)\', slug VARCHAR(255) NOT NULL, INDEX IDX_2A6C6AF4A76ED395 (user_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE creature_type (creature_id INT NOT NULL, type_id INT NOT NULL, INDEX IDX_167238A4F9AB048 (creature_id), INDEX IDX_167238A4C54C8C93 (type_id), PRIMARY KEY(creature_id, type_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE creature_habitat (creature_id INT NOT NULL, habitat_id INT NOT NULL, INDEX IDX_EFDFA20AF9AB048 (creature_id), INDEX IDX_EFDFA20AAFFE2D26 (habitat_id), PRIMARY KEY(creature_id, habitat_id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE habitat (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE profile_picture (id INT AUTO_INCREMENT NOT NULL, title VARCHAR(255) NOT NULL, url VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE type (id INT AUTO_INCREMENT NOT NULL, name VARCHAR(255) NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE user (id INT AUTO_INCREMENT NOT NULL, picture_id INT DEFAULT NULL, email VARCHAR(255) NOT NULL, name VARCHAR(255) NOT NULL, password VARCHAR(255) NOT NULL, is_valid TINYINT(1) NOT NULL, token VARCHAR(255) DEFAULT NULL, role LONGTEXT NOT NULL COMMENT \'(DC2Type:json)\', created_at DATETIME NOT NULL, INDEX IDX_8D93D649EE45BDBF (picture_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('CREATE TABLE messenger_messages (id BIGINT AUTO_INCREMENT NOT NULL, body LONGTEXT NOT NULL, headers LONGTEXT NOT NULL, queue_name VARCHAR(190) NOT NULL, created_at DATETIME NOT NULL, available_at DATETIME NOT NULL, delivered_at DATETIME DEFAULT NULL, INDEX IDX_75EA56E0FB7336F0 (queue_name), INDEX IDX_75EA56E0E3BD61CE (available_at), INDEX IDX_75EA56E016BA31DB (delivered_at), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
        $this->addSql('ALTER TABLE creature ADD CONSTRAINT FK_2A6C6AF4A76ED395 FOREIGN KEY (user_id) REFERENCES user (id)');
        $this->addSql('ALTER TABLE creature_type ADD CONSTRAINT FK_167238A4F9AB048 FOREIGN KEY (creature_id) REFERENCES creature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creature_type ADD CONSTRAINT FK_167238A4C54C8C93 FOREIGN KEY (type_id) REFERENCES type (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creature_habitat ADD CONSTRAINT FK_EFDFA20AF9AB048 FOREIGN KEY (creature_id) REFERENCES creature (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE creature_habitat ADD CONSTRAINT FK_EFDFA20AAFFE2D26 FOREIGN KEY (habitat_id) REFERENCES habitat (id) ON DELETE CASCADE');
        $this->addSql('ALTER TABLE user ADD CONSTRAINT FK_8D93D649EE45BDBF FOREIGN KEY (picture_id) REFERENCES profile_picture (id)');
    }

    public function down(Schema $schema): void
    {
        // this down() migration is auto-generated, please modify it to your needs
        $this->addSql('ALTER TABLE creature DROP FOREIGN KEY FK_2A6C6AF4A76ED395');
        $this->addSql('ALTER TABLE creature_type DROP FOREIGN KEY FK_167238A4F9AB048');
        $this->addSql('ALTER TABLE creature_type DROP FOREIGN KEY FK_167238A4C54C8C93');
        $this->addSql('ALTER TABLE creature_habitat DROP FOREIGN KEY FK_EFDFA20AF9AB048');
        $this->addSql('ALTER TABLE creature_habitat DROP FOREIGN KEY FK_EFDFA20AAFFE2D26');
        $this->addSql('ALTER TABLE user DROP FOREIGN KEY FK_8D93D649EE45BDBF');
        $this->addSql('DROP TABLE creature');
        $this->addSql('DROP TABLE creature_type');
        $this->addSql('DROP TABLE creature_habitat');
        $this->addSql('DROP TABLE habitat');
        $this->addSql('DROP TABLE profile_picture');
        $this->addSql('DROP TABLE type');
        $this->addSql('DROP TABLE user');
        $this->addSql('DROP TABLE messenger_messages');
    }
}
