<?php

declare(strict_types=1);

namespace App\Common\Infrastructure\Migrations;

use Doctrine\DBAL\Schema\Schema;
use Doctrine\Migrations\AbstractMigration;

/**
 * Auto-generated Migration: Please modify to your needs!
 */
final class Version20251115181354 extends AbstractMigration
{
    public function getDescription(): string
    {
        return 'Create employee table with UUIDv7 primary key and FK to department';
    }

    public function up(Schema $schema): void
    {
        $platform = $this->connection->getDatabasePlatform()->getName();

        if ('sqlite' === $platform) {
            $this->addSql('
                CREATE TABLE employee (
                    id BLOB NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    surname VARCHAR(255) NOT NULL,
                    remuneration_base INT NOT NULL,
                    years_of_work INT NOT NULL,
                    department_id BLOB NOT NULL,
                    PRIMARY KEY(id),
                    CONSTRAINT FK_EMPLOYEE_DEPT FOREIGN KEY (department_id)
                        REFERENCES department (id)
                        ON DELETE CASCADE
                )
            ');

            $this->addSql('
                CREATE INDEX IDX_EMPLOYEE_DEPT ON employee (department_id)
            ');
        } else {
            $this->addSql('
                CREATE TABLE employee (
                    id BINARY(16) NOT NULL,
                    name VARCHAR(255) NOT NULL,
                    surname VARCHAR(255) NOT NULL,
                    remuneration_base INT NOT NULL,
                    years_of_work INT NOT NULL,
                    department_id BINARY(16) NOT NULL,
                    PRIMARY KEY(id),
                    INDEX IDX_EMPLOYEE_DEPT (department_id),
                    CONSTRAINT FK_EMPLOYEE_DEPT FOREIGN KEY (department_id)
                        REFERENCES department (id)
                        ON DELETE CASCADE
                ) DEFAULT CHARACTER SET utf8mb4 COLLATE `utf8mb4_unicode_ci` ENGINE=InnoDB
            ');
        }
    }

    public function down(Schema $schema): void
    {
        $this->addSql('DROP TABLE employee');
    }
}
