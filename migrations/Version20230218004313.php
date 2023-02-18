<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Add Users external Id.
 */
final class Version20230218004313 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Add Users external Id.';
    }

    public function up(Schema $schema): void
    {
        $table = $schema->getTable("users");

        $table->addColumn("externalId", "string")->setNotnull(true);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("users");

        $table->dropColumn("externalId");
    }
}
