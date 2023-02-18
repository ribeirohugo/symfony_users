<?php

declare(strict_types=1);

namespace DoctrineMigrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;
use Symfony\Bridge\Doctrine\Types\UuidType;

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

        $table->addColumn("external_id", UuidType::NAME);
        $table->addUniqueIndex(["external_id"]);
    }

    public function down(Schema $schema): void
    {
        $table = $schema->getTable("users");

        $table->dropColumn("external_id");
    }
}
