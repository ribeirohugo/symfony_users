<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Adds User entity table
 */
final class Version20230206223110 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add User entity table';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->createTable("users");

        $table->addColumn("id", "integer")->setLength(16)->setNotnull(true);
        $table->addColumn("name", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("email", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("password", "string")->setLength(255)->setNotnull(true);
        $table->addColumn("phone", "string")->setLength(20);

        $table->addIndex(["id"]);
        $table->addUniqueIndex(["email"]);
    }

    public function down(Schema $schema): void
    {
        $schema->dropTable("users");
    }
}
