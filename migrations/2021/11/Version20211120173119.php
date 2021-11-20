<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211120173119 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Also store project scheme';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project ADD host_scheme VARCHAR(255) DEFAULT NULL AFTER host');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE project DROP host_scheme');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
