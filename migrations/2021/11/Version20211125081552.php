<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20211125081552 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Add performance index';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE INDEX IDX_7FE581058B8E8428 ON stored_event (created_at)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX IDX_7FE581058B8E8428 ON stored_event');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
