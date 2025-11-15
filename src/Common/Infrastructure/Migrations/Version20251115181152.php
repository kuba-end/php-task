<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115181152 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create department table';
    }

    public function up(Schema $schema): void
    {
        $this->addSql('
            CREATE TABLE department (
                id BINARY(16) NOT NULL,
                name VARCHAR(255) NOT NULL,
                bonus_type VARCHAR(20) NOT NULL,
                bonus_value INT NOT NULL,
                PRIMARY KEY(id)
            )
        ');
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE department');
    }
}
