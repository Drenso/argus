<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20220404074537 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Bring back messenger queue indexes';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('ALTER TABLE _messenger_queue CHANGE queue_name queue_name VARCHAR(190) NOT NULL');
    $this->addSql('CREATE INDEX IDX_39F8CEBBFB7336F0 ON _messenger_queue (queue_name)');
    $this->addSql('CREATE INDEX IDX_39F8CEBBE3BD61CE ON _messenger_queue (available_at)');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP INDEX IDX_39F8CEBBFB7336F0 ON _messenger_queue');
    $this->addSql('DROP INDEX IDX_39F8CEBBE3BD61CE ON _messenger_queue');
    $this->addSql('ALTER TABLE _messenger_queue CHANGE queue_name queue_name VARCHAR(255) CHARACTER SET utf8mb4 NOT NULL COLLATE `utf8mb4_unicode_ci`');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
