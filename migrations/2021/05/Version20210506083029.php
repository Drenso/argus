<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506083029 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Add project environments';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE project_environment (id INT AUTO_INCREMENT NOT NULL, project_id INT DEFAULT NULL, gitlab_id INT NOT NULL, name VARCHAR(255) NOT NULL, current_state VARCHAR(10) NOT NULL, last_event DATETIME DEFAULT NULL COMMENT \'(DC2Type:datetime_immutable)\', INDEX IDX_8EE929D9166D1F9C (project_id), PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
    $this->addSql('ALTER TABLE project_environment ADD CONSTRAINT FK_8EE929D9166D1F9C FOREIGN KEY (project_id) REFERENCES project (id)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE project_environment');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
