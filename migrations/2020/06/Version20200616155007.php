<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20200616155007 extends AbstractMigration
{
  public function getDescription(): string
  {
    return 'Add StoredEvent entity';
  }

  public function up(Schema $schema): void
  {
    // this up() migration is auto-generated, please modify it to your needs
    $this->addSql('CREATE TABLE stored_event (id INT AUTO_INCREMENT NOT NULL, direction VARCHAR(20) NOT NULL, handled TINYINT(1) NOT NULL, fully_handled TINYINT(1) NOT NULL, event_name VARCHAR(255) NOT NULL, payload LONGTEXT NOT NULL, PRIMARY KEY(id)) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE = InnoDB');
  }

  public function down(Schema $schema): void
  {
    // this down() migration is auto-generated, please modify it to your needs
    $this->addSql('DROP TABLE stored_event');
  }

  public function isTransactional(): bool
  {
    return false;
  }
}
