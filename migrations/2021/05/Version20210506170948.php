<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20210506170948 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Drop gitlab id for environment, add unique index on project/name combination';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project_environment DROP gitlab_id');
    $this->addSql('CREATE UNIQUE INDEX UNIQ_8EE929D9166D1F9C5E237E06 ON project_environment (project_id, name)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX UNIQ_8EE929D9166D1F9C5E237E06 ON project_environment');
    $this->addSql('ALTER TABLE project_environment ADD gitlab_id INT NOT NULL');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
