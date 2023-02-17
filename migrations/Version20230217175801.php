<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds User roles to database.
 */
final class Version20230217175801 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User roles.';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users ADD roles JSON NOT NULL');
    }
    public function down(Schema $schema): void
    {
        $this->addSql('ALTER TABLE users DROP roles');
    }
}
